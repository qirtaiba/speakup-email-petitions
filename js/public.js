jQuery( document ).ready( function( $ ) {
	'use strict';

	$( '.dk-speakup-submit' ).click( function( e ) {
		e.preventDefault();

		var id             = $( this ).attr( 'name' ),
			lang           = $( '#dk-speakup-lang-' + id ).val(),
			firstname      = $( '#dk-speakup-first-name-' + id ).val(),
			lastname       = $( '#dk-speakup-last-name-' + id ).val(),
			email          = $( '#dk-speakup-email-' + id ).val(),
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
		if ( email === '' || !emailRegEx.test( email ) ) {
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

	// hide or show form labels depending on input fields
	$( '.dk-speakup-petition-wrap input[type=text]' ).focus( function( e ) {
		var label = $( this ).siblings( 'label' );
		if ( $( this ).val() === '' ) {
			$( this ).siblings( 'label' ).addClass( 'dk-speakup-focus' ).removeClass( 'dk-speakup-blur' );
		}
		$( this ).blur( function(){
			if ( this.value === '' ) {
				label.addClass( 'dk-speakup-blur' ).removeClass( 'dk-speakup-focus' );
			}
		}).focus( function() {
			label.addClass( 'dk-speakup-focus' ).removeClass( 'dk-speakup-blur' );
		}).keydown( function( e ) {
			label.addClass( 'dk-speakup-focus' ).removeClass( 'dk-speakup-blur' );
			$( this ).unbind( e );
		});
	});

	// hide labels on filled input fields when page is reloaded
	$( '.dk-speakup-petition-wrap input[type=text]' ).each( function() {
		if ( $( this ).val() !== '' ) {
			$( this ).siblings( 'label' ).addClass( 'dk-speakup-focus' );
		}
	});

});