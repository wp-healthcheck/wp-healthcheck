<?php

namespace THSCD\WPHC\Modules;

/**
 * The Server class.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
class Server {
	/**
	 * Transient to store the minimum requirements.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const MIN_REQUIREMENTS_TRANSIENT = 'wphc_server_min_requirements';

	/**
	 * Transient to store the server data.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const DATA_TRANSIENT = 'wphc_server_data';

	/**
	 * Retrieves the server data.
	 *
	 * @since {VERSION}
	 *
	 * @return array The server data.
	 */
	public function get_data() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		global $wpdb;

		$server = get_transient( self::DATA_TRANSIENT );

		if ( $server === false ) {
			include ABSPATH . WPINC . '/version.php';

			// Retrieves the PHP version.
			preg_match( '/^(\d+\.){2}\d+/', phpversion(), $phpversion );

			// Determines if the database is MySQL or MariaDB.
			$db_service = preg_match( '/MariaDB/', $wpdb->dbh->server_info ) ? 'MariaDB' : 'MySQL';

			// Determines the database software version.
			$db_version = $wpdb->db_version();

			if ( $db_service === 'MariaDB' ) {
				$db_version = preg_replace( '/[^0-9.].*/', '', $wpdb->get_var( 'SELECT @@version;' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			}

			$server = [
				'database' => [
					'service' => $db_service,
					'version' => $db_version,
				],
				'php'      => $phpversion[0],
				'wp'       => $wp_version,
				'web'      => [],
			];

			$server_software = ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : false;

			if ( ! empty( $server_software ) ) {
				$matches = [];

				if ( preg_match( '/(apache|nginx)/i', $server_software, $matches ) ) {
					$server['web'] = [
						'service' => strtolower( $matches[0] ),
						'version' => preg_match( '/([0-9]+\.){2}([0-9]+)?/', $server_software, $matches ) ? trim( $matches[0] ) : false,
					];
				} else {
					$server['web'] = [
						'service' => 'Web',
						'version' => $server_software,
					];
				}
			}

			set_transient( self::DATA_TRANSIENT, $server, DAY_IN_SECONDS );
		}

		/**
		 * Filters the server data.
		 *
		 * @since {VERSION}
		 *
		 * @param array $server The server data.
		 */
		return apply_filters( 'wphc_core_server_data', $server );
	}

	/**
	 * Retrieves the server IP address.
	 *
	 * @since {VERSION}
	 *
	 * @return string|false The IP address, or false if IP was not found.
	 */
	public function get_ip() {

		// gethostname() was added only on PHP 5.3.
		if ( function_exists( 'gethostname' ) ) {
			$ip = gethostbyname( gethostname() );
		}

		// If ip was not found via gethostbyname(), try to retrieve it from the $_SERVER array.
		if ( empty( $ip ) && ! empty( $_SERVER['SERVER_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) );
		}

		$ip = ! empty( $ip ) && filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : false;

		/**
		 * Filters the server IP.
		 *
		 * @since {VERSION}
		 *
		 * @param string|false $server The server IP.
		 */
		return apply_filters( 'wphc_core_server_ip', $ip );
	}

	/**
	 * Retrieves the server requirements from our API.
	 *
	 * @since {VERSION}
	 *
	 * @return array|false The server requirements, or false on error.
	 */
	public function get_requirements() {

		$requirements = get_transient( self::MIN_REQUIREMENTS_TRANSIENT );

		if ( $requirements === false ) {
			$options = [
				'timeout'    => 20,
				'user-agent' => 'WP Healthcheck/' . WPHC_VERSION . '; ' . site_url(),
			];

			$response = wp_remote_get( 'https://api.wp-healthcheck.com/v1/requirements', $options );

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 && is_array( $response ) ) {
				$requirements = json_decode( wp_remote_retrieve_body( $response ), true );

				set_transient( self::MIN_REQUIREMENTS_TRANSIENT, $requirements, WEEK_IN_SECONDS );
			}
		}

		/**
		 * Filters the server requirements.
		 *
		 * @since {VERSION}
		 *
		 * @param array|false $requirements The server requirements.
		 */
		return apply_filters( 'wphc_core_server_requirements', $requirements );
	}

	/**
	 * Determines if the server software is up-to-date or not.
	 *
	 * @since {VERSION}
	 *
	 * @param string $software The software name.
	 *
	 * @return string|false The current status ('updated', 'need_update', 'outdated', or 'obsolete') of the software or false on error.
	 */
	public function is_updated( $software ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		// Stop the execution if software is not found.
		if ( ! preg_match( '/^(php|mysql|mariadb|wp|nginx|apache)$/', $software ) ) {
			return false;
		}

		// Get the minimum requirements from our API.
		$requirements = $this->get_requirements();

		if ( ! $requirements ) {
			return false;
		}

		// Get the system information.
		$sysinfo = $this->get_data();

		// WordPress.
		if ( $software === 'wp' ) {
			// Determines the latest minor version available.
			$minor_latest = $this->get_latest_minor_version( $sysinfo['wp'], $requirements['wordpress'] );

			$requirements[ $software ]['recommended'] = ! empty( $minor_latest ) ? $minor_latest : $requirements['wordpress'][0];

			// Determines the minimum WP version available.
			$minimum_version = preg_replace( '/(\d{1,}\.\d{1,})(\.\d{1,})?/', '$1', end( $requirements['wordpress'] ) );

			$requirements[ $software ]['minimum'] = $minimum_version;
		}

		// Database.
		if ( preg_match( '/^(mysql|mariadb)$/', $software ) ) {
			$sysinfo[ $software ] = $sysinfo['database']['version'];
		}

		// PHP.
		if ( $software === 'php' ) {
			// Determines the latest minor version available.
			$minor_latest = $this->get_latest_minor_version( $sysinfo[ $software ], $requirements['php']['versions'] );

			if ( ! empty( $minor_latest ) ) {
				$requirements[ $software ]['recommended'] = $minor_latest;
			}
		}

		// Web server.
		if ( preg_match( '/^(nginx|apache)$/', $software ) ) {
			$sysinfo[ $software ] = $sysinfo['web']['version'];

			$requirements[ $software ]['minimum'] = end( $requirements[ $software ]['versions'] );
		}

		// Compare the versions and return the status.
		if ( version_compare( $sysinfo[ $software ], $requirements[ $software ]['recommended'], '>=' ) ) {
			$status = 'updated';
		} elseif ( version_compare( $sysinfo[ $software ], $requirements[ $software ]['minimum'], '>=' ) ) {
			$status = 'outdated';
		} else {
			$status = 'obsolete';
		}

		/**
		 * Filters the status of the software update.
		 *
		 * @since {VERSION}
		 *
		 * @param string $status   The software update status ('updated', 'outdated', or 'obsolete').
		 * @param string $software The software name ('php', 'mysql', 'mariadb', 'wp', 'nginx', or 'apache').
		 */
		return apply_filters( 'wphc_core_server_is_software_updated', $status, $software );
	}

	/**
	 * Gets the latest minor version available.
	 *
	 * @since {VERSION}
	 *
	 * @param string $version           The version currently installed.
	 * @param array  $upstream_versions The versions available on upstream.
	 *
	 * @return string|false The upstream latest version, or false on error.
	 */
	private function get_latest_minor_version( $version, $upstream_versions ) {

		if ( ! empty( $version ) && is_array( $upstream_versions ) ) {
			$major = preg_replace( '/(\d{1,}\.\d{1,})(\.\d{1,})?/', '$1', $version );

			foreach ( $upstream_versions as $upstream_version ) {
				if ( preg_match( '/^' . $major . '(\.\d{1,})?/', $upstream_version ) ) {
					return $upstream_version;
				}
			}
		}

		return false;
	}

	/**
	 * Determines if the minor version is updated to the latest version.
	 *
	 * @since {VERSION}
	 *
	 * @param string $version           The version currently installed.
	 * @param array  $upstream_versions The versions available on upstream.
	 *
	 * @return bool True if it is up-to-date.
	 */
	private function is_minor_version_updated( $version, $upstream_versions ) {

		if ( empty( $version ) || ! is_array( $upstream_versions ) ) {
			return false;
		}

		return (bool) version_compare( $version, $this->get_latest_minor_version( $version, $upstream_versions ), '>=' );
	}
}
