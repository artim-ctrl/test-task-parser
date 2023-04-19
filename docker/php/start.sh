#!/bin/sh

# Run composer install
composer install

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf