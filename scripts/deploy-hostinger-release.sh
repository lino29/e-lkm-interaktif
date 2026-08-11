#!/usr/bin/env bash
set -euo pipefail

ARCHIVE_PATH="${1:-}"
EXPECTED_CHECKSUM="${2:-}"
RELEASE_ID="${3:-}"
DOMAIN_ROOT="${4:-}"
APP_URL="${5:-}"

fail() {
    echo "Deployment failed: $*" >&2
    exit 1
}

[[ -n "$ARCHIVE_PATH" ]] || fail "archive path is required"
[[ "$EXPECTED_CHECKSUM" =~ ^[a-f0-9]{64}$ ]] || fail "invalid archive checksum"
[[ "$RELEASE_ID" =~ ^[a-f0-9]{40}-[0-9]+-[0-9]+$ ]] || fail "invalid release identifier"
[[ "$DOMAIN_ROOT" == /* ]] || fail "domain root must be an absolute path"
[[ -d "$DOMAIN_ROOT" ]] || fail "domain root does not exist"
[[ "$APP_URL" =~ ^https://[^/]+$ ]] || fail "application URL must be an HTTPS origin"

DOMAIN_ROOT="$(cd "$DOMAIN_ROOT" && pwd -P)"
APP_HOST="${APP_URL#https://}"
[[ "$APP_HOST" =~ ^[A-Za-z0-9.-]+$ ]] || fail "application URL contains an invalid hostname"

APP_DOMAIN_ROOT="$HOME/domains/$APP_HOST"
if [[ ! -d "$DOMAIN_ROOT/public_html" ]] && [[ -d "$APP_DOMAIN_ROOT/public_html" ]]; then
    DOMAIN_ROOT="$(cd "$APP_DOMAIN_ROOT" && pwd -P)"
fi

RELEASES_DIR="$DOMAIN_ROOT/releases"
SHARED_DIR="$DOMAIN_ROOT/shared"
PUBLIC_DIR="$DOMAIN_ROOT/public_html"
LEGACY_APP_DIR="$DOMAIN_ROOT/e-lkm-interaktif"
CURRENT_LINK="$DOMAIN_ROOT/current"
RELEASE_DIR="$RELEASES_DIR/$RELEASE_ID"
SHARED_ENV="$SHARED_DIR/.env"
SHARED_STORAGE="$SHARED_DIR/storage"
SHARED_UPLOADS="$SHARED_DIR/uploads"

case "$ARCHIVE_PATH" in
    .elkm-deploy/incoming/*.tar.gz)
        ARCHIVE_PATH="$HOME/$ARCHIVE_PATH"
        ;;
    "$HOME/.elkm-deploy/incoming/"*.tar.gz) ;;
    *) fail "archive must be inside ~/.elkm-deploy/incoming" ;;
esac

[[ -f "$ARCHIVE_PATH" ]] || fail "deployment archive does not exist"
if [[ ! -d "$PUBLIC_DIR" ]]; then
    echo "Available public_html directories beneath the SSH home:" >&2
    find "$HOME" -mindepth 1 -maxdepth 5 -type d -name public_html -print >&2 || true
    fail "public_html directory does not exist beneath the configured domain root"
fi

mkdir -p "$RELEASES_DIR" "$SHARED_DIR"
exec 9>"$DOMAIN_ROOT/.deployment.lock"
flock -n 9 || fail "another deployment is already running"

ACTUAL_CHECKSUM="$(sha256sum "$ARCHIVE_PATH" | awk '{print $1}')"
[[ "$ACTUAL_CHECKSUM" == "$EXPECTED_CHECKSUM" ]] || fail "archive checksum mismatch"

if tar -tzf "$ARCHIVE_PATH" | grep -Eq '(^/|(^|/)\.\.(/|$))'; then
    fail "archive contains an unsafe path"
fi

ACTIVE_APP_DIR=""
PREVIOUS_TARGET=""

if [[ -L "$CURRENT_LINK" ]] && [[ -d "$(readlink -f "$CURRENT_LINK")" ]]; then
    PREVIOUS_TARGET="$(readlink -f "$CURRENT_LINK")"
    ACTIVE_APP_DIR="$PREVIOUS_TARGET"
elif [[ -d "$LEGACY_APP_DIR" ]]; then
    PREVIOUS_TARGET="$LEGACY_APP_DIR"
    ACTIVE_APP_DIR="$LEGACY_APP_DIR"
fi

[[ -n "$ACTIVE_APP_DIR" ]] || fail "no active Laravel application was found"
[[ -f "$ACTIVE_APP_DIR/artisan" ]] || fail "active application is missing artisan"
[[ -f "$ACTIVE_APP_DIR/.env" ]] || fail "active application is missing .env"

DEPLOYMENT_SUCCEEDED=false
CURRENT_SWITCHED=false

recover_deployment() {
    local exit_code=$?

    if [[ "$DEPLOYMENT_SUCCEEDED" == true ]]; then
        return
    fi

    echo "Deployment did not complete; restoring the previous application." >&2

    if [[ "$CURRENT_SWITCHED" == true ]] && [[ -d "$PREVIOUS_TARGET" ]]; then
        local rollback_link="$DOMAIN_ROOT/.current-rollback-$RELEASE_ID"
        ln -s "$PREVIOUS_TARGET" "$rollback_link"
        mv -Tf "$rollback_link" "$CURRENT_LINK"
    fi

    if [[ -f "$PREVIOUS_TARGET/artisan" ]]; then
        php "$PREVIOUS_TARGET/artisan" up --no-interaction >/dev/null 2>&1 || true
    fi

    exit "$exit_code"
}

trap recover_deployment EXIT

php "$ACTIVE_APP_DIR/artisan" down --retry=60 --refresh=15 --no-interaction

if [[ ! -f "$SHARED_ENV" ]]; then
    cp "$ACTIVE_APP_DIR/.env" "$SHARED_ENV"
fi
chmod 600 "$SHARED_ENV"

if [[ ! -e "$SHARED_STORAGE" ]]; then
    if [[ -d "$ACTIVE_APP_DIR/storage" ]] && [[ ! -L "$ACTIVE_APP_DIR/storage" ]]; then
        mv "$ACTIVE_APP_DIR/storage" "$SHARED_STORAGE"
        ln -s "$SHARED_STORAGE" "$ACTIVE_APP_DIR/storage"
    else
        mkdir -p "$SHARED_STORAGE"
    fi
fi

mkdir -p \
    "$SHARED_STORAGE/app/public" \
    "$SHARED_STORAGE/framework/cache/data" \
    "$SHARED_STORAGE/framework/sessions" \
    "$SHARED_STORAGE/framework/views" \
    "$SHARED_STORAGE/logs"

if [[ ! -e "$SHARED_UPLOADS" ]]; then
    if [[ -d "$ACTIVE_APP_DIR/public/uploads" ]] && [[ ! -L "$ACTIVE_APP_DIR/public/uploads" ]]; then
        mv "$ACTIVE_APP_DIR/public/uploads" "$SHARED_UPLOADS"
        ln -s "$SHARED_UPLOADS" "$ACTIVE_APP_DIR/public/uploads"
    else
        mkdir -p "$SHARED_UPLOADS"
    fi
fi

merge_and_link_directory() {
    local public_path="$1"
    local shared_path="$2"
    local backup_path="$public_path.pre-cicd-$RELEASE_ID"

    if [[ -L "$public_path" ]]; then
        rm -f "$public_path"
    elif [[ -d "$public_path" ]]; then
        rsync -a "$public_path/" "$shared_path/"
        mv "$public_path" "$backup_path"
    elif [[ -e "$public_path" ]]; then
        fail "$public_path exists and is not a directory or symlink"
    fi

    ln -s "$shared_path" "$public_path"
}

merge_and_link_directory "$PUBLIC_DIR/uploads" "$SHARED_UPLOADS"
merge_and_link_directory "$PUBLIC_DIR/storage" "$SHARED_STORAGE/app/public"

set_environment_value() {
    local key="$1"
    local value="$2"
    local temporary_file="$SHARED_DIR/.env.$RELEASE_ID.tmp"

    awk -v environment_key="$key" '
        index($0, environment_key "=") != 1 { print }
    ' "$SHARED_ENV" > "$temporary_file"
    printf '%s=%s\n' "$key" "$value" >> "$temporary_file"
    chmod 600 "$temporary_file"
    mv "$temporary_file" "$SHARED_ENV"
}

set_environment_value APP_ENV production
set_environment_value APP_DEBUG false
set_environment_value APP_URL "$APP_URL"
set_environment_value APP_LOCALE id
set_environment_value APP_FALLBACK_LOCALE id
set_environment_value LOG_LEVEL error
set_environment_value FILESYSTEM_DISK public_uploads
set_environment_value QUEUE_CONNECTION sync

[[ ! -e "$RELEASE_DIR" ]] || fail "release directory already exists"
mkdir -p "$RELEASE_DIR"
tar -xzf "$ARCHIVE_PATH" -C "$RELEASE_DIR"

[[ -f "$RELEASE_DIR/artisan" ]] || fail "release is missing artisan"
[[ -f "$RELEASE_DIR/vendor/autoload.php" ]] || fail "release is missing Composer dependencies"
[[ -f "$RELEASE_DIR/public/build/manifest.json" ]] || fail "release is missing Vite assets"
[[ -f "$RELEASE_DIR/resources/templates/contoh_template_import.xlsx" ]] || fail "release is missing the question import template"

rm -rf "$RELEASE_DIR/storage" "$RELEASE_DIR/public/uploads" "$RELEASE_DIR/public/storage"
ln -s "$SHARED_STORAGE" "$RELEASE_DIR/storage"
ln -s "$SHARED_UPLOADS" "$RELEASE_DIR/public/uploads"
ln -s "$SHARED_STORAGE/app/public" "$RELEASE_DIR/public/storage"
ln -s "$SHARED_ENV" "$RELEASE_DIR/.env"

mkdir -p "$RELEASE_DIR/bootstrap/cache"
chmod -R u+rwX "$RELEASE_DIR/bootstrap/cache" "$SHARED_STORAGE" "$SHARED_UPLOADS"

cd "$RELEASE_DIR"
composer check-platform-reqs --no-dev
php artisan optimize:clear --no-interaction
php artisan migrate --force --no-interaction
php artisan optimize --no-interaction
php artisan up --no-interaction

mkdir -p "$PUBLIC_DIR/build"
rsync -a --delete "$RELEASE_DIR/public/build/" "$PUBLIC_DIR/build/"
rsync -a \
    --exclude=index.php \
    --exclude=build \
    --exclude=uploads \
    --exclude=storage \
    "$RELEASE_DIR/public/" "$PUBLIC_DIR/"

TEMPORARY_INDEX="$PUBLIC_DIR/.index.php.$RELEASE_ID"
cat > "$TEMPORARY_INDEX" <<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../current/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../current/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../current/bootstrap/app.php';

$app->handleRequest(Request::capture());
PHP

TEMPORARY_CURRENT="$DOMAIN_ROOT/.current-$RELEASE_ID"
ln -s "$RELEASE_DIR" "$TEMPORARY_CURRENT"
mv -Tf "$TEMPORARY_CURRENT" "$CURRENT_LINK"
CURRENT_SWITCHED=true
mv -f "$TEMPORARY_INDEX" "$PUBLIC_DIR/index.php"

curl --fail --silent --show-error --max-time 30 "$APP_URL/up" >/dev/null

DEPLOYMENT_SUCCEEDED=true
trap - EXIT

rm -f "$ARCHIVE_PATH"

OLD_RELEASE_LIST="$DOMAIN_ROOT/.old-releases-$RELEASE_ID"
find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %f\n' \
    | sort -rn \
    | awk 'NR > 3 {print $2}' > "$OLD_RELEASE_LIST"

while IFS= read -r old_release; do
    [[ "$old_release" =~ ^[a-f0-9]{40}-[0-9]+-[0-9]+$ ]] || continue
    old_release_path="$RELEASES_DIR/$old_release"
    [[ "$old_release_path" != "$(readlink -f "$CURRENT_LINK")" ]] || continue
    rm -rf "$old_release_path"
done < "$OLD_RELEASE_LIST"

rm -f "$OLD_RELEASE_LIST"

echo "Deployment completed successfully: $RELEASE_ID"
