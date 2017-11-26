<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}
?>

<p>
    <?php
    /* translators: %1$s is the URL to our community forum */
    echo sprintf( __( 'If you have any questions, you can post a new thread in our <a href="%1$s">Community Forum</a>, available on WordPress.org. We review it weekly and our team will be happy to assist you there.', 'wp-healthcheck' ), 'https://wordpress.org/support/plugin/wp-healthcheck' );
    ?>
</p>

<p><?php _e( '<strong>Looking for professional services?</strong> We can help you to review your server configuration, improve your site performance, or even build a high-performance server from scratch to host your sites.', 'wp-healthcheck' ); ?></p>

<p>
    <?php
    /* translators: %1$s is the URL to our premium services page */
    echo sprintf( __( 'You can read more details about our services and hire us through our <a href="%1$s">Premium Services</a> area.', 'wp-healthcheck' ), 'https://wp-healthcheck.com/services' );
    ?>
</p>

<div class="wphc_support_btns">
    <a href="https://wp-healthcheck.com" class="button"><span class="wphc-icons-home"></span> <?php _e( 'Home Page', 'wp-healthcheck' ); ?></a>&nbsp;
    <a href="https://wp-healthcheck.com/contributors" class="button"><span class="wphc-icons-contributors"></span> <?php _e( 'Contributors', 'wp-healthcheck' ); ?></a>&nbsp;
    <a href="https://github.com/wp-healthcheck/wp-healthcheck/issues" class="button"><span class="wphc-icons-bug-report"></span> <?php _e( 'Report a Bug', 'wp-healthcheck' ); ?></a>
</div>
