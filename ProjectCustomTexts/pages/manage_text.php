<?php
//plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
access_ensure_project_level( CPT_threshold( array( 'edit_all_threshold', 'edit_own_threshold' ) ) );

html_page_top( plugin_lang_get( 'manage_text_title' ) );
print_manage_menu();

$t_this_page = 'manage_text';
CPT_print_menu( $t_this_page );




/*
 * prepare screen elements
 */
$scr['legend'] = plugin_lang_get( 'manage_predefined_texts' );
$scr['head'][1] = plugin_lang_get( 'name' );
$scr['head'][2] = plugin_lang_get( 'description' );
$scr['head'][3] = plugin_lang_get( 'contents' );
$scr['head'][4] = plugin_lang_get( 'created_by' );
$scr['head'][5] = plugin_lang_get( 'action' );

$t_all = CPT_get_all_texts( );
$t_page_edit = plugin_page( 'manage_text_edit' );
$t_label_edit = plugin_lang_get( 'edit' );
$t_page_delete = plugin_page( 'manage_text_delete' );
$t_label_delete =  plugin_lang_get( 'delete_button' ) ;
$t_token_delete = form_security_token( 'CPT_manage_text_delete' );
$scr['row'] = array();
foreach ($t_all as $t_item ) {
	$t_obj = new CPT_Text( $t_item );
	$t_row = array();
	$t_row[1] = $t_obj->name;
	$t_row[2] = $t_obj->description;
	$t_row[3] = implode( ', ', $t_obj->get_langs() );
	$t_row[4] = user_get_name( $t_obj->user );
	$t_row[5] = '';
		if( access_has_global_level( CPT_threshold( 'edit_all_threshold', ALL_PROJECTS ) )
				|| ( access_has_project_level( CPT_threshold( 'edit_own_threshold' ) ) && $t_obj->user == auth_get_current_user_id() )
			) {
				$t_row[5] .= CPT_print_button( $t_page_edit, $t_label_edit, array( 'txt_name' => $t_obj->name), OFF);
				$t_row[5] .= CPT_print_button( $t_page_delete, $t_label_delete, array( 'txt_name' => $t_obj->name, 'CPT_manage_text_delete_token' => $t_token_delete ), OFF);
			}
	$scr['row'][] = $t_row;
}

$scr['add']['action'] = plugin_page( 'manage_text_new ');
$scr['add']['input'] = '<input type="text" name="newtxt_name" size="25" maxlength="25" />';
$scr['add']['submit'] = '<input type="submit" class="button-small" name="btn_new" value="' . plugin_lang_get( 'new_txt' ) . '" />';
$scr['add']['token'] = form_security_field( 'CPT_manage_text_new' );
?>


<?php
#
# HTML for mantis 1.2
#
if( '1.2' === GET_VER ) {
?>

<table class="width100" cellspacing="1">
	<tr>
		<td class="form-title" colspan="5">
			<?php echo $scr['legend']; ?>
		</td>
	</tr>
	<tr class="row-category">
		<td width="20%"><?php echo $scr['head'][1]; ?></td>
		<td width="40%"><?php echo $scr['head'][2]; ?></td>
		<td width="20%"><?php echo $scr['head'][3]; ?></td>
		<td width="10%"><?php echo $scr['head'][4]; ?></td>
		<td width="10%"><?php echo $scr['head'][5]; ?></td>
	</tr>
	<?php foreach( $scr['row'] as $row ){ ?>
		<tr <?php echo helper_alternate_class( 1 ) ?>>
			<td width="20%" class="center"><strong><?php echo $row[1]; ?></strong></td>
			<td width="40%"><?php echo $row[2]; ?></td>
			<td width="20%" class="center"><?php echo $row[3]; ?></td>
			<td width="10%" class="center"><?php echo $row[4]; ?></td>
			<td width="10%"><?php echo $row[5]; ?></td>
		</tr>
	<?php } ?>
	<tr>
		<td colspan="5">
			<form method="post" action="<?php echo $scr['add']['action']; ?>" >
				<?php
					echo $scr['add']['input'];
					echo $scr['add']['submit'];
					echo $scr['add']['token'];
				?>
			</form>
		</td>
	</tr>
</table>


<?php
} #endif html 1.2
else {
#
# HTML for mantis 1.3
#
?>

<div class="form-container">
<h2><?php echo $scr['legend']; ?></h2>
	<table>
		<thead>
			<tr class="row-category">
				<td><?php echo $scr['head'][1]; ?></td>
				<td><?php echo $scr['head'][2]; ?></td>
				<td><?php echo $scr['head'][3]; ?></td>
				<td><?php echo $scr['head'][4]; ?></td>
				<td><?php echo $scr['head'][5]; ?></td>
			</tr>
		</thead>
		<tbody>
		<?php foreach( $scr['row'] as $row ){ ?>
			<tr>
				<td class="center">
					<strong><?php echo $row[1]; ?></strong>
				</td>
				<td>
					<?php echo $row[2]; ?>
				</td>
				<td class="center">
					<?php echo $row[3]; ?>
				</td>
				<td class="center">
					<?php echo $row[4]; ?>
				</td>
				<td>
					<?php echo $row[5]; ?>
				</td>
			</tr>
		<?php }	?>
		</tbody>
	</table>
	<hr>
	<span>
		<form method="post" action="<?php echo $scr['add']['action']; ?>" >
			<?php
				echo $scr['add']['input'];
				echo $scr['add']['submit'];
				echo $scr['add']['token'];
			?>
		</form>
	</span>
</div>

<?php
} #end html 1.3

html_page_bottom();
?>
