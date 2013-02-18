<?php

function dk_speakup_settings_page() {

	// security check
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Insufficient privileges: You need to be an administrator to do that.' );

	include_once( 'class.speakup.php' );
	include_once( 'class.settings.php' );
	include_once( 'class.wpml.php' );
	$the_settings = new dk_speakup_Settings();
	$wpml         = new dk_speakup_WPML();

	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	$tab    = isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'dk-speakup-tab-01';

	switch( $action ) {

		case 'update' :

			// security check
			check_admin_referer( 'dk_speakup-update_settings' );

			$the_settings->update();
			$the_settings->retrieve();

			// attempt to resgister strings for translation in WPML
			$options = get_option( 'dk_speakup_options' );
			$wpml->register_options( $options );

			$message_update = __( 'Settings updated.', 'dk_speakup' );

			break;

		default :

			$the_settings->retrieve();

			$message_update = '';
	}

	$nonce  = 'dk_speakup-update_settings';
	$action = 'update';
	include_once( dirname( __FILE__ ) . '/settings.view.php' );
}

?>