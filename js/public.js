jQuery( document ).ready( function( $ ) {
	'use strict';

	// display required asterisks
	$( '.dk-speakup-petition label.required' ).append( '<span> *</span>');

	// handle form submission
	$( '.dk-speakup-submit' ).click( function( e ) {
		e.preventDefault();

		var id             = $( this ).attr( 'name' ),
			lang           = $( '#dk-speakup-lang-' + id ).val(),
			firstname      = $( '#dk-speakup-first-name-' + id ).val(),
			lastname       = $( '#dk-speakup-last-name-' + id ).val(),
			email          = $( '#dk-speakup-email-' + id ).val(),
			email_confirm  = $( '#dk-speakup-email-confirm-' + id ).val(),
			street         = $( '#dk-speakup-street-' + id ).val(),
			city           = $( '#dk-speakup-city-' + id ).val(),
			state          = $( '#dk-speakup-state-' + id ).val(),
			postcode       = $( '#dk-speakup-postcode-' + id ).val(),
			country        = $( '#dk-speakup-country-' + id ).val(),
			custom_field   = $( '#dk-speakup-custom-field-' + id ).val(),
			custom_message = $( 'textarea#dk-speakup-message-' + id ).val(),
			optin          = '';

		if ( $( '#dk-speakup-optin-' + id ).attr( 'checked' ) ) {
			optin = 'on';
		}

		// make sure error notices are turned off before checking for new errors
		$( '#dk-speakup-petition-' + id + ' input' ).removeClass( 'dk-speakup-error' );

		// validate form values
		var errors = 0,
			emailRegEx = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,6})?$/;

		if ( typeof email_confirm !== undefined ) {
			if ( email_confirm !== email ) {
				$( '#dk-speakup-email-' + id ).addClass( 'dk-speakup-error' );
				$( '#dk-speakup-email-confirm-' + id ).addClass( 'dk-speakup-error' );
				errors ++;
			}
		}
		if ( email === '' || ! emailRegEx.test( email ) ) {
			$( '#dk-speakup-email-' + id ).addClass( 'dk-speakup-error' );
			errors ++;
		}
		if ( firstname === '' ) {
			$( '#dk-speakup-first-name-' + id ).addClass( 'dk-speakup-error' );
			errors ++;
		}
		if ( lastname === '' ) {
			$( '#dk-speakup-last-name-' + id ).addClass( 'dk-speakup-error' );
			errors ++;
		}

		// if no errors found, submit the data via ajax
		if ( errors === 0 && $( this ).attr( 'rel' ) !== 'disabled' ) {

			// set rel to disabled as flag to block double clicks
			$( this ).attr( 'rel', 'disabled' );

			var data = {
				action:         'dk_speakup_sendmail',
				id:             id,
				first_name:     firstname,
				last_name:      lastname,
				email:          email,
				street:         street,
				city:           city,
				state:          state,
				postcode:       postcode,
				country:        country,
				custom_field:   custom_field,
				custom_message: custom_message,
				optin:          optin,
				lang:           lang
			};

			// submit form data and handle ajax response
			$.post( dk_speakup_js.ajaxurl, data,
				function( response ) {
					$( '#dk-speakup-petition-' + id + ' .dk-speakup-petition' ).fadeTo( 400, 0.35 );
					$( '#dk-speakup-petition-' + id + ' .dk-speakup-response' ).fadeIn().html( response );
				}
			);
		}
	});

	// launch Facebook sharing window
	$( '.dk-speakup-facebook' ).click( function( e ) {
		e.preventDefault();

		var id           = $( this ).attr( 'rel' ),
			posttitle    = $( '#dk-speakup-posttitle-' + id ).val(),
			share_url    = document.URL,
			facebook_url = 'http://www.facebook.com/sharer.php?u=' + share_url + '&amp;t=' + posttitle;

		window.open( facebook_url, 'facebook', 'height=400,width=550,left=100,top=100,resizable=yes,location=no,status=no,toolbar=no' );
	});

	// launch Twitter sharing window
	$( '.dk-speakup-twitter' ).click( function( e ) {
		e.preventDefault();

		var id          = $( this ).attr( 'rel' ),
			tweet       = $( '#dk-speakup-tweet-' + id ).val(),
			current_url = document.URL,
			share_url   = current_url.split('#')[0],
			twitter_url = 'http://twitter.com/share?url=' + share_url + '&text=' + tweet;

		window.open( twitter_url, 'twitter', 'height=400,width=550,left=100,top=100,resizable=yes,location=no,status=no,toolbar=no' );
	});

	$('a.dk-speakup-readme').click( function( e ) {
		e.preventDefault();

		var id = $( this ).attr( 'rel' ),
			sourceOffset = $(this).offset(),
			sourceTop    = sourceOffset.top - $(window).scrollTop(),
			sourceLeft   = sourceOffset.left - $(window).scrollLeft(),
			screenHeight  = $( document ).height(),
			screenWidth   = $( window ).width(),
			windowHeight = $( window ).height(),
			windowWidth  = $( window ).width(),
			readerHeight = 520,
			readerWidth  = 640,
			readerTop    = ( ( windowHeight / 2 ) - ( readerHeight / 2 ) ),
			readerLeft   = ( ( windowWidth / 2 ) - ( readerWidth / 2 ) ),
			petitionText = $( 'div#dk-speakup-message-' + id ).html(),
			reader       = '<div id="dk-speakup-reader"><div id="dk-speakup-reader-close"></div><div id="dk-speakup-reader-content"></div></div>';

		if ( petitionText === undefined ) {
			petitionText = $( '#dk-speakup-message-editable-' + id ).html();
		}

		$( '#dk-speakup-windowshade' ).css( {
				'width'  : screenWidth,
				'height' : screenHeight
			});
			$( '#dk-speakup-windowshade' ).fadeTo( 500, 0.8 );

		if ( $( '#dk-speakup-reader' ).length > 0 ) {
			$( '#dk-speakup-reader' ).remove();
		}

		$( 'body' ).append( reader );

		$('#dk-speakup-reader').css({
			background : '#fff',
			position   : 'fixed',
			left       : sourceLeft,
			top        : sourceTop,
			zIndex     : 100002
		});

		$('#dk-speakup-reader').animate({
			width  : readerWidth,
			height : readerHeight,
			top    : readerTop,
			left   : readerLeft
		}, 500, function() {
			$( '#dk-speakup-reader-content' ).html( petitionText );
		});
	});

	/* Close the pop-up petition reader */
	// by clicking windowshade area
	$( '#dk-speakup-windowshade' ).click( function () {
		$( this ).fadeOut( 'slow' );
		$( '#dk-speakup-reader' ).hide();
	});
	// or by clicking the close button
	$( '#dk-speakup-reader-close' ).live( 'click', function() {
		$( '#dk-speakup-windowshade' ).fadeOut( 'slow' );
		$( '#dk-speakup-reader' ).hide();
	});
	// or by pressing ESC
	$( document ).keyup( function( e ) {
		if ( e.keyCode === 27 ) {
			$( '#dk-speakup-windowshade' ).fadeOut( 'slow' );
			$( '#dk-speakup-reader' ).hide();
		}
	});

});