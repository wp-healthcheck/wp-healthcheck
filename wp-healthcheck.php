<?php
/**
 * Plugin Name: WP Healthcheck
 * Plugin URI:  https://wp-healthcheck.com
 * Description: Checks the health of your WordPress install.
 * Version:     1.2
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
define( 'WPHC_VERSION', '1.2' );
define( 'WPHC_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'WPHC_PLUGIN_URL', plugins_url( '', __FILE__ ) );

define( 'WPHC_INC_DIR', WPHC_PLUGIN_DIR . '/includes' );

require_once WPHC_INC_DIR . '/class-wp-healthcheck-upgrade.php';

register_activation_hook( __FILE__, array( 'WP_Healthcheck', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'WP_Healthcheck', 'plugin_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'WP_Healthcheck', 'plugin_uninstall' ) );

require_once WPHC_INC_DIR . '/class-wp-healthcheck.php';

add_action( 'init', array( 'WP_Healthcheck', 'init' ) );

if ( is_admin() ) {
    require_once WPHC_INC_DIR . '/class-wp-healthcheck-admin.php';

    add_action( 'init', array( 'WP_Healthcheck_Admin', 'init' ) );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once WPHC_INC_DIR . '/class-wp-healthcheck-cli.php';
}
