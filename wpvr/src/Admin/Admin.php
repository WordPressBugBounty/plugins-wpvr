<?php

namespace RexTheme\WPVR\Admin;

/**
 * Manages the custom tour-editor admin page and CPT edit suppression.
 *
 * Step 1  — Register hidden admin page (wpvr-tour-editor).
 * Step 2  — Redirect post.php / post-new.php for wpvr_item to custom page.
 * Step 3  — Override edit links in WP posts list.
 * Step 4  — Render page shell (#wpvr-tour-editor-root) + enqueue assets.
 * Step 5  — New tour: React creates post on mount; history.replaceState URL.
 */
class Admin {

    const POST_TYPE  = 'wpvr_item';
    const PAGE_SLUG  = 'wpvr-tour-editor';
    const HOOK_SUFFIX = 'admin_page_wpvr-tour-editor';

    private $editor_enabled;

    public function __construct( bool $editor_enabled = true ) {
        $this->editor_enabled = $editor_enabled;

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_listing_assets' ] );

        if ( $this->editor_enabled ) {
            add_action( 'admin_footer', [ $this, 'render_listing_modal' ] );
        }

        if ( ! $this->editor_enabled ) {
            return;
        }

        add_action( 'admin_menu',             [ $this, 'register_editor_page' ] );
        add_action( 'load-post.php',          [ $this, 'redirect_cpt_edit' ] );
        add_action( 'load-post-new.php',      [ $this, 'redirect_cpt_new' ] );
        add_filter( 'get_edit_post_link',     [ $this, 'filter_edit_link' ], 10, 3 );
        add_filter( 'post_row_actions',       [ $this, 'filter_row_actions' ], 10, 2 );
        add_action( 'admin_enqueue_scripts',  [ $this, 'enqueue_tour_editor_assets' ] );
        add_filter( 'admin_body_class',       [ $this, 'add_body_class' ] );
        add_filter( 'admin_title',            [ $this, 'filter_admin_title' ], 10, 2 );
        add_action( 'current_screen',         [ $this, 'set_screen_title' ] );
        add_filter( 'parent_file',            [ $this, 'set_menu_parent' ] );
        add_filter( 'submenu_file',           [ $this, 'set_menu_submenu' ] );
        add_action( 'admin_head',             [ $this, 'editor_head_scripts' ] );
        add_action( 'admin_footer',           [ $this, 'editor_footer_scripts' ] );
    }

    // -------------------------------------------------------------------------
    // Step 1 — Register hidden admin page
    // -------------------------------------------------------------------------

    public function register_editor_page(): void {
        $tour_id    = isset( $_GET['tour_id'] ) ? (int) $_GET['tour_id'] : 0;
        $page_title = $tour_id
            ? __( 'Edit Tour', 'wpvr' )
            : __( 'Add New Tour', 'wpvr' );

        $post_type_obj = get_post_type_object( self::POST_TYPE );
        $capability    = $post_type_obj ? $post_type_obj->cap->edit_posts : 'edit_wpvr_tours';

        add_submenu_page(
            '',
            $page_title,
            $page_title,
            $capability,
            self::PAGE_SLUG,
            [ $this, 'render_editor_page' ]
        );
    }

    // -------------------------------------------------------------------------
    // Step 2 — Redirect post.php / post-new.php
    // -------------------------------------------------------------------------

    public function redirect_cpt_edit(): void {
        $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
        if ( $action && $action !== 'edit' ) {
            return;
        }
        $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
        if ( ! $post_id ) {
            return;
        }
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== self::POST_TYPE ) {
            return;
        }
        wp_redirect( $this->editor_url( $post_id ) );
        exit;
    }

    public function redirect_cpt_new(): void {
        $post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : '';
        if ( $post_type !== self::POST_TYPE ) {
            return;
        }
        wp_redirect( $this->editor_url() );
        exit;
    }

    // -------------------------------------------------------------------------
    // Step 3 — Override edit links
    // -------------------------------------------------------------------------

    public function filter_edit_link( string $url, int $post_id, string $context ): string {
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== self::POST_TYPE ) {
            return $url;
        }
        return $this->editor_url( $post_id );
    }

    public function filter_row_actions( array $actions, \WP_Post $post ): array {
        if ( $post->post_type !== self::POST_TYPE ) {
            return $actions;
        }
        if ( isset( $actions['edit'] ) ) {
            $actions['edit'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url( $this->editor_url( $post->ID ) ),
                __( 'Edit', 'wpvr' )
            );
        }
        return $actions;
    }

    // -------------------------------------------------------------------------
    // Step 4 — Render + enqueue
    // -------------------------------------------------------------------------

    public function render_editor_page(): void {
        $tour_id = isset( $_GET['tour_id'] ) ? (int) $_GET['tour_id'] : 0;
        if ( $tour_id ) {
            $post = get_post( $tour_id );
            if ( ! $post || $post->post_type !== self::POST_TYPE || ! current_user_can( 'edit_post', $tour_id ) ) {
                wp_die( esc_html__( 'Sorry, you are not allowed to edit this tour.', 'wpvr' ), 403 );
            }
        }

        echo '<div id="wpvr-tour-editor-root"></div>';

        if ( ! wpvr_is_pro_active() ) {
            require WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/partials/wpvr-premium-feature-popup.php';
        }

        // Hidden wp_editor() output forces TinyMCE HTML/scripts to load so
        // wp.editor.initialize() is available for WYSIWYG fields in React.
        echo '<div style="display:none">';
        wp_editor( '', 'wpvr_wysiwyg_seed', [
            'tinymce'       => true,
            'quicktags'     => false,
            'media_buttons' => false,
        ] );
        echo '</div>';
    }

    public function enqueue_tour_editor_assets( string $hook ): void {
        if ( ! $this->is_editor_page( $hook ) ) {
            return;
        }

        $tour_id_cfg = isset( $_GET['tour_id'] ) ? (int) $_GET['tour_id'] : 0;
        if ( $tour_id_cfg ) {
            $post = get_post( $tour_id_cfg );
            if ( ! $post || $post->post_type !== self::POST_TYPE || ! current_user_can( 'edit_post', $tour_id_cfg ) ) {
                return;
            }
        }

        $plugin_url  = plugin_dir_url( WPVR_FILE );
        $plugin_path = plugin_dir_path( WPVR_FILE );
        $build_dir   = $plugin_path . 'build/tour-editor/';

        // Load Pannellum (from legacy bundle).
        $pano_base = $plugin_url . 'legacy/admin/lib/pannellum/src/';
        wp_enqueue_style( 'wpvr-pannellum-css', $pano_base . 'css/pannellum.css', [], WPVR_VERSION );
        wp_enqueue_script( 'wpvr-libpannellum', $pano_base . 'js/libpannellum.js', [], WPVR_VERSION, true );
        wp_enqueue_script( 'wpvr-pannellum',    $pano_base . 'js/pannellum.js', [ 'wpvr-libpannellum' ], WPVR_VERSION, true );

        // Load Video.js & VideoJS-VR (for 360 video tours).
        $public_base = $plugin_url . 'legacy/public/';
        wp_enqueue_style( 'wpvr-videojs-css',    $public_base . 'lib/pannellum/src/css/video-js.css', [], WPVR_VERSION );
        wp_enqueue_style( 'wpvr-videojs-vr-css', $public_base . 'lib/videojs-vr/videojs-vr.css',       [], WPVR_VERSION );
        wp_enqueue_script( 'wpvr-videojs',       $public_base . 'js/video.js',                   [],                 WPVR_VERSION, true );
        wp_enqueue_script( 'wpvr-videojs-vr',    $public_base . 'lib/videojs-vr/videojs-vr.js', [ 'wpvr-videojs' ], WPVR_VERSION, true );

        wp_enqueue_style( 'wpvr-fontawesome', $plugin_url . 'legacy/admin/lib/fontawesome/css/all.min.css', [], WPVR_VERSION );

        // Tour editor React bundle.
        $js_file   = $build_dir . 'index.js';
        $css_file  = $build_dir . 'style-index.css';
        $asset_file = $build_dir . 'index.asset.php';

        if ( ! file_exists( $js_file ) ) {
            return;
        }

        $asset = file_exists( $asset_file )
            ? require $asset_file
            : [ 'dependencies' => [ 'wp-element', 'wp-data', 'wp-api-fetch' ], 'version' => WPVR_VERSION ];

        wp_enqueue_script(
            'wpvr-tour-editor',
            $plugin_url . 'build/tour-editor/index.js',
            array_merge( $asset['dependencies'], [ 'wpvr-pannellum', 'wpvr-videojs-vr' ] ),
            $asset['version'],
            true
        );

        $tour_id_cfg    = isset( $_GET['tour_id'] ) ? (int) $_GET['tour_id'] : 0;
        $tour_status    = $tour_id_cfg ? ( get_post_status( $tour_id_cfg ) ?: 'draft' ) : 'draft';

        $fa_icons = [];
        if ( class_exists( 'Wpvr_fontawesome_icons' ) ) {
            $fa_icons = ( new \Wpvr_fontawesome_icons() )->icon;
        }

        wp_localize_script( 'wpvr-tour-editor', 'wpvrTourEditor', [
            'tourId'      => $tour_id_cfg ?: null,
            'tourStatus'  => $tour_status,
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'apiBase'     => rest_url( 'wpvr/v1' ),
            'mediaUrl'    => rest_url( 'wp/v2/media' ),
            'isPro'       => wpvr_is_pro_active(),
            'pluginUrl'   => $plugin_url,
            'listUrl'     => admin_url( 'edit.php?post_type=' . self::POST_TYPE ),
            'postEditUrl' => admin_url( 'post.php' ),
            'faIcons'     => $fa_icons,
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'exportNonce' => wp_create_nonce( 'wpvr_export_tour' ),
            'imageResizeWarning' => [
                'ajaxNonce'         => wp_create_nonce( 'wpvr' ),
                'canManageSettings' => current_user_can( 'manage_options' ),
                'settingEnabled'    => get_option( 'high_res_image' ) === 'true',
                'heading'           => __( 'Keep this panorama in High Resolution', 'wpvr' ),
                'scaledMessage'     => __( 'By default, a resized version is created while the original image is still available.', 'wpvr' ),
                'disableHandler'    => __( 'Disable WordPress Large Image Handler on WP VR for future uploads', 'wpvr' ),
                'highResolutionNote'=> __( '*Note: Turn this on to use the high-resolution image.', 'wpvr' ),
                'close'             => __( 'Close', 'wpvr' ),
            ],
        ] );

        if ( file_exists( $css_file ) ) {
            wp_enqueue_style(
                'wpvr-tour-editor',
                $plugin_url . 'build/tour-editor/style-index.css',
                [ 'wpvr-pannellum-css' ],
                $asset['version']
            );
        }
    }

    public function filter_admin_title( string $admin_title, string $title ): string {
        if ( ! isset( $_GET['page'] ) || sanitize_key( $_GET['page'] ) !== self::PAGE_SLUG ) {
            return $admin_title;
        }
        $tour_id    = isset( $_GET['tour_id'] ) ? (int) $_GET['tour_id'] : 0;
        $page_title = $tour_id ? __( 'Edit Tour', 'wpvr' ) : __( 'Add New Tour', 'wpvr' );
        return $page_title . ' &lsaquo; ' . get_bloginfo( 'name' ) . ' &#8212; WordPress';
    }

    public function add_body_class( string $classes ): string {
        if ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === self::PAGE_SLUG ) {
            $classes .= ' wpvr-tour-editor-page';
        }
        return $classes;
    }

    public function set_menu_parent( string $parent_file ): string {
        if ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === self::PAGE_SLUG ) {
            return 'edit.php?post_type=' . self::POST_TYPE;
        }
        return $parent_file;
    }

    public function set_menu_submenu( ?string $submenu_file ): ?string {
        if ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === self::PAGE_SLUG ) {
            return 'post-new.php?post_type=' . self::POST_TYPE;
        }
        return $submenu_file;
    }

    public function editor_head_scripts(): void {
        if ( ! isset( $_GET['page'] ) || sanitize_key( $_GET['page'] ) !== self::PAGE_SLUG ) {
            return;
        }
        ?>
        <script>
        ( function() {
            var narrowEditor = window.matchMedia( '(max-width: 1279px)' );

            function applyEditorMenuState() {
                if ( ! document.body ) {
                    window.requestAnimationFrame( applyEditorMenuState );
                    return;
                }

                document.body.classList.toggle( 'folded', narrowEditor.matches );
            }

            applyEditorMenuState();

            if ( narrowEditor.addEventListener ) {
                narrowEditor.addEventListener( 'change', applyEditorMenuState );
            } else {
                narrowEditor.addListener( applyEditorMenuState );
            }
        }() );
        </script>
        <?php
    }

    public function editor_footer_scripts(): void {
        if ( ! isset( $_GET['page'] ) || sanitize_key( $_GET['page'] ) !== self::PAGE_SLUG ) {
            return;
        }
        ?>
        <script>
        jQuery( window ).on( 'load', function() {
            // Intercept WP sidebar toggle so it doesn't persist state to user meta.
            // The editor's initial state is viewport-dependent; manual toggling should
            // work visually but must not overwrite the user's real preference.
            jQuery( '#collapse-button' ).off( 'click' ).on( 'click', function() {
                jQuery( 'body' ).toggleClass( 'folded' );
            } );
        } );
        </script>
        <?php
    }

    public function render_mode_switch_notice(): void {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        if ( ! $this->is_listing_page() && ! $this->is_editor_request() ) {
            return;
        }

        $tour_id      = isset( $_GET['tour_id'] ) ? absint( $_GET['tour_id'] ) : 0;
        $switch_url   = $this->get_ui_mode_switch_url( 'legacy', $tour_id );
        $button_label = __( 'Switch to Classic UI', 'wpvr' );
        ?>
        <div class="notice notice-info">
            <p>
                <?php esc_html_e( 'You are using the latest WP VR editor.', 'wpvr' ); ?>
                <a class="button button-secondary" href="<?php echo esc_url( $switch_url ); ?>" style="margin-left:8px;">
                    <?php echo esc_html( $button_label ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    public function handle_ui_mode_switch(): void {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You are not allowed to switch UI modes.', 'wpvr' ) );
        }

        check_admin_referer( 'wpvr_switch_ui_mode' );

        $mode    = isset( $_GET['mode'] ) && sanitize_key( $_GET['mode'] ) === 'latest' ? 'latest' : 'legacy';
        $tour_id = isset( $_GET['tour_id'] ) ? absint( $_GET['tour_id'] ) : 0;

        update_option( 'wpvr_ui_mode', $mode );

        wp_safe_redirect( $this->get_ui_mode_redirect_url( $mode, $tour_id ) );
        exit;
    }

    // -------------------------------------------------------------------------
    // Step 5 — Set $title global so admin-header.php doesn't get null
    // -------------------------------------------------------------------------

    public function set_screen_title( \WP_Screen $screen ): void {
        if ( $screen->id !== 'admin_page_' . self::PAGE_SLUG ) {
            return;
        }
        global $title;
        $tour_id = isset( $_GET['tour_id'] ) ? (int) $_GET['tour_id'] : 0;
        $title   = $tour_id ? __( 'Edit Tour', 'wpvr' ) : __( 'Add New Tour', 'wpvr' );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function editor_url( int $post_id = 0 ): string {
        $args = [ 'page' => self::PAGE_SLUG ];
        if ( $post_id > 0 ) {
            $args['tour_id'] = $post_id;
        }
        return add_query_arg( $args, admin_url( 'admin.php' ) );
    }

    private function is_editor_page( string $hook ): bool {
        return $hook === self::HOOK_SUFFIX
            || ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === self::PAGE_SLUG );
    }

    private function is_editor_request(): bool {
        return isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === self::PAGE_SLUG;
    }

    private function is_listing_page(): bool {
        $screen = get_current_screen();
        return $screen && $screen->id === 'edit-' . self::POST_TYPE;
    }

    private function get_ui_mode_switch_url( string $mode, int $tour_id = 0 ): string {
        $args = [
            'action' => 'wpvr_switch_ui_mode',
            'mode'   => $mode,
        ];

        if ( $tour_id > 0 ) {
            $args['tour_id'] = $tour_id;
        }

        return wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), 'wpvr_switch_ui_mode' );
    }

    private function get_ui_mode_redirect_url( string $mode, int $tour_id = 0 ): string {
        if ( $mode === 'latest' ) {
            return $this->editor_url( $tour_id );
        }

        if ( $tour_id > 0 ) {
            return add_query_arg(
                [
                    'post'   => $tour_id,
                    'action' => 'edit',
                ],
                admin_url( 'post.php' )
            );
        }

        return admin_url( 'edit.php?post_type=' . self::POST_TYPE );
    }

    // -------------------------------------------------------------------------
    // Listing page — Create Tour modal
    // -------------------------------------------------------------------------

    public function enqueue_listing_assets( string $hook ): void {
        if ( ! $this->is_listing_page() ) {
            return;
        }

        $plugin_url = plugin_dir_url( WPVR_FILE );
        $style_dependencies = [];
        $script_dependencies = [];

        if ( $this->editor_enabled ) {
            wp_enqueue_style(
                'wpvr-create-tour-modal',
                $plugin_url . 'app/admin/create-tour-modal.css',
                [],
                WPVR_VERSION
            );

            wp_enqueue_script(
                'wpvr-create-tour-modal',
                $plugin_url . 'app/admin/create-tour-modal.js',
                [],
                WPVR_VERSION,
                true
            );

            wp_localize_script( 'wpvr-create-tour-modal', 'wpvrListingModal', [
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'apiBase'   => rest_url( 'wpvr/v1' ),
                'editorUrl' => admin_url( 'admin.php?page=' . self::PAGE_SLUG ),
                'isPro'     => wpvr_is_pro_active(),
                'iconsUrl'  => $plugin_url . 'app/assets/icons/tour-types/',
            ] );

            $style_dependencies[] = 'wpvr-create-tour-modal';
            $script_dependencies[] = 'wpvr-create-tour-modal';
        }

        wp_enqueue_style(
            'wpvr-tour-listing',
            $plugin_url . 'app/admin/tour-listing.css',
            $style_dependencies,
            WPVR_VERSION
        );

        wp_enqueue_script(
            'wpvr-tour-listing',
            $plugin_url . 'app/admin/tour-listing.js',
            $script_dependencies,
            WPVR_VERSION,
            true
        );
    }

    public function render_listing_modal(): void {
        if ( ! $this->editor_enabled || ! $this->is_listing_page() ) {
            return;
        }

        $is_pro    = apply_filters( 'is_wpvr_pro_active', false ) && get_option( 'wpvr_edd_license_status' ) === 'valid';
        $icons_url = plugin_dir_url( WPVR_FILE ) . 'app/assets/icons/tour-types/';
        ?>
        <div id="wpvr-ctm-overlay" class="wpvr-ctm-overlay" style="display:none;" role="dialog" aria-modal="true" aria-label="Create New Tour">
            <div class="wpvr-ctm">

                <header class="wpvr-ctm__header">
                    <div>
                        <h2 class="wpvr-ctm__title"><?php esc_html_e( 'Create New Tour', 'wpvr' ); ?></h2>
                        <p class="wpvr-ctm__subtitle"><?php esc_html_e( 'Choose your tour type and get started', 'wpvr' ); ?></p>
                    </div>
                    <button class="wpvr-ctm__close" id="wpvr-ctm-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'wpvr' ); ?>">×</button>
                </header>

                <div class="wpvr-ctm__body">

                    <div class="wpvr-ctm__field">
                        <label for="wpvr-ctm-name" class="wpvr-ctm__label">
                            <?php esc_html_e( 'Tour Name', 'wpvr' ); ?>
                            <span class="wpvr-ctm__required">*</span>
                        </label>
                        <input
                            type="text"
                            id="wpvr-ctm-name"
                            class="wpvr-ctm__input"
                            placeholder="<?php esc_attr_e( 'Enter tour name...', 'wpvr' ); ?>"
                            autocomplete="off"
                        />
                        <span class="wpvr-ctm__error" id="wpvr-ctm-name-error" style="display:none;">
                            <?php esc_html_e( 'Tour name is required.', 'wpvr' ); ?>
                        </span>
                    </div>

                    <div class="wpvr-ctm__field">
                        <label class="wpvr-ctm__label">
                            <?php esc_html_e( 'Tour Type', 'wpvr' ); ?>
                            <span class="wpvr-ctm__required">*</span>
                        </label>
                        <div class="wpvr-ctm__cards">

                            <?php /* 360° Image — always free, selected by default */ ?>
                            <button type="button" class="wpvr-ctm__card wpvr-ctm__card--selected" data-type="image">
                                <img class="wpvr-ctm__card-icon"
                                     src="<?php echo esc_url( $icons_url . 'scene-image-filled.svg' ); ?>"
                                     data-active="<?php echo esc_url( $icons_url . 'scene-image-filled.svg' ); ?>"
                                     data-inactive="<?php echo esc_url( $icons_url . 'scene-image.svg' ); ?>"
                                     alt="" />
                                <span class="wpvr-ctm__card-title"><?php esc_html_e( '360° Image', 'wpvr' ); ?></span>
                                <span class="wpvr-ctm__card-desc"><?php esc_html_e( 'Create immersive panoramic image tours', 'wpvr' ); ?></span>
                                <span class="wpvr-ctm__card-radio wpvr-ctm__card-radio--checked"></span>
                            </button>

                            <?php /* 360° Video — free */ ?>
                            <button type="button" class="wpvr-ctm__card" data-type="video">
                                <img class="wpvr-ctm__card-icon"
                                     src="<?php echo esc_url( $icons_url . 'scene-video.svg' ); ?>"
                                     data-active="<?php echo esc_url( $icons_url . 'scene-video-filled.svg' ); ?>"
                                     data-inactive="<?php echo esc_url( $icons_url . 'scene-video.svg' ); ?>"
                                     alt="" />
                                <span class="wpvr-ctm__card-title"><?php esc_html_e( '360° Video', 'wpvr' ); ?></span>
                                <span class="wpvr-ctm__card-desc"><?php esc_html_e( 'Build dynamic video-based experiences', 'wpvr' ); ?></span>
                                <span class="wpvr-ctm__card-radio"></span>
                            </button>

                            <?php /* Street View — PRO only */ ?>
                            <button type="button" class="wpvr-ctm__card<?php echo $is_pro ? '' : ' wpvr-ctm__card--disabled'; ?>"
                                data-type="street-view"
                                    <?php echo $is_pro ? '' : 'disabled'; ?>
                                    title="<?php echo $is_pro ? '' : esc_attr__( 'Requires WP VR Pro', 'wpvr' ); ?>">
                                <?php if ( ! $is_pro ) : ?>
                                <span class="is-pro" aria-hidden="true">Pro</span>
                                <?php endif; ?>
                                <img class="wpvr-ctm__card-icon"
                                     src="<?php echo esc_url( $icons_url . 'scene-street.svg' ); ?>"
                                     data-active="<?php echo esc_url( $icons_url . 'scene-street-filled.svg' ); ?>"
                                     data-inactive="<?php echo esc_url( $icons_url . 'scene-street.svg' ); ?>"
                                     alt="" />
                                <span class="wpvr-ctm__card-title"><?php esc_html_e( 'Street View', 'wpvr' ); ?></span>
                                <span class="wpvr-ctm__card-desc"><?php esc_html_e( 'Integrate Google Street View locations', 'wpvr' ); ?></span>
                                <span class="wpvr-ctm__card-radio"></span>
                            </button>

                        </div>
                    </div>

                </div>

                <div class="wpvr-ctm__footer">
                    <button type="button" class="wpvr-ctm__btn wpvr-ctm__btn--secondary" id="wpvr-ctm-cancel">
                        <?php esc_html_e( 'Cancel', 'wpvr' ); ?>
                    </button>
                    <button type="button" class="wpvr-ctm__btn wpvr-ctm__btn--primary" id="wpvr-ctm-submit">
                        <?php esc_html_e( '+ Create Tour', 'wpvr' ); ?>
                    </button>
                </div>

            </div>
        </div>
        <?php
    }
}
