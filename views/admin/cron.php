<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}
?>

<p>The WordPress Cron runs the scheduled events in your install. Some of the actions performed by WordPress Cron are: check for updates, publish scheduled posts, empty the trash, run themes and plugins scheduled events, etc.</p>

<p>Since the WordPress Cron runs only when your server receives HTTP or HTTPS requests, sometimes it can fail and not complete the tasks properly. Besides that, some bad coded themes and plugins can add a huge number of scheduled events and, therefore, decrease your site performance.</p>

<p>To avoid this situation, we highly recommend you to follow the instructions below to disable the native WordPress Cron and enable a Crontab job in their place.</p>

<?php if ( ! WP_Healthcheck::is_wpcron_disabled() ) : ?>
	<p>Add the following line to your wp-config.php:</p>

	<pre>define( 'DISABLE_WP_CRON', true );</pre>
<?php endif; ?>

<p>Add the following line to your crontab (usually available at /etc/crontab):</p>

<pre>*/5 * * * * <?php echo WP_Healthcheck::get_site_owner(); ?> wget -qO- <?php echo home_url(); ?>/wp-cron.php &amp;> /dev/null</pre>

<p>If you don't know what crontab is or how to use it, please send this request to your developers or to your hosting company. They will be able to easily enable it for you.</p>
