<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

if ( wphc( 'module.ssl' )->is_available() ) {
	return false;
}
?>

<div class="notice wphc-notice wphc-notice-https notice-error is-dismissible">
	<p>
		<strong><?php esc_html_e( 'WP Healthcheck:', 'wp-healthcheck' ); ?></strong>
		<?php esc_html_e( 'Your site is not currently using HTTPS. This is insecure and can negatively impact your search engine rankings. Please contact your developer(s) and/or hosting company to enable HTTPS for you as soon as possible!', 'wp-healthcheck' ); ?>

		<strong><?php echo wp_kses_post( __( '<a href="https://letsencrypt.org/">Let\'s Encrypt</a> offers free SSL certificates!', 'wp-healthcheck' ) ); ?></strong>
	</p>
</div>
