#!/bin/sh

# envファイル複合化
if [ -n "${LARAVEL_ENV_ENCRYPTION_KEY}" ]; then
    echo "Decrypting environment file..."
    php artisan env:decrypt --key="${LARAVEL_ENV_ENCRYPTION_KEY}" --env="${APP_ENV}"
fi

# laravel最適化（env複合化後に実行しないとenvの内容が反映されない）
php artisan optimize

# ファイルのパーミッション調整
chown -R www-data:www-data ./

# 元のentrypointを実行
exec docker-php-entrypoint "$@"
