<?php
/**
 * Admin pointers handler.
 *
 * Handles WordPress admin pointers for help tooltips.
 *
 * @package wp-healthcheck
 * @since   {VERSION}
 */

namespace THSCD\WPHC\Admin;

/**
 * Class Pointers.
 *
 * Manages admin pointers functionality.
 */
class Pointers {

	/**
	 * Whether hooks have been initialized.
	 *
	 * @since {VERSION}
	 *
	 * @var bool
	 */
	private $initiated = false;

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @since {VERSION}
	 */
	private function hooks() {

		if ( $this->initiated ) {
			return;
		}

		$this->initiated = true;

		add_action( 'admin_menu', [ $this, 'setup_hooks' ], 20 );
	}

	/**
	 * Setup hooks after menu is registered.
	 *
	 * @since {VERSION}
	 */
	public function setup_hooks() {

		$dashboard = wphc( 'admin.dashboard' );
		$hookname  = $dashboard->get_hookname();

		if ( ! $hookname ) {
			return;
		}

		add_action( 'load-' . $hookname, [ $this, 'load_resources' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'enqueue_pointers' ] );
	}

	/**
	 * Load pointer resources.
	 *
	 * @since {VERSION}
	 */
	public function load_resources() {

		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );
	}

	/**
	 * Enqueue admin pointers.
	 *
	 * @since {VERSION}
	 */
	public function enqueue_pointers() {

		$screen = get_current_screen();

		if ( ! $screen || 'toplevel_page_wp-healthcheck' !== $screen->id ) {
			return;
		}
		include WPHC_PLUGIN_DIR . '/views/admin/help.php';

		$pointers = [
			[
				'title'   => $transients_help['title'],
				'content' => $transients_help['content'],
				'target'  => 'wphc-btn-transients-help',
			],
			[
				'title'   => $autoload_help['title'],
				'content' => $autoload_help['content'],
				'target'  => 'wphc-btn-autoload-help',
			],
		];

		$js = '';

		foreach ( $pointers as $pointer ) {
			$js .= sprintf(
				'wphc_do_pointer("%1$s", "%2$s", "%3$s");',
				$pointer['title'],
				$pointer['content'],
				$pointer['target']
			);
		}

		echo '<script type="text/javascript">' . $js . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

