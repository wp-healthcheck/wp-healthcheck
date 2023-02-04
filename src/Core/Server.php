<?php
namespace WPHC\Core;

/**
 * The Server class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Server {
	/**
	 * Transient to store the minimum requirements.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const MIN_REQUIREMENTS_TRANSIENT = 'wphc_min_requirements';

	/**
	 * Transient to store the server data.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const SERVER_DATA_TRANSIENT = 'wphc_server_data';

	/**
	 * Retrieves the server data.
	 *
	 * @since 1.0
	 *
	 * @return array The server data.
	 */
	public function get_server_data() {

		global $wpdb;

		$server = get_transient( self::SERVER_DATA_TRANSIENT );

		if ( false === $server ) {
			include ABSPATH . WPINC . '/version.php';

			$php = preg_match( '/^(\d+\.){2}\d+/', phpversion(), $phpversion );

			$db_service = ( preg_match( '/MariaDB/', $wpdb->dbh->server_info ) ) ? 'MariaDB' : 'MySQL';
			$db_version = $wpdb->db_version();

			if ( 'MariaDB' === $db_service ) {
				$db_version = preg_replace( '/[^0-9.].*/', '', $wpdb->get_var( 'SELECT @@version;' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
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
					$server['web']['service'] = strtolower( $matches[0] );

					if ( preg_match( '/([0-9]{1,}\.){2}([0-9]{1,})?/', $server_software, $matches ) ) {
						$server['web']['version'] = trim( $matches[0] );
					} else {
						$server['web']['version'] = '';
					}
				} else {
					$server['web'] = [
						'service' => 'Web',
						'version' => $server_software,
					];
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
	 * @return array|false The server requirements, or false on error.
	 */
	public function get_server_requirements() {

		$requirements = get_transient( self::MIN_REQUIREMENTS_TRANSIENT );

		if ( false === $requirements ) {
			$options = [
				'timeout'    => 20,
				'user-agent' => 'WP Healthcheck/' . WPHC_VERSION . '; ' . site_url(),
			];

			$res = wp_remote_get( 'https://api.wp-healthcheck.com/v1/requirements', $options );

			if ( is_array( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
				$requirements = json_decode( $res['body'], true );

				set_transient( self::MIN_REQUIREMENTS_TRANSIENT, $requirements, WEEK_IN_SECONDS );
			} else {
				return false;
			}
		}

		return $requirements;
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
	public function is_software_updated( $software ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		if ( ! preg_match( '/^(php|mysql|mariadb|wp|nginx|apache)$/', $software ) ) {
			return false;
		}

		$requirements = self::get_server_requirements();

		if ( ! $requirements ) {
			return false;
		}

		$server_data = self::get_server_data();

		if ( 'wp' === $software ) {
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
}
