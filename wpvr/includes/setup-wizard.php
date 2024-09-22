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
        $this->output_html();
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
