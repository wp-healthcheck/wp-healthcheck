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
if [[ -z $WP_VERSION ]]; then
    WP_VERSION="latest"
fi

if [[ "$TRAVIS" = true ]]; then
    phpenv config-rm xdebug.ini

    ci/install-wp-tests.sh wphealthcheck root "" "127.0.0.1" $WP_VERSION false
else
    ci/install-wp-tests.sh wphealthcheck root dBtpgSwWHy mysql $WP_VERSION true
fi

