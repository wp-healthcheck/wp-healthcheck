<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$opts = WP_Healthcheck::get_autoload_history();
?>

<div class="wphc_autoload_list">
  <ul>
    <li><?php _e( 'Name', 'wp-healthcheck' ); ?></li>
    <li><?php _e( 'Deactivation Time', 'wp-healthcheck' ); ?></li>
  </ul>

  <?php foreach ( $opts as $name => $timestamp ) : ?>
    <?php if ( get_option( $name ) ) : ?>
      <ul>
        <li class="wphc-col-name"><?php echo $name; ?></li>
        <li><?php echo date( 'Y-m-d H:i:s', $timestamp ); ?></li>
      </ul>
    <?php endif; ?>
  <?php endforeach; ?>
</div>

<div>
  <button type="submit" class="button button-small" id="wphc-btn-autoload-close"><?php _e( 'Close', 'wp-healthcheck' ); ?></button></div>
</div>
