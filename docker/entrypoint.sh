#!/bin/sh

# envファイル複合化
if [ -n "$LARAVEL_ENV_ENCRYPTION_KEY" ]; then
    echo "Decrypting environment file..."
    php artisan env:decrypt --key="$LARAVEL_ENV_ENCRYPTION_KEY" --force
fi

# 元のentrypointを実行
exec docker-php-entrypoint "$@"
