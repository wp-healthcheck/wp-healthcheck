<?php
/**
 * The WP_Healthcheck class
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class WP_Healthcheck {
    /**
     * Option to store the history of disabled autoload options.
     *
     * @since 1.0
     * @var string
     */
    const DISABLE_AUTOLOAD_OPTION = 'wphc_disable_autoload_history';

    /**
     * Option to disable admin notices.
     *
     * @since 1.0
     * @var string
     */
    const DISABLE_NOTICES_OPTION = 'wphc_disable_admin_notices';

    /**
     * Option to store the auto update status.
     *
     * @since 1.3.0
     * @var string
     */
    const CORE_AUTO_UPDATE_OPTION = 'wphc_auto_update_status';

    /**
     * Option to disable outdated plugins check.
     *
     * @since 1.3.0
     * @var string
     */
    const DISABLE_OUTDATED_PLUGINS_OPTION = 'wphc_disable_outdated_plugins_check';

    /**
     * Transient to store if an admin notice should be displayed or not.
     *
     * @since 1.0
     * @var string
     */
    const HIDE_NOTICES_TRANSIENT = 'wphc_hide_admin_notices';

    /**
     * Transient to store the minimum requirements.
     *
     * @since 1.0
     * @var string
     */
    const MIN_REQUIREMENTS_TRANSIENT = 'wphc_min_requirements';

    /**
     * Transient to store the outdated plugins.
     *
     * @since 1.3.0
     * @var string
     */
    const OUTDATED_PLUGINS_TRANSIENT = 'wphc_plugins_outdated';

    /**
     * Transient to store the server data.
     *
     * @since 1.0
     * @var string
     */
    const SERVER_DATA_TRANSIENT = 'wphc_server_data';

    /**
     * Transient to store the SSL data.
     *
     * @since 1.2
     * @var string
     */
    const SSL_DATA_TRANSIENT = 'wphc_ssl_data';

    /**
     * Transient to store if SSL is available or not.
     *
     * @since 1.3.0
     * @var string
     */
    const SSL_AVAILABLE_TRANSIENT = 'wphc_ssl_available';

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
            WP_Healthcheck_Upgrade::maybe_upgrade_db();

            self::init_hooks();
        }

        add_action( 'wp_loaded', array( 'WP_Healthcheck', 'check_core_updates' ) );
    }

    /**
     * Initialize the WordPress hooks.
     *
     * @since 1.0
     */
    public static function init_hooks() {
        self::$initiated = true;

        add_action( 'upgrader_process_complete', array( 'WP_Healthcheck', 'plugin_deactivation' ) );
        add_action( 'shutdown', array( 'WP_Healthcheck', 'get_ssl_data' ) );
    }

    /**
     * Cleans up the WordPress transients, or flushes the object cache if
     * it is enabled.
     *
     * @since 1.0
     *
     * @param boolean $only_expired Only expired transients.
     *
     * @return int|false Number of affected rows or false on error.
     */
    public static function cleanup_transients( $only_expired = true ) {
        global $wpdb;

        if ( wp_using_ext_object_cache() ) {
            return wp_cache_flush();
        }

        if ( $only_expired ) {
            return $wpdb->query( $wpdb->prepare( "DELETE a, b FROM $wpdb->options a INNER JOIN $wpdb->options b ON b.option_name = REPLACE(a.option_name, '_timeout', '') WHERE
            a.option_name REGEXP '^(_site)?_transient_timeout' AND a.option_value < %s;", time() ) );
        }

        return $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name REGEXP '^_(site_)?transient';" );
    }

    /**
     * Deactivates an autoload option.
     *
     * @since 1.0
     *
     * @param string  $option_name The name of the option to disable.
     * @param boolean $logging Save deactivation to history.
     *
     * @return int|false Number of affected rows or false on error.
     */
    public static function deactivate_autoload_option( $option_name, $logging = true ) {
        return self::_update_autoload_option( $option_name, 'no', $logging );
    }

    /**
     * Reactivates an autoload option that was disabled previously.
     *
     * @since 1.1
     *
     * @param string $option_name The name of the option to disable.
     *
     * @return int|false Number of affected rows or false on error.
     */
    public static function reactivate_autoload_option( $option_name ) {
        return self::_update_autoload_option( $option_name, 'yes' );
    }

    /**
     * Returns the autoload options deactivated via WP Healthcheck.
     *
     * @since 1.0
     *
     * @return array|false Name and timestamp of the options or false if none.
     */
    public static function get_autoload_history() {
        $history = get_option( self::DISABLE_AUTOLOAD_OPTION );

        if ( $history ) {
            $updated = false;
            $expiration = strtotime( '-2 weeks' );

            foreach ( $history as $name => $timestamp ) {
                if ( ! get_option( $name ) || ( get_option( $name ) && $timestamp < $expiration ) ) {
                    unset( $history[ $name ] );

                    $updated = true;
                }
            }

            if ( $updated ) {
                update_option( self::DISABLE_AUTOLOAD_OPTION, $history );
            }

            $history = array_reverse( $history, true );
        }

        return $history;
    }

    /**
     * Returns the 10 biggest WordPress autoload options.
     *
     * @since 1.0
     *
     * @return array The name and size of the biggest autoload options.
     */
    public static function get_autoload_options() {
        global $wpdb;

        $options = array();

        $result = $wpdb->get_results( "SELECT option_name, ROUND(LENGTH(option_value) / POWER(1024,2), 3) AS size FROM $wpdb->options WHERE autoload = 'yes' AND option_name NOT REGEXP '^_(site_)?transient' ORDER BY size DESC LIMIT 0,10;" );

        foreach ( $result as $option ) {
            $options[ $option->option_name ] = (float) $option->size;
        }

        return $options;
    }

    /**
     * Returns the WordPress autoload options count and size.
     *
     * @since 1.0
     *
     * @return array Stats of the autoload options.
     */
    public static function get_autoload_stats() {
        global $wpdb;

        $result = $wpdb->get_row( "SELECT COUNT(*) AS count, SUM(LENGTH(option_value)) / POWER(1024,2) AS size FROM $wpdb->options WHERE autoload = 'yes' AND option_name NOT REGEXP '^_(site_)?transient';" );

        $count = (int) $result->count;
        $size = (float) $result->size;

        return array(
            'count' => $count,
            'size'  => $size,
        );
    }

    /**
     * Retrieves a list of plugins with no updates released on the
     * last 2 years.
     *
     * @since 1.3.0
     *
     * @return array|false Slug and number of days since last update
     * of the plugins or false if none.
     */
    public static function get_outdated_plugins() {
        if ( get_option( self::DISABLE_OUTDATED_PLUGINS_OPTION ) ) {
            return false;
        }

        $outdated_plugins = get_transient( self::OUTDATED_PLUGINS_TRANSIENT );

        if ( false === $outdated_plugins ) {
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }

            $outdated_plugins = array();

            foreach ( get_plugins() as $file => $plugin ) {
                $slug = explode( '/', $file );
                $slug = preg_replace( '/\.php/', '', $slug[0] );

                $wp_api = plugins_api( 'plugin_information', array(
                    'slug' => $slug,
                ) );

                if ( empty( $wp_api->errors ) && ! empty( $wp_api->last_updated ) ) {
                    $today = new DateTime();
                    $last_update = new DateTime( $wp_api->last_updated );

                    $days = $today->diff( $last_update )->format( '%a' );

                    if ( $days > 730 ) {
                        $outdated_plugins[ $slug ] = $days;
                    }
                }
            }

            set_transient( self::OUTDATED_PLUGINS_TRANSIENT, $outdated_plugins, WEEK_IN_SECONDS );
        }

        return $outdated_plugins;
    }

    /**
     * Retrieves the server data.
     *
     * @since 1.0
     *
     * @return array The server data.
     */
    public static function get_server_data() {
        global $wpdb;

        $server = get_transient( self::SERVER_DATA_TRANSIENT );

        if ( false === $server ) {
            include ABSPATH . WPINC . '/version.php';

            $php = preg_match( '/^(\d+\.){2}\d+/', phpversion(), $phpversion );

            $db_service = ( preg_match( '/MariaDB/', $wpdb->dbh->server_info ) ) ? 'MariaDB' : 'MySQL';
            $db_version = $wpdb->db_version();

            if ( 'MariaDB' == $db_service ) {
                $db_version = preg_replace( '/[^0-9.].*/', '', $wpdb->get_var( 'SELECT @@version;' ) );
            }

            $server = array(
                'database' => array(
                    'service' => $db_service,
                    'version' => $db_version,
                ),
                'php'      => $phpversion[0],
                'wp'       => $wp_version,
                'web'      => '',
            );

            if ( ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
                $matches = array();

                if ( preg_match( '/(apache|nginx)/i', $_SERVER['SERVER_SOFTWARE'], $matches ) ) {
                    $server['web']['service'] = strtolower( $matches[0] );

                    if ( preg_match( '/([0-9]{1,}\.){2}([0-9]{1,})?/', $_SERVER['SERVER_SOFTWARE'], $matches ) ) {
                        $server['web']['version'] = trim( $matches[0] );
                    } else {
                        $server['web']['version'] = '';
                    }
                } else {
                    $server['web'] = array(
                        'service' => 'Web',
                        'version' => $_SERVER['SERVER_SOFTWARE'],
                    );
                }
            }

            set_transient( self::SERVER_DATA_TRANSIENT, $server, DAY_IN_SECONDS );
        }

        return $server;
    }

    /**
     * Retrieves the server requirements from our API.
     *
     * @since 1.0
     *
     * @return array The server requirements.
     */
    public static function get_server_requirements() {
        $requirements = get_transient( self::MIN_REQUIREMENTS_TRANSIENT );

        if ( false === $requirements ) {
            $options = array(
                'timeout'    => 20,
                'user-agent' => 'WP Healthcheck/' . WPHC_VERSION,
            );

            $res = wp_remote_get( 'https://api.wp-healthcheck.com/v1/requirements', $options );

            if ( is_array( $res ) && 200 == wp_remote_retrieve_response_code( $res ) ) {
                $requirements = json_decode( $res['body'], true );

                set_transient( self::MIN_REQUIREMENTS_TRANSIENT, $requirements, WEEK_IN_SECONDS );
            } else {
                return false;
            }
        }

        return $requirements;
    }

    /**
     * Returns the system's username of the site owner.
     *
     * @since 1.0
     *
     * @return string Username of the site owner.
     */
    public static function get_site_owner() {
        $uid = fileowner( ABSPATH );

        $owner = ( is_numeric( $uid ) ) ? posix_getpwuid( $uid ) : null;

        $user = ( is_null( $owner ) || ! isset( $owner['name'] ) ) ? 'root' : $owner['name'];

        return $user;
    }

    /**
     * Retrieves some information from SSL certificate associated with site
     * url.
     *
     * @since 1.2
     *
     * @return array|false SSL data or false on error.
     */
    public static function get_ssl_data() {
        if ( ! is_ssl() && ( ! defined( 'WP_CLI' ) || ! WP_CLI ) ) {
            return false;
        }

        $ssl_data = get_transient( self::SSL_DATA_TRANSIENT );

        if ( false === $ssl_data ) {
            $context = stream_context_create( array(
                'ssl' => array(
                    'capture_peer_cert' => true,
                    'verify_peer'       => false,
                ),
            ) );

            $siteurl = parse_url( get_option( 'siteurl' ) );

            if ( empty( $siteurl['host'] ) ) {
                return false;
            }

            $socket = @stream_socket_client( 'ssl://' . $siteurl['host'] . ':443', $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged

            if ( ! $socket ) {
                set_transient( self::SSL_DATA_TRANSIENT, array(), DAY_IN_SECONDS );

                return false;
            }

            $params = stream_context_get_params( $socket );

            if ( ! empty( $params['options']['ssl']['peer_certificate'] ) ) {
                $certificate = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );

                $ssl_data = array(
                    'common_name' => $certificate['subject']['CN'],
                    'issuer'      => $certificate['issuer']['CN'],
                    'validity'    => array(
                        'from' => date( 'Y-m-d H:i:s', $certificate['validFrom_time_t'] ),
                        'to'   => date( 'Y-m-d H:i:s', $certificate['validTo_time_t'] ),
                    ),
                );

                set_transient( self::SSL_DATA_TRANSIENT, $ssl_data, DAY_IN_SECONDS );
            }
        }

        return $ssl_data;
    }

    /**
     * Returns the 10 biggest transients.
     *
     * @since 1.0
     *
     * @return array The name and size of the biggest transients.
     */
    public static function get_transients() {
        global $wpdb;

        $transients = array();

        $result = $wpdb->get_results( "SELECT option_name, ROUND(LENGTH(option_value) / POWER(1024,2), 3) AS size FROM $wpdb->options WHERE option_name REGEXP '^_(site_)?transient' ORDER BY size DESC LIMIT 0,10;" );

        foreach ( $result as $transient ) {
            $transients[ $transient->option_name ] = (float) $transient->size;
        }

        return $transients;
    }

    /**
     * Returns the WordPress transients count and size.
     *
     * @since 1.0
     *
     * @return array Stats of the transients.
     */
    public static function get_transients_stats() {
        global $wpdb;

        $result = $wpdb->get_row( "SELECT COUNT(*) AS count, SUM(LENGTH(option_value)) / POWER(1024,2) AS size FROM $wpdb->options WHERE option_name REGEXP '^_(site_)?transient';" );

        $count = (int) $result->count;
        $size = (float) $result->size;

        return array(
            'count' => $count,
            'size'  => $size,
        );
    }

    /**
     * Returns the wp-healthcheck auto update option value.
     *
     * @since 1.3.0
     *
     * @return string|bool It can assume 'disabled', 'minor', 'major', 'dev' or false.
     */
    public static function get_core_auto_update_option() {
        if ( self::is_wp_auto_update_disabled() ) {
            return false;
        }

        $core_auto_update = get_option( self::CORE_AUTO_UPDATE_OPTION );

        return  ( $core_auto_update ) ? $core_auto_update : 'minor';
    }

    /**
     * Sets the wp-healthcheck auto update option value
     * which could be 'disabled', 'minor', 'major' or 'dev'.
     *
     * @param string $option_value Auto update value.
     *
     * @since 1.3.0
     */
    public static function set_core_auto_update_option( $option_value ) {
        $core_auto_update_option = get_option( self::CORE_AUTO_UPDATE_OPTION );

        if ( self::is_wp_auto_update_disabled() ) {
            if ( $core_auto_update_option ) {
                delete_option( self::CORE_AUTO_UPDATE_OPTION );
            }
        }

        update_option( self::CORE_AUTO_UPDATE_OPTION, $option_value );
    }

    /**
     * Determine if an option is set to autoload or not.
     *
     * @since 1.0
     *
     * @param string $option_name The option name.
     *
     * @return boolean True if autoload is disabled.
     */
    public static function is_autoload_disabled( $option_name ) {
        global $wpdb;

        $autoload = $wpdb->get_var( $wpdb->prepare( "SELECT autoload FROM $wpdb->options WHERE option_name = %s;", $option_name ) );

        return ( 'no' == $autoload );
    }

    /**
     * Determine if an option is a WP core one or not.
     *
     * @since 1.0
     *
     * @param string $option_name The option name.
     *
     * @return boolean True if it is a WP core option.
     */
    public static function is_core_option( $option_name ) {
        $wp_opts_file = WPHC_INC_DIR . '/data/wp_options.json';

        if ( file_exists( $wp_opts_file ) ) {
            $wp_opts = json_decode( file_get_contents( $wp_opts_file ) );
        }

        return ( in_array( $option_name, $wp_opts ) );
    }

    /**
     * Determine if server software is up-to-date or not.
     *
     * @since 1.0
     *
     * @param string $software The name of the software.
     *
     * @return string|false The current status (updated, outdated, or obsolete) of the software or false on error.
     */
    public static function is_software_updated( $software ) {
        if ( ! preg_match( '/^(php|mysql|mariadb|wp|nginx|apache)$/', $software ) ) {
            return false;
        }

        $requirements = self::get_server_requirements();

        if ( ! $requirements ) {
            return false;
        }

        $server_data = self::get_server_data();

        if ( 'wp' == $software ) {
            $current_local = preg_replace( '/(\d{1,}\.\d{1,})(\.\d{1,})?/', '$1', $server_data['wp'] );

            foreach ( $requirements['wordpress'] as $version ) {
                if ( preg_match( '/^' . $current_local . '(\.\d{1,})?/', $version ) ) {
                    $current_live = $version;

                    break;
                }
            }

            if ( ! isset( $current_live ) ) {
                $current_live = $requirements['wordpress'][0];
            }

            $requirements[ $software ]['recommended'] = $current_live;

            $minimum_version = preg_replace( '/(\d{1,}\.\d{1,})(\.\d{1,})?/', '$1', end( $requirements['wordpress'] ) );
            $requirements[ $software ]['minimum'] = $minimum_version;
        }

        if ( preg_match( '/^(mysql|mariadb)$/', $software ) ) {
            $server_data[ $software ] = $server_data['database']['version'];
        }

        if ( preg_match( '/^(nginx|apache)$/', $software ) ) {
            $server_data[ $software ] = $server_data['web']['version'];
            $requirements[ $software ]['minimum'] = end( $requirements[ $software ]['versions'] );
        }

        if ( version_compare( $server_data[ $software ], $requirements[ $software ]['recommended'], '>=' ) ) {
            return 'updated';
        } elseif ( version_compare( $server_data[ $software ], $requirements[ $software ]['minimum'], '>=' ) ) {
            return 'outdated';
        } else {
            return 'obsolete';
        }
    }

    /**
     * Determine if a SSL certificate is available or not.
     *
     * @since 1.3.0
     *
     * @return boolean True if SSL is available.
     */
    public static function is_ssl_available() {
        if ( is_ssl() ) {
            return true;
        }

        $is_available = get_transient( self::SSL_AVAILABLE_TRANSIENT );

        if ( false === $is_available ) {
            $siteurl = parse_url( get_option( 'siteurl' ) );

            if ( empty( $siteurl['host'] ) ) {
                return false;
            }

            $socket = @fsockopen( 'ssl://' . $siteurl['host'], 443, $errno, $errstr, 20 ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged

            $is_available = ( false != $socket );

            set_transient( self::SSL_AVAILABLE_TRANSIENT, $is_available, DAY_IN_SECONDS );
        }

        return $is_available;
    }

    /**
     * Determines if a SSL certificate will expire soon.
     *
     * @since 1.2
     *
     * @return int|false Number of days until certificate expiration or false on error.
     */
    public static function is_ssl_expiring() {
        $ssl_data = get_transient( self::SSL_DATA_TRANSIENT );

        if ( false !== $ssl_data && ! empty( $ssl_data['validity']['to'] ) ) {
            $current = time();
            $expiration = strtotime( $ssl_data['validity']['to'] );

            $diff = intval( floor( $expiration - $current ) / DAY_IN_SECONDS );

            return ( ( $diff <= 15 ) ? $diff : false );
        }

        return false;
    }

    /**
     * Determines if WordPress cron constant is enabled or not.
     *
     * @since 1.0
     *
     * @return boolean True if WordPress cron is disabled.
     */
    public static function is_wpcron_disabled() {
        return ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );
    }

    /**
     * Determines if WordPress auto update constants are enabled or not.
     *
     * @since 1.3.0
     *
     * @return boolean True if WordPress auto update constants are available.
     */
    public static function is_wp_auto_update_disabled() {
        return ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && defined( 'WP_AUTO_UPDATE_CORE' ) );
    }

    /**
     * Check and apply WordPress core updates option.
     *
     * @since 1.3.0
     */
    public static function check_core_updates() {
        $core_auto_update_option = self::get_core_auto_update_option();

        if ( $core_auto_update_option && preg_match( '/^(minor|major|dev|disabled)$/', $core_auto_update_option ) ) {
            if ( 'disabled' == $core_auto_update_option ) {
                add_filter( 'automatic_updater_disabled', '__return_true' );
            } else {
                add_filter( 'allow_' . $core_auto_update_option . '_auto_core_updates', '__return_true' );
            }
        }
    }

    /**
     * Add options when plugin is activated.
     *
     * @since 1.0
     */
    public static function plugin_activation() {
        if ( ! get_option( self::DISABLE_AUTOLOAD_OPTION ) ) {
            add_option( self::DISABLE_AUTOLOAD_OPTION, '', '', 'no' );
        }

        WP_Healthcheck::get_outdated_plugins();
        WP_Healthcheck::is_ssl_available();
    }

    /**
     * Cleanup transients when plugin is deactivated.
     *
     * @since 1.0
     */
    public static function plugin_deactivation() {
        self::_cleanup_options( true );
    }

    /**
     * Cleanup options and transients when plugin is uninstalled.
     *
     * @since 1.0
     */
    public static function plugin_uninstall() {
        self::_cleanup_options();
    }

    /**
     * Cleans up the plugin options and transients.
     *
     * @since 1.0
     *
     * @param boolean $only_transients True to remove only the transients.
     */
    public static function _cleanup_options( $only_transients = false ) {
        if ( ! $only_transients ) {
            $options = array(
                self::DISABLE_AUTOLOAD_OPTION,
                self::DISABLE_NOTICES_OPTION,
                self::CORE_AUTO_UPDATE_OPTION,
                self::DISABLE_OUTDATED_PLUGINS_OPTION,
                WP_Healthcheck_Upgrade::PLUGIN_VERSION_OPTION,
            );

            foreach ( $options as $option ) {
                if ( get_option( $option ) ) {
                    delete_option( $option );
                }
            }
        }

        $transients = array(
            self::HIDE_NOTICES_TRANSIENT,
            self::MIN_REQUIREMENTS_TRANSIENT,
            self::OUTDATED_PLUGINS_TRANSIENT,
            self::SERVER_DATA_TRANSIENT,
            self::SSL_AVAILABLE_TRANSIENT,
            self::SSL_DATA_TRANSIENT,
        );

        foreach ( $transients as $transient ) {
            if ( get_transient( $transient ) ) {
                delete_transient( $transient );
            }
        }
    }

    /**
     * Updates the autoload value for the given option.
     *
     * @since 1.1
     *
     * @param string $option_name The name of the option to disable.
     * @param string $autoload The new value for the autoload field. Only 'yes' or 'no'.
     * @param string $logging Save deactivation to history.
     *
     * @return int|false Number of affected rows or false on error.
     */
    private static function _update_autoload_option( $option_name, $autoload = 'no', $logging = true ) {
        global $wpdb;

        if ( get_option( $option_name ) ) {
            $should_autoload = ( 'yes' == $autoload ) ? true : false;

            // update option's autoload value to $autoload.
            $result = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->options SET autoload = %s WHERE option_name LIKE %s;", $autoload, $option_name ) );

            if ( 0 == $result ) {
                return false;
            }

            if ( $should_autoload && self::is_autoload_disabled( $option_name ) ) {
                return false;
            }

            if ( ! $should_autoload && ! self::is_autoload_disabled( $option_name ) ) {
                return false;
            }

            if ( ! $logging ) {
                return $result;
            }

            $updated = false;

            if ( $should_autoload ) {
                // removes option name and timestamp from history.
                $history = get_option( self::DISABLE_AUTOLOAD_OPTION );

                if ( $history && is_array( $history ) ) {
                    foreach ( $history as $name => $timestamp ) {
                        if ( get_option( $name ) && $name == $option_name ) {
                            unset( $history[ $name ] );

                            $updated = true;

                            break;
                        }
                    }
                }
            } else {
                // adds option name and timestamp to history.
                if ( ! get_option( self::DISABLE_AUTOLOAD_OPTION ) ) {
                    add_option( self::DISABLE_AUTOLOAD_OPTION, '', '', 'no' );
                }

                $history = get_option( self::DISABLE_AUTOLOAD_OPTION );

                if ( ! is_array( $history ) ) {
                    $history = array();
                }

                $history[ $option_name ] = time();
            }

            if ( ! $should_autoload || $updated ) {
                update_option( self::DISABLE_AUTOLOAD_OPTION, $history );
            }

            return $result;
        }

        return false;
    }
}
