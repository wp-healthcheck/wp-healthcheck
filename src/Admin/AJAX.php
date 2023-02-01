<?php
namespace WPHC\Admin;

/**
 * The WP_Healthcheck_AJAX class
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class AJAX {
	/**
	 * Stores all the AJAX hooks.
	 *
	 * @since 1.0
	 *
	 * @var array
	 */
	private $ajax_actions;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->ajax_actions = [
			'autoload_deactivate',
			'autoload_history',
			'autoload_list',
			'autoload_reactivate',
			'hide_admin_notice',
			'transients_cleanup',
			'wp_auto_update',
		];

		$this->hooks();
	}

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 1.0
	 */
	public function hooks() {
		add_action( 'admin_init', [ $this, 'add_ajax_actions' ] );
		add_action( 'admin_footer', [ $this, 'add_wp_nonces' ] );
	}

	/**
	 * Add the AJAX hooks.
	 *
	 * @since 1.0
	 */
	public function add_ajax_actions() {
		foreach ( $this->ajax_actions as $action ) {
			add_action( 'wp_ajax_wphc_' . $action, [ $this, $action ] );
		}
	}

	/**
	 * Create a WP nonce for each hook.
	 *
	 * @since 1.0
	 */
	public function add_wp_nonces() {
		foreach ( $this->ajax_actions as $action ) {
			wp_nonce_field( 'wphc_' . $action, 'wphc_' . $action . '_wpnonce' );
		}
	}

	/**
	 * Determines if current request is WordPress AJAX request.
	 *
	 * @since 1.0
	 *
	 * @return True if it's an WordPress AJAX request.
	 */
	public function is_doing_ajax() {
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Hook: deactivate an autoload option.
	 *
	 * @since 1.0
	 */
	public function autoload_deactivate() {
		check_ajax_referer( 'wphc_autoload_deactivate' );

		$options = [];

		foreach ( $_POST as $name => $value ) {
			if ( preg_match( '/^wphc-opt-/', $name ) ) {
				$option_name = preg_replace( '/^wphc-opt-/', '', urldecode( $name ) );

				$options[ $option_name ] = wphc()->core()->options()->deactivate_autoload_option( $option_name );
			}
		}

		wphc_view(
			'admin/autoload-list-status',
			[
				'options' => $options,
			]
		);

		wp_die();
	}

	/**
	 * Hook: list autoload options deactivated via WP Healthcheck.
	 *
	 * @since 1.0
	 */
	public function autoload_history() {
		check_ajax_referer( 'wphc_autoload_history' );

		wphc_view( 'admin/autoload-history' );

		wp_die();
	}

	/**
	 * Hook: list autoload options.
	 *
	 * @since 1.0
	 */
	public function autoload_list() {
		check_ajax_referer( 'wphc_autoload_list' );

		wphc_view( 'admin/autoload-list' );

		wp_die();
	}

	/**
	 * Hook: reactivate an autoload option.
	 *
	 * @since 1.1
	 */
	public function autoload_reactivate() {
		check_ajax_referer( 'wphc_autoload_reactivate' );

		$options = [];

		foreach ( $_POST as $name => $value ) {
			if ( preg_match( '/^wphc-hopt-/', $name ) ) {
				$option_name = preg_replace( '/^wphc-hopt-/', '', urldecode( $name ) );

				$options[ $option_name ] = wphc()->core()->options()->reactivate_autoload_option( $option_name );
			}
		}

		wphc_view(
			'admin/autoload-list-status',
			[
				'options'    => $options,
				'reactivate' => true,
			]
		);

		wp_die();
	}

	/**
	 * Hook: hide an admin notice.
	 *
	 * @since 1.0
	 */
	public function hide_admin_notice() {
		check_ajax_referer( 'wphc_hide_admin_notice' );

		if ( isset( $_POST['software'] ) && preg_match( '/(?:php|database|wordpress|web|ssl|https|plugins)/', sanitize_key( $_POST['software'] ) ) ) {
			$notices_transient = get_transient( Dashboard::HIDE_NOTICES_TRANSIENT );

			if ( false === $notices_transient ) {
				$notices_transient = array();
			}

			$notices_transient[ trim( $_POST['software'] ) ] = 1;

			set_transient( Dashboard::HIDE_NOTICES_TRANSIENT, $notices_transient, DAY_IN_SECONDS );
		}

		wp_die();
	}

	/**
	 * Hook: clean up transients.
	 *
	 * @since 1.0
	 */
	public function transients_cleanup() {
		check_ajax_referer( 'wphc_transients_cleanup' );

		$cleanup = wphc()->core()->transients()->cleanup_transients( isset( $_POST['expired'] ) );

		$object_cache = isset( $_POST['object_cache'] );

		wphc_view(
			'admin/transients-stats',
			[
				'cleanup'      => $cleanup,
				'object_cache' => $object_cache,
			]
		);

		wp_die();
	}

	/**
	 * Hook: set WordPress auto update option.
	 *
	 * @since 1.3.0
	 */
	public function wp_auto_update() {
		check_ajax_referer( 'wphc_wp_auto_update' );

		if ( preg_match( '/^(?:minor|major|disabled|dev)$/', $_POST['wp_auto_update'] ) ) {
			wphc()->core()->wordpress()->set_core_auto_update_option( $_POST['wp_auto_update'] );
		}

		wp_die();
	}
}
