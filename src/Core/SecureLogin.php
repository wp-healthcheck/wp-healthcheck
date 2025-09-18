<?php
namespace THSCD\WPHC\Core;

/**
 * The SecureLogin class.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
class SecureLogin {

	/**
	 * The default Secure Login settings.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	const DEFAULT_SETTINGS = [
		'max-retries'      => 5,
		'lockout-time'     => 10,
		'max-lockouts'     => 3,
		'extended-lockout' => 24,
		'reset-retries'    => 24,
		'enabled'          => 0,
	];

	/**
	 * Option to store the log of login attempts.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const LOGIN_ATTEMPTS_LOG_OPTION = 'wphc_secure_login_attempts_log';

	/**
	 * Option to store the Secure Login settings.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	const SETTINGS_OPTION = 'wphc_secure_login_settings';

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		// Creates the settings if they do not exist.
		$this->maybe_reset_settings();

		// Loads the hooks only if the Secure Login is enabled.
		if ( $this->is_enabled() ) {
			$this->hooks();
		}
	}

	/**
	 * WordPress actions and filters.
	 *
	 * @since {VERSION}
	 */
	public function hooks() {

		add_action( 'init', [ $this, 'cleanup_expired_login_attempts' ] );

		add_filter( 'authenticate', [ $this, 'check_login_attempt' ], 21, 3 );
		add_filter( 'authenticate', [ $this, 'maybe_replace_invalid_username_error' ], 21, 3 );

		add_filter( 'shake_error_codes', [ $this, 'add_error_to_login_shake_codes' ] );
	}

	/**
	 * Adds Supervisor's error codes to the list of 'shake_error_codes'.
	 *
	 * @since {VERSION}
	 *
	 * @param string[] $error_codes The error codes that shake the login form.
	 *
	 * @return string[]
	 */
	public function add_error_to_login_shake_codes( $error_codes ) {

		$error_codes[] = 'wphc_too_many_attempts';

		return $error_codes;
	}

	/**
	 * Checks the login attempt.
	 *
	 * @since {VERSION}
	 *
	 * @param WP_User|WP_Error|null $user     WP_User or WP_Error object from a previous callback. Default null.
	 * @param string                $username Username. If not empty, cancels the cookie authentication.
	 * @param string                $password Password. If not empty, cancels the cookie authentication.
	 *
	 * @return WP_User|WP_Error WP_User on success, WP_Error on failure.
	 */
	public function check_login_attempt( $user, $username, $password ) {

		$user_ip  = wphc_get_user_ip();
		$username = strtolower( $username );

		if ( $this->is_user_blocked( $user_ip, $username ) ) {
			/**
			 * Fires when a blocked IP address tries to log into your site.
			 *
			 * @since {VERSION}
			 *
			 * @param string|false $user_ip  The user's IP address, or false on error.
			 * @param string       $username The username.
			 */
			do_action( 'wphc_core_secure_login_blocked_ip_attempt', $user_ip, $username );

			$error = wphc_prepare_wp_error(
				'wphc_too_many_attempts',
				esc_html__( 'You have exceeded the maximum number of login attempts. Please try again later.', 'supervisor' )
			);
		} else {
			$this->log_login_attempt( $user_ip, $username );
		}

		return ! empty( $error ) ? $error : $user;
	}

	/**
	 * Cleans up the expired login attempts.
	 *
	 * @since {VERSION}
	 */
	public function cleanup_expired_login_attempts() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$log = get_option( self::LOGIN_ATTEMPTS_LOG_OPTION );

		if ( ! $log ) {
			return;
		}

		$now           = time();
		$reset_retries = $this->get_settings( 'reset-retries' );

		foreach ( $log['ips'] as $user_ip => $data ) {
			foreach ( $data['timestamps'] as $index => $timestamp ) {
				if ( ! is_numeric( $timestamp ) ) {
					continue;
				}

				$hours_diff = ( $now - (int) $timestamp ) / 3600;

				if ( $hours_diff > $reset_retries ) {
					unset( $log['ips'][ $user_ip ]['timestamps'][ $index ] );
				}
			}

			// If no timestamps were found associated with the given IP, then reset the retries and lockouts.
			if ( empty( $log['ips'][ $user_ip ]['timestamps'] ) ) {
				unset( $log['ips'][ $user_ip ] );

				/**
				 * Fires when an user IP is unblocked from your site.
				 *
				 * @since {VERSION}
				 *
				 * @param string|false $user_ip The user's IP address, or false on error.
				 */
				do_action( 'wphc_core_secure_login_unblock_ip', $user_ip );
			}
		}

		update_option( self::LOGIN_ATTEMPTS_LOG_OPTION, $log, false );
	}

	/**
	 * Maybe replaces the invalid_username error message. Per default, this error message indicates whether the user
	 * exists in the database or not.
	 *
	 * @since {VERSION}
	 *
	 * @param WP_User|WP_Error|null $user     WP_User or WP_Error object from a previous callback. Default null.
	 * @param string                $username Username. If not empty, cancels the cookie authentication.
	 * @param string                $password Password. If not empty, cancels the cookie authentication.
	 *
	 * @return WP_User|WP_Error WP_User on success, WP_Error on failure.
	 */
	public function maybe_replace_invalid_username_error( $user, $username, $password ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( is_wp_error( $user ) && $user->get_error_code() === 'invalid_username' ) {
			$message = sprintf(
				/* translators: %s is the username. */
				esc_html__( 'The password you entered for the username %s is incorrect.', 'supervisor' ),
				'<strong>' . $username . '</strong>'
			);

			$error = wphc_prepare_wp_error(
				'invalid_username',
				$message
			);
		}

		return ! empty( $error ) ? $error : $user;
	}

	/**
	 * Determines whether the Secure Login setting is enabled or not.
	 *
	 * @since {VERSION}
	 */
	public function is_enabled() {

		return ! empty( $this->get_settings( 'enabled' ) );
	}

	/**
	 * Retrieves all settings, or the value from a given setting.
	 *
	 * @since {VERSION}
	 *
	 * @param string|false $name The setting name.
	 *
	 * @return string|false
	 */
	public function get_settings( $name = false ) {

		$settings = get_option( self::SETTINGS_OPTION, [] );

		return $settings[ $name ] ?? $settings;
	}

	/**
	 * Maybe resets the settings.
	 *
	 * @since {VERSION}
	 */
	public function maybe_reset_settings() {

		$this->update_settings( [] );
	}

	/**
	 * Updates the settings.
	 *
	 * @since {VERSION}
	 *
	 * @param array $new_settings The new settings.
	 */
	public function update_settings( $new_settings ) {

		// Populates the settings.
		$settings = get_option( self::SETTINGS_OPTION, [] );

		foreach ( $new_settings as $key => $value ) {
			$settings[ $key ] = $value;
		}

		// Populates the settings with the default values if needed.
		foreach ( self::DEFAULT_SETTINGS as $key => $value ) {
			if ( empty( $settings[ $key ] ) ) {
				$settings[ $key ] = $value;
			}
		}

		// Updates the settings.
		update_option( self::SETTINGS_OPTION, $settings );
	}

	/**
	 * Get the login attempts.
	 *
	 * @since {VERSION}
	 *
	 * @param string $user_ip  The user IP address.
	 * @param string $username The username.
	 */
	private function get_login_attempts( $user_ip, $username ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$response = [];

		$log = get_option( self::LOGIN_ATTEMPTS_LOG_OPTION );

		if ( $log && ! empty( $user_ip ) && ! empty( $log['ips'][ $user_ip ] ) ) {
			$response['ip'] = $log['ips'][ $user_ip ];
		}

		return $response;
	}

	/**
	 * Confirms whether a given username and/or IP address are blocked or not.
	 *
	 * @since {VERSION}
	 *
	 * @param string $user_ip  The user IP address.
	 * @param string $username The username.
	 *
	 * @return bool
	 */
	private function is_user_blocked( $user_ip, $username ) {

		$attempts = $this->get_login_attempts( $user_ip, $username );

		return ! empty( $attempts['ip']['lock_until'] ) && time() < $attempts['ip']['lock_until'];
	}

	/**
	 * Logs the login attempt to the option.
	 *
	 * @since {VERSION}
	 *
	 * @param string $user_ip  The user IP address.
	 * @param string $username The username.
	 */
	private function log_login_attempt( $user_ip, $username ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded,Generic.Metrics.CyclomaticComplexity.TooHigh

		$log = get_option( self::LOGIN_ATTEMPTS_LOG_OPTION );

		$now = time();

		// If the user is already locked, do not log the login attempt.
		$lock_until = ! empty( $log['ips'][ $user_ip ]['lock_until'] ) && is_numeric( $log['ips'][ $user_ip ]['lock_until'] ) ? $log['ips'][ $user_ip ]['lock_until'] : false;

		if ( ! empty( $lock_until ) ) {
			// If the lock is expired, then allow user to proceed with the login.
			if ( $lock_until < $now ) {
				$lock_until = false;
			} else {
				return;
			}
		}

		// Retrieves the settings and the current IP information from the logs.
		$settings = $this->get_settings();

		$retries    = ! empty( $log['ips'][ $user_ip ]['retries'] ) && is_numeric( $log['ips'][ $user_ip ]['retries'] ) ? (int) $log['ips'][ $user_ip ]['retries'] : 0;
		$lockouts   = ! empty( $log['ips'][ $user_ip ]['lockouts'] ) && is_numeric( $log['ips'][ $user_ip ]['lockouts'] ) ? (int) $log['ips'][ $user_ip ]['lockouts'] : 0;
		$timestamps = ! empty( $log['ips'][ $user_ip ]['timestamps'] ) && is_array( $log['ips'][ $user_ip ]['timestamps'] ) ? $log['ips'][ $user_ip ]['timestamps'] : [];

		++$retries;

		// If the number of retries is equal or greater than allowed, then add a lockout.
		$max_retries = $lockouts === 0 ? $settings['max-retries'] : ( $lockouts + 1 ) * $settings['max-retries'];

		if ( $retries >= $max_retries ) {
			++$lockouts;

			$lock_until = strtotime( '+' . $settings['lockout-time'] . ' minutes' );
		}

		// If the number of lockouts is equal or greater than allowed, then add an extended lockout.
		if ( $lockouts >= $settings['max-lockouts'] ) {
			$lock_until = strtotime( '+' . $settings['extended-lockout'] . ' hours' );
		}

		array_unshift( $timestamps, $now );

		$log['ips'][ $user_ip ] = [
			'retries'    => $retries,
			'lockouts'   => $lockouts,
			'lock_until' => $lock_until,
			'timestamps' => $timestamps,
		];

		update_option( self::LOGIN_ATTEMPTS_LOG_OPTION, $log, false );
	}
}
