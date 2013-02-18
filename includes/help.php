<?php

// contextual help to Add New page
function dk_speakup_help_addnew() {
	$tab_petitions = '
		<p><strong>' . __( "Title", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the title of your petition, which will appear at the top of the petition form.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Do not send email (only collect signatures)", "dk_speakup" ) . '</strong>&mdash;' . __( "Use this option if do not wish to send petition emails to a target address.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Target Email", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the email address to which the petition will be sent. You may enter multiple email addresses, separated by commas.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Email Subject", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the subject of your petition email.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Greeting", "dk_speakup" ) . '</strong>&mdash;' . __( "Include a greeting to the recipient of your petition, such as \"Dear Sir,\" which will appear as the first line of the email.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Petition Message", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the content of your petition email.", "dk_speakup" ) . '</p>
	';
	$tab_twitter_message = '
		<p><strong>' . __( "Twitter Message", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter a prepared tweet that will be presented to users when the Twitter button is clicked.", "dk_speakup" ) . '</p>
	';
	$tab_petition_options = '
		<p><strong>' . __( "Confirm signatures", "dk_speakup" ) . '</strong>&mdash;' . __( "Use this option to cause an email to be sent to the signers of your petition. This email contains a special link must be clicked to confirm the signer's email address. Petition emails will not be sent until the signature is confirmed.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Allow custom messages", "dk_speakup" ) . '</strong>&mdash;' . __( "Check this option to allow signatories to customize the text of their petition email.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Set signature goal", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the number of signatures you hope to collect. This number is used to calculate the progress bar display.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Set expiration date", "dk_speakup" ) . '</strong>&mdash;' . __( "Use this option to stop collecting signatures on a specific date.", "dk_speakup" ) . '</p>
	';
	$tab_display_options = '
		<p><strong>' . __( "Display address fields", "dk_speakup" ) . '</strong>&mdash;' . __( "Select the address fields to display in the petition form.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Display custom field", "dk_speakup" ) . '</strong>&mdash;' . __( "Add a custom field to the petition form for collecting additional data.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Display opt-in checkbox", "dk_speakup" ) . '</strong>&mdash;' . __( "Include a checkbox that allows users to consent to receiving further email.", "dk_speakup" ) . '</p>
	';

	// create the tabs
	$screen = get_current_screen();

	$screen->add_help_tab( array (
		'id'      => 'dk_speakup_help_petition',
		'title'   => __( "Petition", "dk_speakup" ),
		'content' => $tab_petitions
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakup_help_twitter_message',
		'title'   => __( "Twitter Message", "dk_speakup" ),
		'content' => $tab_twitter_message
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakup_help_petition_options',
		'title'   => __( "Petition Options", "dk_speakup" ),
		'content' => $tab_petition_options
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakup_help_display_options',
		'title'   => __( "Display Options", "dk_speakup" ),
		'content' => $tab_display_options
	));
}

// contextual help for Settings page
function dk_speakup_help_settings() {
	$tab_petition_form = '
		<p>' . __( "These settings control the display of the [emailpetition] shortcode and sidebar widget.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Petition Theme", "dk_speakup" ) . '</strong>&mdash;' . __( "Select a CSS theme that will control the appearance of petition forms.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Widget Theme", "dk_speakup" ) . '</strong>&mdash;' . __( "Select a CSS theme that will control the appearance of petition widgets.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Submit Button Text", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the text that displays in the orange submit button on petition forms.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Success Message", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the text that appears when a user successfully signs your petition with a unique email address.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Share Message", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the text that appears above the Twitter and Facebook buttons after the petition form has been submitted.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Expiration Message", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the text to display in place of the petition form when a petition is past its expiration date.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Already Signed Message", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the text to display when a petition is signed using an email address that has already been submitted.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Opt-in Default", "dk_speakup" ) . '</strong>&mdash;' . __( "Choose whether the opt-in checkbox is checked or unchecked by default.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Display signature count", "dk_speakup" ) . '</strong>&mdash;' . __( "Choose whether you wish to display the number of signatures that have been collected.", "dk_speakup" ) . '</p>
	';
	$tab_confirmation_emails = '
		<p>' . __( "These settings control the content of the confirmation emails.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Email From", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the email address associated with your website. Confirmation emails will be sent from this address.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Email Subject", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the subject of the confirmation email.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Email Message", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the content of the confirmation email.", "dk_speakup" ) . '</p>
	';
	$tab_signature_list = '
		<p>' . __( "These settings control the display of the [signaturelist] shortcode.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Title", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the text that appears above the signature list.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Theme", "dk_speakup" ) . '</strong>&mdash;' . __( "Select a CSS theme that will control the appearance of signature lists.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Rows", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the number of signatures that will be displayed in the signature list.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Columns", "dk_speakup" ) . '</strong>&mdash;' . __( "Select the columns that will appear in the signature list.", "dk_speakup" ) . '</p>
	';
	$tab_admin_display = '
		<p>' . __( "These settings control the look of the plugin's options pages within the WordPress administrator.", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Petitions table shows", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the number of rows to display in the \"Email Petitions\" table", "dk_speakup" ) . '</p>
		<p><strong>' . __( "Signatures table shows", "dk_speakup" ) . '</strong>&mdash;' . __( "Enter the number of rows to display in the \"Signatures\" table", "dk_speakup" ) . '</p>
		<p><strong>' . __( "CSV file includes", "dk_speakup" ) . '</strong>&mdash;' . __( "Select the subset of signatures that will be included in CSV file downloads", "dk_speakup" ) . '</p>
	';

	// create the tabs
	$screen = get_current_screen();

	$screen->add_help_tab( array (
		'id'      => 'dk_speakup_help_petition_form',
		'title'   => __( "Petition Form", "dk_speakup" ),
		'content' => $tab_petition_form
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakup_help_signature_list',
		'title'   => __( "Signature List", "dk_speakup" ),
		'content' => $tab_signature_list
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakup_help_confirmation_emails',
		'title'   => __( "Confirmation Emails", "dk_speakup" ),
		'content' => $tab_confirmation_emails
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakup_help_admin_display',
		'title'   => __( "Admin Display", "dk_speakup" ),
		'content' => $tab_admin_display
	));
}
?>