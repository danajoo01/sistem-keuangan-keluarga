#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

mkdir -p storage/app/public bootstrap/cache

php -r "
file_put_contents('.env', preg_replace([
    '/^APP_ENV=.*/m',
    '/^APP_DEBUG=.*/m',
    '/^APP_URL=.*/m',
    '/^DB_CONNECTION=.*/m',
    '/^#?DB_HOST=.*/m',
    '/^#?DB_PORT=.*/m',
    '/^#?DB_DATABASE=.*/m',
    '/^#?DB_USERNAME=.*/m',
    '/^#?DB_PASSWORD=.*/m',
    '/^SESSION_DRIVER=.*/m',
    '/^QUEUE_CONNECTION=.*/m',
    '/^CACHE_STORE=.*/m',
    '/^FILESYSTEM_DISK=.*/m',
    '/^MAIL_MAILER=.*/m'
], [
    'APP_ENV=' . getenv('APP_ENV'),
    'APP_DEBUG=' . getenv('APP_DEBUG'),
    'APP_URL=' . getenv('APP_URL'),
    'DB_CONNECTION=' . getenv('DB_CONNECTION'),
    'DB_HOST=' . getenv('DB_HOST'),
    'DB_PORT=' . getenv('DB_PORT'),
    'DB_DATABASE=' . getenv('DB_DATABASE'),
    'DB_USERNAME=' . getenv('DB_USERNAME'),
    'DB_PASSWORD=' . getenv('DB_PASSWORD'),
    'SESSION_DRIVER=' . getenv('SESSION_DRIVER'),
    'QUEUE_CONNECTION=' . getenv('QUEUE_CONNECTION'),
    'CACHE_STORE=' . getenv('CACHE_STORE'),
    'FILESYSTEM_DISK=' . getenv('FILESYSTEM_DISK'),
    'MAIL_MAILER=' . getenv('MAIL_MAILER')
], file_get_contents('.env')));
"

if ! grep -q '^APP_KEY=base64:' .env && [ -z "${APP_KEY:-}" ]; then
    php artisan key:generate --force --no-interaction
fi

php artisan optimize:clear || true
php artisan storage:link || true
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
