<?php
namespace WPHC\Core;

/**
 * The WordPress class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class WordPress {
	/**
	 * Option to store the auto update status.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	const CORE_AUTO_UPDATE_OPTION = 'wphc_auto_update_status';

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 1.0
	 */
	public function hooks() {
		add_action( 'wp_loaded', [ $this, 'check_core_updates' ] );
	}

	/**
	 * Check and apply WordPress core updates option.
	 *
	 * @since 1.3.0
	 */
	public function check_core_updates() {
		$core_auto_update_option = $this->get_core_auto_update_option();

		if ( $core_auto_update_option && preg_match( '/^(minor|major|dev|disabled)$/', $core_auto_update_option ) ) {
			if ( 'disabled' === $core_auto_update_option ) {
				add_filter( 'automatic_updater_disabled', '__return_true' );
			} else {
				add_filter( 'allow_' . $core_auto_update_option . '_auto_core_updates', '__return_true' );
			}
		}
	}

	/**
	 * Returns the wp-healthcheck auto update option value.
	 *
	 * @since 1.3.0
	 *
	 * @return string|bool It can assume 'disabled', 'minor', 'major', 'dev' or false.
	 */
	public function get_core_auto_update_option() {
		if ( $this->is_wp_auto_update_available() ) {
			return false;
		}

		$core_auto_update = get_option( self::CORE_AUTO_UPDATE_OPTION );

		return ( $core_auto_update ) ? $core_auto_update : 'minor';
	}

	/**
	 * Determines if WordPress auto update constants are enabled or not.
	 *
	 * @since 1.3.0
	 *
	 * @return boolean True if WordPress auto update constants are available.
	 */
	public function is_wp_auto_update_available() {
		return ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) || defined( 'WP_AUTO_UPDATE_CORE' ) );
	}

	/**
	 * Sets the wp-healthcheck auto update option value
	 * which could be 'disabled', 'minor', 'major' or 'dev'.
	 *
	 * @param string $option_value Auto update value.
	 *
	 * @since 1.3.0
	 */
	public function set_core_auto_update_option( $option_value ) {
		$core_auto_update_option = get_option( self::CORE_AUTO_UPDATE_OPTION );

		if ( $this->is_wp_auto_update_available() ) {
			if ( $core_auto_update_option ) {
				delete_option( self::CORE_AUTO_UPDATE_OPTION );
			}
		}

		update_option( self::CORE_AUTO_UPDATE_OPTION, $option_value );
	}
}
