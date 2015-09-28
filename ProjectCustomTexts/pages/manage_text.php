<?php
//plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
access_ensure_project_level( CPT_threshold( array( 'edit_all_threshold', 'edit_own_threshold' ) ) );

html_page_top( plugin_lang_get( 'manage_text_title' ) );
print_manage_menu();

$t_this_page = 'manage_text';
CPT_print_menu( $t_this_page );

?>
	<div class="form-container">
	<h2><?php echo plugin_lang_get( 'manage_predefined_texts' ); ?></h2>

		<table>
			<thead>
				<tr class="row-category">
					<td><?php echo plugin_lang_get( 'name' ); ?>
					</td>
					<td><?php echo plugin_lang_get( 'description' ); ?>
					</td>
					<td><?php echo plugin_lang_get( 'languages' ); ?>
					</td>
					<td><?php echo plugin_lang_get( 'created_by' ); ?>
					</td>
					<td><?php echo plugin_lang_get( 'action' ); ?>
					</td>
				</tr>
			</thead>

			<tbody>
			<?php
				$t_all = CPT_get_all_texts( ALL_PROJECTS );
				$t_page_edit = plugin_page( 'manage_text_edit' );
				$t_label_edit = plugin_lang_get( 'edit' );
				//$t_token_edit = form_security_token( 'CPT_manage_text_edit' );
				$t_page_delete = plugin_page( 'manage_text_delete' );
				$t_label_delete =  plugin_lang_get( 'delete_button' ) ;
				$t_token_delete = form_security_token( 'CPT_manage_text_delete' );
				
				foreach ($t_all as $t_item ) {
				   $t_obj = new CPT_Text( $t_item );
			?>
			<tr>
				<td class="center"><strong><?php echo $t_obj->name; ?></strong></td>
				<td><?php echo $t_obj->description ?></td>
				<td class="center"><?php echo implode( ', ', $t_obj->get_langs() ); ?></td>
				<td class="center"><?php echo user_get_name( $t_obj->user ); ?></td>
				<td>
				<?php
				if( access_has_global_level( CPT_threshold( 'edit_all_threshold', ALL_PROJECTS ) )
							|| ( access_has_project_level( CPT_threshold( 'edit_own_threshold' ) ) && $t_obj->user == auth_get_current_user_id() )
						) {
							print_button( $t_page_edit, $t_label_edit, array( 'txt_name' => $t_obj->name, /*'CPT_manage_text_edit_token' => $t_token_edit*/ ), OFF);
							print_button( $t_page_delete, $t_label_delete, array( 'txt_name' => $t_obj->name, 'CPT_manage_text_delete_token' => $t_token_delete ), OFF);
						}
				?>
				</td>
			</tr>
			<?php
				} //foreach
			?>

			</tbody>
		</table>
			<hr>
			<span>
				<form method="post" action="<?php echo plugin_page( 'manage_text_new '); ?>" class="action-button">
					<?php  echo form_security_field( 'CPT_manage_text_new' )?>
					<input type="text" name="newtxt_name" size="25" maxlength="25" />
					<input type="submit" class="button-small" name="btn_new" value="<?php echo plugin_lang_get( 'new_txt' ); ?>" />
				</form>
			</span>
	</div>

<?php
html_page_bottom();
?>
