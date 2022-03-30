<?php
namespace WPHC\Core;

/**
 * The Upgrade class.
 *
 * @package wp-healthcheck
 *
 * @since 1.1.0
 */
class Upgrade {
	/**
	 * Option to store the current plugin version.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	const PLUGIN_VERSION_OPTION = 'wphc_version';

	/**
	 * Clean up the transients if plugin is updated.
	 *
	 * @since 1.1.0
	 */
	public function maybe_upgrade_db() {
		$version = get_option( self::PLUGIN_VERSION_OPTION );

		if ( WPHC_VERSION != $version ) {
			wphc()->main->cleanup_options( true );

			update_option( self::PLUGIN_VERSION_OPTION, WPHC_VERSION );
		}
	}
}
