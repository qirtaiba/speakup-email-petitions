<?php
/*
Plugin Name: SpeakUp! Email Petitions
Plugin URI: http://speakup.designkode.com/
Description: Create custom email petition forms and include them on your site via shortcode or widget. Signatures are saved in the database and can be exported to CSV.
Version: 2.4.2
Author: Kreg Wallace
Author URI: http://speakup.designkode.com/kreg-wallace
License: GPL2
*/

global $wpdb, $db_petitions, $db_signatures, $dk_speakup_version;
$db_petitions  = $wpdb->prefix . 'dk_speakup_petitions';
$db_signatures = $wpdb->prefix . 'dk_speakup_signatures';
$dk_speakup_version = '2.4.2';

// enable localizations
add_action( 'init', 'dk_speakup_translate' );
function dk_speakup_translate() {
	load_plugin_textdomain( 'dk_speakup', false, 'speakup-email-petitions/languages' );
}

// load admin functions only on admin pages
if ( is_admin() ) {
	include_once( dirname( __FILE__ ) . '/includes/install.php' );
	include_once( dirname( __FILE__ ) . '/includes/admin.php' );
	include_once( dirname( __FILE__ ) . '/includes/petitions.php' );
	include_once( dirname( __FILE__ ) . '/includes/addnew.php' );
	include_once( dirname( __FILE__ ) . '/includes/signatures.php' );
	include_once( dirname( __FILE__ ) . '/includes/settings.php' );
	include_once( dirname( __FILE__ ) . '/includes/csv.php' );
	include_once( dirname( __FILE__ ) . '/includes/ajax.php' );

	if ( version_compare( get_bloginfo( 'version' ), '3.3', '>' ) == 1 ) {
		include_once( dirname( __FILE__ ) . '/includes/help.php' );
	}

	// enable plugin activation
	register_activation_hook( __FILE__, 'dk_speakup_install' );
}
// public pages
else {
	include_once( dirname( __FILE__ ) . '/includes/emailpetition.php' );
	include_once( dirname( __FILE__ ) . '/includes/signaturelist.php' );
	include_once( dirname( __FILE__ ) . '/includes/confirmations.php' );
}

// load the widget (admin and public)
include_once( dirname( __FILE__ ) . '/includes/widget.php' );

// add Support and Donate links to the Plugins page
add_filter( 'plugin_row_meta', 'dk_speakup_meta_links', 10, 2 );
function dk_speakup_meta_links( $links, $file ) {
	$plugin = plugin_basename( __FILE__ );

	// create link
	if ( $file == $plugin ) {
		return array_merge(
			$links,
			array(
				sprintf( '<a href="http://wordpress.org/tags/speakup-email-petitions?forum_id=10">%s</a>', __( 'Support', 'dk_speakup' ) ),
				sprintf( '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ADP2TPGYEU5NU">%s</a>', __( 'Donate', 'dk_speakup' ) )
			)
		);
	}

	return $links;
}

?>