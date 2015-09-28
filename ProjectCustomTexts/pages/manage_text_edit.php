<?php
//plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
access_ensure_project_level( CPT_threshold( array( 'edit_all_threshold', 'edit_own_threshold' ) ) );

form_security_validate( 'CPT_manage_text_edit' );
html_page_top( plugin_lang_get( 'configuration' ) ); 
print_manage_menu();
CPT_print_menu();

$f_name = gpc_get( 'txt_name' );
$t_obj = CPT_text_load( $f_name, ALL_PROJECTS );
if( null == $t_obj ) {
	error_parameters( $f_name );
	trigger_error( ERROR_LANG_STRING_NOT_FOUND, ERROR );
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
?>

<div class="form-container">
	<fieldset>
		<div class="floatright">
		<?php print_button( plugin_page( 'manage_text_delete' ), plugin_lang_get( 'delete_button' ) , array( 'txt_name' => $t_obj->name, 'CPT_manage_text_delete_token' => form_security_token( 'CPT_manage_text_delete' ) ), OFF); ?>
		</div>
	<legend>
		<span><?php echo plugin_lang_get( 'edit_predefined_text' ); ?></span>
	</legend>
	<form method="post" action="<?php echo plugin_page( 'manage_text_edit_update' ); ?>">
		<?php  echo form_security_field( 'CPT_manage_text_edit_update' ); ?>

		   <div class="field-container">
				   <label class="required"><span><?php echo plugin_lang_get( 'name' ); ?></span></label>
				   <span class="input"><input type="text" name="txt_name" size="25" maxlength="25" readonly value="<?php echo $t_obj->name; ?>" /></span>
				   <span class="label-style"></span>
		   </div>
		   <div class="field-container">
				   <label><span><?php echo plugin_lang_get( 'description' ); ?></span></label>
				   <span class="input"><input type="text" name="txt_descr" size="60" maxlength="128" value="<?php echo $t_obj->description; ?>"/></span>
				   <span class="label-style"></span>
		   </div>
		<?php
		$i = 0;
		foreach( $t_obj->contents as $t_lang => $t_str ) {
			$t_def = ($t_lang == $t_lang_default ) ? '[' . plugin_lang_get( 'default' ) . ']' : '';
			$t_fbk = ($t_lang == $t_lang_fallback ) ? '[' . plugin_lang_get( 'fallback' ) . ']' : '';
		?>
			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'contents' ); ?></span><br><?php echo "<span>$t_lang</span><br>" . '<span class="small">' . "$t_def $t_fbk</span>"; ?></span></label>
				<span class="textarea"><textarea name="txt_cont[<?php echo $i; ?>]" cols="128" rows="5"><?php echo string_textarea( $t_str ); ?></textarea></span>
				<input type="hidden" name="txt_lang[<?php echo $i; ?>]" value="<?php echo $t_lang; ?>" />
				<span class="label-style"></span>
			</div>
		<?php
		$i += 1;
		}
		?>
		</fieldset>
		<span>
			<?php if( !empty( $t_lang_unused ) ) {
			?>
			<select name="new_lang">
				<?php
				foreach( $t_lang_unused as $t_l ) {
					if( $t_l != 'auto' ) {
					   echo '<option value="' . $t_l . '">' . $t_l . '</option>';
					}
				}
				?>
			</select>
			<input type="submit" class="button-small" name="btn_addlang" value="<?php echo plugin_lang_get( 'add_lang_button' ); ?>" />
			<?php
			   }
			?>
		</span>
		<hr>
		<span class="submit-button"><input type="submit" class="button" name="btn_upd" value="<?php echo plugin_lang_get( 'save' ); ?>" /></span>

	</form>
</div>

<?php
form_security_purge( 'CPT_manage_text_edit' );
html_page_bottom();
?>