<?php

namespace THSCD\WPHC\Modules;

/**
 * The Transients class.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
class Transients {
	/**
	 * Returns the 10 biggest transients.
	 *
	 * @since {VERSION}
	 *
	 * @return array The name and size of the biggest transients.
	 */
	public function get() {

		global $wpdb;

		$transients = [];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_results( "SELECT option_name, ROUND(LENGTH(option_value) / POWER(1024,2), 3) AS size FROM $wpdb->options WHERE option_name REGEXP '^_(site_)?transient' ORDER BY size DESC LIMIT 0,10;" );

		foreach ( $result as $transient ) {
			$transients[ $transient->option_name ] = (float) $transient->size;
		}

		return $transients;
	}

	/**
	 * Returns the WordPress transients count and size.
	 *
	 * @since {VERSION}
	 *
	 * @return array Stats of the transients.
	 */
	public function get_stats() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row( "SELECT COUNT(*) AS count, SUM(LENGTH(option_value)) / POWER(1024,2) AS size FROM $wpdb->options WHERE option_name REGEXP '^_(site_)?transient';" );

		$count = (int) $result->count;
		$size  = (float) $result->size;

		$stats = [
			'count' => $count,
			'size'  => $size,
		];

		/**
		 * Filters the transients stats.
		 *
		 * @since {VERSION}
		 *
		 * @param array $stats Array with the total count and size of the transients.
		 */
		return apply_filters( 'wphc_core_transients_stats', $stats );
	}

	/**
	 * Cleans up the WordPress transients, or flushes the object cache if it is enabled.
	 *
	 * @since {VERSION}
	 *
	 * @param bool $only_expired Only expired transients.
	 *
	 * @return int|false Number of affected rows or false on error.
	 */
	public function cleanup( $only_expired = true ) {

		global $wpdb;

		if ( wp_using_ext_object_cache() ) {
			return wp_cache_flush();
		}

		if ( $only_expired ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->query(
				$wpdb->prepare(
					"DELETE a, b FROM $wpdb->options a INNER JOIN $wpdb->options b ON b.option_name = REPLACE(a.option_name, '_timeout', '') WHERE a.option_name REGEXP '^(_site)?_transient_timeout' AND a.option_value < %s;",
					time()
				)
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name REGEXP '^_(site_)?transient';" );
	}
}
