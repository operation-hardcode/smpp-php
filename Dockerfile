FROM php:8.1-alpine

RUN set -xe \
    && apk update \
    && apk add oniguruma-dev libevent-dev autoconf zlib-dev g++ libtool make libzip-dev git gmp-dev \
    && docker-php-ext-install \
        gmp \
        pcntl \
        mbstring \
        sysvsem \
        zip \
        sockets \
        ## ext-buffer
        && git clone https://github.com/phpinnacle/ext-buffer.git \
        && cd ext-buffer \
        && phpize \
        && ./configure \
        && make \
        && make install \
        && echo "extension=buffer.so" > /usr/local/etc/php/conf.d/buffer.ini \
#        && git clone https://github.com/amphp/ext-uv.git \
#        && cd ext-uv \
#        && phpize \
#        && ./configure \
#        && make \
#        && make install \
#        && echo "extension=uv.so" > /usr/local/etc/php/conf.d/uv.ini \
        && curl -L -o /usr/local/bin/pickle https://github.com/FriendsOfPHP/pickle/releases/latest/download/pickle.phar && chmod +x /usr/local/bin/pickle \
        ## event extension
        && pickle install event \
        && docker-php-ext-enable event \
        && mv /usr/local/etc/php/conf.d/docker-php-ext-event.ini /usr/local/etc/php/conf.d/docker-php-ext-zz-event.ini \
        ## raphf
        && pickle install raphf \
        && docker-php-ext-enable raphf \
        && rm -rf /tmp/* /var/cache/apk/* /usr/local/bin/pickle \
        && apk del autoconf g++ make git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.1.8 \
   && chmod +x /usr/local/bin/composer \
   && composer clear-cache

WORKDIR /var/www