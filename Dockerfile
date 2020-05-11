FROM php:cli-alpine3.11

LABEL maintainer="panychek@gmail.com"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/mws

CMD ["/bin/sh"]

ENTRYPOINT ["/bin/sh", "-c"]