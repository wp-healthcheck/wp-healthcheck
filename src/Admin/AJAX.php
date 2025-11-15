<?php
/**
 * AJAX handler.
 *
 * Handles AJAX requests for the admin interface.
 *
 * @package wp-healthcheck
 * @since   {VERSION}
 */

namespace THSCD\WPHC\Admin;

/**
 * Class AJAX.
 *
 * Manages AJAX functionality.
 */
class AJAX {

	/**
	 * Stores all the AJAX hooks.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	private $ajax_actions = [];

	/**
	 * Whether hooks have been initialized.
	 *
	 * @since {VERSION}
	 *
	 * @var bool
	 */
	private $initiated = false;

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		$this->hooks();
		$this->add_ajax_actions();
	}

	/**
	 * Determines if current request is WordPress AJAX request.
	 *
	 * @since {VERSION}
	 *
	 * @return bool True if it's a WordPress AJAX request.
	 */
	public function is_doing_ajax() {

		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @since {VERSION}
	 */
	private function hooks() {

		if ( $this->initiated ) {
			return;
		}

		$this->initiated = true;

		add_action( 'admin_footer', [ $this, 'add_wp_nonces' ] );

		$this->ajax_actions = [
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
	 * Add AJAX action hooks.
	 *
	 * @since {VERSION}
	 */
	private function add_ajax_actions() {

		foreach ( $this->ajax_actions as $action ) {
			add_action( 'wp_ajax_wphc_' . $action, [ $this, $action ] );
		}
	}

	/**
	 * Create WP nonces for each AJAX action.
	 *
	 * @since {VERSION}
	 */
	public function add_wp_nonces() {

		foreach ( $this->ajax_actions as $action ) {
			wp_nonce_field( 'wphc_' . $action, 'wphc_' . $action . '_wpnonce' );
		}
	}

	/**
	 * Verify AJAX request nonce and user capabilities.
	 *
	 * @since {VERSION}
	 *
	 * @param string $action The action name for nonce verification.
	 */
	private function verify_ajax_request( $action ) {

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
	 * @since {VERSION}
	 */
	public function autoload_deactivate() {

		$this->verify_ajax_request( 'autoload_deactivate' );

		$options = [];

		foreach ( $_POST as $name => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( preg_match( '/^wphc-opt-/', sanitize_text_field( $name ) ) ) {
				$option_name = preg_replace( '/^wphc-opt-/', '', urldecode( $name ) );

				$options[ $option_name ] = wphc( 'module.autoload' )->deactivate( $option_name );
			}
		}

		wphc( 'util.view' )->render( 'admin/partials/autoload-list-status', compact( 'options' ) );

		wp_die();
	}

	/**
	 * Hook: list autoload options deactivated via WP Healthcheck.
	 *
	 * @since {VERSION}
	 */
	public function autoload_history() {

		$this->verify_ajax_request( 'autoload_history' );

		wphc( 'util.view' )->render( 'admin/partials/autoload-history' );

		wp_die();
	}

	/**
	 * Hook: list autoload options.
	 *
	 * @since {VERSION}
	 */
	public function autoload_list() {

		$this->verify_ajax_request( 'autoload_list' );

		wphc( 'util.view' )->render( 'admin/partials/autoload-list' );

		wp_die();
	}

	/**
	 * Hook: reactivate an autoload option.
	 *
	 * @since {VERSION}
	 */
	public function autoload_reactivate() {

		$this->verify_ajax_request( 'autoload_reactivate' );

		$options = [];

		foreach ( $_POST as $name => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$name = sanitize_text_field( $name );

			if ( preg_match( '/^wphc-hopt-/', $name ) ) {
				$option_name = preg_replace( '/^wphc-hopt-/', '', urldecode( $name ) );

				$options[ $option_name ] = wphc( 'module.autoload' )->reactivate( $option_name );
			}
		}

		$reactivate = true;

		wphc( 'util.view' )->render( 'admin/partials/autoload-list-status', compact( 'options', 'reactivate' ) );

		wp_die();
	}

	/**
	 * Hook: hide an admin notice.
	 *
	 * @since {VERSION}
	 */
	public function hide_admin_notice() {

		$this->verify_ajax_request( 'hide_admin_notice' );

		if ( isset( $_POST['software'] ) && preg_match( '/(?:php|database|wordpress|web|ssl|https|plugins)/', sanitize_key( $_POST['software'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$notices_transient = get_transient( Notices::HIDE_NOTICES_TRANSIENT );

			if ( false === $notices_transient ) {
				$notices_transient = [];
			}

			$notices_transient[ trim( sanitize_key( $_POST['software'] ) ) ] = 1; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			set_transient( Notices::HIDE_NOTICES_TRANSIENT, $notices_transient, DAY_IN_SECONDS );
		}

		wp_die();
	}

	/**
	 * Hook: clean up transients.
	 *
	 * @since {VERSION}
	 */
	public function transients_cleanup() {

		$this->verify_ajax_request( 'transients_cleanup' );

		$cleanup = wphc( 'module.transients' )->cleanup( isset( $_POST['expired'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$object_cache = isset( $_POST['object_cache'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		wphc( 'util.view' )->render( 'admin/partials/transients-stats', compact( 'cleanup', 'object_cache' ) );

		wp_die();
	}

	/**
	 * Hook: set WordPress auto update option.
	 *
	 * @since {VERSION}
	 */
	public function wp_auto_update() {

		$this->verify_ajax_request( 'wp_auto_update' );

		if ( preg_match( '/^(?:minor|major|disabled|dev)$/', sanitize_key( $_POST['wp_auto_update'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			wphc( 'module.wordpress' )->set_auto_update_policy( sanitize_key( $_POST['wp_auto_update'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		wp_die();
	}
}

