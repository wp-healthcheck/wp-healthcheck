<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

if ( WP_Healthcheck::is_ssl_available() ) {
    return false;
}
?>

<div class="notice wphc-notice wphc-notice-https notice-error is-dismissible">
  <p>
    <strong>WP Healthcheck:</strong>
    <?php _e( 'Your site is not currently using HTTPS. This is insecure and can negatively impact your search engine rankings. Please contact your developer(s) and/or hosting company to enable HTTPS for you as soon as possible!', 'wp-healthcheck' ); ?>

    <strong><?php _e( '<a href="https://letsencrypt.org/">Let\'s Encrypt</a> offers free SSL certificates!', 'wp-healthcheck' ); ?></strong>
  </p>
</div>
