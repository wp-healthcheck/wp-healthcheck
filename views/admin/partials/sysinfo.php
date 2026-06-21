<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$server_data = wphc( 'module.server' )->get_data();

if ( ! empty( $server_data['web']['version'] ) && ! empty( $server_data['web']['service'] ) ) {
	if ( preg_match( '/(?:nginx|apache)/', $server_data['web']['service'] ) ) {
		$web_server = $server_data['web']['service'] . '/' . $server_data['web']['version'];
	} else {
		$web_server = $server_data['web']['version'];
	}
}
?>

<div class="wphc_system_info">
	<ul>
		<li><?php esc_html_e( 'WordPress', 'wp-healthcheck' ); ?></li>
		<li class="<?php echo esc_attr( wphc( 'module.server' )->is_updated( 'wp' ) ); ?>"><?php echo esc_html( $server_data['wp'] ); ?></li>
	</ul>
	<ul>
		<li><?php esc_html_e( 'PHP', 'wp-healthcheck' ); ?></li>
		<li class="<?php echo esc_attr( wphc( 'module.server' )->is_updated( 'php' ) ); ?>"><?php echo esc_html( $server_data['php'] ); ?></li>
	</ul>
	<ul>
		<?php if ( $server_data['database']['service'] === 'MariaDB' ) : ?>
			<li><?php esc_html_e( 'MariaDB', 'wp-healthcheck' ); ?></li>
		<?php else : ?>
			<li><?php esc_html_e( 'MySQL', 'wp-healthcheck' ); ?></li>
		<?php endif; ?>

		<li class="<?php echo esc_attr( wphc( 'module.server' )->is_updated( strtolower( $server_data['database']['service'] ) ) ); ?>"><?php echo esc_html( $server_data['database']['version'] ); ?></li>
	</ul>

	<?php if ( ! empty( $web_server ) ) : ?>
		<ul>
			<li><?php esc_html_e( 'Web Server', 'wp-healthcheck' ); ?></li>

			<li class="<?php echo esc_attr( wphc( 'module.server' )->is_updated( $server_data['web']['service'] ) ); ?>"><?php echo esc_html( $web_server ); ?></li>
		</ul>
	<?php endif; ?>
</div>
