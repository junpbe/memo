# アプリケーションディレクトリ
ARG APP_DIR=/var/app

# --- Stage 1: Node.js でフロントエンドをビルド ---
FROM node:20-slim AS frontend-builder
ARG APP_DIR
WORKDIR "${APP_DIR}"
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Stage 2: PHP & Apache ---
FROM arm64v8/php:8.5-apache-trixie

# パッケージ追加
RUN apt-get update && apt-get install -y \
        # 開発用
        vim nano less \
 && docker-php-ext-install bcmath \
    ;

# composer追加
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG APP_DIR
WORKDIR "${APP_DIR}"

# composer依存関係のインストール（依存定義ファイルを先にコピーして、ソースコードが変わっても再インストールを防ぐ）
COPY composer.json composer.lock .
RUN composer install --no-scripts --no-autoloader --prefer-dist

# ソースコピー
COPY . .

# フロントエンドのビルド成果物 (public/build) をStage 1からコピー
COPY --from=frontend-builder /app/public/build ./public/build

RUN : \
    # composer最適化
 && composer dump-autoload --optimize \
    # laravel最適化
 && php artisan optimize \
    # ファイルのパーミッション調整
 && chown -R www-data:www-data ./ \
    # ドキュメントルートをpublicのシンボリックリンクにする
 && rmdir /var/www/html && ln -s ./public /var/www/html \
    # php.ini追加
 && mv ./docker/php.ini "${PHP_INI_DIR}/php.ini" \
    # mod_rewrite有効化
 && a2enmod rewrite \
    ;

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["apache2-foreground"]
