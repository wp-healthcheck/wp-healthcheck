<?php
namespace WPHC\Core;

/**
 * The Transients class.
 *
 * @package wp-healthcheck
 * @since 1.0
 */
class Transients {

	/**
	 * Cleans up the WordPress transients, or flushes the object cache if it is enabled.
	 *
	 * @since 1.0
	 *
	 * @param boolean $only_expired Only expired transients.
	 *
	 * @return int|false Number of affected rows or false on error.
	 */
	public function cleanup_transients( $only_expired = true ) {

		global $wpdb;

		if ( wp_using_ext_object_cache() ) {
			return wp_cache_flush();
		}

		if ( $only_expired ) {
			return $wpdb->query(
				$wpdb->prepare(
					"DELETE a, b FROM $wpdb->options a INNER JOIN $wpdb->options b ON b.option_name = REPLACE(a.option_name, '_timeout', '') WHERE a.option_name REGEXP '^(_site)?_transient_timeout' AND a.option_value < %s;",
					time()
				)
			);
		}

		return $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name REGEXP '^_(site_)?transient';" );
	}

	/**
	 * Returns the 10 biggest transients.
	 *
	 * @since 1.0
	 *
	 * @return array The name and size of the biggest transients.
	 */
	public function get_transients() {

		global $wpdb;

		$transients = [];

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
	public function get_transients_stats() {

		global $wpdb;

		$result = $wpdb->get_row( "SELECT COUNT(*) AS count, SUM(LENGTH(option_value)) / POWER(1024,2) AS size FROM $wpdb->options WHERE option_name REGEXP '^_(site_)?transient';" );

		$count = (int) $result->count;
		$size  = (float) $result->size;

		return [
			'count' => $count,
			'size'  => $size,
		];
	}
}
