<?php
/**
 * Loads a view.
 *
 * @since 1.4.0
 *
 * @param string $name The view name.
 * @param array  $args The arguments to send to the view.
 */
function wphc_view( $name, $args = [] ) {

	$file = WPHC_PLUGIN_DIR . '/views/' . $name . '.php';

	if ( file_exists( $file ) ) {
		include $file;
	}
}
