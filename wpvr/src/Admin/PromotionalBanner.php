<?php

namespace RexTheme\WPVR\Admin;

/**
 * Renders and manages the promotional countdown banner on the WPVR tour listing page.
 */
class PromotionalBanner {

	const OPTION_DISMISSED = 'wpvr_new_ui_promo_banner_dismissed';
	const POST_TYPE        = 'wpvr_item';
	const TARGET_SCREEN    = 'edit-wpvr_item';
	const END_DATE         = '2026-09-30 23:59:59';
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
		<div id="wpvr-promotional-banner" class="wpvr-promotional-banner" data-end-time="<?php echo esc_attr( (string) $countdown['end_timestamp'] ); ?>" role="region" aria-label="<?php esc_attr_e( 'WPVR Promotional Offer', 'wpvr' ); ?>">
			<div class="wpvr-promo-banner__content">
				<div class="wpvr-promo-banner__badge" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="54" height="54" viewBox="0 0 54 54" fill="none" focusable="false">
						<path d="M26.2456 23.3063L29.1152 20.7481L28.4685 17.0989L31.9848 18.8294L35.501 17.1365L34.8139 20.7857L37.6431 23.3816L33.6823 23.8706L31.9039 27.1813L30.166 23.8706L26.2456 23.3063Z" fill="white"></path>
						<rect width="53.18" height="53.18" rx="9" fill="white" fill-opacity="0.1"></rect>
						<rect x="6.22736" y="6.22733" width="40.7259" height="40.7259" rx="5.5" fill="#EFEAFF" stroke="white"></rect>
						<path d="M30.6591 40.1589V37.234H33.0726V28.7661H30.6591V25.8412H38.9838V28.7661H36.5498V37.234H38.9838V40.1589H30.6591Z" fill="#3F04FE"></path>
						<path d="M21.3401 40.2816C20.1537 40.2816 19.097 40.0429 18.1697 39.5657C17.2425 39.0748 16.513 38.4066 15.9812 37.5612C15.463 36.7158 15.2039 35.7476 15.2039 34.6568V25.8411H18.722V34.4727C18.722 34.9908 18.8379 35.4613 19.0697 35.884C19.3015 36.2931 19.6151 36.6203 20.0106 36.8658C20.406 37.0976 20.8492 37.2135 21.3401 37.2135C21.8582 37.2135 22.315 37.0976 22.7105 36.8658C23.1196 36.6203 23.4468 36.2931 23.6923 35.884C23.9377 35.4613 24.0604 34.9908 24.0604 34.4727V25.8411H27.4558V34.6568C27.4558 35.7476 27.1899 36.7158 26.6581 37.5612C26.1399 38.4066 25.4172 39.0748 24.49 39.5657C23.5627 40.0429 22.5128 40.2816 21.3401 40.2816Z" fill="#3F04FE"></path>
						<path d="M31.0628 21.3415L28.8824 13.4177H30.2119L31.9775 20.2779H31.6478L33.4772 13.4177H34.7854L36.6148 20.2779H36.2745L38.0507 13.4177H39.3695L37.1998 21.3415H35.7852L33.9452 14.5877H34.3174L32.4668 21.3415H31.0628Z" fill="#3F04FE"></path>
						<path d="M23.197 21.3415V13.4177H28.4192V14.5345H24.452V16.8106H28.2065V17.9274H24.452V20.2247H28.4192V21.3415H23.197Z" fill="#3F04FE"></path>
						<path d="M15.3333 21.3415V13.4176H16.3437L20.7895 19.5546L20.3109 19.6291V13.4176H21.5553V21.3415H20.5449L16.131 15.1619L16.5883 15.0769V21.3415H15.3333Z" fill="#3F04FE"></path>
					</svg>
				</div>

				<div class="wpvr-promo-banner__text">
					<span class="wpvr-promo-banner__title"><?php esc_html_e( 'WPVR New UI, Save Big', 'wpvr' ); ?></span>
					<span class="wpvr-promo-banner__subtitle"><?php esc_html_e( 'Flat 30% Off', 'wpvr' ); ?></span>
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
					<span><?php esc_html_e( 'Get 30% OFF', 'wpvr' ); ?></span>
					<svg class="wpvr-promo-banner__cta-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M2.5 9.5L9.5 2.5M9.5 2.5H4M9.5 2.5V8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			</div>

			<button type="button" class="wpvr-promo-banner__dismiss" id="wpvr-promo-banner-dismiss" aria-label="<?php esc_attr_e( 'Dismiss banner permanently', 'wpvr' ); ?>">
				<svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M1 1L10 10M10 1L1 10" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
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
