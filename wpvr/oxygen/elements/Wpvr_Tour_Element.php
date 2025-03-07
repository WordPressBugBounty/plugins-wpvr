<?php
/**
 * WP VR Tour Element for Oxygen Builder
 *
 * Creates a custom element to display virtual reality tours created with WP VR plugin
 * within the Oxygen Builder interface.
 *
 * @package WPVR
 * @version 8.5.21
 * @subpackage Oxygen Integration
 */

class Wpvr_Tour_Element extends WPVR_CUSTOM_OXY_ELEMENT {

    /**
     * Returns the name of the element for Oxygen Builder
     *
     * @return string Element name
     */
    public function name() {
        return esc_html__('WP VR Tour', 'wpvr');
    }

    /**
     * Defines the UI controls for this element
     * 
     * Sets up all configuration options available to the user
     */
    public function controls() {
        // Get all published VR tours
        $posts = get_posts([
            'post_type'         => 'wpvr_item',
            'post_status'       => 'publish',
            'orderby'           => 'ID',
            'order'             => 'DESC',
            'numberposts'       => -1,
        ]);

        // Build the tour selection array
        $tour_options = [0 => __("None", "wpvr")];
        foreach ($posts as $post) {
            $id = $post->ID;
            $title = $post->post_title ? $post->post_title : __("No title", "wpvr");
            $tour_options[$id] = "{$id} : {$title}";
        }

        // Tour selection control
        $this->addOptionControl([
            "type"  => "dropdown",
            "name"  => __("Select Tour", "wpvr"),
            "slug"  => "tour_id",
            "value" => $tour_options
        ])->rebuildElementOnChange();

        // Width controls
        $this->addOptionControl([
            "type"      => "textfield",
            "name"      => __("Tour Width", "wpvr"),
            "slug"      => "tour_width",
            "value"     => "600",
            "condition" => "tour_width_fullwidth=off",
        ]);
        
        $this->addOptionControl([
            "type"      => "dropdown",
            "name"      => __("Tour Width Unit", "wpvr"),
            "slug"      => "tour_width_unit",
            "default"   => "px",
            "value"     => [
                'px' => __('px', "wpvr"),
                '%'  => __('%', "wpvr"),
                'vw' => __('vw', "wpvr"),
            ],
            "condition" => "tour_width_fullwidth=off",
        ])->rebuildElementOnChange();

        $this->addOptionControl([
            "type"      => "dropdown",
            "name"      => __("Tour Width Fullwidth", "wpvr"),
            "slug"      => "tour_width_fullwidth",
            "default"   => "off",
            "value"     => [
                'off' => __('OFF', "wpvr"),
                'on'  => __('ON', "wpvr"),
            ]
        ])->rebuildElementOnChange();

        // Height controls
        $this->addOptionControl([
            "type"  => "textfield",
            "name"  => __("Tour Height", "wpvr"),
            "slug"  => "tour_height",
            "value" => "400"
        ]);
        
        $this->addOptionControl([
            "type"    => "dropdown",
            "name"    => __("Tour Height Unit", "wpvr"),
            "slug"    => "tour_height_unit",
            "default" => "px",
            "value"   => [
                'px' => __('px', "wpvr"),
                'vh' => __('vh', "wpvr"),
            ]
        ])->rebuildElementOnChange();

        // Mobile-specific height controls
        $this->addOptionControl([
            "type"  => "textfield",
            "name"  => __("Tour Mobile Height", "wpvr"),
            "slug"  => "tour_mobile_height",
            "value" => "300"
        ])->setParam('description', __('Height on mobile devices. Leave empty to use the main height.', 'wpvr'));
        
        $this->addOptionControl([
            "type"    => "dropdown",
            "name"    => __("Tour Mobile Height Unit", "wpvr"),
            "slug"    => "tour_mobile_height_unit",
            "default" => "px",
            "value"   => [
                'px' => __('px', "wpvr"),
                'vh' => __('vh', "wpvr"),
            ]
        ])->rebuildElementOnChange();

        // Border radius control
        $this->addOptionControl([
            "type"  => "textfield",
            "name"  => __("Tour Radius", "wpvr"),
            "slug"  => "tour_radius",
            "value" => "0"
        ]);
        
        $this->addOptionControl([
            "type"    => "dropdown",
            "name"    => __("Tour Radius Unit", "wpvr"),
            "slug"    => "tour_radius_unit",
            "default" => "px",
            "value"   => [
                'px' => __('px', "wpvr"),
            ]
        ])->rebuildElementOnChange();
    }
    
    /**
     * Renders the element on the frontend
     *
     * @param array $options   Values set in the controls
     * @param array $defaults  Default values for all controls
     * @param array $content   Shortcode content for nested elements
     */
    public function render($options, $defaults, $content) {
        // Get and validate tour ID
        $id = isset($options['tour_id']) ? absint($options['tour_id']) : 0;
        
        if (!$id) {
            echo '<div class="wpvr-no-tour-selected">';
            echo esc_html__('Please select a WP VR Tour from the dropdown in the sidebar.', 'wpvr');
            echo '</div>';
            return;
        }

        // Process dimension settings
        $width = !empty($options['tour_width']) ? esc_attr($options['tour_width'] . $options['tour_width_unit']) : '600px';
        $height = !empty($options['tour_height']) ? esc_attr($options['tour_height'] . $options['tour_height_unit']) : '400px';
        $radius = !empty($options['tour_radius']) ? esc_attr($options['tour_radius'] . $options['tour_radius_unit']) : '0px';
        
        // Handle full width setting
        if (isset($options['tour_width_fullwidth']) && $options['tour_width_fullwidth'] === 'on') {
            $width = 'fullwidth';
        }
        
        // Process mobile height settings
        $mobile_height = !empty($options['tour_mobile_height']) ? 
            esc_attr($options['tour_mobile_height'] . $options['tour_mobile_height_unit']) : 
            '300px';

        // Construct and output the shortcode
        $shortcode = sprintf(
            '[wpvr id="%d" width="%s" height="%s" radius="%s" mobile_height="%s"]',
            $id,
            esc_attr($width),
            esc_attr($height),
            esc_attr($radius),
            esc_attr($mobile_height)
        );
        
        echo do_shortcode(shortcode_unautop($shortcode));
    }
}

// Initialize the element
new Wpvr_Tour_Element();


/**
 * Modify the text of specific translatable strings in WordPress.
 *
 * This function replaces "Apply Params" with "Save Changes" anywhere it appears 
 * in WordPress translations. It uses the `gettext` filter to intercept and modify 
 * translatable strings dynamically.
 *
 * @param string $translated_text The translated text.
 * @param string $text The original text.
 * @version 8.5.21
 * @param string $domain The text domain (used to identify different plugins/themes).
 * @return string Modified text if it matches; otherwise, returns the original text.
 */
function modify_wpvr_save_param_text($translated_text, $text, $domain) {
    if ($text === 'Apply Params') { // Check if the text matches the target phrase
        return 'Save Changes'; // Replace it with the desired text
    }
    return $translated_text; // Return unchanged text if no match is found
}

// Hook the function into the 'gettext' filter to modify translatable strings dynamically
add_filter('gettext', 'modify_wpvr_save_param_text', 10, 3);


