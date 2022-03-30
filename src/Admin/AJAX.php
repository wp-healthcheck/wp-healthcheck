<?php
namespace WPHC\Admin;

use WPHC\Core\WP_Healthcheck;

/**
 * AJAX class
 *
 * @package wp-healthcheck
 *
 * @since 1.0.0
 */
class AJAX {
	/**
	 * Stores all the AJAX hooks.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $ajax_actions = [];

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->add_ajax_actions();

		add_action( 'admin_footer', [ $this, 'add_wp_nonces' ] );
	}

	/**
	 * Add the AJAX hooks.
	 *
	 * @since 1.0.0
	 */
	public function add_ajax_actions() {
		$this->ajax_actions = [
			'autoload_deactivate',
			'autoload_history',
			'autoload_list',
			'autoload_reactivate',
			'hide_admin_notice',
			'transients_cleanup',
			'wp_auto_update',
		];

		foreach ( $this->ajax_actions as $action ) {
			add_action( 'wp_ajax_wphc_' . $action, [ $this, $action ] );
		}
	}

	/**
	 * Create a WP nonce for each hook.
	 *
	 * @since 1.0.0
	 */
	public function add_wp_nonces() {
		foreach ( $this->ajax_actions as $action ) {
			wp_nonce_field( 'wphc_' . $action, 'wphc_' . $action . '_wpnonce' );
		}
	}

	/**
	 * Determines if current request is WordPress AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if it's an WordPress AJAX request.
	 */
	public function is_doing_ajax() {
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Hook: deactivate an autoload option.
	 *
	 * @since 1.0.0
	 */
	public function autoload_deactivate() {
		check_ajax_referer( 'wphc_autoload_deactivate' );

		$options = array();

		foreach ( $_POST as $name => $value ) {
			if ( preg_match( '/^wphc-opt-/', $name ) ) {
				$option_name = preg_replace( '/^wphc-opt-/', '', urldecode( $name ) );

				$options[ $option_name ] = wphc()->main->deactivate_autoload_option( $option_name );
			}
		}

		include WPHC_PLUGIN_DIR . '/views/admin/autoload-list-status.php';

		wp_die();
	}

	/**
	 * Hook: list autoload options deactivated via WP Healthcheck.
	 *
	 * @since 1.0.0
	 */
	public function autoload_history() {
		check_ajax_referer( 'wphc_autoload_history' );

		include WPHC_PLUGIN_DIR . '/views/admin/autoload-history.php';

		wp_die();
	}

	/**
	 * Hook: list autoload options.
	 *
	 * @since 1.0.0
	 */
	public function autoload_list() {
		check_ajax_referer( 'wphc_autoload_list' );

		include WPHC_PLUGIN_DIR . '/views/admin/autoload-list.php';

		wp_die();
	}

	/**
	 * Hook: reactivate an autoload option.
	 *
	 * @since 1.1.0
	 */
	public function autoload_reactivate() {
		check_ajax_referer( 'wphc_autoload_reactivate' );

		$options = array();

		foreach ( $_POST as $name => $value ) {
			if ( preg_match( '/^wphc-hopt-/', $name ) ) {
				$option_name = preg_replace( '/^wphc-hopt-/', '', urldecode( $name ) );

				$options[ $option_name ] = wphc()->main->reactivate_autoload_option( $option_name );
			}
		}

		$reactivate = true;

		include WPHC_PLUGIN_DIR . '/views/admin/autoload-list-status.php';

		wp_die();
	}

	/**
	 * Hook: hide an admin notice.
	 *
	 * @since 1.0.0
	 */
	public function hide_admin_notice() {
		check_ajax_referer( 'wphc_hide_admin_notice' );

		if ( isset( $_POST['software'] ) && preg_match( '/(?:php|database|wordpress|web|ssl|https|plugins)/', $_POST['software'] ) ) {
			$notices_transient = get_transient( WP_Healthcheck::HIDE_NOTICES_TRANSIENT );

			if ( false === $notices_transient ) {
				$notices_transient = array();
			}

			$notices_transient[ trim( $_POST['software'] ) ] = 1;

			set_transient( WP_Healthcheck::HIDE_NOTICES_TRANSIENT, $notices_transient, DAY_IN_SECONDS );
		}

		wp_die();
	}

	/**
	 * Hook: clean up transients.
	 *
	 * @since 1.0.0
	 */
	public function transients_cleanup() {
		check_ajax_referer( 'wphc_transients_cleanup' );

		$cleanup = wphc()->main->cleanup_transients( isset( $_POST['expired'] ) );

		$object_cache = isset( $_POST['object_cache'] );

		include WPHC_PLUGIN_DIR . '/views/admin/transients-stats.php';

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
			wphc()->main->set_core_auto_update_option( $_POST['wp_auto_update'] );
		}

		wp_die();
	}
}
