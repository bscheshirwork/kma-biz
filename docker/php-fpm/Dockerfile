FROM php:8.2.11-fpm-alpine3.18
RUN apk --update add \
      libxml2-dev  \
      tzdata \
      linux-headers && \
    docker-php-ext-install \
      sockets  \
      mysqli \
      pdo \
      pdo_mysql && \
    rm -rf /tmp/* /var/cache/apk/*
WORKDIR /app
ENV PATH "$PATH:/app"
EXPOSE 9000

RUN if [ "${http_proxy}" != "" ]; then \
    pear config-set http_proxy ${http_proxy} \
    ; fi

ENV XDEBUG_VERSION 3.2.2
ARG XDEBUG_PORT=9003
RUN if [ "${XDEBUG_PORT}" -gt 0 ]; then \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug-$XDEBUG_VERSION \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps\
    && rm -r /tmp/pear/*\
    ; fi

# Convert PSR-0/4 autoloading to classmap to get a faster autoloader.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer global require --optimize-autoloader

#нам требуется локаль ru_RU.UTF-8 для корректной работы php функций (например pathinfo)
RUN apk add --no-cache tzdata
RUN cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime
RUN echo "Europe/Moscow" >  /etc/timezone

ENV TZ Europe/Moscow
ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU.UTF-8
ENV LC_ALL ru_RU.UTF-8

#настройки php - общие
COPY 99-php.ini /usr/local/etc/php/conf.d/99-php.ini

#настрока php-fpm pool для работы с веб-сервером
RUN rm /usr/local/etc/php-fpm.d/www.conf
COPY php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

#донастройка php-fpm pool - добавление имени сервиса в переменные среды при запуске каждого воркера
ARG PHP_SERVER_NAME=php-server-name
RUN echo "env[PHP_IDE_CONFIG]='serverName=$PHP_SERVER_NAME'" >> /usr/local/etc/php-fpm.d/www.conf
ENV PHP_IDE_CONFIG="serverName=$PHP_SERVER_NAME"

#настройки xdebug v3 для запуска с триггером в параметрах запроса. см.: https://xdebug.org/docs/upgrade_guide
ENV XDEBUG_MODE=debug
COPY 99-php-xdebug.ini /usr/local/etc/php/conf.d/99-php-xdebug.ini
RUN echo -e "\nxdebug.client_port=$XDEBUG_PORT" >> /usr/local/etc/php/conf.d/99-php-xdebug.ini

#дебаг версия php - настройка для вызова из консоли с включённым debug
#(аналог установки переменной среды export XDEBUG_TRIGGER="PHPSTORM")
COPY dphp /usr/bin/dphp
RUN chmod +x /usr/bin/dphp

#добавление пользователя appuser
ARG USERNAME=appuser
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} -S ${USERNAME} && adduser -u ${UID} -S ${USERNAME} -G ${USERNAME}

RUN chown ${USERNAME}:${USERNAME} /app /usr/local/bin/composer
USER ${UID}:${GID}

CMD  ["php-fpm", "-F", "-R", "-O"]