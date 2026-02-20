FROM arm64v8/php:8.5-apache-trixie

RUN apt-get update && apt-get install -y \
        # 開発用
        vim nano less \
    ;

# アプリケーションディレクトリ
ARG APP_DIR=/var/app
COPY ./ "${APP_DIR}/"

RUN \
    # php.ini追加
    mv "${APP_DIR}/docker/php.ini" "{$PHP_INI_DIR}/php.ini" && \
    # ドキュメントルートをpublicのシンボリックリンクにする
    rmdir /var/www/html && ln -s "${APP_DIR}/public" /var/www/html && \
    # パーミッション調整
    chown www-data:www-data /var/www/html && \
    # mod_rewrite有効化
    a2enmod rewrite && \
    ;

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["apache2-foreground"]
