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
     * Transient to store the server data.
     *
     * @since 1.0
     * @var string
     */
    const SERVER_DATA_TRANSIENT = 'wphc_server_data';

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
        }
    }

    /**
     * Initialize the WordPress hooks.
     *
     * @since 1.0
     */
    public static function init_hooks() {
        self::$initiated = true;

        add_action( 'upgrader_process_complete', array( 'WP_Healthcheck', 'plugin_deactivation' ) );
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

            $server = array(
                'database' => array(
                    'service' => $db_service,
                    'version' => $wpdb->db_version(),
                ),
                'php'   => $phpversion[0],
                'wp'    => $wp_version,
            );

            if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
                $server['web'] = $_SERVER['SERVER_SOFTWARE'];
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
        if ( ! preg_match( '/^(php|mysql|mariadb|wp)$/', $software ) ) {
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

            $minimum = preg_replace( '/(\d{1,}\.\d{1,})(\.\d{1,})?/', '$1', end( $requirements['wordpress'] ) );
            $requirements[ $software ]['minimum'] = $minimum;
        }

        if ( preg_match( '/^(mysql|mariadb)$/', $software ) ) {
            $server_data[ $software ] = $server_data['database']['version'];
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
     * Add options when plugin is activated.
     *
     * @since 1.0
     */
    public static function plugin_activation() {
        if ( ! get_option( self::DISABLE_AUTOLOAD_OPTION ) ) {
            add_option( self::DISABLE_AUTOLOAD_OPTION, '', '', 'no' );
        }
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
            self::SERVER_DATA_TRANSIENT,
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
