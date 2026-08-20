/* global wpvrListingModal */
(function () {
	'use strict';

	var cfg         = wpvrListingModal;
	var selectedType = 'image';
	var isSubmitting = false;

	document.addEventListener( 'DOMContentLoaded', function () {
		var overlay   = document.getElementById( 'wpvr-ctm-overlay' );
		var nameInput = document.getElementById( 'wpvr-ctm-name' );
		var nameError = document.getElementById( 'wpvr-ctm-name-error' );
		var submitBtn = document.getElementById( 'wpvr-ctm-submit' );
		var cards     = document.querySelectorAll( '.wpvr-ctm__card:not([disabled])' );

		// Intercept "Add New" button.
		var addNewLinks = document.querySelectorAll( 'a.page-title-action:not(.wpvr-import-button), a[href*="post-new.php?post_type=wpvr_item"]' );
		addNewLinks.forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				openModal();
			} );
		} );

		// Close triggers.
		document.getElementById( 'wpvr-ctm-close' ).addEventListener( 'click', closeModal );
		document.getElementById( 'wpvr-ctm-cancel' ).addEventListener( 'click', closeModal );

		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) closeModal();
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && overlay.style.display !== 'none' ) closeModal();
		} );

		// Card selection.
		cards.forEach( function ( card ) {
			card.addEventListener( 'click', function () {
				selectCard( this );
			} );
		} );

		// Name input — clear error on type.
		nameInput.addEventListener( 'input', function () {
			nameError.style.display = 'none';
		} );

		// Submit.
		submitBtn.addEventListener( 'click', handleSubmit );

		// Allow Enter in name field to submit.
		nameInput.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' ) {
				e.preventDefault();
				handleSubmit();
			}
		} );
	} );

	function openModal() {
		var overlay   = document.getElementById( 'wpvr-ctm-overlay' );
		var nameInput = document.getElementById( 'wpvr-ctm-name' );
		var nameError = document.getElementById( 'wpvr-ctm-name-error' );
		var submitBtn = document.getElementById( 'wpvr-ctm-submit' );

		nameInput.value            = '';
		nameError.style.display    = 'none';
		submitBtn.textContent      = '+ Create Tour';
		submitBtn.disabled         = false;
		isSubmitting               = false;

		// Reset to image selection.
		selectCardByType( 'image' );

		overlay.style.display = 'flex';
		setTimeout( function () { nameInput.focus(); }, 60 );
	}

	function closeModal() {
		document.getElementById( 'wpvr-ctm-overlay' ).style.display = 'none';
	}

	function selectCard( card ) {
		document.querySelectorAll( '.wpvr-ctm__card' ).forEach( function ( c ) {
			c.classList.remove( 'wpvr-ctm__card--selected' );
			var radio = c.querySelector( '.wpvr-ctm__card-radio' );
			if ( radio ) radio.classList.remove( 'wpvr-ctm__card-radio--checked' );
			var img = c.querySelector( '.wpvr-ctm__card-icon' );
			if ( img && img.dataset.inactive ) img.src = img.dataset.inactive;
		} );

		card.classList.add( 'wpvr-ctm__card--selected' );
		var radio = card.querySelector( '.wpvr-ctm__card-radio' );
		if ( radio ) radio.classList.add( 'wpvr-ctm__card-radio--checked' );
		var img = card.querySelector( '.wpvr-ctm__card-icon' );
		if ( img && img.dataset.active ) img.src = img.dataset.active;

		selectedType = card.dataset.type;
	}

	function selectCardByType( type ) {
		var card = document.querySelector( '.wpvr-ctm__card[data-type="' + type + '"]' );
		if ( card && ! card.disabled ) selectCard( card );
	}

	function handleSubmit() {
		if ( isSubmitting ) return;

		var nameInput = document.getElementById( 'wpvr-ctm-name' );
		var nameError = document.getElementById( 'wpvr-ctm-name-error' );
		var submitBtn = document.getElementById( 'wpvr-ctm-submit' );
		var name      = nameInput.value.trim();

		if ( ! name ) {
			nameError.style.display = 'block';
			nameInput.focus();
			return;
		}

		isSubmitting          = true;
		submitBtn.textContent = 'Creating…';
		submitBtn.disabled    = true;

		fetch( cfg.apiBase + '/tours', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.nonce,
			},
			body: JSON.stringify( { title: name, tourType: selectedType } ),
		} )
			.then( function ( res ) { return res.json(); } )
			.then( function ( data ) {
				if ( data.id ) {
					window.location.href = cfg.editorUrl + '&tour_id=' + data.id;
				} else {
					throw new Error( 'No id in response' );
				}
			} )
			.catch( function () {
				submitBtn.textContent = '+ Create Tour';
				submitBtn.disabled    = false;
				isSubmitting          = false;
				alert( 'Failed to create tour. Please try again.' );
			} );
	}
} )();
