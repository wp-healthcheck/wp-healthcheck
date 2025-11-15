<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$requirements = wphc( 'module.server' )->get_requirements();
$server_data  = wphc( 'module.server' )->get_data();

$db_service = $server_data['database']['service'];
$db_status  = wphc( 'module.server' )->is_updated( strtolower( $db_service ) );

if ( $db_status === 'updated' || $db_status === false ) {
	return false;
}

$messages = [
	'outdated' => [
		'class'   => 'notice-warning is-dismissible',
		/* translators: %1$s is the current database service (MySQL, MariaDB, etc), %2$s is the database version installed on server, %3$s is the version that WP team recommends. */
		'message' => sprintf( __( 'Your %1$s version (%2$s) is compatible with the current WordPress install. However, the <a href="https://wordpress.org/about/requirements/">WordPress team recommends</a> you upgrade your server to %3$s or greater.', 'wp-healthcheck' ), $db_service, $server_data['database']['version'], $requirements[ strtolower( $db_service ) ]['recommended'] ),
	],
	'obsolete' => [
		'class'   => 'notice-error is-dismissible',
		/* translators: %1$s is the current database service (MySQL, MariaDB, etc), %2$s is the database version installed on server, %3$s is the version that WP team recommends. */
		'message' => sprintf( __( 'The %1$s version you are using (%2$s) is not supported by WordPress anymore! Please contact your developers and/or hosting company to upgrade your %1$s to version %3$s or greater.', 'wp-healthcheck' ), $db_service, $server_data['database']['version'], $requirements[ strtolower( $db_service ) ]['recommended'] ),
	],
];
?>

<div class="notice wphc-notice wphc-notice-database <?php echo esc_attr( $messages[ $db_status ]['class'] ); ?>">
	<p>
		<strong><?php esc_html_e( 'WP Healthcheck:', 'wp-healthcheck' ); ?></strong>
		<?php echo wp_kses_post( $messages[ $db_status ]['message'] ); ?>
	</p>
</div>
