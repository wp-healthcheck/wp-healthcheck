<?php
namespace WPHC\Core;

/**
 * The Options class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Options {
	/**
	 * Option to store the history of disabled autoload options.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const DISABLE_AUTOLOAD_OPTION = 'wphc_disable_autoload_history';

	/**
	 * Deactivates an autoload option.
	 *
	 * @since 1.0
	 *
	 * @param string  $option_name The name of the option to disable.
	 * @param boolean $logging     Save deactivation to history.
	 *
	 * @return int|false Number of affected rows or false on error.
	 */
	public function deactivate_autoload_option( $option_name, $logging = true ) {

		return $this->update_autoload_option( $option_name, 'no', $logging );
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
	public function reactivate_autoload_option( $option_name ) {

		return $this->update_autoload_option( $option_name, 'yes' );
	}

	/**
	 * Returns the autoload options deactivated via WP Healthcheck.
	 *
	 * @since 1.0
	 *
	 * @return array|false Name and timestamp of the options or false if none.
	 */
	public function get_autoload_history() {

		$history = get_option( self::DISABLE_AUTOLOAD_OPTION );

		if ( $history ) {
			$updated    = false;
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
	public function get_autoload_options() {

		global $wpdb;

		$options = [];

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
	public function get_autoload_stats() {

		global $wpdb;

		$result = $wpdb->get_row( "SELECT COUNT(*) AS count, SUM(LENGTH(option_value)) / POWER(1024,2) AS size FROM $wpdb->options WHERE autoload = 'yes' AND option_name NOT REGEXP '^_(site_)?transient';" );

		$count = (int) $result->count;
		$size  = (float) $result->size;

		return [
			'count' => $count,
			'size'  => $size,
		];
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
	public function is_autoload_disabled( $option_name ) {

		global $wpdb;

		$autoload = $wpdb->get_var( $wpdb->prepare( "SELECT autoload FROM $wpdb->options WHERE option_name = %s;", $option_name ) );

		return ( 'no' === $autoload );
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
	public function is_core_option( $option_name ) {

		$wp_opts_file = WPHC_INC_DIR . '/data/wp_options.json';

		if ( file_exists( $wp_opts_file ) ) {
			$wp_opts = json_decode( file_get_contents( $wp_opts_file ) );
		}

		return in_array( $option_name, $wp_opts, true );
	}

	/**
	 * Updates the autoload value for the given option.
	 *
	 * @since 1.1
	 *
	 * @param string $option_name The name of the option to disable.
	 * @param string $autoload    The new value for the autoload field. Only 'yes' or 'no'.
	 * @param string $logging     Save deactivation to history.
	 *
	 * @return int|false Number of affected rows or false on error.
	 */
	private function update_autoload_option( $option_name, $autoload = 'no', $logging = true ) {

		global $wpdb;

		if ( get_option( $option_name ) ) {
			$should_autoload = ( 'yes' === $autoload );

			// update option's autoload value to $autoload.
			$result = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->options SET autoload = %s WHERE option_name LIKE %s;", $autoload, $option_name ) );

			if ( 0 == $result ) {
				return false;
			}

			if ( $should_autoload && $this->is_autoload_disabled( $option_name ) ) {
				return false;
			}

			if ( ! $should_autoload && ! $this->is_autoload_disabled( $option_name ) ) {
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
					$history = [];
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
