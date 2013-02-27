<?php

// register shortcode to display signatures count
add_shortcode( 'signaturecount', 'dk_speakup_signaturescount_shortcode' );
function dk_speakup_signaturescount_shortcode( $attr ) {

	include_once( 'class.petition.php' );
	$petition = new dk_speakup_Petition();

	$id = 1; // default
	if ( isset( $attr['id'] ) && is_numeric( $attr['id'] ) ) {
		$id = $attr['id'];
	}
	
	$get_petition = $petition->retrieve( $id );

	if ( $get_petition ) {
		return $petition->signatures;
	}
	else {
		return '';
	}
}

// register shortcode to display petition form
add_shortcode( 'emailpetition', 'dk_speakup_emailpetition_shortcode' );
function dk_speakup_emailpetition_shortcode( $attr ) {

	// only query a petition if the "id" attribute has been set
	if ( isset( $attr['id'] ) && is_numeric( $attr['id'] ) ) {

		global $dk_speakup_version;
		include_once( 'class.speakup.php' );
		include_once( 'class.petition.php' );
		include_once( 'class.wpml.php' );
		$petition = new dk_speakup_Petition();
		$wpml     = new dk_speakup_WPML();
		$options  = get_option( 'dk_speakup_options' );

		// get petition data from database
		$id = absint( $attr['id'] );
		$get_petition = $petition->retrieve( $id );
		$wpml->translate_petition( $petition );
		$options = $wpml->translate_options( $options );

		// get the current language for WPML
		$wpml_lang = '';
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$wpml_lang = ICL_LANGUAGE_CODE;
		}

		// check if petition exists...
		// if a petition has been deleted, but its shortcode still exists in the post, don't try to display the form
		if ( $get_petition ) {

			$expired = ( $petition->expires == 1 && current_time( 'timestamp' ) >= strtotime( $petition->expiration_date ) ) ? 1 : 0;

			// handle shortcode attributes
			$width  = isset( $attr['width'] ) ? 'style="width: ' . $attr['width'] . ';"' : '';
			$height = isset( $attr['height'] ) ? 'style="height: ' . $attr['height'] . ' !important;"' : '';
			$shortcode_classes      = isset( $attr['class'] ) ? $shortcode_classes = $attr['class'] : '';
			$progress_width         = ( $options['petition_theme'] == 'basic' ) ? 300 : 200; // defaults
			if ( isset( $attr['progresswidth'] ) ) {
				$progress_width = $attr['progresswidth'];
			}

			// if petition has expired, display expiration notice
			if ( ! $expired ) {
				$userdata = dk_speakup_SpeakUp::userinfo();

				// compose the petition form
				$petition_form = '
					<!-- SpeakUp! Email Petitions ' . $dk_speakup_version . ' -->
					<div class="dk-speakup-petition-wrap ' . $shortcode_classes . '" id="dk-speakup-petition-' . $petition->id . '" ' . $width . '>
						<h3>' . stripslashes( esc_html( $petition->title ) ) . '</h3>
						<div class="dk-speakup-response"></div>
						<form class="dk-speakup-petition">
							<input type="hidden" id="dk-speakup-posttitle-' . $petition->id . '" value="' . esc_attr( urlencode( stripslashes( $petition->title ) ) ) .'" />
							<input type="hidden" id="dk-speakup-tweet-' . $petition->id . '" value="' . dk_speakup_SpeakUp::twitter_encode( $petition->twitter_message ) .'" />
							<input type="hidden" id="dk-speakup-lang-' . $petition->id . '" value="' . $wpml_lang .'" />
							<div class="dk-speakup-half">
								<label for="dk-speakup-first-name-' . $petition->id . '" class="required">' . __( 'First Name', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-first-name" id="dk-speakup-first-name-' . $petition->id . '" value="' . $userdata['firstname'] . '" type="text" />
							</div>
							<div class="dk-speakup-half">
								<label for="dk-speakup-last-name-' . $petition->id . '" class="required">' . __( 'Last Name', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-last-name" id="dk-speakup-last-name-' . $petition->id . '" value="' . $userdata['lastname'] . '" type="text" />
							</div>
							<div class="dk-speakup-full">
								<label for="dk-speakup-email-' . $petition->id . '" class="required">' . __( 'Email', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-email" id="dk-speakup-email-' . $petition->id . '" value="' . $userdata['email'] . '" type="text" />
							</div>
				';
				if ( in_array( 'street', $petition->address_fields ) ) {
					$petition_form .= '
							<div class="dk-speakup-full">
								<label for="dk-speakup-street-' . $petition->id . '">' . __( 'Street', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-street" id="dk-speakup-street-' . $petition->id . '" maxlength="200" type="text" />
							</div>
					';
				}
				if ( in_array( 'city', $petition->address_fields ) ) {
					$petition_form .= '
							<div class="dk-speakup-half">
								<label for="dk-speakup-city-' . $petition->id . '">' . __( 'City', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-city" id="dk-speakup-city-' . $petition->id . '" maxlength="200" type="text" />
							</div>
					';
				}
				if ( in_array( 'state', $petition->address_fields ) ) {
					$petition_form .= '
							<div class="dk-speakup-half">
								<label for="dk-speakup-state-' . $petition->id . '">' . __( 'State / Province', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-state" id="dk-speakup-state-' . $petition->id . '" maxlength="200" type="text" />
							</div>
					';
				}
				if ( in_array( 'postcode', $petition->address_fields ) ) {
					$petition_form .= '
							<div class="dk-speakup-half">
								<label for="dk-speakup-postcode-' . $petition->id . '">' . __( 'Post Code', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-postcode" id="dk-speakup-postcode-' . $petition->id . '" maxlength="200" type="text" />
							</div>
					';
				}
				if ( in_array( 'country', $petition->address_fields ) ) {
					$petition_form .= '
							<div class="dk-speakup-half">
								<label for="dk-speakup-country-' . $petition->id . '">' . __( 'Country', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-country" id="dk-speakup-country-' . $petition->id . '" maxlength="200" type="text" />
							</div>
					';
				}
				if ( $petition->displays_custom_field == 1 ) {
					$petition_form .= '
							<div class="dk-speakup-half">
								<label for="dk-speakup-custom-field-' . $petition->id . '">' . stripslashes( esc_html( $petition->custom_field_label ) ) . '</label>
								<input name="dk-speakup-custom-field" id="dk-speakup-custom-field-' . $petition->id . '" maxlength="200" type="text" />
							</div>
					';
				}
				if ( $petition->is_editable == 1 ) {
					$petition_form .= '
							<div class="dk-speakup-full">
								<textarea name="dk-speakup-message" id="dk-speakup-message-' . $petition->id . '" class="dk-speakup-message" ' . $height . ' rows="8">' . stripslashes( esc_textarea( $petition->petition_message ) ) . '</textarea>
							</div>
					';
				}
				else {
					$petition_form .= '
							<div class="dk-speakup-full">
								<div class="dk-speakup-message" ' . $height . '>' . stripslashes( wpautop( $petition->petition_message ) ) . '</div>
							</div>
					';
				}
				if ( $petition->displays_optin == 1 ) {
					$optin_default = ( $options['optin_default'] == 'checked' ) ? ' checked="checked"' : '';
					$petition_form .= '
							<div class="dk-speakup-optin-wrap">
								<input type="checkbox" name="dk-speakup-optin" id="dk-speakup-optin-' . $petition->id . '"' . $optin_default . ' />
								<label for="dk-speakup-optin-' . $petition->id . '">' . stripslashes( esc_html( $petition->optin_label ) ) . '</label>
							</div>
					';
				}
				$petition_form .= '
							<div class="dk-speakup-submit-wrap">
								<a name="' . $petition->id . '" class="dk-speakup-submit"><span>' . stripslashes( esc_html( $options['button_text'] ) ) . '</span></a>
							</div>
						</form>
				';
				if ( $options['display_count'] == 1 ) {
					$petition_form .= '
							<div class="dk-speakup-progress-wrap">
								<div class="dk-speakup-signature-count">
									<span>' . number_format( $petition->signatures ) . '</span> ' . _n( 'signature', 'signatures', $petition->signatures, 'dk_speakup' ) . '
								</div>
								' . dk_speakup_SpeakUp::progress_bar( $petition->goal, $petition->signatures, $progress_width ) . '
							</div>
					';
				}
				$petition_form .= '
						<div class="dk-speakup-share">
							<div>' . stripslashes( esc_html( $options['share_message'] ) ) . '<br />
							<a class="dk-speakup-facebook" href="#" title="Facebook" rel="' . $petition->id . '"></a>
							<a class="dk-speakup-twitter" href="#" title="Twitter" rel="' . $petition->id . '"></a>
						</div>
							<div class="dk-speakup-clear"></div>
						</div>
					</div>
				';
			}
			else { // petition has expired
				$goal_text = ( $petition->goal != 0 ) ? '<div class="dk-speakup-expired-goal"><span>' . __( 'Signature goal', 'dk_speakup' ) . ':</span> ' . $petition->goal . '</div>' : '';
				$petition_form = '
					<div class="dk-speakup-petition-wrap" id="dk-speakup-petition-' . $petition->id . '">
						<h3>' . stripslashes( esc_html( $petition->title ) ) . '</h3>
						<div class="dk-speakup-notice">
							<p>' . stripslashes( esc_html( $options['expiration_message'] ) ) . '</p>
							<div class="dk-speakup-expired-deadline">
								<span>' . __( 'End date', 'dk_speakup' ) . ':</span> ' . date( 'M d, Y', strtotime( $petition->expiration_date ) ) . '
							</div>
							<div class="dk-speakup-expired-signatures">
								<span>' . __( 'Signatures collected', 'dk_speakup' ) . ':</span> ' . $petition->signatures . '
							</div>
							' . $goal_text . '
						</div>
						<div class="dk-speakup-progress-wrap">
								<div class="dk-speakup-signature-count">
									<span>' . number_format( $petition->signatures ) . '</span> ' . _n( 'signature', 'signatures', $petition->signatures, 'dk_speakup' ) . '
								</div>
								' . dk_speakup_SpeakUp::progress_bar( $petition->goal, $petition->signatures, 250 ) . '
							</div>
					</div>
				';
			}

		}
		// ...otherwise, display nothing
		// most likely scenario is a user deletes an old petition, but fails to remove shortcode from their post
		else {
			$petition_form = '';
		}
	}

	// if id attribute is left out, as in [emailpetition], display error
	else {
		$petition_form = '
			<div class="dk-speakup-petition-wrap dk-speakup-petition-expired">
				<h3>' . __( 'Petition', 'dk_speakup' ) . '</h3>
				<div class="dk-speakup-notice">
					<p>' . __( 'Error: You must include a valid id.', 'dk_speakup' ) . '</p>
				</div>
			</div>
		';
	}

	return $petition_form;
}

// load public CSS only on pages/posts that contain the [emailpetition] shortcode
add_filter( 'the_posts', 'dk_speakup_public_css_js' );
function dk_speakup_public_css_js( $posts ) {

	// ignore if there are no posts
	if ( empty( $posts ) ) return $posts;

	$options = get_option( 'dk_speakup_options' );

	// set flag to determine if post contains shortcode
	$shortcode_found = false;

	foreach ( $posts as $post ) {
		// if post content contains the shortcode
		if ( strstr( $post->post_content, '[emailpetition' ) ) {
			// update flag
			$shortcode_found = true;
			break;
		}
	}

	// if flag is now true, load the CSS and JavaScript
	if ( $shortcode_found ) {
		$theme = $options['petition_theme'];

		switch( $theme ) {
			case 'default' :
				wp_enqueue_style( 'dk_speakup_css', plugins_url( 'speakup-email-petitions/css/theme-default.css' ) );
				break;
			case 'basic' :
				wp_enqueue_style( 'dk_speakup_css', plugins_url( 'speakup-email-petitions/css/theme-basic.css' ) );
				break;
			case 'none' : // look for custom theme file, petition.css
				$parent_dir = get_template_directory_uri();
				$parent_petition_theme_url = $parent_dir . '/petition.css';

				// if a child theme is in use
				// try to load style from child theme folder
				if ( is_child_theme() ) {
					$child_dir = get_stylesheet_directory_uri();
					$child_petition_theme_url = $child_dir . '/petition.css';
					$child_petition_theme_path = STYLESHEETPATH . '/petition.css';

					// use child theme if it exists
					if ( file_exists( $child_petition_theme_path ) ) {
						wp_enqueue_style( 'dk_speakup_css', $child_petition_theme_url );
					}
					// else try to load style from parent theme folder
					else {
						wp_enqueue_style( 'dk_speakup_css', $parent_petition_theme_url );
					}
				}
				// if not using a child theme, just try to load style from active theme folder
				else {
					wp_enqueue_style( 'dk_speakup_css', $parent_petition_theme_url );
				}
				break;
		}

		wp_enqueue_script( 'dk_speakup_js', plugins_url( 'speakup-email-petitions/js/public.js' ), array( 'jquery' ) );

		// make sure ajax callback url works on both https and http
		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
		$params = array(
			'ajaxurl' => admin_url( 'admin-ajax.php', $protocol )
		);
		wp_localize_script( 'dk_speakup_js', 'dk_speakup_js', $params );
	}

	return $posts;
}

?>