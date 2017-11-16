<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$transients = WP_Healthcheck::get_transients_stats();
?>

<?php if ( ! wp_using_ext_object_cache() ) : ?>
  <div class="wphc_stats">
    <ul>
      <li><?php _e( 'Total:', 'wp-healthcheck' ); ?></li>
      <li><?php echo $transients['count']; ?></li>
    </ul>
    <ul>
      <li><?php _e( 'Size:', 'wp-healthcheck' ); ?></li>
      <li><?php echo number_format( $transients['size'], 2 ); ?>MB</li>
    </ul>
  </div>
<?php endif; ?>

<?php if ( WP_Healthcheck_AJAX::is_doing_ajax() ) : ?>
  <?php $message = ( $object_cache ) ? __( 'object cache items', 'wp-healthcheck' ) : __( 'transients', 'wp-healthcheck' ); ?>

  <?php if ( isset( $cleanup ) && false !== $cleanup ) : ?>
    <p class="wphc_internal_notice wphc_success">
      <?php
      /* translators: %1$s is 'transients' or 'object cache items' */
      echo sprintf( __( 'Yay! The %1$s were cleaned up successfully.', 'wp-healthcheck' ), $message );
      ?>
    </p>
  <?php else : ?>
    <p class="wphc_internal_notice wphc_error">
      <?php
      /* translators: %1$s is 'transients' or 'object cache items' */
      echo sprintf( __( 'Oops, for some reason we couldn\'t clean up your %1$s.', 'wp-healthcheck' ), $message );
      ?>
    </p>
  <?php endif; ?>
<?php endif; ?>
