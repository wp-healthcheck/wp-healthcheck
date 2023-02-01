<?php
namespace WPHC\Core;

/**
 * The WP_Healthcheck class
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class Core {
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

				$wp_api = plugins_api(
					'plugin_information',
					array(
						'slug' => $slug,
					)
				);

				if ( empty( $wp_api->errors ) && ! empty( $wp_api->last_updated ) ) {
					$today       = new DateTime();
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
			$context = stream_context_create(
				array(
					'ssl' => array(
						'capture_peer_cert' => true,
						'verify_peer'       => false,
					),
				)
			);

			$siteurl = parse_url( get_option( 'siteurl' ) );

			if ( empty( $siteurl['host'] ) ) {
				return false;
			}

			$socket = @stream_socket_client( 'ssl://' . $siteurl['host'] . ':443', $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

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

			$socket = @fsockopen( 'ssl://' . $siteurl['host'], 443, $errno, $errstr, 20 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

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
			$current    = time();
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
