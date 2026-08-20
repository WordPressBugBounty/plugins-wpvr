<?php
/**
 * WPVR New User Guided Tour
 *
 * Automatically launches the Shepherd.js guided tour the first time a
 * brand-new user opens the "Add New Virtual Tour" editor page.
 *
 * Visibility rules (ALL must be true):
 *  1. No real (non-demo) wpvr_item has ever been created on this site.
 *     Tracked via `wpvr_has_ever_created_tour` (set by WPVR_Onboarding_Notice backfill).
 *  2. The setup wizard has NOT been completed (`wpvr_wizard_onboarding_done`).
 *  3. The guided tour has NOT been permanently dismissed
 *     (`wpvr_new_user_guided_tour_dismissed`).
 *  4. Current admin screen is a NEW `wpvr_item` post (no post ID in the URL).
 *
 * @package WPVR
 * @since   8.5.68
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPVR_New_User_Tour {

    /**
     * Option: permanently set when the user dismisses or completes the tour.
     */
    const OPT_DISMISSED = 'wpvr_new_user_guided_tour_dismissed';

    /**
     * AJAX action for the dismiss request.
     */
    const AJAX_DISMISS = 'wpvr_dismiss_new_user_tour';

    /**
     * Constructor — wire up all hooks.
     */
    public function __construct() {
        // Register AJAX handler (must be registered regardless of screen).
        add_action( 'wp_ajax_' . self::AJAX_DISMISS, array( $this, 'handle_dismiss' ) );

        // Conditionally enqueue scripts only on admin pages.
        add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
    }

    /* -----------------------------------------------------------------------
     * Visibility check
     * --------------------------------------------------------------------- */

    /**
     * Whether the auto-start tour should be launched.
     *
     * @return bool
     */
    private function should_launch() {
        // 1. Only on admin screens.
        if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
            return false;
        }

        // 2. Must be the Single Tour editor screen (wpvr_item).
        $screen = get_current_screen();
        if ( ! $screen || 'wpvr_item' !== $screen->id ) {
            return false;
        }

        // 3. Must be a NEW post (no post ID = creating, not editing).
        //    When editing an existing tour, $_GET['post'] is set.
        if ( isset( $_GET['post'] ) && absint( wp_unslash($_GET['post']) ) > 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return false;
        }

        // 4. Admins only.
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        // 5. If a real tour was ever created on this site → existing user, skip.
        if ( get_option( WPVR_Onboarding_Notice::OPT_HAS_EVER_CREATED ) ) {
            return false;
        }

        // 6. If the setup wizard was completed → skip (they already built a tour).
        if ( get_option( WPVR_Onboarding_Notice::OPT_WIZARD_DONE ) ) {
            return false;
        }

        // 7. If the guided tour was already dismissed / completed → skip.
        if ( get_option( self::OPT_DISMISSED ) ) {
            return false;
        }

        return true;
    }

    /* -----------------------------------------------------------------------
     * Asset enqueueing
     * --------------------------------------------------------------------- */

    /**
     * Enqueue Shepherd assets and localize the auto-start flag when needed.
     */
    public function maybe_enqueue() {
        if ( ! $this->should_launch() ) {
            return;
        }

        $plugin_url = WPVR_ASSET_PATH; // already defined as plugin_url . 'admin/'

        // --- CSS ---
        wp_enqueue_style(
            'wpvr-shepherd-css',
            $plugin_url . 'lib/shepherd/css/shepherd-theme-arrows-plain-buttons.css',
            array(),
            WPVR_VERSION
        );
        wp_enqueue_style(
            'wpvr-tour-guide-css',
            $plugin_url . 'lib/shepherd/css/wpvr-tour-guide.min.css',
            array(),
            WPVR_VERSION
        );

        // --- JS ---
        wp_enqueue_script(
            'wpvr-tether-js',
            $plugin_url . 'lib/shepherd/tether/tether.js',
            array(),
            WPVR_VERSION,
            true
        );
        wp_enqueue_script(
            'wpvr-shepherd-js',
            $plugin_url . 'lib/shepherd/tether-shepherd/shepherd.js',
            array( 'wpvr-tether-js' ),
            WPVR_VERSION,
            true
        );
        wp_enqueue_script(
            'wpvr-tour-guide',
            $plugin_url . 'js/wpvr-tour-guide.js',
            array( 'jquery', 'wpvr-tether-js', 'wpvr-shepherd-js' ),
            WPVR_VERSION,
            true
        );

        // Translations already used by the tour guide script.
        $tour_guide_translation = new WPVR_Tour_Guide_Translation();

        wp_localize_script(
            'wpvr-tour-guide',
            'wpvr_tour_guide_obj',
            array(
                'Tour_Guide_Translation' => $tour_guide_translation->get_translatable_string(),
                'step1_bg_image'         => plugins_url( 'admin/icon/first-step-bg.png', WPVR_FILE ),
                'next_button_arrow'      => plugins_url( 'admin/icon/next-button-arrow.png', WPVR_FILE ),
            )
        );

        // Auto-start config + dismiss endpoint.
        wp_localize_script(
            'wpvr-tour-guide',
            'wpvr_new_user_tour',
            array(
                'autoStart' => true,
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'nonce'     => wp_create_nonce( self::AJAX_DISMISS ),
                'action'    => self::AJAX_DISMISS,
            )
        );
    }

    /* -----------------------------------------------------------------------
     * AJAX handler
     * --------------------------------------------------------------------- */

    /**
     * Permanently dismiss the guided tour so it never auto-fires again.
     */
    public function handle_dismiss() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
            return;
        }

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, self::AJAX_DISMISS ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

        update_option( self::OPT_DISMISSED, '1', false );

        wp_send_json_success( array( 'message' => 'Tour dismissed' ) );
    }
}
