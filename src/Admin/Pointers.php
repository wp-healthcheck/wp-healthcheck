<?php
namespace WPHC\Admin;

/**
 * The Pointers class.
 *
 * @package wp-healthcheck
 *
 * @since 1.0.0
 */
class Pointers {
	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'load_resources' ], 5 );
		add_action( 'admin_print_footer_scripts', [ $this, 'enqueue_pointers' ] );
	}

	/**
	 * Loads the resources.
	 *
	 * @since 1.0.0
	 */
	public function load_resources() {
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );
	}

	/**
	 * Enqueue the admin pointers.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_pointers() {
		include WPHC_PLUGIN_DIR . '/views/admin/help.php';

		$pointers = array(
			array(
				'title'   => $transients_help['title'],
				'content' => $transients_help['content'],
				'target'  => 'wphc-btn-transients-help',
			),
			array(
				'title'   => $autoload_help['title'],
				'content' => $autoload_help['content'],
				'target'  => 'wphc-btn-autoload-help',
			),
		);

		$js = '';

		foreach ( $pointers as $pointer ) {
			$js .= sprintf( 'wphc_do_pointer("%1$s", "%2$s", "%3$s");',
				$pointer['title'], $pointer['content'], $pointer['target'] );
		}

		echo '<script type="text/javascript">' . $js . '</script>';
	}
}
