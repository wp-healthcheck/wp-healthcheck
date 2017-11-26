#!/bin/bash

set -ex

# Update phpunit version for PHP 5.6
if php -v | grep -q "PHP 5.6"; then
    composer remove phpunit/phpunit --dev --no-update
    composer require phpunit/phpunit --dev --no-update
fi

# Download Composer dependencies
composer install -q

# Add extensions to PHP Code Sniffer
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs/,vendor/wimg/php-compatibility/

# Install WordPress test suite
wp_version="$1"

if [[ -z $wp_version ]]; then
    wp_version="latest"
fi

if [[ "$TRAVIS" = true ]]; then
    db_host="127.0.0.1"
    db_password="''"
    db_skip=false
else
    db_host="mysql"
    db_password="dBtpgSwWHy"
    db_skip=true
fi

ci/install-wp-tests.sh wphealthcheck root $db_password $db_host $wp_version $db_skip
