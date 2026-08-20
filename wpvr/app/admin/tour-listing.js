(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( ! document.body.classList.contains( 'wpvr-tour-list' ) ) {
			return;
		}

		var wrap = document.querySelector( '#wpbody-content > .wrap' );
		if ( ! wrap ) {
			return;
		}

		prepareHeader( wrap );
		prepareToolbar( wrap );
		prepareSearch( wrap );
		prepareCheckboxes( wrap );
		prepareEmptyState( wrap );
		prepareShortcodeCopy();
		revealCompletedLayout();
	} );

	function prepareShortcodeCopy() {
		document.addEventListener( 'click', function ( event ) {
			var copyButton = event.target.closest( '.wpvr-copy-shortcode-listing' );

			if ( ! copyButton ) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			event.stopImmediatePropagation();

			copyShortcode( copyButton.getAttribute( 'data-shortcode' ) || '' )
				.then( function () {
					showListingToast( 'success', 'Shortcode copied.' );
				} )
				.catch( function () {
					showListingToast( 'error', 'Unable to copy shortcode.' );
				} );
		}, true );
	}

	function copyShortcode( shortcode ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( shortcode );
		}

		return new Promise( function ( resolve, reject ) {
			var textArea = document.createElement( 'textarea' );

			textArea.value = shortcode;
			textArea.setAttribute( 'readonly', '' );
			textArea.style.position = 'fixed';
			textArea.style.top = '-9999px';
			textArea.style.opacity = '0';
			document.body.appendChild( textArea );
			textArea.focus();
			textArea.select();
			textArea.setSelectionRange( 0, textArea.value.length );

			try {
				if ( document.execCommand( 'copy' ) ) {
					resolve();
				} else {
					reject( new Error( 'Copy command failed.' ) );
				}
			} catch ( error ) {
				reject( error );
			} finally {
				textArea.remove();
			}
		} );
	}

	function showListingToast( type, message ) {
		var currentToast = document.querySelector( '.wpvr-listing-toast' );
		if ( currentToast ) {
			currentToast.remove();
		}

		var toast = document.createElement( 'div' );
		toast.className = 'wpvr-toast wpvr-toast--' + type + ' wpvr-listing-toast';
		toast.setAttribute( 'role', 'status' );
		toast.setAttribute( 'aria-live', 'polite' );
		toast.textContent = message;
		document.body.appendChild( toast );

		window.setTimeout( function () {
			if ( toast.parentNode ) {
				toast.remove();
			}
		}, 3200 );
	}

	function revealCompletedLayout() {
		window.requestAnimationFrame( function () {
			window.requestAnimationFrame( function () {
				document.body.classList.add( 'wpvr-tour-list-ready' );
			} );
		} );
	}

	function prepareHeader( wrap ) {
		var heading = wrap.querySelector( 'h1.wp-heading-inline' );
		if ( ! heading ) {
			return;
		}

		var header = document.createElement( 'div' );
		header.className = 'wpvr-listing-header';
		heading.parentNode.insertBefore( header, heading );
		header.appendChild( heading );

		Array.prototype.slice.call( wrap.children ).forEach( function ( child ) {
			if ( child.classList && child.classList.contains( 'page-title-action' ) ) {
				header.appendChild( child );
			}
		} );

		var notices = document.querySelectorAll(
			'#wpvr-ui-mode-notice, #wpvr-license-fallback-notice, #wpvr-onboarding-notice, .wpvr-migration-notice'
		);
		if ( notices.length ) {
			var noticeArea = document.createElement( 'div' );
			noticeArea.className = 'wpvr-listing-notices';
			wrap.insertBefore( noticeArea, header );
			Array.prototype.forEach.call( notices, function ( notice ) {
				noticeArea.appendChild( notice );
			} );
		}
	}

	function prepareToolbar( wrap ) {
		var topNav = wrap.querySelector( '#posts-filter .tablenav.top' );
		var search = wrap.querySelector( '#posts-filter .search-box' );
		if ( topNav && search ) {
			topNav.appendChild( search );
		}
	}

	function prepareSearch( wrap ) {
		var input = wrap.querySelector( '#post-search-input' );
		var submit = wrap.querySelector( '#search-submit' );
		var form = wrap.querySelector( '#posts-filter' );

		if ( input ) {
			input.placeholder = 'Search Tour';
			input.setAttribute( 'aria-label', 'Search Tour' );
		}

		if ( submit ) {
			submit.setAttribute( 'aria-label', 'Search Tour' );
		}

		if ( input && form ) {
			bindLiveSearch( wrap, form, input );
		}
	}

	function bindLiveSearch( wrap, form, input ) {
		var debounceTimer = null;
		var activeRequest = null;
		var liveStatus = document.createElement( 'span' );

		liveStatus.className = 'screen-reader-text';
		liveStatus.setAttribute( 'aria-live', 'polite' );
		form.appendChild( liveStatus );

		input.addEventListener( 'input', function ( event ) {
			if ( event.isComposing ) {
				return;
			}

			window.clearTimeout( debounceTimer );
			debounceTimer = window.setTimeout( function () {
				requestSearchResults( wrap, form, input, liveStatus );
			}, 350 );
		} );

		function requestSearchResults( listingWrap, listingForm, searchInput, status ) {
			var requestUrl = buildSearchUrl( listingForm, searchInput.value );

			if ( activeRequest ) {
				activeRequest.abort();
			}

			activeRequest = new AbortController();
			listingForm.classList.add( 'wpvr-listing-searching' );
			listingForm.setAttribute( 'aria-busy', 'true' );

			window.fetch( requestUrl.toString(), {
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
				},
				signal: activeRequest.signal,
			} )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'Search request failed.' );
					}

					return response.text();
				} )
				.then( function ( html ) {
					var parsedDocument = new window.DOMParser().parseFromString( html, 'text/html' );
					var nextTable = parsedDocument.querySelector( 'table.wp-list-table' );
					var nextBottomNav = parsedDocument.querySelector( '#posts-filter .tablenav.bottom' );
					var currentTable = listingForm.querySelector( 'table.wp-list-table' );
					var currentBottomNav = listingForm.querySelector( '.tablenav.bottom' );
					var hasResults = ! nextTable || ! nextTable.querySelector( 'tbody .no-items' );

					if ( ! nextTable || ! currentTable ) {
						throw new Error( 'Search results were not found.' );
					}

					currentTable.replaceWith( nextTable );
					if ( hasResults && nextBottomNav ) {
						if ( currentBottomNav ) {
							currentBottomNav.replaceWith( nextBottomNav );
						} else {
							nextTable.insertAdjacentElement( 'afterend', nextBottomNav );
						}
					} else if ( currentBottomNav ) {
						currentBottomNav.remove();
					}

					window.history.replaceState( {}, '', requestUrl.toString() );
					prepareCheckboxes( listingWrap );
					prepareEmptyState( listingWrap );
					status.textContent = 'Search results updated.';
				} )
				.catch( function ( error ) {
					if ( error.name !== 'AbortError' ) {
						status.textContent = 'Unable to update search results.';
					}
				} )
				.finally( function () {
					listingForm.classList.remove( 'wpvr-listing-searching' );
					listingForm.removeAttribute( 'aria-busy' );
				} );
		}
	}

	function buildSearchUrl( form, searchTerm ) {
		var url = new URL( window.location.href );
		var formData = new window.FormData( form );
		var ignoredFields = [ 'action', 'action2', 'post[]', '_wpnonce', '_wp_http_referer' ];

		formData.forEach( function ( value, key ) {
			if ( ignoredFields.indexOf( key ) === -1 && typeof value === 'string' ) {
				url.searchParams.set( key, value );
			}
		} );

		url.searchParams.set( 's', searchTerm );
		url.searchParams.delete( 'paged' );

		return url;
	}

	function prepareCheckboxes( wrap ) {
		var table = wrap.querySelector( 'table.wp-list-table' );
		if ( ! table ) {
			return;
		}

		var selectAll = table.querySelectorAll(
			'thead .check-column input[type="checkbox"], tfoot .check-column input[type="checkbox"]'
		);

		function rowCheckboxes() {
			return table.querySelectorAll( 'tbody .check-column input[type="checkbox"][name="post[]"]' );
		}

		function updateSelectAll() {
			var rows = rowCheckboxes();
			var checked = table.querySelectorAll(
				'tbody .check-column input[type="checkbox"][name="post[]"]:checked'
			).length;

			Array.prototype.forEach.call( selectAll, function ( checkbox ) {
				checkbox.checked = rows.length > 0 && checked === rows.length;
				checkbox.indeterminate = checked > 0 && checked < rows.length;
			} );
		}

		Array.prototype.forEach.call( selectAll, function ( checkbox ) {
			checkbox.addEventListener( 'change', function () {
				var shouldCheck = checkbox.checked;
				Array.prototype.forEach.call( rowCheckboxes(), function ( rowCheckbox ) {
					rowCheckbox.checked = shouldCheck;
				} );
				updateSelectAll();
			} );
		} );

		table.addEventListener( 'change', function ( event ) {
			if (
				event.target.matches(
					'tbody .check-column input[type="checkbox"][name="post[]"]'
				)
			) {
				updateSelectAll();
			}
		} );

		updateSelectAll();
	}

	function prepareEmptyState( wrap ) {
		var emptyCell = wrap.querySelector( 'table.wp-list-table tbody .no-items td' );
		if ( ! emptyCell ) {
			return;
		}

		var emptyState = document.createElement( 'div' );
		emptyState.className = 'wpvr-listing-empty';
		emptyState.innerHTML =
			'<svg class="wpvr-listing-empty__icon" aria-hidden="true" width="65" height="52" viewBox="0 0 65 52" fill="none" xmlns="http://www.w3.org/2000/svg">' +
			'<path d="M0.5 3.18066C0.500022 1.22699 2.42411 -0.0335511 3.95801 0.733398L3.99023 0.749023L4.02344 0.759766C9.84718 2.70101 19.5527 5.28906 32.167 5.28906C44.7758 5.28902 53.8382 3.02515 60.3164 0.757812L60.3467 0.74707L60.375 0.733398C61.9089 -0.0335512 63.833 1.22699 63.833 3.18066V48.2139C63.833 50.1557 62.202 51.4916 60.2959 50.9512C53.831 48.6897 44.7724 46.1055 32.167 46.1055C19.5455 46.1055 10.8021 48.6965 4.02344 50.9561C2.43759 51.4847 0.5 50.146 0.5 48.2139V3.18066Z" fill="white" stroke="black"/>' +
			'<path d="M30.8906 18.7724C30.8906 18.1456 30.7687 17.5251 30.5318 16.9461C30.2949 16.367 29.9477 15.8409 29.5099 15.3978C29.0722 14.9546 28.5525 14.6031 27.9806 14.3633C27.4087 14.1234 26.7957 14 26.1767 14C25.5576 14 24.9447 14.1234 24.3727 14.3633C23.8008 14.6031 23.2811 14.9546 22.8434 15.3978C22.4057 15.8409 22.0585 16.367 21.8216 16.9461C21.5847 17.5251 21.4627 18.1456 21.4627 18.7724C21.4627 20.0381 21.9594 21.2519 22.8434 22.1469C23.7274 23.0419 24.9265 23.5447 26.1767 23.5447C27.4269 23.5447 28.6259 23.0419 29.5099 22.1469C30.394 21.2519 30.8906 20.0381 30.8906 18.7724ZM35.6093 21.3304L30.3143 29.6677L27.4436 25.0158L17.9273 37.8618H46L35.6093 21.3304Z" fill="black"/>' +
			'</svg>' +
			'<p>No tour created.</p>' +
			'<button type="button" class="wpvr-listing-empty__button">Add Your First Tour</button>';

		emptyCell.textContent = '';
		emptyCell.appendChild( emptyState );

		emptyState.querySelector( 'button' ).addEventListener( 'click', function () {
			var addButton = wrap.querySelector(
				'.wpvr-listing-header .page-title-action:not(.wpvr-import-button)'
			);
			if ( addButton ) {
				addButton.click();
			}
		} );
	}
} )();
