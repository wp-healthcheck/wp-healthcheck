<?php
namespace THSCD\WPHC\Utils;

/**
 * The Upgrade class.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
final class Upgrade {

	/**
	 * Option to store the current plugin version.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const PLUGIN_VERSION_OPTION = 'wphc_version';

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		$this->hooks();
		$this->maybe_upgrade_db();
	}

	/**
	 * WordPress actions and filters.
	 *
	 * @since {VERSION}
	 */
	public function hooks() {

		add_action( 'upgrader_process_complete', [ $this, 'upgrade_completed' ], 10, 2 );
	}

	/**
	 * Cleans up the transients after WordPress updates.
	 *
	 * @since {VERSION}
	 *
	 * @param WP_Upgrader $upgrader The WP_Upgrader instance.
	 * @param array       $options  The update data.
	 */
	public function upgrade_completed( $upgrader, $options ) {

		if ( $options['action'] === 'update' && $options['type'] === 'core' ) {
			Install::cleanup_plugin_options( true );
		}
	}

	/**
	 * Cleans up the transients after plugin updates.
	 *
	 * @since {VERSION}
	 */
	public function maybe_upgrade_db() {

		$version = get_option( self::PLUGIN_VERSION_OPTION );

		if ( $version !== WPHC_VERSION ) {
			Install::cleanup_plugin_options( true );

			update_option( self::PLUGIN_VERSION_OPTION, WPHC_VERSION );
		}
	}
}
