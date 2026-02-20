FROM arm64v8/php:8.5-apache-trixie

#RUN apt-get update && apt-get install -y \
#        # 開発用
#        vim nano less \
#    ;

# アプリケーションディレクトリ
ARG APP_DIR=/var/app
COPY ./ "${APP_DIR}/"

RUN cd "${APP_DIR}/"
    # アプリケーションディレクトリのパーミッション調整
RUN chown -r www-data:www-data "${APP_DIR}/"
    # ドキュメントルートをpublicのシンボリックリンクにする
RUN rmdir /var/www/html 
RUN ln -s "${APP_DIR}/public" /var/www/html
    # php.ini追加
RUN mv "${APP_DIR}/docker/php.ini" "{$PHP_INI_DIR}/php.ini"
    # mod_rewrite有効化
RUN a2enmod rewrite

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["apache2-foreground"]
