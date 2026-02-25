# アプリケーションディレクトリ
ARG APP_DIR=/var/app

# --- ベースイメージ ---
FROM arm64v8/php:8.5-apache-trixie AS base

# パッケージ追加
RUN apt-get update && apt-get install -y \
        unzip \
        # 開発用
        vim nano less \
 && docker-php-ext-install bcmath zip \
    ;


# --- 依存関係インストール＆アセットビルド用中間ステージ ---
FROM base AS builder

ARG APP_DIR
WORKDIR "${APP_DIR}"

# composer追加
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.js追加（Debian Trixie用）
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
 && apt-get install -y nodejs

# composer依存関係のインストール（依存定義ファイルを先にコピーして、ソースコードが変わっても再インストールを防ぐ）
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# node依存関係のインストール（依存定義ファイルを先にコピーして、ソースコードが変わっても再インストールを防ぐ）
COPY package.json package-lock.json ./
RUN npm install

# ソースコピー
COPY . .

# アセットコンパイル
RUN : \
    # composer最適化
 && composer dump-autoload --optimize \
    # laravel最適化
 && php artisan optimize \
    # アセットコンパイル
 && npm run build \
    ;


# --- 最終的な実行用イメージ ---
FROM base

ARG APP_DIR
WORKDIR "${APP_DIR}"

# ビルド済みのアプリケーションディレクトリを丸ごとコピー
COPY --from=builder "${APP_DIR}" "${APP_DIR}"

RUN : \
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
