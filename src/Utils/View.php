<?php
/**
 * View utility class.
 *
 * Handles loading and rendering of view files.
 *
 * @package wp-healthcheck
 * @since 1.4.1
 */

namespace THSCD\WPHC\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class View.
 *
 * Centralized view management.
 *
 * @since 1.4.1
 */
class View {

	/**
	 * Base path for views.
	 *
	 * @since 1.4.1
	 *
	 * @var string
	 */
	private $base_path;

	/**
	 * Constructor.
	 *
	 * @since 1.4.1
	 */
	public function __construct() {

		$this->base_path = WPHC_PLUGIN_DIR . '/views/';
	}

	/**
	 * Render a view file.
	 *
	 * @since 1.4.1
	 *
	 * @param string $path Relative path to the view file (without .php extension).
	 * @param array  $data Optional. Data to pass to the view.
	 */
	public function render( $path, $data = [] ) {

		$file = $this->get_file_path( $path );

		if ( ! file_exists( $file ) ) {
			return;
		}

		// Extract data to make variables available in the view.
		if ( ! empty( $data ) ) {
			extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $file;
	}

	/**
	 * Get the full file path for a view.
	 *
	 * @since 1.4.1
	 *
	 * @param string $path Relative path to the view file (without .php extension).
	 *
	 * @return string The full file path.
	 */
	private function get_file_path( $path ) {

		return $this->base_path . $path . '.php';
	}
}
