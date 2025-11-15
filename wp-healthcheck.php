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

/**
 * Loads the autoloader.
 *
 * @since {VERSION}
 */
if ( ! file_exists( WPHC_PLUGIN_DIR . '/vendor/autoload.php' ) ) {
	return;
}

require_once WPHC_PLUGIN_DIR . '/vendor/autoload.php';

/**
 * Get the available container instance or resolve a service.
 *
 * Similar to Laravel's app() helper.
 *
 * @since {VERSION}
 *
 * @param string|null $service Service name to resolve.
 *
 * @return mixed|\THSCD\WPHC\Core\Container
 */
function wphc( $service = null ) {

	$container = \THSCD\WPHC\Core\Container::get_instance();

	if ( is_null( $service ) ) {
		return $container;
	}

	return $container->get( $service );
}

require_once WPHC_INC_DIR . '/class-wp-healthcheck-upgrade.php';
require_once WPHC_INC_DIR . '/class-wp-healthcheck.php';

register_activation_hook( __FILE__, array( 'WP_Healthcheck', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'WP_Healthcheck', 'plugin_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'WP_Healthcheck', 'plugin_uninstall' ) );

add_action( 'init', array( 'WP_Healthcheck', 'init' ) );

if ( is_admin() ) {
	require_once WPHC_INC_DIR . '/class-wp-healthcheck-admin.php';

	add_action( 'init', array( 'WP_Healthcheck_Admin', 'init' ) );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once WPHC_INC_DIR . '/class-wp-healthcheck-cli.php';
}
