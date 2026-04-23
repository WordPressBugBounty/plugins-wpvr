<?php
/**
 * WPVR Onboarding Notice
 *
 * Displays a notice on the tour listing page (edit-wpvr_item) reminding
 * truly-new users that they can start the setup wizard.
 *
 * Visibility rules:
 *  1. Only shown on the tour listing page (edit-wpvr_item).
 *  2. Only shown to users who have NEVER created a real (non-demo) tour.
 *     Tracked via `wpvr_has_ever_created_tour` — set permanently via backfill
 *     or explicitly when the wizard AJAX creates a real tour.
 *  3. NOT shown if the wizard was already completed (`wpvr_wizard_onboarding_done`).
 *  4. NOT shown if the user already permanently dismissed this notice.
 *  5. Demo tours are explicitly excluded from all checks.
 *
 * @package  WPVR
 * @since    7.4.83
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPVR_Onboarding_Notice {

    /**
     * Option: permanently set once a real (non-demo) wpvr_item is created.
     */
    const OPT_HAS_EVER_CREATED = 'wpvr_has_ever_created_tour';

    /**
     * Option: set when the wizard flow is completed (tour published via wizard).
     */
    const OPT_WIZARD_DONE = 'wpvr_wizard_onboarding_done';

    /**
     * Option: set permanently when user clicks "Dismiss".
     */
    const OPT_DISMISSED = 'wpvr_onboarding_notice_dismissed';

    /**
     * AJAX action name for the dismiss request.
     */
    const AJAX_DISMISS = 'wpvr_onboarding_notice_dismiss';

    /**
     * Bootstrap hooks.
     */
    public function __construct() {
        // Backfill the permanent flag for existing installs (old customers).
        // NOTE: We do NOT use wp_after_insert_post for demo-tour detection because
        // `wpvr_is_demo_tour` meta is set AFTER wp_insert_post returns, making
        // the timing unreliable. The backfill query handles it correctly instead.
        add_action( 'admin_init', array( $this, 'backfill_ever_created_flag' ) );

        // Register AJAX handler (logged-in users only).
        add_action( 'wp_ajax_' . self::AJAX_DISMISS, array( $this, 'handle_dismiss' ) );

        // Render the notice on admin_notices.
        add_action( 'admin_notices', array( $this, 'render_notice' ) );
    }

    /* -----------------------------------------------------------------------
     * Backfill
     * --------------------------------------------------------------------- */

    /**
     * One-time backfill: if the site already has ANY real (non-demo) wpvr_item
     * posts (any status including trash), permanently set the flag so old
     * customers are never shown the onboarding notice.
     *
     * Rate-limited to once per hour via transient. The transient is intentionally
     * short so that a user who creates their first real tour sees the flag update
     * quickly when they return to the listing page.
     */
    public function backfill_ever_created_flag() {
        // Already flagged — bail.
        if ( get_option( self::OPT_HAS_EVER_CREATED ) ) {
            return;
        }

        // Rate-limit with an hourly transient.
        if ( get_transient( 'wpvr_onb_backfill_check' ) ) {
            return;
        }
        set_transient( 'wpvr_onb_backfill_check', 1, HOUR_IN_SECONDS );

        // Exclude demo tours via meta_query.
        $existing = get_posts( array(
            'post_type'      => 'wpvr_item',
            'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => 'wpvr_is_demo_tour',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => 'wpvr_is_demo_tour',
                    'value'   => '1',
                    'compare' => '!=',
                ),
            ),
        ) );

        if ( ! empty( $existing ) ) {
            update_option( self::OPT_HAS_EVER_CREATED, '1', false );
        }
    }

    /* -----------------------------------------------------------------------
     * Visibility check
     * --------------------------------------------------------------------- */

    /**
     * Whether the notice should be rendered.
     *
     * @return bool
     */
    private function should_show() {
        // 1. Listing page only.
        if ( ! $this->is_listing_page() ) {
            return false;
        }

        // 2. Admins only.
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        // 3. If a real tour was ever created on this site → never show.
        if ( get_option( self::OPT_HAS_EVER_CREATED ) ) {
            return false;
        }

        // 4. If the wizard was completed → never show.
        if ( get_option( self::OPT_WIZARD_DONE ) ) {
            return false;
        }

        // 5. If permanently dismissed → never show.
        if ( get_option( self::OPT_DISMISSED ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check if current screen is the tour listing page.
     *
     * @return bool
     */
    private function is_listing_page() {
        if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
            return false;
        }
        $screen = get_current_screen();
        return $screen && 'edit-wpvr_item' === $screen->id;
    }

    /* -----------------------------------------------------------------------
     * Rendering
     * --------------------------------------------------------------------- */

    /**
     * Output the notice HTML + inline CSS + inline JS.
     */
    public function render_notice() {
        if ( ! $this->should_show() ) {
            return;
        }

        $wizard_url = esc_url( admin_url( 'admin.php?page=rex-wpvr-setup-wizard' ) );
        $nonce      = wp_create_nonce( self::AJAX_DISMISS );
        ?>
        <div id="wpvr-onboarding-notice" class="wpvr-onboarding-notice notice">
            <div class="wpvr-onboarding-notice__inner">
                <div class="wpvr-onboarding-notice__icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="14" cy="14" r="14" fill="#EEF0FF"/>
                        <path d="M14 7C11.239 7 9 9.239 9 12c0 2.209 1.343 4.1 3.25 4.934V19h1.5v-2.066A4.999 4.999 0 0 0 19 12c0-2.761-2.239-5-5-5Zm0 8.5A3.504 3.504 0 0 1 10.5 12 3.504 3.504 0 0 1 14 8.5 3.504 3.504 0 0 1 17.5 12 3.504 3.504 0 0 1 14 15.5ZM13.25 20h1.5v1h-1.5z" fill="#3F04FE"/>
                    </svg>
                </div>
                <div class="wpvr-onboarding-notice__body">
                    <p class="wpvr-onboarding-notice__text">
                        <?php esc_html_e( "You haven't created a virtual tour yet. Our quick setup wizard will help you publish your first tour in minutes!", 'wpvr' ); ?>
                    </p>
                </div>
                <div class="wpvr-onboarding-notice__actions">
                    <a href="<?php echo $wizard_url; ?>" class="button button-primary wpvr-onboarding-notice__cta" id="wpvr-onboarding-start-btn">
                        <?php esc_html_e( 'Start Onboarding', 'wpvr' ); ?>
                    </a>
                    <button type="button"
                            class="wpvr-onboarding-notice__dismiss"
                            id="wpvr-onboarding-dismiss-btn"
                            data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
                            data-nonce="<?php echo esc_attr( $nonce ); ?>"
                            aria-label="<?php esc_attr_e( 'Dismiss this notice', 'wpvr' ); ?>">
                        <?php esc_html_e( 'Dismiss', 'wpvr' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <style id="wpvr-onboarding-notice-styles">
        #wpvr-onboarding-notice.wpvr-onboarding-notice {
            border-left: 4px solid #3F04FE;
            background: #fff;
            padding: 0;
            margin: 16px 20px 0 0;
            border-radius: 0 6px 6px 0;
            box-shadow: 0 2px 8px rgba(63,4,254,.08);
        }
        .wpvr-onboarding-notice__inner {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
        }
        .wpvr-onboarding-notice__icon { flex-shrink: 0; }
        .wpvr-onboarding-notice__body { flex: 1; }
        .wpvr-onboarding-notice__text {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
            color: #1e1b4b;
            line-height: 1.5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .wpvr-onboarding-notice__actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .wpvr-onboarding-notice__cta.button-primary {
            background: #3F04FE;
            border-color: #3F04FE;
            color: #fff;
            font-weight: 600;
            padding: 6px 16px;
            height: auto;
            line-height: 1.5;
            border-radius: 5px;
            text-decoration: none;
            transition: background .15s ease, box-shadow .15s ease;
        }
        .wpvr-onboarding-notice__cta.button-primary:hover,
        .wpvr-onboarding-notice__cta.button-primary:focus {
            background: #2b00cc;
            border-color: #2b00cc;
            color: #fff;
            box-shadow: 0 2px 8px rgba(63,4,254,.3);
            outline: none;
        }
        .wpvr-onboarding-notice__dismiss {
            background: none;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 5px 12px;
            cursor: pointer;
            font-size: 13px;
            color: #6b7280;
            font-family: inherit;
            transition: border-color .15s ease, color .15s ease;
        }
        .wpvr-onboarding-notice__dismiss:hover {
            border-color: #9ca3af;
            color: #374151;
        }
        @media screen and (max-width: 782px) {
            .wpvr-onboarding-notice__inner { flex-wrap: wrap; }
            .wpvr-onboarding-notice__actions { width: 100%; }
        }
        </style>

        <script>
        (function($) {
            $(document).on('click', '#wpvr-onboarding-dismiss-btn', function() {
                var $btn  = $(this);
                var $wrap = $('#wpvr-onboarding-notice');

                // Immediately hide.
                $wrap.fadeOut(250, function() { $wrap.remove(); });

                // Permanently dismiss via AJAX.
                $.post($btn.data('ajax'), {
                    action : '<?php echo esc_js( self::AJAX_DISMISS ); ?>',
                    nonce  : $btn.data('nonce')
                });
            });
        }(jQuery));
        </script>
        <?php
    }

    /* -----------------------------------------------------------------------
     * AJAX handler
     * --------------------------------------------------------------------- */

    /**
     * Handle the "Dismiss" AJAX request — permanently suppresses the notice.
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

        // Permanent dismiss — no expiry.
        update_option( self::OPT_DISMISSED, '1', false );

        wp_send_json_success( array( 'message' => 'Dismissed' ) );
    }
}
