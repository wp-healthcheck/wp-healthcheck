<?php
namespace WPHC\Utils;

/**
 * The Upgrade class.
 *
 * @package wp-healthcheck
 * @since 1.1
 */
class Upgrade {
	/**
	 * Option to store the current plugin version.
	 *
	 * @since 1.1
	 *
	 * @var string
	 */
	const PLUGIN_VERSION_OPTION = 'wphc_version';

	/**
	 * Clean up the transients if plugin is updated.
	 *
	 * @since 1.1
	 */
	public function maybe_upgrade_db() {

		$version = get_option( self::PLUGIN_VERSION_OPTION );

		if ( WPHC_VERSION !== $version ) {
			RegisterHooks::cleanup_options( true );

			update_option( self::PLUGIN_VERSION_OPTION, WPHC_VERSION );
		}
	}
}
