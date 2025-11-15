<?php

namespace THSCD\WPHC\Modules;

/**
 * The SSL class.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
class SSL {
	/**
	 * Transient to store the SSL data.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const DATA_TRANSIENT = 'wphc_ssl_data';

	/**
	 * Transient to store if SSL is available or not.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const AVAILABLE_TRANSIENT = 'wphc_ssl_available';

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since {VERSION}
	 */
	public function hooks() {

		add_action( 'shutdown', [ $this, 'get_data' ] );
	}

	/**
	 * Retrieves some information from SSL certificate associated with site url.
	 *
	 * @since {VERSION}
	 *
	 * @return array|false SSL data or false on error.
	 */
	public function get_data() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! is_ssl() ) {
			return false;
		}

		$ssl_data = get_transient( self::DATA_TRANSIENT );

		if ( $ssl_data === false ) {
			$context = stream_context_create(
				[
					'ssl' => [
						'capture_peer_cert' => true,
						'verify_peer'       => false,
					],
				]
			);

			$siteurl = wp_parse_url( get_option( 'siteurl' ) );

			if ( empty( $siteurl['host'] ) ) {
				return false;
			}

			$socket = @stream_socket_client( 'ssl://' . $siteurl['host'] . ':443', $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			if ( ! $socket ) {
				set_transient( self::DATA_TRANSIENT, [], DAY_IN_SECONDS );

				return false;
			}

			$params = stream_context_get_params( $socket );

			if ( ! empty( $params['options']['ssl']['peer_certificate'] ) ) {
				$certificate = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );

				$ssl_data = [
					'common_name' => ! empty( $certificate['subject']['CN'] ) ? $certificate['subject']['CN'] : '',
					'issuer'      => ! empty( $certificate['issuer']['CN'] ) ? $certificate['issuer']['CN'] : '',
					'validity'    => [
						'from' => gmdate( 'Y-m-d H:i:s', $certificate['validFrom_time_t'] ),
						'to'   => gmdate( 'Y-m-d H:i:s', $certificate['validTo_time_t'] ),
					],
				];

				set_transient( self::DATA_TRANSIENT, $ssl_data, DAY_IN_SECONDS );
			}
		}

		/**
		 * Filters the SSL data.
		 *
		 * @since {VERSION}
		 *
		 * @param array $ssl_data An array with the SSL data.
		 */
		return apply_filters( 'wphc_core_ssl_data', $ssl_data );
	}

	/**
	 * Determine if a SSL certificate is available or not.
	 *
	 * @since {VERSION}
	 *
	 * @return bool True if SSL is available.
	 */
	public function is_available() {

		if ( is_ssl() ) {
			return true;
		}

		$is_available = get_transient( self::AVAILABLE_TRANSIENT );

		if ( $is_available === false ) {
			$siteurl = wp_parse_url( get_option( 'siteurl' ) );

			if ( empty( $siteurl['host'] ) ) {
				return false;
			}

			$socket = @fsockopen( 'ssl://' . $siteurl['host'], 443, $errno, $errstr, 20 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_fsockopen

			$is_available = ( $socket !== false );

			set_transient( self::AVAILABLE_TRANSIENT, $is_available, DAY_IN_SECONDS );
		}

		/**
		 * Filters if the SSL is available or not.
		 *
		 * @since {VERSION}
		 *
		 * @param bool $is_available True if the SSL is available.
		 */
		return apply_filters( 'wphc_core_ssl_is_available', $is_available );
	}

	/**
	 * Determines if a SSL certificate will expire soon.
	 *
	 * @since {VERSION}
	 *
	 * @return int|false Number of days until certificate expiration, or false on error.
	 */
	public function is_expiring() {

		/**
		 * Filters the number of days prior to the SSL expiration date that the admin notice should be displayed.
		 *
		 * @since {VERSION}
		 *
		 * @param int $days The number of days.
		 */
		$days = apply_filters( 'wphc_core_ssl_is_expiring_days_before_notice', 15 );

		$ssl_data = get_transient( self::DATA_TRANSIENT );

		if ( $ssl_data !== false && ! empty( $ssl_data['validity']['to'] ) ) {
			$current    = time();
			$expiration = strtotime( $ssl_data['validity']['to'] );

			$diff = intval( floor( $expiration - $current ) / DAY_IN_SECONDS );
		}

		$is_expiring = ( ! empty( $diff ) && is_int( $diff ) && $diff <= $days ) ? $diff : false;

		/**
		 * Filters if the SSL is about to expire or not.
		 *
		 * @since {VERSION}
		 *
		 * @param int|false $is_expiring Number of days until certificate expiration, or false on error.
		 */
		return apply_filters( 'wphc_core_ssl_is_expiring', $is_expiring );
	}
}
