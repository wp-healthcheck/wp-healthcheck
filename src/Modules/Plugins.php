<?php
/**
 * Plugins module.
 *
 * Handles plugin-related checks and monitoring.
 *
 * @package wp-healthcheck
 * @since   {VERSION}
 */

namespace THSCD\WPHC\Modules;

use DateTime;

/**
 * Class Plugins.
 *
 * Manages plugin health checks.
 *
 * @since {VERSION}
 */
class Plugins {

	/**
	 * Option to disable outdated plugins check.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const DISABLE_OUTDATED_PLUGINS_OPTION = 'wphc_disable_outdated_plugins_check';

	/**
	 * Transient to store the outdated plugins.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const OUTDATED_PLUGINS_TRANSIENT = 'wphc_plugins_outdated';

	/**
	 * Number of days to consider a plugin outdated (2 years).
	 *
	 * @since {VERSION}
	 *
	 * @var int
	 */
	const OUTDATED_THRESHOLD_DAYS = 730;

	/**
	 * Retrieves a list of plugins with no updates released in the last 2 years.
	 *
	 * @since {VERSION}
	 *
	 * @return array|false Slug and number of days since last update of the plugins or false if none.
	 */
	public function get_outdated_plugins() {

		if ( get_option( self::DISABLE_OUTDATED_PLUGINS_OPTION ) ) {
			return false;
		}

		$outdated_plugins = get_transient( self::OUTDATED_PLUGINS_TRANSIENT );

		if ( $outdated_plugins !== false ) {
			return $outdated_plugins;
		}

		$this->load_plugin_dependencies();

		$outdated_plugins = $this->check_plugins_freshness();

		set_transient( self::OUTDATED_PLUGINS_TRANSIENT, $outdated_plugins, WEEK_IN_SECONDS );

		return $outdated_plugins;
	}

	/**
	 * Load required WordPress plugin dependencies.
	 *
	 * @since {VERSION}
	 */
	private function load_plugin_dependencies() {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}
	}

	/**
	 * Check all plugins for outdated status.
	 *
	 * @since {VERSION}
	 *
	 * @return array Array of outdated plugins with days since last update.
	 */
	private function check_plugins_freshness() {

		$outdated_plugins  = [];
		$installed_plugins = get_plugins();

		foreach ( $installed_plugins as $file => $plugin ) {
			$slug              = $this->extract_plugin_slug( $file );
			$days_since_update = $this->get_days_since_last_update( $slug );

			if ( $days_since_update && $days_since_update > self::OUTDATED_THRESHOLD_DAYS ) {
				$outdated_plugins[ $slug ] = $days_since_update;
			}
		}

		return $outdated_plugins;
	}

	/**
	 * Extract plugin slug from plugin file path.
	 *
	 * @since {VERSION}
	 *
	 * @param string $file Plugin file path.
	 *
	 * @return string Plugin slug.
	 */
	private function extract_plugin_slug( $file ) {

		$slug = dirname( $file );

		// If plugin is in root (single file plugin), use filename.
		if ( $slug === '.' ) {
			$slug = basename( $file, '.php' );
		}

		return $slug;
	}

	/**
	 * Get the number of days since a plugin was last updated.
	 *
	 * @since {VERSION}
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return int|false Number of days since last update, or false on error.
	 */
	private function get_days_since_last_update( $slug ) {

		$plugin_info = plugins_api(
			'plugin_information',
			[
				'slug'   => $slug,
				'fields' => [
					'last_updated' => true,
				],
			]
		);

		if ( is_wp_error( $plugin_info ) || empty( $plugin_info->last_updated ) ) {
			return false;
		}

		return $this->calculate_days_difference( $plugin_info->last_updated );
	}

	/**
	 * Calculate days between last update and today.
	 *
	 * @since {VERSION}
	 *
	 * @param string $last_updated_date Last updated date string.
	 *
	 * @return int Number of days.
	 */
	private function calculate_days_difference( $last_updated_date ) {

		$today       = new DateTime();
		$last_update = new DateTime( $last_updated_date );

		return (int) $today->diff( $last_update )->format( '%a' );
	}
}
