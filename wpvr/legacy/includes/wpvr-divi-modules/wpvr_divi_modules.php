<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/includes
 */

namespace WPVR\Builder\DIVI;

use WPVR\Builder\DIVI\Modules\WPVR_Modules;

/**
 * Manages the singleton instance for DIVI modules integration and initialization.
 *
 * This class handles the setup and initialization of WPVR modules within the DIVI theme.
 * It ensures a single instance of this class is created (singleton pattern).
 *
 * @since 8.4.8
 */
class WPVR_Divi_modules { //phpcs:ignore

	/**
	 * Holds the class instance.
	 *
	 * @var WPVR_Divi_modules|null The single instance of the class.
	 */
	private static $_instance = null; //phpcs:ignore

	/**
	 * Provides a single instance of this class.
	 *
	 * If the instance does not exist yet, it creates one and returns it. If it exists, it simply returns it.
	 * This method ensures that WPVR_Divi_modules can only have one instance.
	 *
	 * @return WPVR_Divi_modules Returns the single instance of this class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * The class constructor.
	 *
	 * Private to prevent creating multiple instances directly.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * The class constructor.
	 *
	 * Private to prevent creating multiple instances directly.
	 */
	private function init() {
		add_action( 'divi_extensions_init', array( $this, 'wpvr_initialize_extension' ) );
	}

	/**
	 * Initializes the DIVI module extension.
	 *
	 * Registers action hooks necessary for the extension setup, specifically
	 * initializing the DIVI extension when appropriate.
	 */
	public function wpvr_initialize_extension() {
		new WPVR_Modules();
	}
}
