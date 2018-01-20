<?php
/**
 * The WP_Healthcheck_Admin class
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class WP_Healthcheck_Admin {
    /**
     * Admin page hookname.
     *
     * @since 1.0
     * @var string
     */
    private static $hookname = null;

    /**
     * Whether to initiate the WordPress hooks.
     *
     * @since 1.0
     * @var boolean
     */
    private static $initiated = false;

    /**
     * Constructor.
     *
     * @since 1.0
     */
    public static function init() {
        if ( current_user_can( 'manage_options' ) && ! self::$initiated ) {
            self::init_ajax();
            self::init_hooks();
        }
    }

    /**
     * Initialize the WordPress hooks.
     *
     * @since 1.0
     */
    public static function init_hooks() {
        self::$initiated = true;

        add_action( 'admin_menu', array( 'WP_Healthcheck_Admin', 'admin_menu' ), 5 );
        add_action( 'admin_notices', array( 'WP_Healthcheck_Admin', 'admin_notices' ) );
        add_action( 'admin_init', array( 'WP_Healthcheck_Admin', 'load_resources' ) );
    }

    /**
     * Initialize the AJAX implementation.
     *
     * @since 1.0
     */
    public static function init_ajax() {
        require_once WPHC_INC_DIR . '/class-wp-healthcheck-ajax.php';
        require_once WPHC_INC_DIR . '/class-wp-healthcheck-pointers.php';

        add_action( 'admin_init', array( 'WP_Healthcheck_AJAX', 'init' ) );
        add_action( 'admin_init', array( 'WP_Healthcheck_Pointers', 'init' ), 4 );
    }

    /**
     * Loads the admin resources.
     *
     * @since 1.0
     */
    public static function load_resources() {
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
    public static function admin_menu() {
        self::$hookname = add_menu_page( 'WP Healthcheck', 'WP Healthcheck', 'manage_options', 'wp-healthcheck', array( 'WP_Healthcheck_Admin', 'admin_page' ), 'none', 200 );

        add_action( 'load-' . self::$hookname, array( 'WP_Healthcheck_Admin', 'admin_meta_boxes' ) );
        add_action( 'load-' . self::$hookname, array( 'WP_Healthcheck_Admin', 'admin_tabs' ) );
    }

    /**
     * Registers the meta boxes.
     *
     * @since 1.0
     */
    public static function admin_meta_boxes() {
        $metaboxes = array(
            'wphc-transients' => __( 'Transients', 'wp-healthcheck' ),
            'wphc-autoload'   => __( 'Autoload Options', 'wp-healthcheck' ),
            'wphc-support'    => __( 'Support &amp; Services', 'wp-healthcheck' ),
        );

        foreach ( $metaboxes as $id => $title ) {
            $args = array(
                'name' => 'admin/' . preg_replace( '/^wphc-/', '', $id ),
            );

            add_meta_box( $id, $title, array( 'WP_Healthcheck_Admin', 'view' ), self::$hookname, 'normal', 'default', $args );
        }
    }

    /**
     * Loads the admin notices.
     *
     * @since 1.0
     */
    public static function admin_notices() {
        if ( get_option( WP_Healthcheck::DISABLE_NOTICES_OPTION ) ) {
            return;
        }

        $screen = get_current_screen();

        if ( ! preg_match( '/^(' . self::$hookname . '|dashboard)$/', $screen->id ) ) {
            return;
        }

        $notices = array( 'php', 'database', 'wordpress', 'web', 'ssl' );

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
     * @since 1.0
     */
    public static function admin_page() {
        self::view( 'admin/admin' );
    }

    /**
     * Adds the help tab and hide the screen options one.
     *
     * @since 1.0
     */
    public static function admin_tabs() {
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
    public static function do_meta_boxes() {
        do_meta_boxes( self::$hookname, 'normal', null );
    }

    /**
     * Includes a view.
     *
     * @since 1.0
     *
     * @param string $name The name of the view to load.
     * @param string $metabox The metabox data.
     */
    public static function view( $name, $metabox = null ) {
        if ( isset( $metabox['args']['name'] ) ) {
            $name = $metabox['args']['name'];
        }

        $file = WPHC_PLUGIN_DIR . '/views/' . $name . '.php';

        if ( file_exists( $file ) ) {
            include $file;
        }
    }
}
