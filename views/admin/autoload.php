<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}
?>

<p><?php esc_html_e( 'WordPress autoload options are very similar to transients. The main difference is: transients are used to store temporary data, while options are used to store permanent data.', 'wp-healthcheck' ); ?></p>

<p><?php esc_html_e( 'All the autoload options, as well as transients, are loaded automatically when WordPress loads itself. Thus, the number and size of these options can directly affect your site performance.', 'wp-healthcheck' ); ?></p>

<div id="wphc-autoload-stats">
  <?php include 'autoload-stats.php'; ?>
</div>
