#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="$ROOT_DIR/build/hostinger-release"
APP_DIR="$BUILD_DIR/elkm-app"
PUBLIC_DIR="$BUILD_DIR/public_html"

cd "$ROOT_DIR"

echo "Removing previous Hostinger release build..."
rm -rf "$BUILD_DIR"
mkdir -p "$APP_DIR" "$PUBLIC_DIR"

echo "Installing production Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

if [ -f package-lock.json ]; then
    echo "Installing NPM dependencies with npm ci..."
    npm ci
else
    echo "Installing NPM dependencies with npm install..."
    npm install
fi

echo "Building frontend assets..."
npm run build

echo "Copying Laravel application files..."
APP_ITEMS=(
    app
    bootstrap
    config
    database
    resources
    routes
    storage
    vendor
    artisan
    composer.json
    composer.lock
)

for item in "${APP_ITEMS[@]}"; do
    if [ -e "$ROOT_DIR/$item" ]; then
        cp -R "$ROOT_DIR/$item" "$APP_DIR/"
    fi
done

echo "Removing local-only release artifacts..."
rm -rf "$APP_DIR/storage/logs"
mkdir -p "$APP_DIR/storage/logs" "$APP_DIR/storage/framework/cache" "$APP_DIR/storage/framework/sessions" "$APP_DIR/storage/framework/views"
touch "$APP_DIR/storage/logs/.gitkeep"

echo "Copying Laravel public directory..."
cp -R "$ROOT_DIR/public/." "$PUBLIC_DIR/"
rm -rf "$PUBLIC_DIR/uploads"
mkdir -p "$PUBLIC_DIR/uploads"
touch "$PUBLIC_DIR/uploads/.gitkeep"

cat > "$PUBLIC_DIR/index.php" <<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../elkm-app/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../elkm-app/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../elkm-app/bootstrap/app.php';

$app->handleRequest(Request::capture());
PHP

echo "Hostinger release is ready at: $BUILD_DIR"
echo "Zip the contents of build/hostinger-release and upload it to the domain root that contains public_html."
echo "Do not upload local .env, node_modules, .git, tests, or credentials."
