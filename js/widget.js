jQuery( document ).ready( function( $ ) {
	'use strict';

	// display required asteriscs
	$( '.dk-speakup-widget-popup-wrap label.required' ).append('<span> *</span>');

	// run only if widget is on the page
	if( $( '.dk-speakup-widget-wrap' ).length ) {
		$( '.dk-speakup-widget-button' ).click( function( e ) {
			var petition_form = '#' + $( this ).attr( 'rel' ),
				screenHeight  = $( document ).height(),
				screenWidth   = $( window ).width(),
				windowHeight  = $( window ).height(),
				windowWidth   = $( window ).width();

			$( '#dk-speakup-windowshade' ).css( {
				'width' : screenWidth,
				'height' : screenHeight
			});
			$( '#dk-speakup-windowshade' ).fadeTo( 500, 0.8 );

			// center the pop-up window
			$( petition_form ).css( 'top',  ( ( windowHeight / 2 ) - ( $( petition_form ).height() / 2 ) ) );
			$( petition_form ).css( 'left', ( windowWidth / 2 ) - ( $( petition_form ).width() / 2 ) );

			// display the form
			$( petition_form ).fadeIn( 500 );
		});

		/* Close the pop-up petition form */
		// by clicking windowshade area
		$( '#dk-speakup-windowshade' ).click( function () {
			$( this ).fadeOut( 'slow' );
			$( '.dk-speakup-widget-popup-wrap' ).hide();
		});
		// or by clicking the close button
		$( '.dk-speakup-widget-close' ).click( function() {
			$( '#dk-speakup-windowshade' ).fadeOut( 'slow' );
			$( '.dk-speakup-widget-popup-wrap' ).hide();
		});
		// or by pressing ESC
		$( document ).keyup( function( e ) {
			if ( e.keyCode === 27 ) {
				$( '#dk-speakup-windowshade' ).fadeOut( 'slow' );
				$( '.dk-speakup-widget-popup-wrap' ).hide();
			}
		});

		// process petition form submissions
		$( '.dk-speakup-widget-submit' ).click( function( e ) {
			e.preventDefault();

			var id             = $( this ).attr( 'name' ),
				current_url    = document.URL,
				share_url      = $( '#dk-speakup-widget-shareurl-' + id ).val(),
				posttitle      = $( '#dk-speakup-widget-posttitle-' + id ).val(),
				tweet          = $( '#dk-speakup-widget-tweet-' + id ).val(),
				lang           = $( '#dk-speakup-widget-lang-' + id ).val(),
				firstname      = $( '#dk-speakup-widget-first-name-' + id ).val(),
				lastname       = $( '#dk-speakup-widget-last-name-' + id ).val(),
				email          = $( '#dk-speakup-widget-email-' + id ).val(),
				email_confirm  = $( '#dk-speakup-widget-email-confirm-' + id ).val(),
				street         = $( '#dk-speakup-widget-street-' + id ).val(),
				city           = $( '#dk-speakup-widget-city-' + id ).val(),
				state          = $( '#dk-speakup-widget-state-' + id ).val(),
				postcode       = $( '#dk-speakup-widget-postcode-' + id ).val(),
				country        = $( '#dk-speakup-widget-country-' + id ).val(),
				custom_field   = $( '#dk-speakup-widget-custom-field-' + id ).val(),
				custom_message = $( 'textarea#dk-speakup-widget-message-' + id ).val(),
				optin          = '';

			if ( share_url === '' ) {
				share_url = current_url.split('#')[0];
			}

			if ( $( '#dk-speakup-widget-optin-' + id ).attr( 'checked' ) ) {
				optin = 'on';
			}

			// make sure error notices are turned off before checking for new errors
			$( '#dk-speakup-widget-popup-wrap-' + id + ' input' ).removeClass( 'dk-speakup-error' );

			// validate form values
			var errors = 0,
				emailRegEx = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

			if ( typeof email_confirm !== undefined && email_confirm !== email ) {
				console.log('gotit');
				$( '#dk-speakup-widget-email-' + id ).addClass( 'dk-speakup-error' );
				$( '#dk-speakup-widget-email-confirm-' + id ).addClass( 'dk-speakup-error' );
				errors ++;
			}
			if ( email === '' || !emailRegEx.test( email ) ) {
				$( '#dk-speakup-widget-email-' + id ).addClass( 'dk-speakup-error' );
				errors ++;
			}
			if ( firstname === '' ) {
				$( '#dk-speakup-widget-first-name-' + id ).addClass( 'dk-speakup-error' );
				errors ++;
			}
			if ( lastname === '' ) {
				$( '#dk-speakup-widget-last-name-' + id ).addClass( 'dk-speakup-error' );
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
				$.post( dk_speakup_widget_js.ajaxurl, data,
					function( response ) {
						$( '#dk-speakup-widget-popup-wrap-' + id + ' .dk-speakup-widget-form' ).hide();
						$( '#dk-speakup-widget-popup-wrap-' + id + ' .dk-speakup-widget-response' ).fadeIn().html( response );
						$( '#dk-speakup-widget-popup-wrap-' + id + ' .dk-speakup-widget-share' ).fadeIn();

						// launch Facebook sharing window
						$( '.dk-speakup-widget-facebook' ).click( function() {
							var url = 'http://www.facebook.com/sharer.php?u=' + share_url + '&t=' + posttitle;
							window.open( url, 'facebook', 'height=420,width=550,left=100,top=100,resizable=yes,location=no,status=no,toolbar=no' );
						});
						// launch Twitter sharing window
						$( '.dk-speakup-widget-twitter' ).click( function() {
							var url = 'http://twitter.com/share?url=' + share_url + '&text=' + tweet;
							window.open( url, 'twitter', 'height=420,width=550,left=100,top=100,resizable=yes,location=no,status=no,toolbar=no' );
						});
					}
				);
			}
		});

		// hide or show form labels depending on input fields
		$( '.dk-speakup-widget-popup-wrap input[type=text]' ).focus( function( e ) {
			var label = $( this ).siblings( 'label' );
			if ( $( this ).val() === '' ) {
				$( this ).siblings( 'label' ).addClass( 'dk-speakup-widget-focus' ).removeClass( 'dk-speakup-widget-blur' );
			}
			$( this ).blur( function(){
				if ( this.value === '' ) {
					label.addClass( 'dk-speakup-blur' ).removeClass( 'dk-speakup-widget-focus' );
				}
			}).focus( function() {
				label.addClass( 'dk-speakup-widget-focus' ).removeClass( 'dk-speakup-widget-blur' );
			}).keydown( function( e ) {
				label.addClass( 'dk-speakup-widget-focus' ).removeClass( 'dk-speakup-widget-blur' );
				$( this ).unbind( e );
			});
		});

		// hide labels on filled input fields when page is reloaded
		$( '.dk-speakup-widget-popup-wrap input[type=text]' ).each( function() {
			if ( $( this ).val() !== '' ) {
				$( this ).siblings( 'label' ).addClass( 'dk-speakup-widget-focus' );
			}
		});

	}

});