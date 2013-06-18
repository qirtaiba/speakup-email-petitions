<?php

/**
 * Class for displaying signatures via [signaturelist] shortcode
 */
class dk_speakup_Signaturelist
{

	/**
	 * generates HTML table of signatures for a single petition
	 *
	 * @param int $id the ID petition for which we are displaying signatures
	 * @param int $start the first signature to be retrieved
	 * @param int $limit number of signatures to be retrieved
	 * @param string $context either 'shortcode' or 'ajax' to distinguish between calls from the initia page load (shortcode) and calls from pagination buttons (ajax)
	 * @param string $dateformat PHP date format provided by shortcode attribute - also relayed in ajax requests
	 * @param string $nextbuttontext provided by shortcode attribute
	 * @param string $prevtbuttontext provided by shortcode attribute
	 *
	 * @return string HTML table containing signatures (or just the table rows if context is ajax)
	 */
	public static function table( $id, $start, $limit, $context = 'shortcode', $dateformat = 'M d, Y', $nextbuttontext = '&gt;', $prevbuttontext = '&lt;', $org = '0' ) {

		include_once( 'class.signature.php' );
		$the_signatures = new dk_speakup_Signature();
		$options = get_option( 'dk_speakup_options' );

		// get list of columns to display - as defined in settings
		$columns = unserialize( $options['signaturelist_columns'] );
        
        if($org == 1) {
            // get the signatures
            $signatures = $the_signatures->all( $id, $start, $limit, 'signaturelist', '1' );
    
            $total = $the_signatures->count( $id, 'signaturelist', '1' );
        } else {
            // get the signatures
            $signatures = $the_signatures->all( $id, $start, $limit, 'signaturelist', '0' );
    
            $total = $the_signatures->count( $id, 'signaturelist', '0' );
        }

		
		$current_signature_number = $total - $start;
		$signatures_list = '';

		// only show signature lists if there are signatures
		if ( $total > 0 ) {
			// determine which columns to display
			$display_city     = ( in_array( 'sig_city', $columns ) ) ? 1 : 0;
			$display_state    = ( in_array( 'sig_state', $columns ) ) ? 1 : 0;
			$display_postcode = ( in_array( 'sig_postcode', $columns ) ) ? 1 : 0;
			$display_country  = ( in_array( 'sig_country', $columns ) ) ? 1 : 0;
			$display_custom   = ( in_array( 'sig_custom', $columns ) ) ? 1 : 0;
			$display_date     = ( in_array( 'sig_date', $columns ) ) ? 1 : 0;

			if ( $context !== 'ajax' ) { // only include on initial page load (not when paging)
				$signatures_list = '
					<!-- signaturelist -->
					<table class="dk-speakup-signaturelist dk-speakup-signaturelist-' . $id . '">
						<caption>' . $options['signaturelist_header'] . '</caption>';
			}

			$row_count = 0;
			foreach ( $signatures as $signature ) {
				if ( $row_count % 2 ) {
					$signatures_list .= '<tr class="dk-speakup-even">';
				}
				else {
					$signatures_list .= '<tr class="dk-speakup-odd">';
				}
				$signatures_list .= '<td class="dk-speakup-signaturelist-count">' . number_format( $current_signature_number, 0, '.', ',' ) . '</td>';
				// modified by terminus to show organisation name instead of personal name if we have both
				$signatures_list .= '<td class="dk-speakup-signaturelist-name">';
				if ( $signature->custom_field ) {
				    $signatures_list .= '<td class="dk-speakup-signaturelist-name">' . stripslashes( $signature->first_name . ' ' . $signature->last_name );
					$signatures_list .= '<td class="dk-speakup-signaturelist-name">' . stripslashes( $signature->custom_field );
				} else {
					$signatures_list .= '<td class="dk-speakup-signaturelist-name">' . stripslashes( $signature->first_name . ' ' . $signature->last_name );
                    //$signatures_list .= '<td class="dk-speakup-signaturelist-name">' . stripslashes( $signature->last_name );
				}

				//$signatures_list .= '<td class="dk-speakup-signaturelist-name">' . stripslashes( $signature->first_name . ' ' . $signature->last_name );
			    //$signatures_list .= '<td class="dk-speakup-signaturelist-name">' . stripslashes( $signature->custom_field );
				$signatures_list .= '</td>';

				// if we display both city and state, combine them into one column
				$city  = ( $display_city )  ? $signature->city : '';
				$state = ( $display_state ) ? $signature->state : '';
				if ( $display_city && $display_state ) {
					// should we separate with a comma?
					$delimiter = ( $city !='' && $state != '' ) ? ', ' : '';
					$signatures_list .= '<td class="dk-speakup-signaturelist-city">' . stripslashes( $city . $delimiter . $state ) . '</td>';
				}
				// else keep city or state values in their own column
				else {
					if ( $display_city ) $signatures_list  .= '<td class="dk-speakup-signaturelist-city">' . stripslashes( $city ) . '</td>';
					if ( $display_state ) $signatures_list .= '<td class="dk-speakup-signaturelist-state">' . stripslashes( $state ) . '</td>';
				}

				if ( $display_postcode ) $signatures_list .= '<td class="dk-speakup-signaturelist-postcode">' . stripslashes( $signature->postcode ) . '</td>';
				if ( $display_country ) $signatures_list  .= '<td class="dk-speakup-signaturelist-country">' . stripslashes( $signature->country ) . '</td>';
				if ( $display_custom ) $signatures_list   .= '<td class="dk-speakup-signaturelist-custom">' . stripslashes( $signature->custom_field ) . '</td>';
				if ( $display_date ) $signatures_list     .= '<td class="dk-speakup-signaturelist-date">' . date_i18n( $dateformat, strtotime( $signature->date ) ) . '</td>';
				$signatures_list .= '</tr>';
 
				$current_signature_number --;
				$row_count ++;
			}

			if ( $context !== 'ajax' ) { // only include on initial page load

				if ( $limit != 0 && $start + $limit < $total  ) {
					$colspan = ( count( $columns ) + 2 );
					$signatures_list .= '
					<tr class="dk-speakup-signaturelist-pagelinks">
						<td colspan="' . $colspan . '">
							<a class="dk-speakup-signaturelist-prev dk-speakup-signaturelist-disabled" rel="' . $id .  ',' . $total . ',' . $limit . ',' . $total . ',0">' . $prevbuttontext . '</a>
							<a class="dk-speakup-signaturelist-next" rel="' . $id .  ',' . ( $start + $limit ) . ',' . $limit . ',' . $total . ',1">' . $nextbuttontext . '</a>
						</td>
					</tr>
					';
				}
				$signatures_list .= '</table>';
			}

		}

		return $signatures_list;
	}

    public static function tableOrg( $id, $start, $limit, $context = 'shortcode', $dateformat = 'M d, Y', $nextbuttontext = '&gt;', $prevbuttontext = '&lt;', $org = '1' ) {

        include_once( 'class.signature.php' );
        $the_signatures = new dk_speakup_Signature();
        $options = get_option( 'dk_speakup_options' );

        // get list of columns to display - as defined in settings
        $columns = unserialize( $options['signaturelist_columns'] );
        
        if($org == 1) {
            // get the signatures
            $signatures = $the_signatures->all( $id, $start, $limit, 'signaturelist', '1' );
    
            $total = $the_signatures->count( $id, 'signaturelist', '1' );
        } else {
            // get the signatures
            $signatures = $the_signatures->all( $id, $start, $limit, 'signaturelist', '0' );
    
            $total = $the_signatures->count( $id, 'signaturelist', '0' );
        }

        
        $current_signature_number = $total - $start;
        $signatures_list = '';

        // only show signature lists if there are signatures
        if ( $total > 0 ) {
            // determine which columns to display
            $display_city     = ( in_array( 'sig_city', $columns ) ) ? 1 : 0;
            $display_state    = ( in_array( 'sig_state', $columns ) ) ? 1 : 0;
            $display_postcode = ( in_array( 'sig_postcode', $columns ) ) ? 1 : 0;
            $display_country  = ( in_array( 'sig_country', $columns ) ) ? 1 : 0;
            $display_custom   = ( in_array( 'sig_custom', $columns ) ) ? 1 : 0;
            $display_date     = ( in_array( 'sig_date', $columns ) ) ? 1 : 0;

            if ( $context !== 'ajax' ) { // only include on initial page load (not when paging)
                $signatures_list = '
                    <!-- signaturelist -->
                    <table class="dk-speakup-signaturelist-org dk-speakup-signaturelist-org-' . $id . '">
                        <caption>' . $options['signaturelist_header_org'] . '</caption>';
            }

            $row_count = 0;
            foreach ( $signatures as $signature ) {
                if ( $row_count % 2 ) {
                    $signatures_list .= '<tr class="dk-speakup-even">';
                }
                else {
                    $signatures_list .= '<tr class="dk-speakup-odd">';
                }
                $signatures_list .= '<td class="dk-speakup-signaturelist-org-count">' . number_format( $current_signature_number, 0, '.', ',' ) . '</td>';
                // modified by terminus to show organisation name instead of personal name if we have both
                $signatures_list .= '<td class="dk-speakup-signaturelist-org-name">';
                if ( $signature->custom_field ) {
                    //$signatures_list .= '<td class="dk-speakup-signaturelist-org-name">' . stripslashes( $signature->first_name . ' ' . $signature->last_name );
                    $signatures_list .= '<td class="dk-speakup-signaturelist-org-name">' . stripslashes( $signature->custom_field );
                } else {
                    $signatures_list .= '<td class="dk-speakup-signaturelist-org-name">' . stripslashes( $signature->first_name . ' ' . $signature->last_name );
                }

                //$signatures_list .= '<td class="dk-speakup-signaturelist-org-name">' . stripslashes( $signature->first_name . ' ' . $signature->last_name );
                //$signatures_list .= '<td class="dk-speakup-signaturelist-org-name">' . stripslashes( $signature->custom_field );
                $signatures_list .= '</td>';

                // if we display both city and state, combine them into one column
                $city  = ( $display_city )  ? $signature->city : '';
                $state = ( $display_state ) ? $signature->state : '';
                if ( $display_city && $display_state ) {
                    // should we separate with a comma?
                    $delimiter = ( $city !='' && $state != '' ) ? ', ' : '';
                    $signatures_list .= '<td class="dk-speakup-signaturelist-org-city">' . stripslashes( $city . $delimiter . $state ) . '</td>';
                }
                // else keep city or state values in their own column
                else {
                    if ( $display_city ) $signatures_list  .= '<td class="dk-speakup-signaturelist-org-city">' . stripslashes( $city ) . '</td>';
                    if ( $display_state ) $signatures_list .= '<td class="dk-speakup-signaturelist-org-state">' . stripslashes( $state ) . '</td>';
                }

                if ( $display_postcode ) $signatures_list .= '<td class="dk-speakup-signaturelist-org-postcode">' . stripslashes( $signature->postcode ) . '</td>';
                if ( $display_country ) $signatures_list  .= '<td class="dk-speakup-signaturelist-org-country">' . stripslashes( $signature->country ) . '</td>';
                if ( $display_custom ) $signatures_list   .= '<td class="dk-speakup-signaturelist-org-custom">' . stripslashes( $signature->custom_field ) . '</td>';
                if ( $display_date ) $signatures_list     .= '<td class="dk-speakup-signaturelist-org-date">' . date_i18n( $dateformat, strtotime( $signature->date ) ) . '</td>';
                $signatures_list .= '</tr>';
 
                $current_signature_number --;
                $row_count ++;
            }

            if ( $context !== 'ajax' ) { // only include on initial page load

                if ( $limit != 0 && $start + $limit < $total  ) {
                    $colspan = ( count( $columns ) + 2 );
                    $signatures_list .= '
                    <tr class="dk-speakup-signaturelist-org-pagelinks">
                        <td colspan="' . $colspan . '">
                            <a class="dk-speakup-signaturelist-org-prev dk-speakup-signaturelist-org-disabled" rel="' . $id .  ',' . $total . ',' . $limit . ',' . $total . ',0">' . $prevbuttontext . '</a>
                            <a class="dk-speakup-signaturelist-org-next" rel="' . $id .  ',' . ( $start + $limit ) . ',' . $limit . ',' . $total . ',1">' . $nextbuttontext . '</a>
                        </td>
                    </tr>
                    ';
                }
                $signatures_list .= '</table>';
            }

        }

        return $signatures_list;
    }

}

?>
