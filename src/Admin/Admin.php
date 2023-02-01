<?php
namespace WPHC\Admin;

/**
 * The Admin class
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Admin {
	/**
	 * Option to disable admin notices.
	 *
	 * @since 1.0
	 * @var string
	 */
	const DISABLE_NOTICES_OPTION = 'wphc_disable_admin_notices';

	/**
	 * Transient to store if an admin notice should be displayed or not.
	 *
	 * @since 1.0
	 * @var string
	 */
	const HIDE_NOTICES_TRANSIENT = 'wphc_hide_admin_notices';

	/**
	 * Admin page hookname.
	 *
	 * @since 1.0
	 * @var string
	 */
	private $hookname = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->hooks();
		$this->ajax();
	}

	/**
	 * WordPress actions and filters.
	 *
	 * @since 1.0
	 */
	public function hooks() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 5 );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'admin_init', [ $this, 'load_resources' ] );
	}

	/**
	 * Initialize the AJAX implementation.
	 *
	 * @since 1.0
	 */
	public function ajax() {
		new Pointers();
		//require_once WPHC_INC_DIR . '/class-wp-healthcheck-ajax.php';
		//require_once WPHC_INC_DIR . '/class-wp-healthcheck-pointers.php';

		//add_action( 'admin_init', array( 'WP_Healthcheck_AJAX', 'init' ) );
		//add_action( 'admin_init', array( 'WP_Healthcheck_Pointers', 'init' ), 4 );
	}

	/**
	 * Loads the admin resources.
	 *
	 * @since 1.0
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
	 * @since 1.0
	 */
	public function admin_menu() {
		$this->hookname = add_menu_page( 'WP Healthcheck', 'WP Healthcheck', 'manage_options', 'wp-healthcheck', [ $this, 'admin_page' ], 'none', 200 );

		add_action( 'load-' . $this->hookname, [ $this, 'admin_meta_boxes' ] );
		add_action( 'load-' . $this->hookname, [ $this, 'admin_tabs' ] );
	}

	/**
	 * Registers the meta boxes.
	 *
	 * @since 1.0
	 */
	public function admin_meta_boxes() {
		$metaboxes = array(
			'wphc-transients' => __( 'Transients', 'wp-healthcheck' ),
			'wphc-autoload'   => __( 'Autoload Options', 'wp-healthcheck' ),
			'wphc-wp-updates' => __( 'WordPress Automatic Background Updates', 'wp-healthcheck' ),
			'wphc-support'    => __( 'Support &amp; Services', 'wp-healthcheck' ),
		);

		foreach ( $metaboxes as $id => $title ) {
			$args = [
				'name' => 'admin/' . preg_replace( '/^wphc-/', '', $id ),
			];

			add_meta_box( $id, $title, [ $this, 'view' ], $this->hookname, 'normal', 'default', $args );
		}
	}

	/**
	 * Loads the admin notices.
	 *
	 * @since 1.0
	 */
	public function admin_notices() {
		if ( get_option( self::DISABLE_NOTICES_OPTION ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! preg_match( '/^(' . $this->hookname . '|dashboard)$/', $screen->id ) ) {
			return;
		}

		$notices = array( 'php', 'database', 'wordpress', 'web', 'ssl', 'https', 'plugins' );

		$notices_transient = get_transient( self::HIDE_NOTICES_TRANSIENT );

		foreach ( $notices as $notice ) {
			if ( ! isset( $notices_transient[ $notice ] ) ) {
				$this->view( 'notices/' . $notice );
			}
		}
	}

	/**
	 * Loads the admin page view.
	 *
	 * @since 1.0
	 */
	public function admin_page() {
		$this->view( 'admin/admin' );
	}

	/**
	 * Adds the help tab and hide the screen options one.
	 *
	 * @since 1.0
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
	 * @since 1.0
	 */
	public function do_meta_boxes() {
		do_meta_boxes( $this->hookname, 'normal', null );
	}

	/**
	 * Includes a view.
	 *
	 * @since 1.0
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
