<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}
?>

<div class="wrap">
    <h2>WP Healthcheck</h2>

    <p><?php esc_html_e( 'Welcome and thank you for choosing the WP Healthcheck plugin.', 'wp-healthcheck' ); ?></p>

    <?php require_once 'sysinfo.php'; ?>

    <div id="poststuff">
        <div id="post-body" class="postbox-container">
            <?php WP_Healthcheck_Admin::do_meta_boxes(); ?>
        </div>
    </div>

    <?php
    wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
    ?>
</div>
