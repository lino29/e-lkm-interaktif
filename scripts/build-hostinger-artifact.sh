#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ARCHIVE_PATH="${1:-$ROOT_DIR/build/hostinger-release/application.tar.gz}"

if [[ "$ARCHIVE_PATH" != /* ]]; then
    ARCHIVE_PATH="$ROOT_DIR/$ARCHIVE_PATH"
fi

BUILD_ROOT="$ROOT_DIR/build/hostinger-release"
STAGING_DIR="$BUILD_ROOT/staging"
APPLICATION_DIR="$STAGING_DIR/application"

case "$ARCHIVE_PATH" in
    "$BUILD_ROOT"/*.tar.gz) ;;
    *)
        echo "Archive path must be a .tar.gz file inside $BUILD_ROOT" >&2
        exit 1
        ;;
esac

if [[ ! -f "$ROOT_DIR/vendor/autoload.php" ]]; then
    echo "Production Composer dependencies are missing." >&2
    exit 1
fi

if [[ ! -f "$ROOT_DIR/public/build/manifest.json" ]]; then
    echo "Vite production assets are missing." >&2
    exit 1
fi

mkdir -p "$BUILD_ROOT"
rm -rf "$STAGING_DIR"
mkdir -p "$APPLICATION_DIR"

git -C "$ROOT_DIR" archive --format=tar HEAD | tar -xf - -C "$APPLICATION_DIR"

rm -rf \
    "$APPLICATION_DIR/.agents" \
    "$APPLICATION_DIR/.claude" \
    "$APPLICATION_DIR/.codex" \
    "$APPLICATION_DIR/.github" \
    "$APPLICATION_DIR/.kiro" \
    "$APPLICATION_DIR/docs" \
    "$APPLICATION_DIR/scripts" \
    "$APPLICATION_DIR/tests"

if [[ ! -f "$APPLICATION_DIR/resources/templates/contoh_template_import.xlsx" ]]; then
    echo "Question import template is missing from the deployment artifact." >&2
    exit 1
fi

cp -a "$ROOT_DIR/vendor" "$APPLICATION_DIR/vendor"
mkdir -p "$APPLICATION_DIR/public/build"
cp -a "$ROOT_DIR/public/build/." "$APPLICATION_DIR/public/build/"

rm -rf \
    "$APPLICATION_DIR/public/storage" \
    "$APPLICATION_DIR/public/uploads" \
    "$APPLICATION_DIR/storage/logs" \
    "$APPLICATION_DIR/storage/framework/cache" \
    "$APPLICATION_DIR/storage/framework/sessions" \
    "$APPLICATION_DIR/storage/framework/views"

mkdir -p \
    "$APPLICATION_DIR/public/uploads" \
    "$APPLICATION_DIR/storage/app/public" \
    "$APPLICATION_DIR/storage/framework/cache/data" \
    "$APPLICATION_DIR/storage/framework/sessions" \
    "$APPLICATION_DIR/storage/framework/views" \
    "$APPLICATION_DIR/storage/logs"

tar -czf "$ARCHIVE_PATH" -C "$APPLICATION_DIR" .
rm -rf "$STAGING_DIR"

echo "Hostinger artifact created: $ARCHIVE_PATH"
