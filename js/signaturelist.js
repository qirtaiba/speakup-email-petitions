jQuery( document ).ready( function( $ ) {
	'use strict';

	// next pagination button is clicked
	$( '.dk-speakup-signaturelist-next' ).click( function( e ) {
		e.preventDefault();
		get_signaturelist( $( this ) );
	});

	// prev pagination button is clicked
	$( '.dk-speakup-signaturelist-prev' ).click( function( e ) {
		e.preventDefault();
		get_signaturelist( $( this ) );
	});

	// pagination: query new signatures and display results
	function get_signaturelist( button, link ) {
		// change button appearance to disabled while ajax request is processing
		$( this ).addClass( 'dk-speakup-signaturelist-disabled' );
		
		var link   = button.attr( 'rel' ).split( ',' ),
			id     = link[0],
			start  = link[1],
			limit  = link[2],
			total  = link[3],
			status = link[4],
			ajax   = {
				action: 'dk_speakup_paginate_signaturelist',
				id:         id,
				start:      start,
				limit:      limit,
				dateformat: dk_speakup_signaturelist_js.dateformat
			};

		if ( status === '1' ) {
			// submit data and handle ajax response
			$.post( dk_speakup_signaturelist_js.ajaxurl, ajax,
				function( response ) {
					var next_link = get_next_link( id, start, limit, total );
					var prev_link = get_prev_link( id, start, limit, total );

					toggle_button_display( id, next_link, prev_link );

					$( '.dk-speakup-signaturelist-' + id + ' tr:not(:last-child)' ).remove();
					$( '.dk-speakup-signaturelist-' + id ).prepend( response );
					$( '.dk-speakup-signaturelist-' + id + ' .dk-speakup-signaturelist-next' ).attr( 'rel', next_link );
					$( '.dk-speakup-signaturelist-' + id + ' .dk-speakup-signaturelist-prev' ).attr( 'rel', prev_link );
				}
			);
		}
	}

	// format new link for next pagination button
	function get_next_link( id, start, limit, total ) {
		var start = parseInt( start ),
			limit = parseInt( limit ),
			total = parseInt( total ),
			new_start = '',
			status    = '',
			link      = '';

		if ( start + limit  < total ) {
			new_start = start + limit;
			status = '1';
		}
		else {
			new_start = total;
			status = '0';
		}

		link = id + ',' + new_start + ',' + limit + ',' + total + ',' + status;
		return link;
	}

	// format new link for prev pagination button
	function get_prev_link( id, start, limit, total ) {
		var start = parseInt( start ),
			limit = parseInt( limit ),
			total = parseInt( total ),
			new_start = '',
			status    = '',
			link      = '';

		if ( start - limit >= 0 ) {
			new_start = start - limit;
			status = '1';
		}
		else {
			new_start = total;
			status = '0';
		}

		link = id + ',' + new_start + ',' + limit + ',' + total + ',' + status;
		return link;
	}

	function toggle_button_display( id, next_link, prev_link ) {
		if ( next_link.split( ',' )[4] === '0' ) {
			$( '.dk-speakup-signaturelist-' + id + ' .dk-speakup-signaturelist-next' ).addClass( 'dk-speakup-signaturelist-disabled' );
		}
		else {
			$( '.dk-speakup-signaturelist-' + id + ' .dk-speakup-signaturelist-next' ).removeClass( 'dk-speakup-signaturelist-disabled' );
		}

		if ( prev_link.split( ',' )[4] === '0' ) {
			$( '.dk-speakup-signaturelist-' + id + ' .dk-speakup-signaturelist-prev' ).addClass( 'dk-speakup-signaturelist-disabled' );
		}
		else {
			$( '.dk-speakup-signaturelist-' + id + ' .dk-speakup-signaturelist-prev' ).removeClass( 'dk-speakup-signaturelist-disabled' );
		}
	}

});