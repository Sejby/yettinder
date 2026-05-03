FROM dunglas/frankenphp:1-php8.4

WORKDIR /app

RUN install-php-extensions \
	@composer \
	pdo_sqlite \
	intl \
	opcache \
	zip

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link frankenphp/Caddyfile /etc/frankenphp/Caddyfile
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-autoloader --no-scripts --no-progress

COPY . .
RUN composer dump-autoload --classmap-authoritative --no-dev \
	&& composer run-script --no-dev post-install-cmd \
	&& chmod +x bin/console

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
