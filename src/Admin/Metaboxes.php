<?php
/**
 * Meta boxes handler.
 *
 * Handles the registration and rendering of meta boxes.
 *
 * @package wp-healthcheck
 * @since   {VERSION}
 */

namespace THSCD\WPHC\Admin;

/**
 * Class Metaboxes.
 *
 * Manages meta boxes for the admin page.
 *
 * @since {VERSION}
 */
class Metaboxes {

	/**
	 * Register meta boxes.
	 *
	 * @since {VERSION}
	 */
	public function register() {

		$hookname = wphc( 'admin.dashboard' )->get_hookname();

		$metaboxes = [
			'wphc-transients' => __( 'Transients', 'wp-healthcheck' ),
			'wphc-autoload'   => __( 'Autoload Options', 'wp-healthcheck' ),
			'wphc-wp-updates' => __( 'WordPress Automatic Background Updates', 'wp-healthcheck' ),
			'wphc-support'    => __( 'Support &amp; Services', 'wp-healthcheck' ),
		];

		foreach ( $metaboxes as $id => $title ) {
			$args = [
				'name' => 'admin/metaboxes/' . preg_replace( '/^wphc-/', '', $id ),
			];

			add_meta_box(
				$id,
				$title,
				[ $this, 'render' ],
				$hookname,
				'normal',
				'default',
				$args
			);
		}
	}

	/**
	 * Render a meta box.
	 *
	 * @since {VERSION}
	 *
	 * @param mixed $post    Not used (required by WordPress).
	 * @param array $metabox The metabox data.
	 */
	public function render( $post, $metabox ) {

		if ( isset( $metabox['args']['name'] ) ) {
			wphc( 'util.view' )->render( $metabox['args']['name'] );
		}
	}

	/**
	 * Output meta boxes for the current page.
	 *
	 * @since {VERSION}
	 */
	public function output() {

		$hookname = wphc( 'admin.dashboard' )->get_hookname();

		do_meta_boxes( $hookname, 'normal', null );
	}
}
