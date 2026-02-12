<?php
/**
 * Setup wizard for the plugin
 *
 * @package ''
 * @since 8.4.10
 */

class WPVR_Setup_Wizard
{

    /**
     * Initialize setup wizards
     *
     * @since 8.4.10
     */
    public function setup_wizard()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        $this->output_html();
    }

    public function enqueue_scripts($hook) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'rex-wpvr-setup-wizard') {
            return;
        }
        wp_enqueue_media();

        wp_enqueue_style(
            'wpvr-setup-wizard',
            WPVR_CSS_PATH . 'setup-wizard.css',
            [],
            WPVR_VERSION
        );
        wp_enqueue_style(
            'wpvr-pannellum-css',
            WPVR_PLUGIN_DIR_URL . 'admin/lib/pannellum/src/css/pannellum.css',
            [],
            WPVR_VERSION
        );

        wp_enqueue_script(
            'wpvr-libpannellum-js',
            WPVR_PLUGIN_DIR_URL . 'admin/lib/pannellum/src/js/libpannellum.js',
            ['jquery'],
            WPVR_VERSION,
            true
        );
        wp_enqueue_script(
            'wpvr-pannellum-js',
            WPVR_PLUGIN_DIR_URL . 'admin/lib/pannellum/src/js/pannellum.js',
            ['jquery'],
            WPVR_VERSION,
            true
        );
        wp_enqueue_script(
            'wpvr-onboarding-js',
            WPVR_PLUGIN_DIR_URL . 'admin/lib/onboarding/js/onboarding.js',
            ['jquery'],
            WPVR_VERSION,
            true
        );
        wp_enqueue_script(
            'wpvr-setup-wizard',
            WPVR_JS_PATH . 'setup-wizard.js',
            ['jquery'],
            WPVR_VERSION,
            true
        );
    }

    /**
     * Output the rendered contents
     *
     * @since 8.4.10
     */
    private function output_html()
    {
        require_once plugin_dir_path(__FILE__) . '../admin/partials/wpvr-setup-wizard-views.php';
        exit();
    }
}
