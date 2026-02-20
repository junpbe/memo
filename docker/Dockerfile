FROM arm64v8/php:8.5-apache-trixie

RUN apt-get update && apt-get install -y \
        vim nano less \ # 開発用
        ;

ARG APP_DIR=/var/app

COPY ./ "${APP_DIR}/"

RUN mv "${APP_DIR}/docker/php.ini" "{$PHP_INI_DIR}/php.ini" \ # php.ini追加
 && rmdir /var/www/html \ # いったんドキュメントルート削除
 && ln -s "${APP_DIR}/public" /var/www/html \ # ドキュメントルートをpublicのシンボリックリンクにする
 && chown www-data:www-data /var/www/html \ # パーミッション
 && a2enmod rewrite \ # mod_rewrite有効化 \
 ;

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["apache2-foreground"]
