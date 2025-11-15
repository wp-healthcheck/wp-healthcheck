<?php
$tests_dir = ( getenv( 'WPHC_TESTS_DIR' ) ) ? getenv( 'WPHC_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';

define( 'WPHC_VERSION', '1.0' );
define( 'WPHC_PLUGIN_DIR', dirname( __DIR__ ) );
define( 'WPHC_TESTS_DIR', $tests_dir );

require_once WPHC_TESTS_DIR . '/includes/functions.php';

require_once WPHC_PLUGIN_DIR . '/vendor/autoload.php';

require WPHC_TESTS_DIR . '/includes/bootstrap.php';
