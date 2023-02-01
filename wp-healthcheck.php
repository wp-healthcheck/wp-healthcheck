<?php
/**
 * Plugin Name: WP Healthcheck
 * Plugin URI:  https://wp-healthcheck.com
 * Description: Checks the health of your WordPress install.
 * Version:     1.4.0
 * Author:      Tiago Hillebrandt
 * Author URI:  https://wp-healthcheck.com/contributors
 * License:     GPL-3.0+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Text Domain: wp-healthcheck
 * Domain Path: /languages
 *
 * @package wp-healthcheck
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPHC', true );
define( 'WPHC_VERSION', '1.4.0' );
define( 'WPHC_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'WPHC_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'WPHC_INC_DIR', WPHC_PLUGIN_DIR . '/includes' );

if ( file_exists( WP_CONTENT_DIR . '/vendor/autoload.php' ) ) {
	require_once WP_CONTENT_DIR . '/vendor/autoload.php';
} else {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Plugin loader.
 *
 * @since 1.4.0
 *
 * @return \WPHC\Loader
 */
function wphc() {

	static $wphc;

	if ( ! $wphc ) {
		$wphc = new \WPHC\Loader();
	}

	return $wphc;
}

wphc();

/*register_activation_hook( __FILE__, [ 'WP_Healthcheck', 'plugin_activation' ] );
register_deactivation_hook( __FILE__, [ 'WP_Healthcheck', 'plugin_deactivation' ] );
register_uninstall_hook( __FILE__, [ 'WP_Healthcheck', 'plugin_uninstall' ] );*/
