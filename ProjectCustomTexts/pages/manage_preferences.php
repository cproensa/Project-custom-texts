<?php
//plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'manage_preferences_title' ) );
print_manage_menu();

$t_this_page = 'manage_preferences';
CPT_print_menu( $t_this_page );

$t_access = plugin_config_get( 'access_level', array(), FALSE, ALL_USERS, ALL_PROJECTS );
$t_token_field = form_security_field( 'CPT_manage_preferences_update' );

/*
 * prepare screen elements
 */
$scr['form1']['action'] = plugin_page( 'manage_preferences_update' );
$scr['form1']['legend'] = plugin_lang_get( 'permissions_config' );
$scr['form1']['fields'][1]['label'] = plugin_lang_get( 'configure_all_projects' );
$scr['form1']['fields'][1]['input'] =  '<select name="access_allpr">'
									. CPT_print_enum_string_option_list( 'access_levels', $t_access['manage_allprojects_threshold'] )
									. '</select>';
$scr['form1']['fields'][2]['label'] = plugin_lang_get( 'configure_own_projects' );
$scr['form1']['fields'][2]['input'] = '<select name="access_pr">'
									. CPT_print_enum_string_option_list( 'access_levels', $t_access['manage_project_threshold'] )
									. '</select>';
$scr['form1']['fields'][3]['label'] = plugin_lang_get( 'edit_all_texts' );
$scr['form1']['fields'][3]['input'] = '<select name="access_alltxt">'
									. CPT_print_enum_string_option_list( 'access_levels', $t_access['edit_all_threshold'] )
									. '</select>';
$scr['form1']['fields'][4]['label'] = plugin_lang_get( 'edit_own_texts' );
$scr['form1']['fields'][4]['input'] = '<select name="access_owntxt">'
									. CPT_print_enum_string_option_list( 'access_levels', $t_access['edit_own_threshold'] )
									. '</select>';
$scr['form1']['submit'] = '<input type="submit" name="btn_upd" class="button" name="btn_update" value="' . plugin_lang_get( 'update_button' ) . '" />';


$scr['form2']['action'] = plugin_page( 'manage_preferences_update' );
$scr['form2']['legend'] = plugin_lang_get( 'saved_configurations' );
$scr['form2']['fields'][1]['button'] = '<input type="submit" name="btn_reset" value="' . plugin_lang_get( 'reset_default_permissions' ) . '" />';
$scr['form2']['fields'][1]['descr'] = plugin_lang_get( 'reset_default_permissions_txt' );
$scr['form2']['fields'][2]['button'] = '<input type="submit" name="btn_delete" value="' . plugin_lang_get( 'delete_all_configuration' ) . '" />';
$scr['form2']['fields'][2]['descr'] = plugin_lang_get( 'delete_all_configuration_txt' )
?>

<?php
#
# HTML for mantis 1.2
#
if( '1.2' === GET_VER ) {
?>

<div align="center">
	<form method="post" action="<?php echo $scr['form1']['action']; ?>">
	<?php echo $t_token_field; ?>
		<table class="width75" cellspacing="1">
			<tr>
				<td class="form-title" colspan="2">
					<?php echo $scr['form1']['legend']; ?>
				</td>
			</tr>

			<?php foreach( $scr['form1']['fields'] as $field ){ ?>
			<tr <?php echo helper_alternate_class( 1 ) ?>>
				<td class="category" width="30%">
					<?php echo $field['label']; ?>
				</td>
				<td width="70%">
					<?php echo $field['input']; ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2" class="center">
					<?php echo $scr['form1']['submit']; ?>
				</td>
			</tr>
		</table>
	</form>
</div>
<br />
<div align="center">
	<form method="post" action="<?php echo $scr['form2']['action']; ?>">
	<?php echo $t_token_field; ?>
		<table class="width75" cellspacing="1">
			<tr>
				<td class="form-title" colspan="2">
					<?php echo $scr['form2']['legend']; ?>
				</td>
			</tr>
			<?php foreach( $scr['form2']['fields'] as $field ){ ?>
			<tr <?php echo helper_alternate_class( 1 ) ?>>
				<td width="30%">
					<?php echo $field['button']; ?>
				</td>
				<td width="70%">
					<?php echo $field['descr']; ?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</form>
</div>

<?php
} #endif html 1.2
else {
#
# HTML for mantis 1.3
#
?>

<div class="form-container">
<form method="post" action="<?php echo $scr['form1']['action']; ?>">
<?php echo $t_token_field; ?>
	<fieldset>
		<legend>
		<div>
			<?php echo $scr['form1']['legend']; ?>
		</div>
		</legend>

		<?php foreach( $scr['form1']['fields'] as $field ){ ?>
			<div class="field-container">
				<label>
					<?php echo $field['label']; ?>
				</label>
				<span class="input">
					<?php echo $field['input']; ?>
				</span>
				<span class="label-style"></span>
			</div>
		<?php } ?>

	</fieldset>
	<fieldset>
		<span class="submit-button">
		<?php echo $scr['form1']['submit']; ?>
		</span>
	</fieldset>
</form>
</div>

<div class="form-container">
<form method="post" action="<?php echo $scr['form2']['action']; ?>">
<?php  echo $t_token_field; ?>
	<fieldset>
		<legend>
		<div>
			<?php echo $scr['form2']['legend']; ?>
		</div>
		</legend>

		<?php foreach( $scr['form2']['fields'] as $field ){ ?>
			<div class="field-container">
				<label class="right">
				<?php echo $field['button']; ?>
				</label>
				<span class="input"><?php echo $field['descr'] ?></span>
			</div>
			<div class="spacer"></div>
		<?php } ?>

	</fieldset>
</form>
</div>

<?php
} #end html 1.3

html_page_bottom();
?>