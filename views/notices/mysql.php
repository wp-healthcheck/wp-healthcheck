<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$mysql_status = wphc( 'module.server' )->is_updated( 'mysql' );

if ( $mysql_status === 'updated' || $mysql_status === false ) {
	return false;
}

$requirements = wphc( 'module.server' )->get_requirements();
$server_data  = wphc( 'module.server' )->get_data();

$messages = [
	'outdated' => [
		'class'   => 'notice-warning is-dismissible',
		/* translators: %1$s is the MySQL version installed on server, %2$s is the one WP team recommends. */
		'message' => sprintf( __( 'Your MySQL version (%1$s) is compatible with the current WordPress install. However, the <a href="https://wordpress.org/about/requirements/">WordPress team recommends</a> you upgrade your server to MySQL %2$s or greater.', 'wp-healthcheck' ), $server_data['mysql'], $requirements['mysql']['recommended'] ),
	],
	'obsolete' => [
		'class'   => 'notice-error is-dismissible',
		/* translators: %1$s is the MySQL version installed on server, %2$s is the one WP team recommends. */
		'message' => sprintf( __( 'The MySQL version you are using (%1$s) is not supported by WordPress anymore! Please contact your developers and/or hosting company to upgrade your MySQL to version %2$s or greater.', 'wp-healthcheck' ), $server_data['mysql'], $requirements['mysql']['recommended'] ),
	],
];
?>

<div class="notice wphc-notice wphc-notice-mysql <?php echo esc_attr( $messages[ $mysql_status ]['class'] ); ?>">
	<p>
		<strong><?php esc_html_e( 'WP Healthcheck:', 'wp-healthcheck' ); ?></strong>
		<?php echo wp_kses_post( $messages[ $mysql_status ]['message'] ); ?>
	</p>
</div>
