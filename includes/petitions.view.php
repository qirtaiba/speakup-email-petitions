<div class="wrap" id="dk-speakup">

	<div id="icon-dk-speakup" class="icon32"><br /></div>
	<h2><?php echo $page_title; ?> <a href="<?php echo $addnew_url; ?>" class="add-new-h2"><?php _e( 'Add New', 'dk_speakup' ); ?></a></h2>
	<?php if ( $message_update ) echo '<div id="message" class="updated"><p>' . $message_update . '</p></div>' ?>

	<div class="tablenav">
		<ul class='subsubsub'>
			<li class='table-label'><?php _e( 'All Petitions', 'dk_speakup' ); ?> <span class="count">(<?php echo $count; ?>)</span></li>
		</ul>
		<?php echo dk_speakup_SpeakUp::pagination( $query_limit, $count, 'dk_speakup', $current_page, site_url( 'wp-admin/admin.php?page=dk_speakup' ), true ); ?>
	</div>

	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e( 'Petition', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Shortcodes', 'dk_speakup' ); ?></th>
				<th class="dk-speakup-right"><?php _e( 'Signatures', 'dk_speakup' ); ?></th>
				<th class="dk-speakup-right"><?php _e( 'Goal', 'dk_speakup' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Petition', 'dk_speakup' ); ?></th>
				<th><?php _e( 'Shortcodes', 'dk_speakup' ); ?></th>
				<th class="dk-speakup-right"><?php _e( 'Signatures', 'dk_speakup' ); ?></th>
				<th class="dk-speakup-right"><?php _e( 'Goal', 'dk_speakup' ); ?></th>
				<th></th>
			</tr>
		</tfoot>
		<tbody>
		<?php if ( $count == 0 ) echo '<tr><td colspan="5">' . __( "No petitions found.", "dk_speakup" ) . ' </td></tr>'; ?>
		<?php foreach ( $petitions as $petition ) : ?>
			<?php $edit_url       = esc_url( wp_nonce_url( site_url() . '/wp-admin/admin.php?page=dk_speakup_addnew&action=edit&id=' . $petition->id, 'dk_speakup-edit_petition' . $petition->id ) ); ?>
			<?php $delete_url     = esc_url( wp_nonce_url( site_url() . '/wp-admin/admin.php?page=dk_speakup&action=delete&id=' . $petition->id, 'dk_speakup-delete_petition' . $petition->id ) ); ?>
			<?php $signatures_url = esc_url( site_url() . '/wp-admin/admin.php?page=dk_speakup_signatures&action=petition&pid=' . $petition->id ); ?>
			<tr class="dk-speakup-tablerow">
				<td>
					<a class="row-title" href="<?php echo $edit_url; ?>"><?php echo stripslashes( esc_html( $petition->title ) ); ?></a>
					<div class="row-actions">
						<span class="edit"><a href="<?php echo $edit_url; ?>"><?php _e( 'Edit' ); ?></a> | </span>
						<span><a href="<?php echo $delete_url; ?>" class="dk-speakup-delete-petition"><?php _e( 'Delete', 'dk_speakup' ); ?></a></span>
					</div>
				</td>
				<td><?php echo '[emailpetition&nbsp;id="' . $petition->id . '"]<br />[signaturelist&nbsp;id="' . $petition->id . '"]'; ?></td>
				<td class="dk-speakup-right"><?php echo number_format( $petition->signatures ); ?></td>
				<td class="dk-speakup-right">
					<?php echo number_format( $petition->goal ); ?>
					<div class="dk_speakup_clear"></div>
					<?php echo dk_speakup_SpeakUp::progress_bar( $petition->goal, $petition->signatures, 65 ); ?>
				</td>
				<td class="dk-speakup-right" style="vertical-align: middle"><a class="button" href="<?php echo $signatures_url; ?>"><?php _e( 'View Signatures', 'dk_speakup' ); ?></a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div class="tablenav">
		<?php echo dk_speakup_SpeakUp::pagination( $query_limit, $count, 'dk_speakup', $current_page, site_url( 'wp-admin/admin.php?page=dk_speakup' ), false ); ?>
	</div>

	<div id="dk-speakup-delete-confirmation" class="dk-speakup-hidden"><?php _e( 'Delete this petition permanently? All of the petition\'s signatures will be deleted as well.', 'dk_speakup' ); ?></div>

</div>