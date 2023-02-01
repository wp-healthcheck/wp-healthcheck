<?php
namespace WPHC\Admin;

/**
 * The WP_Healthcheck_Pointers class
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Pointers {
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Initialize the WordPress hooks.
	 *
	 * @since 1.0
	 */
	public function hooks() {
		add_action( 'admin_init', [ $this, 'load_resources' ], 5 );
		add_action( 'admin_print_footer_scripts', [ $this, 'enqueue_pointers' ] );
	}

	/**
	 * Loads the resources.
	 *
	 * @since 1.0
	 */
	public function load_resources() {
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );
	}

	/**
	 * Enqueue the admin pointers.
	 *
	 * @since 1.0
	 */
	public function enqueue_pointers() {
		$pointers = [
			[
				'title'   => __( 'Cleaning Up Transients', 'wp-healthcheck' ),
				'content' => __( 'You don\'t have to be afraid! Cleaning up transients won\'t affect your site functionality.<br/><br/>In fact, plugins, themes, and WordPress itself will recreate them according to their needs.', 'wp-healthcheck' ),
				'target'  => 'wphc-btn-transients-help',
			],
			[
				'title'   => __( 'Deactivating An Autoload Option', 'wp-healthcheck' ),
				'content' => __( 'No worries, when you deactivate an autoload option, you are not removing it.<br/></br>You are just telling WordPress to not load that option automatically on every request it does.<br/><br/>In other words, the option will be loaded only when it is needed.', 'wp-healthcheck' ),
				'target'  => 'wphc-btn-autoload-help',
			],
		];

		$js = '';

		foreach ( $pointers as $pointer ) {
			$js .= sprintf( 'wphc_do_pointer("%1$s", "%2$s", "%3$s");', $pointer['title'], $pointer['content'], $pointer['target'] );
		}

		echo '<script type="text/javascript">' . $js . '</script>';
	}
}
