<?php
/**
 * DIVI module Loader.
 *
 * This script checks if the base class 'ET_Builder_Element' exists to prevent errors.
 * and then dynamically loads PHP files for custom Divi modules from a specified directory.
 *
 * @package WPVR
 */

namespace WPVR\Builder\DIVI\Modules;

use DiviExtension;


/**
 * WPVR_Modules class extends DiviExtension to implement custom modules for WPVR.
 *
 * This class handles the initialization and setup of WPVR modules within the Divi Builder
 * environment, providing custom functionalities specific to WPVR.
 *
 * @since 1.0.0
 */
class WPVR_Modules extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 * This is used for internationalization and localization.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $gettext_domain = 'wpvr';

	/**
	 * The extension's WP Plugin name.
	 * It is used internally to reference the plugin in various contexts.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $name = 'wpvr-divi-modules';

	/**
	 * The version of the extension.
	 * This can be used to manage updates, compatibility checks, and more.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Constructor for WPVR_Modules.
	 * Initializes the plugin's directory paths and sets up the necessary properties for the extension.
	 *
	 * @since 1.0.0
	 * @param string $name Optional. The name of the plugin. Default 'wpvr-divi-modules'.
	 * @param array  $args Optional. Additional arguments or configurations.
	 */
	public function __construct( $name = 'wpvr-divi-modules', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );
		parent::__construct( $name, $args );
	}
}
