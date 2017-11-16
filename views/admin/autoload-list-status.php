<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

if ( ! WP_Healthcheck_AJAX::is_doing_ajax() || ! isset( $options ) ) {
    return;
}

$fail = array_filter( $options, function( $k ) {
    return false === $k;
} );

$success = array_filter( $options, function( $k ) {
    return false !== $k;
} );
?>

<?php if ( sizeof( $success ) == 1 ) : ?>
  <p class="wphc_autoload_status wphc_success">
    <?php
    /* translators: %1$s is the option name */
    echo sprintf( __( 'Yay, the <strong>%1$s</strong> option was deactivated successfully.', 'wp-healthcheck' ), key( $success ) );
    ?>
  </p>
<?php elseif ( sizeof( $success ) > 1 ) : ?>
  <p class="wphc_autoload_status wphc_success">
    <?php _e( 'Yay, the below options were deactivated successfully:', 'wp-healthcheck' ); ?><br/><br/>

    <?php foreach ( $success as $name => $value ) : ?>
      - <strong><?php echo $name; ?></strong><br/>
    <?php endforeach; ?>
  </p>
<?php endif; ?>

<?php if ( sizeof( $fail ) > 0 ) : ?>
  <?php foreach ( $fail as $name => $value ) : ?>
    <p class="wphc_autoload_status wphc_error">
      <?php
      /* translators: %1$s is the option name */
      echo sprintf( __( 'Oops, for some reason we couldn\'t deactivate the <strong>%1$s</strong> option.', 'wp-healthcheck' ), $name );
      ?>
    </p>
  <?php endforeach; ?>
<?php endif; ?>
