ARG PHP_VERSION=8.3

FROM mlocati/php-extension-installer:latest AS ext-installer
FROM php:${PHP_VERSION}-fpm-alpine AS app

WORKDIR /var/www/html

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY --from=ext-installer /usr/bin/install-php-extensions /usr/bin/install-php-extensions

RUN install-php-extensions @composer pdo_mysql bcmath intl zlib opcache redis pcntl

EXPOSE 9000

FROM app AS dev

RUN install-php-extensions xdebug
COPY ./docker/php-fpm/start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

CMD ["start-container"]

FROM app AS k8s

RUN rm /usr/bin/install-php-extensions

RUN addgroup -g 2000 www \
    && adduser -u 2000 -G www -s /bin/sh -D www

COPY . /var/www/html
RUN chown -R www:www /var/www/html
USER www:www
RUN composer i --no-dev --no-interaction
RUN php ./vendor/symfony/intl/Resources/bin/compress
RUN php artisan optimize

CMD php-fpm
