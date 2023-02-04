<?php
namespace WPHC\Core;

use DateTime;

/**
 * The Plugins class.
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class Plugins {
	/**
	 * Option to disable outdated plugins check.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	const DISABLE_OUTDATED_PLUGINS_OPTION = 'wphc_disable_outdated_plugins_check';

	/**
	 * Transient to store the outdated plugins.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	const OUTDATED_PLUGINS_TRANSIENT = 'wphc_plugins_outdated';

	/**
	 * Retrieves a list of plugins with no updates released on the
	 * last 2 years.
	 *
	 * @since 1.3.0
	 *
	 * @return array|false Slug and number of days since last update of the plugins or false if none.
	 */
	public static function get_outdated_plugins() {

		if ( get_option( self::DISABLE_OUTDATED_PLUGINS_OPTION ) ) {
			return false;
		}

		$outdated_plugins = get_transient( self::OUTDATED_PLUGINS_TRANSIENT );

		if ( false === $outdated_plugins ) {

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			$outdated_plugins = [];

			foreach ( get_plugins() as $file => $plugin ) {
				$slug = explode( '/', $file );
				$slug = preg_replace( '/\.php/', '', $slug[0] );

				$wp_api = plugins_api(
					'plugin_information',
					[
						'slug' => $slug,
					]
				);

				if ( empty( $wp_api->errors ) && ! empty( $wp_api->last_updated ) ) {
					$today       = new DateTime();
					$last_update = new DateTime( $wp_api->last_updated );

					$days = $today->diff( $last_update )->format( '%a' );

					if ( $days > 730 ) {
						$outdated_plugins[ $slug ] = $days;
					}
				}
			}

			set_transient( self::OUTDATED_PLUGINS_TRANSIENT, $outdated_plugins, WEEK_IN_SECONDS );
		}

		return $outdated_plugins;
	}
}
