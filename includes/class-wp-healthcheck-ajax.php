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
     * @var array
     */
    private static $ajax_actions = array();

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
        if ( ! self::$initiated ) {
            self::init_hooks();
            self::add_ajax_actions();

            add_action( 'admin_footer', array( 'WP_Healthcheck_AJAX', 'add_wp_nonces' ) );
        }
    }

    /**
     * Initialize the WordPress hooks.
     *
     * @since 1.0
     */
    public static function init_hooks() {
        self::$initiated = true;

        self::$ajax_actions = array(
            'autoload_deactivate',
            'autoload_history',
            'autoload_list',
            'autoload_reactivate',
            'hide_admin_notice',
            'transients_cleanup',
        );
    }

    /**
     * Add the AJAX hooks.
     *
     * @since 1.0
     */
    public static function add_ajax_actions() {
        foreach ( self::$ajax_actions as $action ) {
            add_action( 'wp_ajax_wphc_' . $action, array( 'WP_Healthcheck_AJAX', $action ) );
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
     * @return True if it's an WordPress AJAX request.
     */
    public static function is_doing_ajax() {
        return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
    }

    /**
     * Hook: deactivate an autoload option.
     *
     * @since 1.0
     */
    public static function autoload_deactivate() {
        check_ajax_referer( 'wphc_autoload_deactivate' );

        $options = array();

        foreach ( $_POST as $name => $value ) {
            if ( preg_match( '/^wphc-opt-/', $name ) ) {
                $option_name = preg_replace( '/^wphc-opt-/', '', urldecode( $name ) );

                $options[ $option_name ] = WP_Healthcheck::deactivate_autoload_option( $option_name );
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
        check_ajax_referer( 'wphc_autoload_history' );

        include WPHC_PLUGIN_DIR . '/views/admin/autoload-history.php';

        wp_die();
    }

    /**
     * Hook: list autoload options.
     *
     * @since 1.0
     */
    public static function autoload_list() {
        check_ajax_referer( 'wphc_autoload_list' );

        include WPHC_PLUGIN_DIR . '/views/admin/autoload-list.php';

        wp_die();
    }

    /**
     * Hook: reactivate an autoload option.
     *
     * @since 1.1
     */
    public static function autoload_reactivate() {
        check_ajax_referer( 'wphc_autoload_reactivate' );

        $options = array();

        foreach ( $_POST as $name => $value ) {
            if ( preg_match( '/^wphc-hopt-/', $name ) ) {
                $option_name = preg_replace( '/^wphc-hopt-/', '', urldecode( $name ) );

                $options[ $option_name ] = WP_Healthcheck::reactivate_autoload_option( $option_name );
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
        check_ajax_referer( 'wphc_hide_admin_notice' );

        if ( isset( $_POST['software'] ) && preg_match( '/(?:php|database|wordpress|web)/', $_POST['software'] ) ) {
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
     * @since 1.0
     */
    public static function transients_cleanup() {
        check_ajax_referer( 'wphc_transients_cleanup' );

        $cleanup = WP_Healthcheck::cleanup_transients( isset( $_POST['expired'] ) );

        $object_cache = isset( $_POST['object_cache'] );

        include WPHC_PLUGIN_DIR . '/views/admin/transients-stats.php';

        wp_die();
    }
}
