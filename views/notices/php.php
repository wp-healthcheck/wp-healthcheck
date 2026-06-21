<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$php_status = wphc( 'module.server' )->is_updated( 'php' );

if ( $php_status === 'updated' || $php_status === false ) {
	return false;
}

$requirements = wphc( 'module.server' )->get_requirements();
$server_data  = wphc( 'module.server' )->get_data();

$messages = [
	'outdated' => [
		'class'   => 'notice-warning is-dismissible',
		/* translators: %1$s is the PHP version installed on current server, %2$s is the one WP team recommends. */
		'message' => sprintf( __( 'Your PHP version (%1$s) is compatible with the current WordPress install. However, in order to get better performance and other improvements, the <a href="https://wordpress.org/about/requirements/">WordPress team recommends</a> you upgrade your server to PHP version %2$s or greater.', 'wp-healthcheck' ), $server_data['php'], $requirements['php']['recommended'] ),
	],
	'obsolete' => [
		'class'   => 'notice-error is-dismissible',
		/* translators: %1$s is the PHP version installed on current server, %2$s is the one WP team recommends. */
		'message' => sprintf( __( 'The PHP version you are using (%1$s) is not supported by WordPress anymore! Please contact your developers and/or hosting company to upgrade your PHP to version %2$s or greater.', 'wp-healthcheck' ), $server_data['php'], $requirements['php']['recommended'] ),
	],
];
?>

<div class="notice wphc-notice wphc-notice-php <?php echo esc_attr( $messages[ $php_status ]['class'] ); ?>">
	<p>
		<strong><?php esc_html_e( 'WP Healthcheck:', 'wp-healthcheck' ); ?></strong>
		<?php echo wp_kses_post( $messages[ $php_status ]['message'] ); ?>
	</p>
</div>
