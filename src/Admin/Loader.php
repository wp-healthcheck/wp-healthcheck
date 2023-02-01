<?php
namespace WPHC\Admin;

/**
 * The Admin\Loader class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Loader {
	private $ajax;
	private $dashboard;
	private $pointers;

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {
		$this->setup();
	}

	public function ajax() {
		return $this->ajax;
	}

	public function dashboard() {
		return $this->dashboard;
	}

	public function pointers() {
		return $this->pointers;
	}

	/**
	 * Set all the things up.
	 *
	 * @since 1.4.0
	 */
	public function setup() {
		$this->dashboard = new Dashboard();
		$this->pointers  = new Pointers();
		$this->ajax      = new AJAX();
	}
}
