<?php
/**
 * Dashboard page handler.
 *
 * Handles the dashboard page registration and rendering.
 *
 * @package wp-healthcheck
 * @since   {VERSION}
 */

namespace THSCD\WPHC\Admin;

/**
 * Class Dashboard.
 *
 * Manages the dashboard page functionality.
 */
class Dashboard {

	/**
	 * Admin page hookname.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	private $hookname = null;

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

		if ( current_user_can( 'manage_options' ) ) {
			$this->hooks();
		}
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

		add_action( 'admin_menu', [ $this, 'register_menu' ], 5 );
		add_action( 'admin_init', [ $this, 'load_resources' ] );
	}

	/**
	 * Register the admin menu.
	 *
	 * @since {VERSION}
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
	 * @since {VERSION}
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
	 * @since {VERSION}
	 */
	public function render_page() {

		wphc( 'util.view' )->render( 'admin/pages/dashboard' );
	}

	/**
	 * Add help tabs to the admin page.
	 *
	 * @since {VERSION}
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
	 * @since {VERSION}
	 *
	 * @return string|null The hookname.
	 */
	public function get_hookname() {

		return $this->hookname;
	}
}
