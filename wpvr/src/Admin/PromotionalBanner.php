<?php

namespace RexTheme\WPVR\Admin;

/**
 * Renders and manages the promotional countdown banner on the WPVR tour listing page.
 */
class PromotionalBanner {

	const OPTION_DISMISSED = 'wpvr_halloween_2026_promo_banner_dismissed';
	const POST_TYPE        = 'wpvr_item';
	const TARGET_SCREEN    = 'edit-wpvr_item';
	const END_DATE         = '2026-10-26 23:59:59';
	const CTA_URL          = 'https://rextheme.com/wpvr/wpvr-pricing/';
	const AJAX_ACTION      = 'wpvr_dismiss_promo_banner';
	const NONCE_ACTION     = 'wpvr_promo_banner_dismiss';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	public static function init(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ $this, 'render_banner' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'handle_dismiss' ] );
	}

	/**
	 * Checks whether all conditions are met to display the banner.
	 */
	public function should_display(): bool {
		if ( ! is_admin() ) {
			return false;
		}

		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		$is_listing = in_array( $screen->id, [ self::TARGET_SCREEN, 'edit-wpvr' ], true )
			|| ( isset( $screen->base, $screen->post_type ) && 'edit' === $screen->base && in_array( $screen->post_type, [ self::POST_TYPE, 'wpvr' ], true ) );

		if ( ! $is_listing ) {
			return false;
		}

		// Suppress if WPVR Pro is active with a valid license.
		if ( function_exists( 'wpvr_is_pro_active' ) && wpvr_is_pro_active() ) {
			return false;
		}

		// Suppress if user permanently dismissed the banner.
		if ( get_option( self::OPTION_DISMISSED, 'no' ) === 'yes' ) {
			return false;
		}

		// Suppress if campaign has ended.
		if ( time() >= strtotime( self::END_DATE ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Computes initial countdown values for server-side render.
	 *
	 * @return array<string, mixed>
	 */
	public function get_countdown_data(): array {
		$end_timestamp = strtotime( self::END_DATE );
		$diff          = max( 0, $end_timestamp - time() );

		$days  = floor( $diff / ( 60 * 60 * 24 ) );
		$hours = floor( ( $diff % ( 60 * 60 * 24 ) ) / ( 60 * 60 ) );
		$mins  = floor( ( $diff % ( 60 * 60 ) ) / 60 );
		$secs  = floor( $diff % 60 );

		return [
			'days'          => sprintf( '%02d', (int) $days ),
			'hours'         => sprintf( '%02d', (int) $hours ),
			'mins'          => sprintf( '%02d', (int) $mins ),
			'secs'          => sprintf( '%02d', (int) $secs ),
			'end_timestamp' => $end_timestamp,
		];
	}

	/**
	 * Enqueue assets strictly when banner should display.
	 */
	public function enqueue_assets(): void {
		if ( ! $this->should_display() ) {
			return;
		}

		$plugin_url  = plugin_dir_url( WPVR_FILE );
		$plugin_path = plugin_dir_path( WPVR_FILE );

		$css_file = $plugin_path . 'app/admin/promotional-banner.css';
		$css_ver  = file_exists( $css_file ) ? (string) filemtime( $css_file ) : WPVR_VERSION;

		$js_file  = $plugin_path . 'app/admin/promotional-banner.js';
		$js_ver   = file_exists( $js_file ) ? (string) filemtime( $js_file ) : WPVR_VERSION;

		wp_enqueue_style(
			'wpvr-promotional-banner',
			$plugin_url . 'app/admin/promotional-banner.css',
			[],
			$css_ver
		);

		wp_enqueue_script(
			'wpvr-promotional-banner',
			$plugin_url . 'app/admin/promotional-banner.js',
			[],
			$js_ver,
			true
		);

		wp_localize_script(
			'wpvr-promotional-banner',
			'wpvrPromoBanner',
			[
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'action'       => self::AJAX_ACTION,
				'nonce'        => wp_create_nonce( self::NONCE_ACTION ),
				'endTimestamp' => strtotime( self::END_DATE ),
			]
		);
	}

	/**
	 * Renders promotional banner HTML markup.
	 */
	public function render_banner(): void {
		if ( ! $this->should_display() ) {
			return;
		}

		$countdown = $this->get_countdown_data();
		?>
		<div id="wpvr-promotional-banner" class="wpvr-promotional-banner" data-end-time="<?php echo esc_attr( (string) $countdown['end_timestamp'] ); ?>" role="region" aria-label="<?php esc_attr_e( 'WPVR Halloween Offer', 'wpvr' ); ?>">
			<div class="wpvr-promo-banner__content">
				<figure class="wpvr-promo-banner__logo">
					<img src="<?php echo esc_url( WPVR_PLUGIN_DIR_URL . 'admin/icon/banner-images/halloween-img.webp' ); ?>" alt="<?php esc_attr_e( 'Halloween', 'wpvr' ); ?>" width="174" height="47">
				</figure>

				<div class="wpvr-promo-banner__text">
					<span class="wpvr-promo-banner__title"><?php esc_html_e( 'Spooktacular Halloween Sale', 'wpvr' ); ?></span>
					<span class="wpvr-promo-banner__subtitle"><?php esc_html_e( 'GET 30% OFF!', 'wpvr' ); ?> <span class="wpvr-promo-banner__emoji" aria-hidden="true">&#127875;</span></span>
				</div>

				<div class="wpvr-promo-banner__timer" id="wpvr-promo-countdown" aria-label="<?php esc_attr_e( 'Offer countdown timer', 'wpvr' ); ?>">
					<div class="wpvr-promo-timer__box">
						<span class="wpvr-promo-timer__num" id="wpvr-promo-days"><?php echo esc_html( $countdown['days'] ); ?></span>
						<span class="wpvr-promo-timer__unit"><?php esc_html_e( 'DAY', 'wpvr' ); ?></span>
					</div>
					<div class="wpvr-promo-timer__box">
						<span class="wpvr-promo-timer__num" id="wpvr-promo-hours"><?php echo esc_html( $countdown['hours'] ); ?></span>
						<span class="wpvr-promo-timer__unit"><?php esc_html_e( 'HR', 'wpvr' ); ?></span>
					</div>
					<div class="wpvr-promo-timer__box">
						<span class="wpvr-promo-timer__num" id="wpvr-promo-mins"><?php echo esc_html( $countdown['mins'] ); ?></span>
						<span class="wpvr-promo-timer__unit"><?php esc_html_e( 'MIN', 'wpvr' ); ?></span>
					</div>
					<div class="wpvr-promo-timer__box">
						<span class="wpvr-promo-timer__num" id="wpvr-promo-secs"><?php echo esc_html( $countdown['secs'] ); ?></span>
						<span class="wpvr-promo-timer__unit"><?php esc_html_e( 'SEC', 'wpvr' ); ?></span>
					</div>
				</div>

				<a href="<?php echo esc_url( self::CTA_URL ); ?>" class="wpvr-promo-banner__cta" target="_blank" rel="noopener noreferrer">
					<span><?php esc_html_e( 'Claim Your Deal', 'wpvr' ); ?></span>
					<svg class="wpvr-promo-banner__cta-arrow" width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M1 10L10 1M10 1H2.75M10 1V8.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			</div>

			<button type="button" class="wpvr-promo-banner__dismiss" id="wpvr-promo-banner-dismiss" aria-label="<?php esc_attr_e( 'Dismiss banner permanently', 'wpvr' ); ?>">
				<svg width="9" height="9" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M1 1L10 10M10 1L1 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
		<?php
	}

	/**
	 * AJAX handler for permanent dismissal.
	 */
	public function handle_dismiss(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'wpvr' ) ], 403 );
		}

		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		update_option( self::OPTION_DISMISSED, 'yes' );

		wp_send_json_success( [ 'message' => esc_html__( 'Banner permanently dismissed.', 'wpvr' ) ] );
	}
}
