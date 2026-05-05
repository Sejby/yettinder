#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    if [ "${APP_ENV:-prod}" = 'dev' ]; then
        # In dev mode always sync deps so newly added packages are available
        composer install --prefer-dist --no-progress --no-interaction
    elif [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
        composer install --prefer-dist --no-progress --no-interaction
    fi

    if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
        php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
    fi

    echo 'App ready!'
fi

exec docker-php-entrypoint "$@"
