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

				<div id="dk-speakup-widget-windowshade"></div>
				<div id="dk-speakup-widget-popup-wrap-' . $petition->id . '" class="dk-speakup-widget-popup-wrap">
					<h3>' . stripslashes( esc_html( $petition->title ) ) . '</h3>
					<div class="dk-speakup-widget-close"></div>
			';
			if ( $petition->is_editable == 1 ) {
				$petition_widget .= '
					<div class="dk-speakup-widget-message-wrap">
						<p class="dk-speakup-greeting">' . $petition->greeting . '</p>
						<textarea name="dk-speakup-widget-message" id="dk-speakup-widget-message-' . $petition->id . '" class="dk-speakup-widget-message">' . stripslashes( esc_textarea( $petition->petition_message ) ) . '</textarea>
						<p class="dk-speakup-caps">[' . __( 'signature', 'dk-speakup' ) . ']</p>
					</div>';
			}
			else {
				$petition_widget .= '
					<div class="dk-speakup-widget-message-wrap">
						<div class="dk-speakup-widget-message">
							<p class="dk-speakup-greeting">' . $petition->greeting . '</p>
							' . stripslashes( wpautop( $petition->petition_message ) ) . '
							<p class="dk-speakup-caps">[' . __( 'signature', 'dk-speakup' ) . ']</p>
						</div>
					</div>';
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
								<input name="dk-speakup-widget-state" id="dk-speakup-widget-state-' . $petition->id . '" maxlength="200" type="text" list="dk-speakup-states" />
								<datalist id="dk-speakup-states">
									<option value="Alabama"><option value="Alaska"><option value="Arizona"><option value="Arkansas"><option value="California"><option value="Colorado"><option value="Connecticut"><option value="Washington DC"><option value="Delaware"><option value="Florida"><option value="Georgia"><option value="Hawaii"><option value="Idaho"><option value="Illinois"><option value="Indiana"><option value="Iowa"><option value="Kansas"><option value="Kentucky"><option value="Louisiana"><option value="Maine"><option value="Maryland"><option value="Massachusetts"><option value="Michigan"><option value="Minnesota"><option value="Mississippi"><option value="Missouri"><option value="Montana"><option value="Nebraska"><option value="Nevada"><option value="New Hampshire"><option value="New Jersey"><option value="New Mexico"><option value="New York"><option value="North Carolina"><option value="North Dakota"><option value="Ohio"><option value="Oklahoma"><option value="Oregon"><option value="Pennsylvania"><option value="Rhode Island"><option value="South Carolina"><option value="South Dakota"><option value="Tennessee"><option value="Texas"><option value="Utah"><option value="Vermont"><option value="Virginia"><option value="Washington"><option value="West Virginia"><option value="Wisconsin"><option value="Wyoming"><option value="Alberta"><option value="British Columbia"><option value="Labrador"><option value="Manitoba"><option value="New Brunswick"><option value="Newfoundland"><option value="Nova Scotia"><option value="Nunavut"><option value="North West Territory"><option value="Ontario"><option value="Prince Edward Island"><option value="Quebec"><option value="Saskatchewen"><option value="Yukon">
								</datalist>
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
								<input name="dk-speakup-widget-country" id="dk-speakup-widget-country-' . $petition->id . '" maxlength="200" type="text" list="dk-speakup-widget-countries" />
								<datalist id="dk-speakup-widget-countries">
									<option value="Afghanistan"><option value="Albania"><option value="Algeria"><option value="American Samoa"><option value="Andorra"><option value="Angola"><option value="Anguilla"><option value="Antarctica"><option value="Antigua and Barbuda"><option value="Argentina"><option value="Armenia"><option value="Aruba"><option value="Australia"><option value="Austria"><option value="Azerbaijan"><option value="Bahrain"><option value="Bangladesh"><option value="Barbados"><option value="Belarus"><option value="Belgium"><option value="Belize"><option value="Benin"><option value="Bermuda"><option value="Bhutan"><option value="Bolivia"><option value="Bosnia and Herzegovina"><option value="Botswana"><option value="Bouvet Island"><option value="Brazil"><option value="British Indian Ocean Territory"><option value="British Virgin Islands"><option value="Brunei"><option value="Bulgaria"><option value="Burkina Faso"><option value="Burundi"><option value="Côte d\'Ivoire"><option value="Cambodia"><option value="Cameroon"><option value="Canada"><option value="Cape Verde"><option value="Cayman Islands"><option value="Central African Republic"><option value="Chad"><option value="Chile"><option value="China"><option value="Christmas Island"><option value="Cocos (Keeling) Islands"><option value="Colombia"><option value="Comoros"><option value="Congo"><option value="Cook Islands"><option value="Costa Rica"><option value="Croatia"><option value="Cuba"><option value="Cyprus"><option value="Czech Republic"><option value="Democratic Republic of the Congo"><option value="Denmark"><option value="Djibouti"><option value="Dominica"><option value="Dominican Republic"><option value="East Timor"><option value="Ecuador"><option value="Egypt"><option value="El Salvador"><option value="Equatorial Guinea"><option value="Eritrea"><option value="Estonia"><option value="Ethiopia"><option value="Faeroe Islands"><option value="Falkland Islands"><option value="Fiji"><option value="Finland"><option value="Former Yugoslav Republic of Macedonia"><option value="France"><option value="French Guiana"><option value="French Polynesia"><option value="French Southern Territories"><option value="Gabon"><option value="Georgia"><option value="Germany"><option value="Ghana"><option value="Gibraltar"><option value="Greece"><option value="Greenland"><option value="Grenada"><option value="Guadeloupe"><option value="Guam"><option value="Guatemala"><option value="Guinea"><option value="Guinea-Bissau"><option value="Guyana"><option value="Haiti"><option value="Heard Island and McDonald Islands"><option value="Honduras"><option value="Hong Kong"><option value="Hungary"><option value="Iceland"><option value="India"><option value="Indonesia"><option value="Iran"><option value="Iraq"><option value="Ireland"><option value="Israel"><option value="Italy"><option value="Jamaica"><option value="Japan"><option value="Jordan"><option value="Kazakhstan"><option value="Kenya"><option value="Kiribati"><option value="Kuwait"><option value="Kyrgyzstan"><option value="Laos"><option value="Latvia"><option value="Lebanon"><option value="Lesotho"><option value="Liberia"><option value="Libya"><option value="Liechtenstein"><option value="Lithuania"><option value="Luxembourg"><option value="Macau"><option value="Madagascar"><option value="Malawi"><option value="Malaysia"><option value="Maldives"><option value="Mali"><option value="Malta"><option value="Marshall Islands"><option value="Martinique"><option value="Mauritania"><option value="Mauritius"><option value="Mayotte"><option value="Mexico"><option value="Micronesia"><option value="Moldova"><option value="Monaco"><option value="Mongolia"><option value="Montserrat"><option value="Morocco"><option value="Mozambique"><option value="Myanmar"><option value="Namibia"><option value="Nauru"><option value="Nepal"><option value="Netherlands"><option value="Netherlands Antilles"><option value="New Caledonia"><option value="New Zealand"><option value="Nicaragua"><option value="Niger"><option value="Nigeria"><option value="Niue"><option value="Norfolk Island"><option value="North Korea"><option value="Northern Marianas"><option value="Norway"><option value="Oman"><option value="Pakistan"><option value="Palau"><option value="Panama"><option value="Papua New Guinea"><option value="Paraguay"><option value="Peru"><option value="Philippines"><option value="Pitcairn Islands"><option value="Poland"><option value="Portugal"><option value="Puerto Rico"><option value="Qatar"><option value="Réunion"><option value="Romania"><option value="Russia"><option value="Rwanda"><option value="São Tomé and Príncipe"><option value="Saint Helena"><option value="Saint Kitts and Nevis"><option value="Saint Lucia"><option value="Saint Pierre and Miquelon"><option value="Saint Vincent and the Grenadines"><option value="Samoa"><option value="San Marino"><option value="Saudi Arabia"><option value="Senegal"><option value="Seychelles"><option value="Sierra Leone"><option value="Singapore"><option value="Slovakia"><option value="Slovenia"><option value="Solomon Islands"><option value="Somalia"><option value="South Africa"><option value="South Georgia and the South Sandwich Islands"><option value="South Korea"><option value="Spain"><option value="Sri Lanka"><option value="Sudan"><option value="Suriname"><option value="Svalbard and Jan Mayen"><option value="Swaziland"><option value="Sweden"><option value="Switzerland"><option value="Syria"><option value="Taiwan"><option value="Tajikistan"><option value="Tanzania"><option value="Thailand"><option value="The Bahamas"><option value="The Gambia"><option value="Togo"><option value="Tokelau"><option value="Tonga"><option value="Trinidad and Tobago"><option value="Tunisia"><option value="Turkey"><option value="Turkmenistan"><option value="Turks and Caicos Islands"><option value="Tuvalu"><option value="US Virgin Islands"><option value="Uganda"><option value="Ukraine"><option value="United Arab Emirates"><option value="United Kingdom"><option value="United States"><option value="United States Minor Outlying Islands"><option value="Uruguay"><option value="Uzbekistan"><option value="Vanuatu"><option value="Vatican City"><option value="Venezuela"><option value="Vietnam"><option value="Wallis and Futuna"><option value="Western Sahara"><option value="Yemen"><option value="Yugoslavia"><option value="Zambia"><option value="Zimbabwe">
								</datalist>
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