<?php
/**
 * Dashboard page handler.
 *
 * Handles the dashboard page registration and rendering.
 *
 * @package wp-healthcheck
 * @since 1.4.1
 */

namespace THSCD\WPHC\Admin;

use THSCD\WPHC\Core\Hookable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Dashboard.
 *
 * Manages the dashboard page functionality.
 *
 * @since 1.4.1
 */
class Dashboard implements Hookable {

	/**
	 * Admin page hookname.
	 *
	 * @since 1.4.1
	 *
	 * @var string
	 */
	private $hookname = null;

	/**
	 * Register the WordPress hooks.
	 *
	 * @since 1.4.1
	 */
	public function hooks() {

		add_action( 'admin_menu', [ $this, 'register_menu' ], 5 );
		add_action( 'admin_init', [ $this, 'load_resources' ] );
	}

	/**
	 * Register the admin menu.
	 *
	 * @since 1.4.1
	 */
	public function register_menu() {

		$this->hookname = add_menu_page(
			'WP Healthcheck',
			'WP Healthcheck',
			'manage_options',
			'wp-healthcheck',
			[ $this, 'render_page' ],
			'none',
			200
		);

		add_action( 'load-' . $this->hookname, [ wphc( 'admin.metaboxes' ), 'register' ] );
		add_action( 'load-' . $this->hookname, [ $this, 'add_help_tabs' ] );
	}

	/**
	 * Load admin resources (scripts and styles).
	 *
	 * @since 1.4.1
	 */
	public function load_resources() {

		$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';

		wp_register_script(
			'wp-healthcheck-js',
			WPHC_PLUGIN_URL . '/assets/wp-healthcheck' . $suffix . '.js',
			false,
			WPHC_VERSION,
			false
		);

		wp_register_style(
			'wp-healthcheck-css',
			WPHC_PLUGIN_URL . '/assets/wp-healthcheck' . $suffix . '.css',
			false,
			WPHC_VERSION
		);

		wp_enqueue_script( 'wp-healthcheck-js' );
		wp_enqueue_style( 'wp-healthcheck-css' );
		wp_enqueue_script( 'postbox' );

		load_plugin_textdomain( 'wp-healthcheck', false, basename( WPHC_PLUGIN_DIR ) . '/languages/' );
	}

	/**
	 * Render the admin page.
	 *
	 * @since 1.4.1
	 */
	public function render_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-healthcheck' ) );
		}

		wphc( 'util.view' )->render( 'admin/pages/dashboard' );
	}

	/**
	 * Add help tabs to the admin page.
	 *
	 * @since 1.4.1
	 */
	public function add_help_tabs() {

		include WPHC_PLUGIN_DIR . '/views/admin/help.php';

		$tabs = [
			[
				'id'      => 'wphc-help-transients',
				'title'   => $transients_help['title'],
				'content' => '<p>' . $transients_help['content'] . '</p>',
			],
			[
				'id'      => 'wphc-help-autoload',
				'title'   => $autoload_help['title'],
				'content' => '<p>' . $autoload_help['content'] . '</p>',
			],
		];

		foreach ( $tabs as $tab ) {
			get_current_screen()->add_help_tab( $tab );
		}

		add_filter( 'screen_options_show_screen', '__return_false' );
	}

	/**
	 * Get the admin page hookname.
	 *
	 * @since 1.4.1
	 *
	 * @return string|null The hookname.
	 */
	public function get_hookname() {

		return $this->hookname;
	}
}
