<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}
?>

<p><?php esc_html_e( 'WordPress transients are used to temporarily cache specific data. For example, developers often use them to improve their themes and plugins performance by caching database queries and script results.', 'wp-healthcheck' ); ?></p>

<p><?php esc_html_e( 'However, some badly coded plugins and themes can store too much information on these transients, or can even create an excessively high number of transients, resulting in performance degradation.', 'wp-healthcheck' ); ?></p>

<div id="wphc-transients-stats">
  <?php include 'transients-stats.php'; ?>
</div>

<div>
  <?php if ( wp_using_ext_object_cache() ) : ?>
    <button type="button" class="button" id="wphc-btn-transients-object">
      <?php _e( 'Flush Object Cache', 'wp-healthcheck' ); ?>
    </button>
  <?php else : ?>
    <button type="button" class="button" id="wphc-btn-transients-all">
      <?php _e( 'Clear All Transients', 'wp-healthcheck' ); ?>
    </button>
    <button type="button" class="button" id="wphc-btn-transients-expired">
      <?php _e( 'Clear Expired Transients', 'wp-healthcheck' ); ?>
    </button>

    <button class="wphc_help_btn button dashicons dashicons-editor-help" id="wphc-btn-transients-help"><span class="screen-reader-text"><?php _e( 'Help: Transients', 'wp-healthcheck' ); ?></span></button>
  <?php endif; ?>
</div>
