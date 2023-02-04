<?php
namespace WPHC\Admin;

/**
 * The Admin\Loader class.
 *
 * @package wp-healthcheck
 * @since 1.4.0
 */
class Loader {
	/**
	 * The AJAX object.
	 *
	 * @since 1.4.0
	 *
	 * @var AJAX
	 */
	private $ajax;

	/**
	 * The Dashboard object.
	 *
	 * @since 1.4.0
	 *
	 * @var Dashboard
	 */
	private $dashboard;

	/**
	 * The Pointers object.
	 *
	 * @since 1.4.0
	 *
	 * @var Pointers
	 */
	private $pointers;

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		$this->setup();
	}

	/**
	 * Get the AJAX object.
	 *
	 * @since 1.4.0
	 *
	 * @return AJAX
	 */
	public function ajax() {

		return $this->ajax;
	}

	/**
	 * Get the Dashboard object.
	 *
	 * @since 1.4.0
	 *
	 * @return Dashboard
	 */
	public function dashboard() {

		return $this->dashboard;
	}

	/**
	 * Get the Pointers object.
	 *
	 * @since 1.4.0
	 *
	 * @return Pointers
	 */
	public function pointers() {

		return $this->pointers;
	}

	/**
	 * Set all the things up.
	 *
	 * @since 1.4.0
	 */
	public function setup() {

		$this->ajax      = new AJAX();
		$this->pointers  = new Pointers();
		$this->dashboard = new Dashboard();
	}
}
