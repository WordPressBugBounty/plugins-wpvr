<?php
/**
 * WPVR Bricks Widget
 *
 * @package WPVR
 * @since 8.5.19
 */
namespace WpvrElement\Bricks\Wpvr;

require_once get_template_directory() . '/includes/elements/base.php';

use \Bricks\Element;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WpvrWidget
 *
 * @package WpvrElement\Bricks\Wpvr
 * @since 8.5.19
 */
class WpvrWidget extends Element {

    /**
     * Category under which the WPVR element will appear in Bricks Builder.
     *
     * @since 8.5.19
     * @var string
     */
    public $category = 'wpvr';

    /**
     * Unique name/identifier for the WPVR element in Bricks Builder.
     *
     * @since 8.5.19
     * @var string
     */
    public $name = 'wpvr';

    /**
     * Font Awesome icon class for the WPVR element in the Bricks Builder panel.
     *
     * @since 8.5.19
     * @var string
     */
    public $icon = 'fa-solid fa-vr-cardboard';
//    public $icon = 'wpvr-custom-icon';

    /**
     * Custom CSS selector for styling the WPVR element in Bricks Builder.
     *
     * @since 8.5.19
     * @var string
     */
    public $css_selector = '.wpvr-bricks-icon';

    /**
     * Scripts required for the WPVR element in Bricks Builder.
     *
     * @since 8.5.19
     * @var array
     */
    public $scripts = [];


    /**
     * Return localised element label
     *
     * @return string
     * @since 8.5.19
     */
    public function get_label()
    {
        return esc_html__('WPVR', 'wpvr');
    }

    /**
     * Set builder control groups
     *
     * @since 8.5.19
     */
    public function set_control_groups()
    {
        $this->control_groups['wpvr'] = [
            'title' => esc_html__('WPVR', 'wpvr'),
            'tab' => 'content',
        ];
    }

    /**
     * Set builder controls
     *
     * @since 8.5.19
     */
    /**
     * Set controls for the WPVR Bricks Builder element.
     *
     * @since 8.5.19
     * @return void
     */
    public function set_controls()
    {
        $common_number_settings = [
            'tab'         => 'content',
            'group'       => 'wpvr',
            'type'        => 'number',
            'min'         => 0,
            'max'         => 2000,
            'step'        => 1,
            'validation'  => 'numeric',
            'class'       => 'wpvr-number-field-width',
        ];

        $this->controls['select_wpvr'] = [
            'tab'         => 'content',
            'group'       => 'wpvr',
            'label'       => esc_html__('Select Tour', 'wpvr'),
            'type'        => 'select',
            'options'     => $this->getPublishedTour(),
            'inline'      => true,
            'clearable'   => false,
            'pasteStyles' => false,
            'default'     => '',
            'width'       => '100%',
        ];

        $this->controls['vr_width'] = array_merge($common_number_settings, [
            'label'       => esc_html__('Width(px)', 'wpvr'),
            'default'     => 600,
        ]);

        $this->controls['vr_height'] = array_merge($common_number_settings, [
            'label'       => esc_html__('Height(px)', 'wpvr'),
            'default'     => 400,
        ]);

        $this->controls['vr_radius'] = array_merge($common_number_settings, [
            'label'       => esc_html__('Radius(px)', 'wpvr'),
            'default'     => 0,
            'max'         => 1000,
        ]);
    }


    /**
     * Render the WPVR tour shortcode or a placeholder message in Bricks Builder editor.
     *
     * @since 8.5.19
     */
    public function render() {
        // Retrieve settings with default fallbacks
        $settings = $this->settings ?? [];
        $id       = $settings['select_wpvr'] ?? 0;
        $width    = ($settings['vr_width'] ?? 600) . 'px';
        $height   = ($settings['vr_height'] ?? 400) . 'px';
        $radius   = ($settings['vr_radius'] ?? 0) . 'px';

        // Check if a valid tour ID is available
        if ($id) {
            // Prevent rendering preview in Bricks Builder editor or during REST API calls
            if ((function_exists('bricks_is_builder') && bricks_is_builder()) ||
                (function_exists('bricks_is_builder_main') && bricks_is_builder_main()) ||
                (function_exists('bricks_is_rest_call') && bricks_is_rest_call())
            ) {
                echo '<p>' . esc_html__('Bricks Editor Mode - WPVR Preview is not available.', 'wpvr') . '</p>';
                return;
            }

            // Render the WPVR tour using the shortcode
            echo do_shortcode('[wpvr id="' . esc_attr($id) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" radius="' . esc_attr($radius) . '"]');
        } else {
            // Display message when no tour is selected
            echo '<p>' . esc_html__('No tour has been selected.', 'wpvr') . '</p>';
        }
    }


    /**
     * Get all published tours
     *
     * @since 8.5.19
     *
     * @access public
     */
    public function getPublishedTour() {
        $posts = get_posts([
            'post_type'   => 'wpvr_item',
            'post_status' => 'publish',
            'orderby'     => 'ID',
            'order'       => 'DESC',
            'numberposts' => -1,
            'fields'      => 'ids',
        ]);

        foreach ($posts as $id) {
            $title = get_the_title($id) ?: 'No title';
            $tours[$id] = "{$title} (ID: {$id})";
        }
        return $tours;
    }

}

