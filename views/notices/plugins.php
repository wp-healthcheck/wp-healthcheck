<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$outdated_plugins = wphc( 'module.plugins' )->get_outdated_plugins();

if ( ! $outdated_plugins || count( $outdated_plugins ) === 0 ) {
	return false;
}
?>

<div class="notice wphc-notice wphc-notice-plugins notice-error is-dismissible">
	<p>
		<strong><?php esc_html_e( 'WP Healthcheck:', 'wp-healthcheck' ); ?></strong>
		<?php
		if ( count( $outdated_plugins ) === 1 ) {
			esc_html_e( 'There is a plugin that is outdated and may need your attention.', 'wp-healthcheck' );
		} else {
			esc_html_e( 'There are plugins that are outdated and may need your attention.', 'wp-healthcheck' );
		}
		?>
	</p>
	<ul style="list-style: disc; margin-left: 2em;">
		<?php foreach ( $outdated_plugins as $slug => $days_since_update ) : ?>
			<?php
			$years       = floor( $days_since_update / 365 );
			$months      = floor( ( $days_since_update % 365 ) / 30 );
			$time_string = '';

			if ( $years > 0 ) {
				/* translators: %d number of years. */
				$time_string = sprintf( _n( '%d year', '%d years', $years, 'wp-healthcheck' ), $years );

				if ( $months > 0 ) {
					/* translators: 1: years string, 2: number of months. */
					$time_string = sprintf( __( '%1$s and %2$d months', 'wp-healthcheck' ), $time_string, $months );
				}
			} else {
				/* translators: %d number of months. */
				$time_string = sprintf( _n( '%d month', '%d months', $months, 'wp-healthcheck' ), $months );
			}
			?>
			<li>
				<strong><?php echo esc_html( $slug ); ?></strong>
				&mdash;
				<?php
				/* translators: %s time since last update (e.g., "3 years and 2 months"). */
				echo esc_html( sprintf( __( 'Last updated %s ago', 'wp-healthcheck' ), $time_string ) );
				?>
			</li>
		<?php endforeach; ?>
	</ul>
	<p>
		<?php
		if ( count( $outdated_plugins ) === 1 ) {
			esc_html_e( 'Please review it in your', 'wp-healthcheck' );
		} else {
			esc_html_e( 'Please review them in your', 'wp-healthcheck' );
		}
		?>
		<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'plugins page', 'wp-healthcheck' ); ?></a>.
		<?php
		if ( count( $outdated_plugins ) === 1 ) {
			esc_html_e( 'This plugin may no longer be maintained or supported and may have security and/or compatibility issues when used with the most recent versions of WordPress.', 'wp-healthcheck' );
		} else {
			esc_html_e( 'These plugins may no longer be maintained or supported and may have security and/or compatibility issues when used with the most recent versions of WordPress.', 'wp-healthcheck' );
		}
		?>
	</p>
</div>
