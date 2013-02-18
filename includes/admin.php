<?php

// create admin menus
add_action( 'admin_menu', 'dk_speakup_create_menus' );
function dk_speakup_create_menus() {

	// load sidebar menus
	$petitions = array(
		'page_title' => __( 'Email Petitions', 'dk_speakup' ),
		'menu_title' => __( 'Email Petitions', 'dk_speakup' ),
		'capability' => 'publish_posts',
		'menu_slug'  => 'dk_speakup',
		'function'   => 'dk_speakup_petitions_page',
		'icon_url'   => plugins_url( 'speakup-email-petitions/images/blank.png' )
	);
	$petitions_page = add_menu_page( $petitions['page_title'], $petitions['menu_title'], $petitions['capability'], $petitions['menu_slug'], $petitions['function'], $petitions['icon_url'] );

	$addnew = array(
		'parent_slug' => 'dk_speakup',
		'page_title'  => __( 'Add New', 'dk_speakup' ),
		'menu_title'  => __( 'Add New', 'dk_speakup' ),
		'capability'  => 'publish_posts',
		'menu_slug'   => 'dk_speakup_addnew',
		'function'    => 'dk_speakup_addnew_page'
	);
	$addnew_page = add_submenu_page( $addnew['parent_slug'], $addnew['page_title'], $addnew['menu_title'], $addnew['capability'], $addnew['menu_slug'], $addnew['function'] );

	$signatures = array(
		'parent_slug' => 'dk_speakup',
		'page_title'  => __( 'Signatures', 'dk_speakup' ),
		'menu_title'  => __( 'Signatures', 'dk_speakup' ),
		'capability'  => 'publish_posts',
		'menu_slug'   => 'dk_speakup_signatures',
		'function'    => 'dk_speakup_signatures_page'
	);
	$signatures_page = add_submenu_page( $signatures['parent_slug'], $signatures['page_title'], $signatures['menu_title'], $signatures['capability'], $signatures['menu_slug'], $signatures['function'] );

	$settings = array(
		'parent_slug' => 'dk_speakup',
		'page_title'  => __( 'Email Petitions Settings', 'dk_speakup' ),
		'menu_title'  => __( 'Settings', 'dk_speakup' ),
		'capability'  => 'manage_options',
		'menu_slug'   => 'dk_speakup_settings',
		'function'    => 'dk_speakup_settings_page'
	);
	$settings_page = add_submenu_page( $settings['parent_slug'], $settings['page_title'], $settings['menu_title'], $settings['capability'], $settings['menu_slug'], $settings['function'] );

	// load contextual help tabs for newer WordPress installs (requires 3.3.1)
	if ( version_compare( get_bloginfo( 'version' ), '3.3', '>' ) == 1 ) {
		add_action( 'load-' . $addnew_page, 'dk_speakup_help_addnew' );
		add_action( 'load-' . $settings_page, 'dk_speakup_help_settings' );
	}
}

// display custom menu icon
add_action( 'admin_head', 'dk_speakup_menu_icon' );
function dk_speakup_menu_icon() {
	echo '
		<style type="text/css">
			#toplevel_page_dk_speakup .wp-menu-image {
				background: url(' . plugins_url( "speakup-email-petitions/images/icon-emailpetitions-16.png" ) . ') no-repeat 6px 7px !important;
			}
			body.admin-color-classic #toplevel_page_dk_speakup .wp-menu-image {
				background: url(' . plugins_url( "speakup-email-petitions/images/icon-emailpetitions-16.png" ) . ') no-repeat 6px -41px !important;
			}
			#toplevel_page_dk_speakup:hover .wp-menu-image, #toplevel_page_dk_speakup.wp-has-current-submenu .wp-menu-image {
				background-position: 6px -17px !important;
			}
			body.admin-color-classic #toplevel_page_dk_speakup:hover .wp-menu-image, body.admin-color-classic #toplevel_page_dk_speakup.wp-has-current-submenu .wp-menu-image {
				background-position: 6px -17px !important;
			}

		</style>
	';
}

// load JavaScript for use on admin pages
add_action( 'admin_print_scripts', 'dk_speakup_admin_js' );
function dk_speakup_admin_js() {
	global $parent_file;

	if ( $parent_file == 'dk_speakup' ) {
		wp_enqueue_script( 'dk_speakup_admin_js', plugins_url( 'speakup-email-petitions/js/admin.js' ), array( 'jquery' ) );
		wp_enqueue_script( 'post', admin_url( 'js/post.js' ), 'jquery' );
	}
}

// load CSS for use on admin pages
add_action( 'admin_print_styles', 'dk_speakup_admin_css' );
function dk_speakup_admin_css() {
	global $parent_file;

	if ( $parent_file == 'dk_speakup' ) {
		wp_enqueue_style( 'dk_speakup_admin_css', plugins_url( 'speakup-email-petitions/css/admin.css' ) );
	}
}

?>