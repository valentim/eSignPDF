#!/bin/sh


if [ -f /var/www/.env ]; then
  export $(grep -v '^#' /var/www/.env | xargs)
fi

exec "$@"