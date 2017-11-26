#!/bin/bash

set -ex

plugin_dir=$(pwd)

# creates wp-config.php file for WP testing install
cd /tmp/wordpress
cp /tmp/wordpress-tests-lib/wp-tests-config.php ./wp-config.php

if egrep -qv "ABSPATH" wp-config.php; then
    echo "if ( !defined('ABSPATH') ) { define('ABSPATH', dirname(__FILE__) . '/'); }" >> wp-config.php
fi

echo "require_once(ABSPATH . 'wp-settings.php');" >> wp-config.php

# downloads WP-CLI
version=`curl -s https://api.github.com/repos/wp-cli/wp-cli/tags | egrep -o "v[0-9].[0-9]{1,}.[0-9]" | sort | uniq -d | tail -n1 | sed 's/^v//g'`
curl -sSL https://github.com/wp-cli/wp-cli/releases/download/v$version/wp-cli-$version.phar -o wp
chmod +x wp

[[ $(id -u) = 0 ]] && cmd="./wp --allow-root" || cmd="./wp"

# install WP on database
if [[ -z $TRAVIS ]]; then
    $cmd core install --url="wp-healthcheck.com" --title=wphc --admin_user=wphc --admin_email=nonexistentemail@wp-healthcheck.com --skip-email
fi

# install plugin on WP
mkdir -p /tmp/wordpress/wp-content/plugins/wp-healthcheck/
cp -R $plugin_dir/* /tmp/wordpress/wp-content/plugins/wp-healthcheck/ > /dev/null
$cmd plugin activate wp-healthcheck

# creates some options and transients
$cmd option add wphc_test "healthcheckclitest"
seq 1 5 | xargs -I {} $cmd transient set wphc_transient{} "$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w ${1:-100} | head -n 1)" 10
seq 6 10 | xargs -I {} $cmd transient set wphc_transient{} "$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w ${1:-100} | head -n 1)"

# runs WP-CLI extension commands
$cmd healthcheck autoload
$cmd healthcheck autoload --deactivate=wphc_test
$cmd healthcheck autoload --history

$cmd healthcheck server

$cmd healthcheck transient
$cmd healthcheck transient --delete-expired
$cmd healthcheck transient --delete-all

$cmd option delete wphc_test
