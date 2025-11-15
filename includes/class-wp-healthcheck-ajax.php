<?php
/**
 * The WP_Healthcheck_AJAX class
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class WP_Healthcheck_AJAX {

	/**
	 * Stores all the AJAX hooks.
	 *
	 * @since 1.0
	 *
	 * @var array
	 */
	private static $ajax_actions = [];

	/**
	 * Whether to initiate the WordPress hooks.
	 *
	 * @since 1.0
	 *
	 * @var boolean
	 */
	private static $initiated = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public static function init() {

		if ( ! self::$initiated ) {
			self::init_hooks();
			self::add_ajax_actions();

			add_action( 'admin_footer', [ 'WP_Healthcheck_AJAX', 'add_wp_nonces' ] );
		}
	}

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 1.0
	 */
	public static function init_hooks() {

		self::$initiated = true;

		self::$ajax_actions = [
			'autoload_deactivate',
			'autoload_history',
			'autoload_list',
			'autoload_reactivate',
			'hide_admin_notice',
			'transients_cleanup',
			'wp_auto_update',
		];
	}

	/**
	 * Add the AJAX hooks.
	 *
	 * @since 1.0
	 */
	public static function add_ajax_actions() {

		foreach ( self::$ajax_actions as $action ) {
			add_action( 'wp_ajax_wphc_' . $action, [ 'WP_Healthcheck_AJAX', $action ] );
		}
	}

	/**
	 * Create a WP nonce for each hook.
	 *
	 * @since 1.0
	 */
	public static function add_wp_nonces() {

		foreach ( self::$ajax_actions as $action ) {
			wp_nonce_field( 'wphc_' . $action, 'wphc_' . $action . '_wpnonce' );
		}
	}

	/**
	 * Determines if current request is WordPress AJAX request.
	 *
	 * @since 1.0
	 *
	 * @return bool True if it's a WordPress AJAX request.
	 */
	public static function is_doing_ajax() {

		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Verify AJAX request nonce and user capabilities.
	 *
	 * @since {VERSION}
	 *
	 * @param string $action The action name for nonce verification.
	 */
	private static function verify_ajax_request( $action ) {

		// Verify nonce.
		check_ajax_referer( 'wphc_' . $action );

		// Verify user has manage_options capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions.' ], 403 );
		}
	}

	/**
	 * Hook: deactivate an autoload option.
	 *
	 * @since 1.0
	 */
	public static function autoload_deactivate() {

		self::verify_ajax_request( 'autoload_deactivate' );

		$options = [];

		foreach ( $_POST as $name => $value ) {
			if ( preg_match( '/^wphc-opt-/', sanitize_text_field( $name ) ) ) {
				$option_name = preg_replace( '/^wphc-opt-/', '', urldecode( $name ) );

				$options[ $option_name ] = wphc( 'autoload' )->deactivate( $option_name );
			}
		}

		include WPHC_PLUGIN_DIR . '/views/admin/autoload-list-status.php';

		wp_die();
	}

	/**
	 * Hook: list autoload options deactivated via WP Healthcheck.
	 *
	 * @since 1.0
	 */
	public static function autoload_history() {

		self::verify_ajax_request( 'autoload_history' );

		include WPHC_PLUGIN_DIR . '/views/admin/autoload-history.php';

		wp_die();
	}

	/**
	 * Hook: list autoload options.
	 *
	 * @since 1.0
	 */
	public static function autoload_list() {

		self::verify_ajax_request( 'autoload_list' );

		include WPHC_PLUGIN_DIR . '/views/admin/autoload-list.php';

		wp_die();
	}

	/**
	 * Hook: reactivate an autoload option.
	 *
	 * @since 1.1
	 */
	public static function autoload_reactivate() {

		self::verify_ajax_request( 'autoload_reactivate' );

		$options = [];

		foreach ( $_POST as $name => $value ) {
			$name = sanitize_text_field( $name );

			if ( preg_match( '/^wphc-hopt-/', $name ) ) {
				$option_name = preg_replace( '/^wphc-hopt-/', '', urldecode( $name ) );

				$options[ $option_name ] = wphc( 'autoload' )->reactivate( $option_name );
			}
		}

		$reactivate = true;

		include WPHC_PLUGIN_DIR . '/views/admin/autoload-list-status.php';

		wp_die();
	}

	/**
	 * Hook: hide an admin notice.
	 *
	 * @since 1.0
	 */
	public static function hide_admin_notice() {

		self::verify_ajax_request( 'hide_admin_notice' );

		if ( isset( $_POST['software'] ) && preg_match( '/(?:php|database|wordpress|web|ssl|https|plugins)/', sanitize_key( $_POST['software'] ) ) ) {
			$notices_transient = get_transient( WP_Healthcheck::HIDE_NOTICES_TRANSIENT );

			if ( false === $notices_transient ) {
				$notices_transient = [];
			}

			$notices_transient[ trim( sanitize_key( $_POST['software'] ) ) ] = 1;

			set_transient( WP_Healthcheck::HIDE_NOTICES_TRANSIENT, $notices_transient, DAY_IN_SECONDS );
		}

		wp_die();
	}

	/**
	 * Hook: clean up transients.
	 *
	 * @since 1.0
	 */
	public static function transients_cleanup() {

		self::verify_ajax_request( 'transients_cleanup' );

		$cleanup = wphc( 'transients' )->cleanup( isset( $_POST['expired'] ) );

		$object_cache = isset( $_POST['object_cache'] );

		include WPHC_PLUGIN_DIR . '/views/admin/transients-stats.php';

		wp_die();
	}

	/**
	 * Hook: set WordPress auto update option.
	 *
	 * @since 1.3.0
	 */
	public static function wp_auto_update() {

		self::verify_ajax_request( 'wp_auto_update' );

		if ( preg_match( '/^(?:minor|major|disabled|dev)$/', sanitize_key( $_POST['wp_auto_update'] ) ) ) {
			wphc( 'wordpress' )->set_auto_update_policy( sanitize_key( $_POST['wp_auto_update'] ) );
		}

		wp_die();
	}
}
