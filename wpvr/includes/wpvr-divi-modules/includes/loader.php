<?php
/**
 * Checks and loads custom Divi Builder modules.
 *
 * This script checks if the base class 'ET_Builder_Element' exists to prevent errors.
 * and then dynamically loads PHP files for custom Divi modules from a specified directory.
 *
 * @package WPVR
 */

if ( ! class_exists( 'ET_Builder_Element' ) ) {
	return;
}
/**
 * Retrieve and load all module PHP files.
 *
 * Searches for PHP files within the 'modules' subdirectory and requires them to.
 * instantiate custom modules for the Divi Builder.
 */
$module_files = glob( __DIR__ . '/modules/*/*.php' );

// Load custom Divi Builder modules.
foreach ( (array) $module_files as $module_file ) {
	if ( $module_file ) {
		require_once $module_file;
	}
}
