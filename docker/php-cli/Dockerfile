FROM php:8.2.11-cli-alpine3.18

RUN apk add --no-cache \
        tzdata \
        linux-headers && \
    cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime && \
    echo "Europe/Moscow" >  /etc/timezone
RUN docker-php-ext-install \
    sockets \
    mysqli \
    pdo \
    pdo_mysql

ENV TZ Europe/Moscow
ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU.UTF-8
ENV LC_ALL ru_RU.UTF-8

COPY 99-php.ini /usr/local/etc/php/conf.d/99-php.ini

WORKDIR /app
ENV PATH "$PATH:/app"
ARG USERNAME=appuser
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} -S ${USERNAME} && adduser -u ${UID} -S ${USERNAME} -G ${USERNAME}

# supervisor
RUN apk add --no-cache \
        supervisor && \
    mkdir /etc/supervisord.d && \
    mkdir -p /var/log/supervisor && \
    echo -e "[program:kma-process]\n\
              directory=/app/\n\
              command=php process.php\n\
              user=${USERNAME}\n\
              environment=HOME="/home/${USERNAME}",USER="${USERNAME}"\n\
              autostart=true\n\
              autorestart=true\n\
              stdout_logfile=/app/runtime/logs/process.log\n\
              stderr_logfile=/app/runtime/logs/process.log.error\n\
            " >> /etc/supervisord.d/kma-process.conf && \
    echo -e "[program:kma-init]\n\
              directory=/app/\n\
              command=php init.php\n\
              user=${USERNAME}\n\
              environment=HOME="/home/${USERNAME}",USER="${USERNAME}"\n\
              autostart=true\n\
              autorestart=false\n\
              stdout_logfile=/app/runtime/logs/init.log\n\
              stderr_logfile=/app/runtime/logs/init.log.error\n\
            " >> /etc/supervisord.d/kma-init.conf
COPY supervisord.conf /etc/supervisord.conf

HEALTHCHECK --interval=5s --timeout=3s --retries=3 \
  CMD ["supervisorctl", "--configuration", "/etc/supervisord.conf", "status" , "kma-process"]

ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]
