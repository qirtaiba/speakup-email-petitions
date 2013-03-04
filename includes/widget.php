<?php

// register widget
add_action( 'widgets_init', 'dk_speakup_register_widgets' );
function dk_speakup_register_widgets() {
	register_widget( 'dk_speakup_petition_widget' );
}

class dk_speakup_petition_widget extends WP_Widget {

	function dk_speakup_petition_widget() {

		$widget_ops = array(
			'classname'   => 'dk_speakup_widget',
			'description' => __( 'Display a petition form.', 'dk_speakup' )
		);
		$this->WP_Widget( 'dk_speakup_petition_widget', 'SpeakUp! Email Petitions', $widget_ops );

		// load widget scripts
		if ( ! is_admin() && is_active_widget( false, false, $this->id_base, true ) ) {

			// load the JavaScript
			wp_enqueue_script( 'dk_speakup_widget_js', plugins_url( 'speakup-email-petitions/js/widget.js' ), array( 'jquery' ) );

			// load the CSS theme
			$options = get_option( 'dk_speakup_options' );
			$theme   = $options['widget_theme'];

			 // load default theme
			if ( $theme === 'default' ) {
				wp_enqueue_style( 'dk_speakup_widget_css', plugins_url( 'speakup-email-petitions/css/widget.css' ) );
			}
			// attempt to load cusom theme (petition-widget.css)
			else {
				$parent_dir       = get_template_directory_uri();
				$parent_theme_url = $parent_dir . '/petition-widget.css';

				// if a child theme is in use
				// try to load style from child theme folder
				if ( is_child_theme() ) {
					$child_dir        = get_stylesheet_directory_uri();
					$child_theme_url  = $child_dir . '/petition-widget.css';
					$child_theme_path = STYLESHEETPATH . '/petition-widget.css';

					// use child theme if it exists
					if ( file_exists( $child_theme_path ) ) {
						wp_enqueue_style( 'dk_speakup_widget_css', $child_theme_url );
					}
					// else try to load style from parent theme folder
					else {
						wp_enqueue_style( 'dk_speakup_widget_css', $parent_theme_url );
					}
				}
				// if not using a child theme, just try to load style from active theme folder
				else {
					wp_enqueue_style( 'dk_speakup_widget_css', $parent_theme_url );
				}
			}

			// set up AJAX callback script
			$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
			$params   = array( 'ajaxurl' => admin_url( 'admin-ajax.php', $protocol ) );
			wp_localize_script( 'dk_speakup_widget_js', 'dk_speakup_widget_js', $params );
		}
	}

	// create widget form (admin)
	function form( $instance ) {
		include_once( 'class.petition.php' );
		$the_petition   = new dk_speakup_Petition();
		$options        = get_option( 'dk_speakup_options' );
		$defaults       = array( 'title' => __( 'Sign the Petition', 'dk_speakup' ), 'call_to_action' => '', 'petition_id' => 1 );
		$instance       = wp_parse_args( ( array ) $instance, $defaults );
		$title          = $instance['title'];
		$call_to_action = $instance['call_to_action'];
		$sharing_url    = $instance['sharing_url'];
		$petition_id    = $instance['petition_id'];

		// get petitions list to fill out select box
		$petitions = $the_petition->quicklist();

		// display the form (admin)
		echo '<p><label>' . __( 'Title', 'dk_speakup' ) . ':</label><br /><input class="widefat" type="text" name="' . $this->get_field_name( 'title' ) . '" value="' . stripslashes( $instance['title'] ) . '"></p>';
		echo '<p><label>' . __( 'Sharing URL', 'dk_speakup' ) . ':</label><br /><input class="widefat" type="text" name="' . $this->get_field_name( 'sharing_url' ) . '" value="' . stripslashes( $instance['sharing_url'] ) . '"></p>';
		echo '<p><label>' . __( 'Call to Action', 'dk_speakup' ) . ':</label><br /><textarea maxlength="140" class="widefat" name="' . $this->get_field_name( 'call_to_action' ) . '">' . $instance['call_to_action'] . '</textarea></p>';
		echo '<p><label>' . __( 'Petition', 'dk_speakup' ) . ':</label><br /><select class="widefat" name="' . $this->get_field_name( 'petition_id' ) . '">';
		foreach ( $petitions as $petition ) {
			$selected = ( $petition_id == $petition->id ) ? ' selected="selected"' : '';
			echo '<option value="' . $petition->id . '" ' . $selected . '>' . stripslashes( esc_html( $petition->title ) ) . '</option>';
		}
		echo '</select></p>';
	}

	// save the widget settings (admin)
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['sharing_url']    = strip_tags( $new_instance['sharing_url'] );
		$instance['call_to_action'] = strip_tags( $new_instance['call_to_action'] );
		$instance['petition_id']    = $new_instance['petition_id'];

		// register widget strings in WPML
		include_once( 'class.wpml.php' );
		$wpml = new dk_speakup_WPML();
		$wpml->register_widget( $instance );

		return $instance;
	}

	// display widget (public)
	function widget( $args, $instance ) {

		global $dk_speakup_version;

		include_once( 'class.speakup.php' );
		include_once( 'class.petition.php' );
		include_once( 'class.wpml.php' );
		$options  = get_option( 'dk_speakup_options' );
		$petition = new dk_speakup_Petition();
		$wpml     = new dk_speakup_WPML();
		extract( $args );

		// get widget data
		$instance       = $wpml->translate_widget( $instance );
		$title          = apply_filters( 'widget_title', $instance['title'] );
		$call_to_action = empty( $instance['call_to_action'] ) ? '&nbsp;' : $instance['call_to_action'];
		$petition->id   = empty( $instance['petition_id'] ) ? 1 : absint( $instance['petition_id'] );
		$get_petition   = $petition->retrieve( $petition->id );
		$wpml->translate_petition( $petition );
		$options = $wpml->translate_options( $options );

		// set up variables for widget display
		$userdata      = dk_speakup_SpeakUp::userinfo();
		$expired       = ( $petition->expires == '1' && current_time( 'timestamp' ) >= strtotime( $petition->expiration_date ) ) ? 1 : 0;
		$greeting      = ( $petition->greeting != '' && $petition->sends_email == 1 ) ? '<p><span class="dk-speakup-widget-greeting">' . $petition->greeting . '</span></p>' : '';
		$optin_default = ( $options['optin_default'] == 'checked' ) ? 'checked' : '';

		// get language value from URL if available (for WPML)
		$wpml_lang = '';
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$wpml_lang = ICL_LANGUAGE_CODE;
		}

		// check if petition exists...
		// if a petition has been deleted, but its widget still exists, don't try to display the form
		if ( $get_petition ) {

			// compose the petition widget and pop-up form
			$petition_widget = '
				<!-- SpeakUp! Email Petitions ' . $dk_speakup_version . ' -->
				<div class="dk-speakup-widget-wrap">
					<h3>' . stripslashes( esc_html( $title ) ) . '</h3>
					<p>' . stripslashes( esc_html( $call_to_action ) ) . '</p>
					<div class="dk-speakup-widget-button-wrap">
						<a rel="dk-speakup-widget-popup-wrap-' . $petition->id . '" class="dk-speakup-widget-button"><span>' . $options['button_text'] . '</span></a>
					</div>
			';
			if ( $options['display_count'] == 1 ) {
				$petition_widget .= '
					<div class="dk-speakup-widget-progress-wrap">
						<div class="dk-speakup-widget-signature-count">
							<span>' . number_format( $petition->signatures ) . '</span> ' . _n( 'signature', 'signatures', $petition->signatures, 'dk_speakup' ) . '
						</div>
						' . dk_speakup_SpeakUp::progress_bar( $petition->goal, $petition->signatures, 150 ) . '
					</div>
				';
			}
			$petition_widget .= '
				</div>

				<div id="dk-speakup-windowshade"></div>
				<div id="dk-speakup-widget-popup-wrap-' . $petition->id . '" class="dk-speakup-widget-popup-wrap">
					<h3>' . stripslashes( esc_html( $petition->title ) ) . '</h3>
					<div class="dk-speakup-widget-close"></div>
			';
			if ( $petition->is_editable == 1 ) {
				$petition_widget .= '
					<div class="dk-speakup-widget-message-wrap">
						<textarea name="dk-speakup-widget-message" id="dk-speakup-widget-message-' . $petition->id . '" class="dk-speakup-widget-message">' . stripslashes( esc_textarea( $petition->petition_message ) ) . '</textarea>
					</div>
				';
			}
			else {
				$petition_widget .= '
					<div class="dk-speakup-widget-message-wrap">
						<div class="dk-speakup-widget-message">' . stripslashes( wpautop( $petition->petition_message ) ) . '</div>
					</div>
				';
			}
			$petition_widget .= '
					<div class="dk-speakup-widget-form-wrap">
						<div class="dk-speakup-widget-response"></div>
						<form class="dk-speakup-widget-form">
							<input type="hidden" id="dk-speakup-widget-posttitle-' . $petition->id . '" value="' . esc_attr( urlencode( stripslashes( $petition->title ) ) ) .'" />
							<input type="hidden" id="dk-speakup-widget-shareurl-' . $petition->id . '" value="' . esc_attr( urlencode( stripslashes( $instance['sharing_url'] ) ) ) .'" />
							<input type="hidden" id="dk-speakup-widget-tweet-' . $petition->id . '" value="' . dk_speakup_SpeakUp::twitter_encode( $petition->twitter_message ) .'" />
							<input type="hidden" id="dk-speakup-widget-lang-' . $petition->id . '" value="' . $wpml_lang .'" />
			';

			if ( $expired ) {
				$petition_widget .= '
							<p><strong>' . $options['expiration_message'] . '</strong></p>
							<p>' . __( 'End date', 'dk_speakup' ) . ': ' . date( 'M d, Y', strtotime( $petition->expiration_date ) ) . '</p>
							<p>' . __( 'Signatures collected', 'dk_speakup' ) . ': ' . $petition->signatures . '</p>
				';
				if ( $petition->goal != 0 ) {
					$petition_widget .= '
							<p><div class="dk-speakup-expired-goal"><span>' . __( 'Signature goal', 'dk_speakup' ) . ':</span> ' . $petition->goal . '</div></p>
					';
				}
			}
			else {
				$petition_widget .= '
							<div class="dk-speakup-widget-full">
								<label for="dk-speakup-widget-first-name-' . $petition->id . '" class="required">' . __( 'First Name', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-first-name" id="dk-speakup-widget-first-name-' . $petition->id . '" value="' . $userdata['firstname'] . '" type="text" />
							</div>
							<div class="dk-speakup-widget-full">
								<label for="dk-speakup-widget-last-name-' . $petition->id . '" class="required">' . __( 'Last Name', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-last-name" id="dk-speakup-widget-last-name-' . $petition->id . '" value="' . $userdata['lastname'] . '" type="text" />
							</div>
							<div class="dk-speakup-widget-full">
								<label for="dk-speakup-widget-email-' . $petition->id . '" class="required">' . __( 'Email', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-email" id="dk-speakup-widget-email-' . $petition->id . '" value="' . $userdata['email'] . '" type="text" />
							</div>
				';
				if ( $petition->requires_confirmation ) {
					$petition_widget .= '
							<div class="dk-speakup-widget-full">
								<label for="dk-speakup-widget-email-confirm-' . $petition->id . '" class="required">' . __( 'Confirm Email', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-email-confirm" id="dk-speakup-widget-email-confirm-' . $petition->id . '" value="" type="text" />
							</div>
					';
				}
				if ( in_array( 'street', $petition->address_fields ) ) {
					$petition_widget .= '
							<div class="dk-speakup-widget-full">
								<label for="dk-speakup-widget-street-' . $petition->id . '">' . __( 'Street', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-street" id="dk-speakup-widget-street-' . $petition->id . '" maxlength="200" type="text" />
							</div>
					';
				}
				if ( in_array( 'city', $petition->address_fields ) ) {
					$petition_widget .= '
							<div class="dk-speakup-widget-half">
								<label for="dk-speakup-widget-city-' . $petition->id . '">' . __( 'City', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-city" id="dk-speakup-widget-city-' . $petition->id . '" maxlength="200" type="text">
							</div>
					';
				}
				if ( in_array( 'state', $petition->address_fields ) ) {
					$petition_widget .= '
							<div class="dk-speakup-widget-half">
								<label for="dk-speakup-widget-state-' . $petition->id . '">' . __( 'State / Province', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-state" id="dk-speakup-widget-state-' . $petition->id . '" maxlength="200" type="text">
							</div>
					';
				}
				if ( in_array( 'postcode', $petition->address_fields ) ) {
					$petition_widget .= '
							<div class="dk-speakup-widget-half">
								<label for="dk-speakup-widget-postcode-' . $petition->id . '">' . __( 'Post Code', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-postcode" id="dk-speakup-widget-postcode-' . $petition->id . '" maxlength="200" type="text">
							</div>
					';
				}
				if ( in_array( 'country', $petition->address_fields ) ) {
					$petition_widget .= '
							<div class="dk-speakup-widget-half">
								<label for="dk-speakup-widget-country-' . $petition->id . '">' . __( 'Country', 'dk_speakup' ) . '</label>
								<input name="dk-speakup-widget-country" id="dk-speakup-widget-country-' . $petition->id . '" maxlength="200" type="text">
							</div>
					';
				}
				if( $petition->displays_custom_field == 1 ) {
					$petition_widget .= '
							<div class="dk-speakup-widget-full">
								<label for="dk-speakup-widget-custom-field-' . $petition->id . '">' . stripslashes( esc_html( $petition->custom_field_label ) ) . '</label>
								<input name="dk-speakup-widget-custom-field" id="dk-speakup-widget-custom-field-' . $petition->id . '" maxlength="400" type="text">
							</div>
					';
				}
				if( $petition->displays_optin == 1 ) {
					$optin_default = ( $options['optin_default'] == 'checked' ) ? ' checked="checked"' : '';
					$petition_widget .= '
							<div class="dk-speakup-widget-optin-wrap">
								<input type="checkbox" name="dk-speakup-widget-optin" id="dk-speakup-widget-optin-' . $petition->id . '"' . $optin_default . ' />
								<label for="dk-speakup-widget-optin-' . $petition->id . '">' . stripslashes( esc_html( $petition->optin_label ) ) . '</label>
							</div>
					';
				}
				$petition_widget .= '
							<div class="dk-speakup-widget-submit-wrap">
								<a name="' . $petition->id . '" class="dk-speakup-widget-submit"><span>' . stripslashes( esc_html( $options['button_text'] ) ) . '</span></a>
							</div>
						</form>
						<div class="dk-speakup-widget-share">
							<p><strong>' . stripslashes( esc_html( $options['share_message'] ) ) . '</strong></p>
							<p>
							<a class="dk-speakup-widget-facebook" href="#" title="Facebook"><span></span></a>
							<a class="dk-speakup-widget-twitter" href="#" title="Twitter"><span></span></a>
							</p>
							<div class="dk-speakup-clear"></div>
						</div>
					</div>
				</div>
				';
			}

			echo $petition_widget;
		}
	}

}

?>