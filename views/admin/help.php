<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$transients_help = [
	'title'   => __( 'Cleaning Up Transients', 'wp-healthcheck' ),
	'content' => __( 'You don\'t have to be afraid! Cleaning up transients won\'t affect your site functionality.<br/><br/>In fact, plugins, themes, and WordPress itself will recreate them according to their needs.', 'wp-healthcheck' ),
];

$autoload_help = [
	'title'   => __( 'Deactivating An Autoload Option', 'wp-healthcheck' ),
	'content' => __( 'No worries, when you deactivate an autoload option, you are not removing it.<br/></br>You are just telling WordPress to not load that option automatically on every request it does.<br/><br/>In other words, the option will be loaded only when it is needed.', 'wp-healthcheck' ),
];
