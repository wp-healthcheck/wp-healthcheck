<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$server_data = wphc( 'server' )->get_data();

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
		<li><?php _e( 'WordPress', 'wp-healthcheck' ); ?></li>
		<li class="<?php echo wphc( 'server' )->is_updated( 'wp' ); ?>"><?php echo $server_data['wp']; ?></li>
	</ul>
	<ul>
		<li><?php _e( 'PHP', 'wp-healthcheck' ); ?></li>
		<li class="<?php echo wphc( 'server' )->is_updated( 'php' ); ?>"><?php echo $server_data['php']; ?></li>
	</ul>
	<ul>
		<?php if ( 'MariaDB' == $server_data['database']['service'] ) : ?>
			<li><?php _e( 'MariaDB', 'wp-healthcheck' ); ?></li>
		<?php else : ?>
			<li><?php _e( 'MySQL', 'wp-healthcheck' ); ?></li>
		<?php endif; ?>

		<li class="<?php echo wphc( 'server' )->is_updated( strtolower( $server_data['database']['service'] ) ); ?>"><?php echo $server_data['database']['version']; ?></li>
	</ul>

	<?php if ( ! empty( $web_server ) ) : ?>
		<ul>
			<li><?php _e( 'Web Server', 'wp-healthcheck' ); ?></li>

			<li class="<?php echo wphc( 'server' )->is_updated( $server_data['web']['service'] ); ?>"><?php echo $web_server; ?></li>
		</ul>
	<?php endif; ?>
</div>
