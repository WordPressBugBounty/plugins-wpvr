(function () {
	'use strict';

	function initBanner() {
		var banner = document.getElementById( 'wpvr-promotional-banner' );
		if ( ! banner ) {
			return;
		}

		positionBanner( banner );
		initCountdown( banner );
		initDismiss( banner );
	}

	function positionBanner( banner ) {
		var wrap = document.querySelector( '#wpbody-content > .wrap' );
		if ( ! wrap ) {
			return;
		}

		var noticeArea = wrap.querySelector( '.wpvr-listing-notices' );
		if ( noticeArea ) {
			if ( noticeArea.firstChild !== banner ) {
				noticeArea.insertBefore( banner, noticeArea.firstChild );
			}
			return;
		}

		var header = wrap.querySelector( '.wpvr-listing-header' ) || wrap.querySelector( 'h1.wp-heading-inline' );
		if ( header ) {
			wrap.insertBefore( banner, header );
		} else {
			wrap.insertBefore( banner, wrap.firstChild );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initBanner );
	} else {
		initBanner();
	}

	function initCountdown( banner ) {
		var rawEnd = banner.getAttribute( 'data-end-time' );
		var endTimestamp = rawEnd ? parseInt( rawEnd, 10 ) : 0;

		if ( ! endTimestamp && window.wpvrPromoBanner && window.wpvrPromoBanner.endTimestamp ) {
			endTimestamp = parseInt( window.wpvrPromoBanner.endTimestamp, 10 );
		}

		if ( ! endTimestamp ) {
			return;
		}

		var daysEl = document.getElementById( 'wpvr-promo-days' );
		var hoursEl = document.getElementById( 'wpvr-promo-hours' );
		var minsEl = document.getElementById( 'wpvr-promo-mins' );
		var secsEl = document.getElementById( 'wpvr-promo-secs' );

		function pad( n ) {
			return n < 10 ? '0' + n : '' + n;
		}

		function tick() {
			var now = Math.floor( Date.now() / 1000 );
			var diff = endTimestamp - now;

			if ( diff <= 0 ) {
				if ( daysEl ) daysEl.textContent = '00';
				if ( hoursEl ) hoursEl.textContent = '00';
				if ( minsEl ) minsEl.textContent = '00';
				if ( secsEl ) secsEl.textContent = '00';

				// Auto-hide banner once campaign has expired
				banner.classList.add( 'is-dismissing' );
				setTimeout( function () {
					if ( banner.parentNode ) {
						banner.remove();
					}
				}, 400 );
				return false;
			}

			var days = Math.floor( diff / 86400 );
			var hours = Math.floor( ( diff % 86400 ) / 3600 );
			var mins = Math.floor( ( diff % 3600 ) / 60 );
			var secs = diff % 60;

			if ( daysEl ) daysEl.textContent = pad( days );
			if ( hoursEl ) hoursEl.textContent = pad( hours );
			if ( minsEl ) minsEl.textContent = pad( mins );
			if ( secsEl ) secsEl.textContent = pad( secs );

			return true;
		}

		// Initial tick and start interval
		if ( tick() ) {
			var timer = setInterval( function () {
				if ( ! tick() ) {
					clearInterval( timer );
				}
			}, 1000 );
		}
	}

	function initDismiss( banner ) {
		var dismissBtn = document.getElementById( 'wpvr-promo-banner-dismiss' );
		if ( ! dismissBtn ) {
			return;
		}

		dismissBtn.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			// Immediately animate out
			banner.classList.add( 'is-dismissing' );
			setTimeout( function () {
				if ( banner.parentNode ) {
					banner.remove();
				}
			}, 360 );

			// Dispatch permanent dismiss AJAX
			if ( ! window.wpvrPromoBanner || ! window.wpvrPromoBanner.ajaxUrl ) {
				return;
			}

			var body = new URLSearchParams();
			body.append( 'action', window.wpvrPromoBanner.action || 'wpvr_dismiss_promo_banner' );
			body.append( 'nonce', window.wpvrPromoBanner.nonce || '' );

			fetch( window.wpvrPromoBanner.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: body.toString(),
			} ).catch( function () {
				// Silently fail if network drops
			} );
		} );
	}
} )();
