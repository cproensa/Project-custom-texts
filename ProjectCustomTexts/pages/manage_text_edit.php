<?php
//plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
CPT_ensure_access_level( array( 'edit_all', 'edit_own' ) );

//form_security_validate( 'CPT_manage_text_edit' );
html_page_top( plugin_lang_get( 'manage_text_edit_title' ) );
print_manage_menu();
CPT_print_menu();

$f_name = gpc_get( 'txt_name' );
$t_obj = CPT_text_load( $f_name );

if( null == $t_obj ) {
	error_parameters( $f_name );
	trigger_error( ERROR_LANG_STRING_NOT_FOUND, ERROR );
}

if( !( CPT_access_has_level( 'edit_all' ) || ( CPT_access_has_level( 'edit_own' ) && $t_obj->user == auth_get_current_user_id() ) ) ) {
	access_denied();
}

$t_lang_available = config_get( 'language_choices_arr' );
$t_lang_default = config_get( 'default_language' );
$t_lang_fallback = config_get( 'fallback_language' );

if( !isset( $t_obj->contents[$t_lang_default] ) ) {
	$t_obj->contents[$t_lang_default] = '';
}
if( !isset( $t_obj->contents[$t_lang_fallback] ) ) {
	$t_obj->contents[$t_lang_fallback] = '';
}

$t_lang_unused = array_diff( $t_lang_available, array_keys( $t_obj->contents ), array( 'auto' ) );



/*
 * prepare screen elements
 */
$scr['b_del'] = CPT_get_button( plugin_page( 'manage_text_delete' ), plugin_lang_get( 'delete_button' ) , array( 'txt_name' => $t_obj->name, 'CPT_manage_text_delete_token' => form_security_token( 'CPT_manage_text_delete' ) ), OFF );
$scr['legend'] = plugin_lang_get( 'edit_predefined_text' );
$scr['action'] = plugin_page( 'manage_text_edit_update' );
$scr['token'] = form_security_field( 'CPT_manage_text_edit_update' );

$field['label'] = plugin_lang_get( 'name' );
$field['input'] = '<input type="text" name="txt_name" size="25" maxlength="25" readonly value="' . $t_obj->name . '" />';
$scr['fields'][1] = $field;
$field['label'] = plugin_lang_get( 'description' );
$field['input'] = '<input type="text" name="txt_descr" size="60" maxlength="128" value="' . $t_obj->description . '"/>';
$scr['fields'][2] = $field;

$scr['txts'] = array();
$i = 0;
foreach( $t_obj->contents as $t_lang => $t_str ) {
	$t_def = ($t_lang == $t_lang_default ) ? '[' . plugin_lang_get( 'default' ) . ']' : '';
	$t_fbk = ($t_lang == $t_lang_fallback ) ? '[' . plugin_lang_get( 'fallback' ) . ']' : '';
	$txt['label'] = plugin_lang_get( 'contents' );
	$txt['sub1'] = $t_lang;
	$txt['sub2'] = "$t_def $t_fbk";
	$txt['area'] = '<textarea name="txt_cont[' . $i . ']" cols="100" rows="5">' . string_textarea( $t_str ) . '</textarea>';
	$txt['hidden'] = '<input type="hidden" name="txt_lang[' . $i . ']" value="' . $t_lang . '" />';
	$i++;
	$scr['txts'][] = $txt;
}

$scr['langlist'] = '';
$scr['langbtn'] = '';
if( !empty( $t_lang_unused ) ) {
	$scr['langlist'] .= '<select name="new_lang">';
	foreach( $t_lang_unused as $t_l ) {
		if( $t_l != 'auto' ) {
		  $scr['langlist'] .= '<option value="' . $t_l . '">' . $t_l . '</option>';
		}
	}
	$scr['langlist'] .= '</select>';
	$scr['langbtn'] = '<input type="submit" class="button-small" name="btn_addlang" value="' . plugin_lang_get( 'add_lang_button' ) . '" />';
}
$scr['savebtn'] = '<input type="submit" class="button" name="btn_upd" value="' . plugin_lang_get( 'save' ) . '" />';
?>


<?php
#
# HTML for mantis 1.2
#
if( '1.2' === GET_VER ) {
?>

<div align="center">
	<table class="width75" cellspacing="1">
		<tr>
			<td width="80%" class="form-title" colspan="2">
				<?php echo $scr['legend']; ?>
			</td>
			<td width="20%" class="right">
				<?php echo $scr['b_del']; ?>
			</td>
		</tr>
		<form method="post" action="<?php echo $scr['action']; ?>">
		<?php echo $scr['token']; ?>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category" width="30%">
					<span class="required">*</span>
					<?php echo $scr['fields'][1]['label']; ?>
				</td>
				<td width="70%" colspan="2">
					<?php echo $scr['fields'][1]['input']; ?>
				</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category" width="30%">
					<?php echo $scr['fields'][2]['label']; ?>
				</td>
				<td width="70%" colspan="2">
					<?php echo $scr['fields'][2]['input']; ?>
				</td>
			</tr>
			<?php foreach( $scr['txts'] as $txt ) { ?>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category" width="30%">
						<span><?php echo $txt['label']; ?></span><br>
						<span><?php echo $txt['sub1']; ?></span><br>
						<span class="small"><?php echo $txt['sub2']; ?></span>
					</td>
					<td width="70%" colspan="2">
						<?php echo $txt['area']; ?>
						<?php echo $txt['hidden']; ?>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="3" class="left">
					<?php echo $scr['langlist']; ?>
					<?php echo $scr['langbtn']; ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="center">
					<?php echo $scr['savebtn']; ?>
				</td>
			</tr>
		</form>
	</table>
</div>


<?php
} #endif html 1.2
else {
#
# HTML for mantis 1.3
#
?>

<div class="form-container">
	<fieldset>
		<div class="floatright">
		<?php echo $scr['b_del']; ?>
		</div>
	<legend>
		<span><?php echo $scr['legend']; ?></span>
	</legend>
	<form method="post" action="<?php echo $scr['action']; ?>">
	<?php echo $scr['token']; ?>
		<div class="field-container">
				<label class="required"><span><?php echo $scr['fields'][1]['label'] ?></span></label>
				<span class="input"><?php echo $scr['fields'][1]['input'] ?></span>
				<span class="label-style"></span>
		</div>
		<div class="field-container">
				<label><span><?php echo $scr['fields'][2]['label'] ?></span></label>
				<span class="input"><?php echo $scr['fields'][2]['input'] ?></span>
				<span class="label-style"></span>
		</div>
		<?php foreach( $scr['txts'] as $txt ) { ?>
			<div class="field-container">
				<label>
					<span><?php echo $txt['label']; ?></span><br>
					<span><?php echo $txt['sub1']; ?></span><br>
					<span class="small"><?php echo $txt['sub2']; ?></span>
				</label>
				<span class="textarea"><?php echo $txt['area']; ?></span>
				<?php echo $txt['hidden']; ?>
				<span class="label-style"></span>
			</div>
		<?php } ?>
		</fieldset>
		<span>
			<?php echo $scr['langlist']; ?>
			<?php echo $scr['langbtn']; ?>
		</span>
		<hr>
		<span class="submit-button"><?php echo $scr['savebtn']; ?></span>

	</form>
</div>

<?php
} #end html 1.3

html_page_bottom();
