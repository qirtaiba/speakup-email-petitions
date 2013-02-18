<div class="wrap" id="dk-speakup">

	<div id="icon-dk-speakup" class="icon32"><br /></div>
	<h2><?php _e( 'Signatures', 'dk_speakup' ); ?></h2>
	<?php if ( $message_update ) echo '<div id="message" class="updated"><p>' . $message_update . '</p></div>' ?>

	<div class="tablenav">
		<ul class='subsubsub'>
			<li class='table-label'><?php echo stripslashes( $table_label ); ?></li>
		</ul>

		<div class="dk_speakup_clear">
			<div class="alignleft">
				<form action="" method="get">
					<select id="dk-speakup-switch-petition">
						<option value=""><?php _e( 'Select petition', 'dk_speakup' ); ?></option>
						<?php foreach( $petitions_list as $petition ) : ?>
							<option value="<?php echo $petition->id; ?>"><?php echo stripslashes( $petition->title ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php
						// Display the 'Download as CSV' and 'Re-send confirmations' buttond only when viewing signatures for a single petition
						// Hide buttons when viewing All Signatures
						if( isset( $_REQUEST['pid'] ) || $pid != '' ) {
							echo ' 
								<a class="button dk-speakup-inline" style="margin: 0 .5em 0 .5em" href="' .  esc_url( wp_nonce_url( $csv_url . '&csv=signatures', 'dk_speakup-download_signatures' ) ) . '">' . __( 'Download as CSV', 'dk_speakup' ) . '</a>
								<a id="dk-speakup-reconfirm" class="button dk-speakup-inline" href="' . esc_url( wp_nonce_url( $reconfirm_url, 'dk_speakup-resend_confirmations' . $pid ) ) . '">' . __( 'Re-send confirmations', 'dk_speakup' ) . '</a>
								<div id="dk-speakup-reconfirm-confirmation" class="dk-speakup-hidden">' . __( "Are you sure you want to do this? A separate confirmation email will be sent for each unconfirmed signature.", "dk_speakup" ) . '</div>
							';
						}
					?>
				</form>
			</div>
			<div class="alignright">
				<?php echo dk_speakup_SpeakUp::pagination( $query_limit, $count, 'dk_speakup_signatures', $current_page, $base_url, true ); ?>
			</div>
		</div>
	</div>

	<table class="widefat">
		<thead>
			<tr>
				<th></th>
				<th><?php _e( 'Name', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Email', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Petition', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Confirmed', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Opt-in', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Date', 'dk_speakup' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th></th>
				<th><?php _e( 'Name', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Email', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Petition', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Confirmed', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Opt-in', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Date', 'dk_speakup' ); ?></th>
				<th></th>
			</tr>
		</tfoot>
		<tbody>
			<?php if ( $count == 0 ) echo '<tr><td colspan="8">' . __( "No signatures found.", "dk_speakup" ) . ' </td></tr>'; ?>
			<?php $current_row = ( $count - $query_start ) + 1; ?>
			<?php foreach ( $signatures as $signature ) : ?>
				<?php $pid_string = ( $pid ) ? '&pid=' . $pid : '' ; ?>
				<?php $delete_url = esc_url( wp_nonce_url( site_url() . '/wp-admin/admin.php?page=dk_speakup_signatures&action=delete&sid=' . $signature->id . $pid_string, 'dk_speakup-delete_signature' . $signature->id ) ); ?>
				<?php
					$current_row --;
					// make confirmed values readable
					$confirmed = $signature->is_confirmed;
					if ( $confirmed == '1' )
						$confirmed = '<span class="dk-speakup-green">'  . __( 'confirmed', 'dk_speakup' ) . '</span>';
					elseif ( $confirmed == '0' )
						$confirmed = __( 'unconfirmed', 'dk_speakup' );
					else
						$confirmed = '...';
					// make email opt-in values readable
					$optin = $signature->optin;
					if ( $optin == '1' )
						$optin = '<span class="dk-speakup-green">'  . __( 'yes', 'dk_speakup' ) . '</span>';
					elseif ( $optin == '0' )
						$optin = __( 'no', 'dk_speakup' );
					else
						$optin = '...';
				?>
			<tr class="dk-speakup-tablerow">
				<td class="dk-speakup-right"><?php echo number_format( $current_row, 0, '.', ',' ); ?></td>
				<td><?php echo stripslashes( esc_html( $signature->first_name . ' ' . $signature->last_name ) ); ?></td>
				<td><?php echo stripslashes( esc_html( $signature->email ) ); ?></td>
				<td><?php echo stripslashes( esc_html( $signature->title ) ); ?></td>
				<td><?php echo $confirmed; ?></td>
				<td><?php echo $optin; ?></td>
				<td><?php echo ucfirst( date_i18n( 'M d, Y', strtotime( $signature->date ) ) ); ?></td>
				<td class="dk-speakup-right"><span class="trash"><a href="<?php echo $delete_url; ?>"><?php _e( 'Delete', 'dk_speakup' ); ?></a></span></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="tablenav">
		<?php echo dk_speakup_SpeakUp::pagination( $query_limit, $count, 'dk_speakup_signatures', $current_page, $base_url, false ); ?>
	</div>

</div>