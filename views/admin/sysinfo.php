<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$server_data = WP_Healthcheck::get_server_data();
?>

<div class="wphc_system_info">
  <ul>
    <li><?php _e( 'WordPress', 'wp-healthcheck' ); ?></li>
    <li class="<?php echo WP_Healthcheck::is_software_updated( 'wp' ); ?>"><?php echo $server_data['wp']; ?></li>
  </ul>
  <ul>
    <li><?php _e( 'PHP', 'wp-healthcheck' ); ?></li>
    <li class="<?php echo WP_Healthcheck::is_software_updated( 'php' ); ?>"><?php echo $server_data['php']; ?></li>
  </ul>
  <ul>
    <?php if ( 'MariaDB' == $server_data['database']['service'] ) : ?>
      <li><?php _e( 'MariaDB', 'wp-healthcheck' ); ?></li>
    <?php else : ?>
      <li><?php _e( 'MySQL', 'wp-healthcheck' ); ?></li>
    <?php endif; ?>
    <li class="<?php echo WP_Healthcheck::is_software_updated( strtolower( $server_data['database']['service'] ) ); ?>"><?php echo $server_data['database']['version']; ?></li>
  </ul>

  <?php if ( ! empty( $server_data['web'] ) ) : ?>
    <ul>
      <li><?php _e( 'Web Server', 'wp-healthcheck' ); ?></li>
      <li><?php echo $server_data['web']; ?></li>
    </ul>
  <?php endif; ?>
</div>
