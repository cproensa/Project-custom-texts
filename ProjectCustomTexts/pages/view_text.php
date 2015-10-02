<?php
html_page_top( plugin_lang_get( 'view_text_title' ) );
print_manage_menu();
CPT_print_menu();

$f_name = gpc_get( 'txt_name' );
$t_obj = CPT_text_load( $f_name );

if( null == $t_obj ) {
	error_parameters( $f_name );
	trigger_error( ERROR_LANG_STRING_NOT_FOUND, ERROR );
}

$t_lang_default = config_get( 'default_language' );
$t_lang_fallback = config_get( 'fallback_language' );

/*
 * prepare screen elements
 */

$scr['legend'] = plugin_lang_get( 'show_custom' );
$scr['name_label'] = plugin_lang_get( 'name' );
$scr['name'] = $t_obj->name;
$scr['user_label'] = plugin_lang_get( 'created_by' );
$scr['user'] = user_get_name( $t_obj->user );
$scr['descr_label'] = plugin_lang_get( 'description' );
$scr['descr'] = $t_obj->description;

$scr['b_edit'] = '';
if( CPT_access_has_level( 'edit_all' ) || ( CPT_access_has_level( 'edit_own' ) && $t_obj->user == auth_get_current_user_id() ) ) { 
	$scr['b_edit'] = CPT_print_button( plugin_page( 'manage_text_edit' ), plugin_lang_get( 'edit' ) , array( 'txt_name' => $t_obj->name), OFF );
}

$scr['contents'] = array();
foreach( $t_obj->contents as $t_lang => $t_str ) {
	$txt = array();
	$t_def = ($t_lang == $t_lang_default ) ? '[' . plugin_lang_get( 'default' ) . ']' : '';
	$t_fbk = ($t_lang == $t_lang_fallback ) ? '[' . plugin_lang_get( 'fallback' ) . ']' : '';
	$txt['label'] = plugin_lang_get( 'contents' );
	$txt['sub1'] = $t_lang;
	$txt['sub2'] = "$t_def $t_fbk";
	$txt['txt'] = $t_str;
	$scr['contents'][] = $txt;
}
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
				<?php echo $scr['legend'] ?>
			</td>
			<td width="20%" class="right">
				<?php echo $scr['b_edit'] ?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category" width="30%">
				<?php echo $scr['name_label'] ?>
			</td>
			<td width="70%" colspan="2">
				<?php echo $scr['name'] ?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category" width="30%">
				<?php echo $scr['user_label'] ?>
			</td>
			<td width="70%" colspan="2">
				<?php echo $scr['user'] ?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category" width="30%">
				<?php echo $scr['descr_label'] ?>
			</td>
			<td width="70%" colspan="2">
				<?php echo $scr['descr'] ?>
			</td>
		</tr>
		<?php foreach( $scr['contents'] as $t_content ) { ?>
			<tr class="spacer"></tr>
			<tr <?php echo helper_alternate_class( 1 ) ?>>
				<td class="bold" colspan="3">
					<span><?php echo $t_content['label'] ?>: </span>
					<span><?php echo $t_content['sub1'] ?> </span>
					<span class="small"><?php echo $t_content['sub2'] ?></span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<?php echo string_nl2br( $t_content['txt'] ) ?>
				</td>
			</tr>
		<?php } ?>
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
			<?php echo $scr['b_edit'] ?>
		</div>
		<legend>
			<span><?php echo $scr['legend'] ?></span>
		</legend>
		<div class="field-container">
				<label><span><?php echo $scr['name_label'] ?></span></label>
				<span class="input"><?php echo $scr['name'] ?></span>
				<span class="label-style"></span>
		</div>
		<div class="field-container">
				<label><span><?php echo $scr['user_label'] ?></span></label>
				<span class="input"><?php echo $scr['user'] ?></span>
				<span class="label-style"></span>
		</div>
		<div class="field-container">
				<label><span><?php echo $scr['descr_label'] ?></span></label>
				<span class="input"><?php echo $scr['descr'] ?></span>
				<span class="label-style"></span>
		</div>
		<?php foreach( $scr['contents'] as $t_content ) { ?>
			<div class="spacer"></div>
			<div class="field-container">
				<label>
					<span><?php echo $t_content['label'] ?>: </span>
					<span><?php echo $t_content['sub1'] ?> </span>
					<span class="small"><?php echo $t_content['sub2'] ?></span>
				</label>
			</div>
			<div class="spacer"></div>
			<div>
				<span>
					<?php echo string_nl2br( $t_content['txt'] ) ?>
				</span>
			</div>
		<?php } ?>
	</fieldset>
</div>

<?php
} #end html 1.3

html_page_bottom();
