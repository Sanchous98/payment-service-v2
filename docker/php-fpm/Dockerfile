ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-fpm-alpine AS app

WORKDIR /var/www/html

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN --mount=type=bind,from=mlocati/php-extension-installer:latest,target=/usr/bin/install-php-extensions,src=/usr/bin/install-php-extensions \
    install-php-extensions @composer pdo_mysql bcmath intl zlib opcache redis pcntl

EXPOSE 9000

FROM app AS dev

RUN --mount=type=bind,from=mlocati/php-extension-installer:latest,target=/usr/bin/install-php-extensions,src=/usr/bin/install-php-extensions \
    install-php-extensions xdebug
COPY ./docker/php-fpm/start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

CMD ["start-container"]

FROM app AS k8s

RUN addgroup -g 2000 www \
    && adduser -u 2000 -G www -s /bin/sh -D www

COPY . /var/www/html
RUN chown -R www:www /var/www/html
USER www:www
RUN composer i --no-dev --no-interaction
RUN php ./vendor/symfony/intl/Resources/bin/compress
RUN php artisan vendor:publish --tag=laravel-assets --ansi --force
RUN php artisan optimize

CMD php-fpm
