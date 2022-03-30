<?php
namespace WPHC\Admin;

use WPHC\Core\WP_Healthcheck;

/**
 * The Dashboard class.
 *
 * @package wp-healthcheck
 *
 * @since 1.0.0
 */
class Dashboard {
	/**
	 * Admin page hookname.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private static $hookname = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the WordPress hooks and AJAX.
	 *
	 * @since 2.0.0
	 */
	public function init() {
		if ( current_user_can( 'manage_options' ) ) {
			$this->init_ajax();
			$this->init_hooks();
		}
	}

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	public function init_hooks() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 5 );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'admin_init', [ $this, 'load_resources' ] );
	}

	/**
	 * Initialize the AJAX implementation.
	 *
	 * @since 1.0.0
	 */
	public function init_ajax() {
		add_action( 'admin_init', [ wphc()->ajax, 'init' ] );
		add_action( 'admin_init', [ wphc()->pointers, 'init' ], 4 );
	}

	/**
	 * Loads the admin resources.
	 *
	 * @since 1.0.0
	 */
	public function load_resources() {
		$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';

		wp_register_script( 'wp-healthcheck-js', WPHC_PLUGIN_URL . '/assets/wp-healthcheck' . $suffix . '.js', false, WPHC_VERSION );
		wp_register_style( 'wp-healthcheck-css', WPHC_PLUGIN_URL . '/assets/wp-healthcheck' . $suffix . '.css', false, WPHC_VERSION );

		wp_enqueue_script( 'wp-healthcheck-js' );
		wp_enqueue_style( 'wp-healthcheck-css' );

		wp_enqueue_script( 'postbox' );

		load_plugin_textdomain( 'wp-healthcheck', false, basename( WPHC_PLUGIN_DIR ) . '/languages/' );
	}

	/**
	 * Adds a menu page on WordPress Dashboard, loads the meta boxes
	 * and the tabs.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		self::$hookname = add_menu_page( 'WP Healthcheck', 'WP Healthcheck', 'manage_options', 'wp-healthcheck', [ $this, 'admin_page' ], 'none', 200 );

		add_action( 'load-' . self::$hookname, [ $this, 'admin_meta_boxes' ] );
		add_action( 'load-' . self::$hookname, [ $this, 'admin_tabs' ] );
	}

	/**
	 * Registers the meta boxes.
	 *
	 * @since 1.0.0
	 */
	public function admin_meta_boxes() {
		$metaboxes = [
			'wphc-transients' => __( 'Transients', 'wp-healthcheck' ),
			'wphc-autoload'   => __( 'Autoload Options', 'wp-healthcheck' ),
			'wphc-wp-updates' => __( 'WordPress Automatic Background Updates', 'wp-healthcheck' ),
			'wphc-support'    => __( 'Support &amp; Services', 'wp-healthcheck' ),
		];

		foreach ( $metaboxes as $id => $title ) {
			$args = [
				'name' => 'admin/' . preg_replace( '/^wphc-/', '', $id ),
			];

			add_meta_box( $id, $title, [ $this, 'view' ], self::$hookname, 'normal', 'default', $args );
		}
	}

	/**
	 * Loads the admin notices.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {
		if ( get_option( WP_Healthcheck::DISABLE_NOTICES_OPTION ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! preg_match( '/^(' . self::$hookname . '|dashboard)$/', $screen->id ) ) {
			return;
		}

		$notices = array( 'php', 'database', 'wordpress', 'web', 'ssl', 'https', 'plugins' );

		$notices_transient = get_transient( WP_Healthcheck::HIDE_NOTICES_TRANSIENT );

		foreach ( $notices as $notice ) {
			if ( ! isset( $notices_transient[ $notice ] ) ) {
				self::view( 'notices/' . $notice );
			}
		}
	}

	/**
	 * Loads the admin page view.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {
		self::view( 'admin/admin' );
	}

	/**
	 * Adds the help tab and hide the screen options one.
	 *
	 * @since 1.0.0
	 */
	public function admin_tabs() {
		include WPHC_PLUGIN_DIR . '/views/admin/help.php';

		$tabs = array(
			array(
				'id'      => 'wphc-help-transients',
				'title'   => $transients_help['title'],
				'content' => '<p>' . $transients_help['content'] . '</p>',
			),
			array(
				'id'      => 'wphc-help-autoload',
				'title'   => $autoload_help['title'],
				'content' => '<p>' . $autoload_help['content'] . '</p>',
			),
		);

		foreach ( $tabs as $tab ) {
			get_current_screen()->add_help_tab( $tab );
		}

		add_filter( 'screen_options_show_screen', '__return_false' );
	}

	/**
	 * Outputs the meta boxes.
	 *
	 * @since 1.0.0
	 */
	public function do_meta_boxes() {
		do_meta_boxes( self::$hookname, 'normal', null );
	}

	/**
	 * Includes a view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name of the view to load.
	 * @param string $metabox The metabox data.
	 */
	public function view( $name, $metabox = null ) {
		if ( isset( $metabox['args']['name'] ) ) {
			$name = $metabox['args']['name'];
		}

		$file = WPHC_PLUGIN_DIR . '/views/' . $name . '.php';

		if ( file_exists( $file ) ) {
			include $file;
		}
	}
}
