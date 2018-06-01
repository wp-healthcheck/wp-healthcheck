<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$plugins = WP_Healthcheck::get_outdated_plugins();

if ( ! $plugins || sizeof( $plugins ) == 0 ) {
    return false;
}
?>

<div class="notice wphc-notice wphc-notice-plugins notice-error is-dismissible">
  <p>
    <strong>WP Healthcheck:</strong>
    <?php
    /* translators: %s name of the outdated plugins */
    echo sprintf( __( 'We have found plugins that haven\'t been updated in over 2 years: %s.', 'wp-healthcheck' ), implode( ', ', array_keys( $plugins ) ) );
    ?>

    <br/><br/>
    <?php _e( 'Please review them in your', 'wp-healthcheck' ); ?>

    <a href="<?php echo admin_url( 'plugins.php' ); ?>"><?php _e( 'plugins page', 'wp-healthcheck' ); ?></a>.

    <?php _e( 'These plugins may no longer be maintained or supported and may have security and/or compatibility issues when used with the most recent versions of WordPress.', 'wp-healthcheck' ); ?>
  </p>
</div>
