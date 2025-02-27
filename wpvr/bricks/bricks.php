<?php
/**
 * WPVR Bricks Widget
 *
 * @package WPVR
 * @since 8.5.19
 */
namespace WpvrElement;

use WpvrElement\Elements\Wpvr\Wpvr_Widget;


if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Class Manager
 *
 * @package WpvrElement\Elements\Wpvr\Wpvr_Widget
 * @since 8.5.19
 */
class Manager {


    /**
     * Instance of the class
     *
     * @access private
     * @static
     *
     * @var Manager The single instance of the class.
     * @since 8.5.19
     */
    private static $instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @return Manager An instance of the class.
     * @since  8.5.19
     *
     * @access public
     * @static
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor for the class
     *
     * @since 8.5.19
     *
     * @access public
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }


    /**
     * Register checkout elements for bricks
     *
     * @since 8.5.19
     *
     * @access public
     */
    public function init() {
        $element_files = [
            WPVR_PLUGIN_DIR_PATH. 'bricks/Wpvr-widget.php'
        ];
        foreach ( $element_files as $file ) {
            \Bricks\Elements::register_element( $file, '', 'WpvrElement\Bricks\Wpvr\WpvrWidget' );
        }
    }

}

Manager::instance();