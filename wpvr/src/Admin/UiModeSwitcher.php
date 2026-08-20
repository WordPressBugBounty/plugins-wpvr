<?php

namespace RexTheme\WPVR\Admin;

class UiModeSwitcher {

	const POST_TYPE = 'wpvr_item';
	const PAGE_SLUG = 'wpvr-tour-editor';

	public static function init(): void {
		$instance = new self();

		add_action( 'admin_post_wpvr_switch_ui_mode', [ $instance, 'handle_switch' ] );
		add_action( 'admin_notices', [ $instance, 'render_notice' ] );
	}

	public function render_notice(): void {
		if ( ! current_user_can( 'edit_posts' ) || ! $this->is_target_screen() ) {
			return;
		}

		// New tour editor has its own "Classic View" button — no notice needed there.
		if ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === self::PAGE_SLUG ) {
			return;
		}

		$current_mode = $this->get_current_mode();
		$target_mode  = $current_mode === 'latest' ? 'legacy' : 'latest';
		$context      = $this->get_current_context();
		$tour_id      = $this->get_current_tour_id();

		$button_label = $current_mode === 'latest'
			? __( 'Switch to Classic UI', 'wpvr' )
			: __( 'Switch to New UI', 'wpvr' );

		$message = $current_mode === 'latest'
			? __( 'You are using the latest WP VR editor.', 'wpvr' )
			: __( 'You are using the classic WP VR editor.', 'wpvr' );
		?>
		<style>
			.wpvr-ui-mode-notice.notice {
				box-sizing: border-box;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 24px;
				min-height: 48px;
				margin: 5px 0 2px 0;
				padding: 6px 18px 6px 20px;
				background: #fff;
				border: 0;
				border-left: 2px solid #3f04fe;
				border-radius: 5px;
				box-shadow: none;
			}
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__message {
				flex: 1 1 auto;
				margin: 0;
				color: #212121;
				font-family: Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
				font-size: 15px;
				font-weight: 400;
				line-height: 20px;
			}
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__actions {
				display: flex;
				flex: 0 0 auto;
				align-items: center;
				gap: 64px;
			}
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__switch {
				box-sizing: border-box;
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-width: 142px;
				height: 34px;
				padding: 0 19px;
				color: #3f04fe;
				font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
				font-size: 14px;
				font-weight: 500;
				line-height: 16px;
				text-decoration: none;
				white-space: nowrap;
				background: #fff;
				border: 1px solid #3f04fe;
				border-radius: 6px;
			}
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__switch:hover,
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__switch:focus {
				color: #fff;
				background: #3f04fe;
				border-color: #3f04fe;
			}
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__dismiss {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 32px;
				height: 32px;
				margin: 0 -2px 0 0;
				padding: 0;
				cursor: pointer;
				background: transparent;
				border: 0;
				border-radius: 4px;
			}
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__dismiss:hover,
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__dismiss:focus {
				background: #f4f4f4;
				outline: none;
			}
			.wpvr-ui-mode-notice .wpvr-ui-mode-notice__dismiss:focus-visible {
				box-shadow: 0 0 0 1px #3f04fe;
			}
			@media screen and (max-width: 782px) {
				.wpvr-ui-mode-notice.notice {
					align-items: flex-start;
					margin-right: 10px;
					padding: 10px 12px;
				}
				.wpvr-ui-mode-notice .wpvr-ui-mode-notice__actions {
					gap: 8px;
				}
			}
		</style>
		<div id="wpvr-ui-mode-notice" class="notice wpvr-ui-mode-notice">
			<p class="wpvr-ui-mode-notice__message"><?php echo esc_html( $message ); ?></p>
			<div class="wpvr-ui-mode-notice__actions">
				<a class="wpvr-ui-mode-notice__switch" href="<?php echo esc_url( $this->get_switch_url( $target_mode, $context, $tour_id ) ); ?>">
					<?php echo esc_html( $button_label ); ?>
				</a>
				<button type="button" class="wpvr-ui-mode-notice__dismiss" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'wpvr' ); ?>">
					<svg width="9" height="9" viewBox="0 0 9 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M7.77482 0.75L0.75 7.75" stroke="#7E7E7E" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7.77482 7.75L0.75 0.75" stroke="#7E7E7E" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
		</div>
		<script>
			(function() {
				var notice = document.getElementById('wpvr-ui-mode-notice');
				var dismissButton = notice ? notice.querySelector('.wpvr-ui-mode-notice__dismiss') : null;

				if (dismissButton) {
					dismissButton.addEventListener('click', function() {
						notice.remove();
					});
				}
			}());
		</script>
		<?php
	}

	public function handle_switch(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You are not allowed to switch UI modes.', 'wpvr' ) );
		}

		check_admin_referer( 'wpvr_switch_ui_mode' );

		$mode    = isset( $_GET['mode'] ) && sanitize_key( $_GET['mode'] ) === 'latest' ? 'latest' : 'legacy';
		$context = isset( $_GET['context'] ) ? sanitize_key( $_GET['context'] ) : 'listing';
		$tour_id = isset( $_GET['tour_id'] ) ? absint( $_GET['tour_id'] ) : 0;

		update_option( 'wpvr_ui_mode', $mode );

		wp_safe_redirect( $this->get_redirect_url( $mode, $context, $tour_id ) );
		exit;
	}

	private function is_target_screen(): bool {
		if ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === self::PAGE_SLUG ) {
			return true;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		return $screen->id === self::POST_TYPE;
	}

	private function get_current_mode(): string {
		return get_option( 'wpvr_ui_mode', 'legacy' ) === 'latest' ? 'latest' : 'legacy';
	}

	private function get_current_context(): string {
		if ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === self::PAGE_SLUG ) {
			return isset( $_GET['tour_id'] ) ? 'edit' : 'new';
		}

		if ( isset( $_GET['post'] ) ) {
			return 'edit';
		}

		if ( isset( $_GET['post_type'] ) && sanitize_key( $_GET['post_type'] ) === self::POST_TYPE && strpos( (string) $_SERVER['PHP_SELF'], 'post-new.php' ) !== false ) {
			return 'new';
		}

		return 'listing';
	}

	private function get_current_tour_id(): int {
		if ( isset( $_GET['tour_id'] ) ) {
			return absint( $_GET['tour_id'] );
		}

		if ( isset( $_GET['post'] ) ) {
			return absint( $_GET['post'] );
		}

		return 0;
	}

	private function get_switch_url( string $mode, string $context, int $tour_id = 0 ): string {
		$args = [
			'action'  => 'wpvr_switch_ui_mode',
			'mode'    => $mode,
			'context' => $context,
		];

		if ( $tour_id > 0 ) {
			$args['tour_id'] = $tour_id;
		}

		return wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), 'wpvr_switch_ui_mode' );
	}

	private function get_redirect_url( string $mode, string $context, int $tour_id = 0 ): string {
		if ( $mode === 'latest' ) {
			if ( $context === 'edit' && $tour_id > 0 ) {
				return add_query_arg(
					[
						'page'    => self::PAGE_SLUG,
						'tour_id' => $tour_id,
					],
					admin_url( 'admin.php' )
				);
			}

			if ( $context === 'new' ) {
				return add_query_arg( [ 'page' => self::PAGE_SLUG ], admin_url( 'admin.php' ) );
			}

			return admin_url( 'edit.php?post_type=' . self::POST_TYPE );
		}

		if ( $context === 'edit' && $tour_id > 0 ) {
			return add_query_arg(
				[
					'post'   => $tour_id,
					'action' => 'edit',
				],
				admin_url( 'post.php' )
			);
		}

		if ( $context === 'new' ) {
			return admin_url( 'post-new.php?post_type=' . self::POST_TYPE );
		}

		return admin_url( 'edit.php?post_type=' . self::POST_TYPE );
	}
}
