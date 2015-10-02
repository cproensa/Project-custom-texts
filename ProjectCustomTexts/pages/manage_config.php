<?php
//plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
CPT_ensure_access_level( array( 'manage_allprojects', 'manage_project' ) );

html_page_top( plugin_lang_get( 'manage_config_title' ) );
print_manage_menu();

$t_this_page = 'manage_config';
CPT_print_menu( $t_this_page );



/*
 * prepare screen elements
 */
$s['formaction'] = plugin_page( 'manage_config_update' );
$s['token'] = form_security_field( 'CPT_manage_config_update' );
if( CPT_access_has_level( 'manage_allprojects' ) ) {
	$t_active = plugin_config_get( 'enable_allpr', FALSE, null, ALL_USERS, ALL_PROJECTS );
	$chk = $t_active ? 'checked="checked"' : '';
	$s[1]['check'] = '<input type="checkbox" name="enable_allpr" ' . $chk . ' />';
	$s[1]['checklabel'] = plugin_lang_get( 'enabled' );
	$s[1]['legend'] = plugin_lang_get( 'all_projects_config' );
	$s[1]['legendsub'] =  lang_get( 'config_all_projects' );
	$s[1]['label'] = plugin_lang_get( 'predefined_text' );
	$t_configitem = plugin_config_get( 'project_all',array(), null, ALL_USERS, ALL_PROJECTS );
	$s[1]['input'] = CPT_get_alltext_option_list( ALL_PROJECTS, TRUE, $t_configitem['txt_pred'] );
	$s[1]['btn_upd'] = '<input type="submit" name="btn_upd" class="button" value="' . plugin_lang_get( 'update_button' ) . '" />';

}

$t_active = plugin_config_get( 'enable_pr', FALSE, null, ALL_USERS, ALL_PROJECTS);
$chk = $t_active ? 'checked="checked"' : '';
$s[2]['check'] = '<input type="checkbox" name="enable_pr" ' . $chk . ' />';
$s[2]['checklabel']	= plugin_lang_get('enabled');
$s[2]['legend'] = plugin_lang_get( 'projects_config' );
$s[2]['legendsub'] = lang_get( 'colour_project' );
$s[2]['header'][1] = plugin_lang_get( 'project_name' );
$s[2]['header'][2] = plugin_lang_get( 'show_allpr' );
$s[2]['header'][3] = plugin_lang_get( 'show_custom' );
$s[2]['header'][4] = plugin_lang_get( 'predefined_text' );
$s[2]['header'][5] = plugin_lang_get( 'delete_selection' );

#get projects
$t_manage_all = CPT_access_has_level( 'manage_allprojects' );
$t_projects = user_get_accessible_projects( auth_get_current_user_id(), true );
$t_full_projects = array();
foreach( $t_projects as $t_project_id ) {
	$t_full_projects[] = project_get_row( $t_project_id );
}
$t_projects = $t_full_projects;
$t_stack = array( $t_projects );
$s[2]['row'] = array();
while( 0 < count( $t_stack ) ) {
	$t_projects = array_shift( $t_stack );
	if( 0 == count( $t_projects ) ) {
		continue;
	}
	$t_project = array_shift( $t_projects );
	$t_project_id = $t_project['id'];
	$t_level = count( $t_stack );

	# only print row if user has project management privileges
	# and exists the configuration for that project
	$t_configitem = @plugin_config_get( 'project', null, null, ALL_USERS, $t_project_id );
	if( $t_configitem && !empty( $t_configitem ) && ( $t_manage_all || CPT_access_has_level('manage_project', $t_project_id ) ) ) {
		$row[1] = str_repeat( '&raquo; ', $t_level ) . string_display( $t_project['name'] );
		$row['hidden'] = '<input type="hidden" name="prid[]" value="' . $t_project_id . '" />';
		$t_flag = $t_configitem['show_all'];
		$t_set = ( ON == $t_flag ) ? 'checked="checked"' : '';
		$row[2] = '<input type="checkbox" name="flag_all[]" value="' . $t_project_id . '" ' . $t_set . ' />';
		$t_flag = $t_configitem['show_custom'];
		$t_set = ( ON == $t_flag ) ? 'checked="checked"' : '';
		$row[3] = '<input type="checkbox" name="flag_custom[]" value="' . $t_project_id . '" ' . $t_set . ' />';
		$row[4] = CPT_get_alltext_option_list( $t_project_id, TRUE, $t_configitem['txt_pred'] );
		$row[5] = '<input type="checkbox" name="cb_delete[' . $t_project_id . ']" />';
		$s[2]['row'][] = $row;
	}

	$t_subprojects = project_hierarchy_get_subprojects( $t_project_id, true );
	if( 0 < count( $t_projects ) || 0 < count( $t_subprojects ) ) {
		array_unshift( $t_stack, $t_projects );
	}
	if( 0 < count( $t_subprojects ) ) {
		$t_full_projects = array();
		foreach ( $t_subprojects as $t_project_id ) {
			$t_full_projects[] = project_get_row( $t_project_id );
		}
		$t_subprojects= $t_full_projects;
		array_unshift( $t_stack, $t_subprojects );
	}
}
$s[2]['btn_del'] = '<input type="submit" class="button-small" name ="btn_del" value="' . plugin_lang_get( 'delete_button' ) . '" />';
$s[2]['btn_upd'] = '<input type="submit" name="btn_upd" class="button" value="' . plugin_lang_get( 'update_button' ) . '" />';

$t_pending_pr_list = CPT_get_pending_project_list();
if( $t_pending_pr_list ){
	$s[3]['legend'] = plugin_lang_get( 'add_projects' );
	$s[3]['label'] =  plugin_lang_get( 'projects_without_config' );
	$s[3]['select'] = '<select id="add-user-project-id" name="add_project_id[]" multiple="multiple" size="5">'
					. $t_pending_pr_list . '</select>';
	$s[3]['btn_add'] = '<input type="submit" class="button" name ="btn_add" value="' . plugin_lang_get( 'add_projects_button' ) . '" />';
}
?>

<?php
#
# HTML for mantis 1.2
#
if( '1.2' === GET_VER ) {
?>

<div align="center">
<form method="post" action="<?php echo $s['formaction']; ?>">
<?php echo $s['token']; ?>

	<!-- Form area #1 -->

	<?php if( $s[1] ) { ?>
	<table class="width75" cellspacing="1">
		<tr>
			<td width="80%" class="form-title" colspan="2">
				<div><?php echo $s[1]['legend']; ?></div>
				<div class="small-normal"><?php echo $s[1]['legendsub']; ?></div>
			</td>
			<td width="20%" class="right">
				<span><?php echo $s[1]['check']; ?></span>
				<span><?php echo $s[1]['checklabel']; ?></span>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category right" width="30%">
				<?php echo $s[1]['label']; ?>
			</td>
			<td width="70%" colspan="2">
				<?php echo $s[1]['input']; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" class="center">
				<?php echo $s[1]['btn_upd']; ?>
			</td>
		</tr>
	</table>
	<br /><br />
	<?php } ?>

	<!-- Form area #2 -->

	<table class="width75" cellspacing="1">
		<tr>
			<td width="80%" class="form-title" colspan="4">
				<div><?php echo $s[2]['legend']; ?></div>
				<div class="small-normal"><?php echo $s[2]['legendsub']; ?></div>
			</td>
			<td width="20%" class="right">
				<span><?php echo $s[2]['check']; ?></span>
				<span><?php echo $s[2]['checklabel']; ?></span>
			</td>
		</tr>
		<tr class="row-category">
			<td width="20%"><?php echo $s[2]['header'][1]; ?></td>
			<td width="20%"><?php echo $s[2]['header'][2]; ?></td>
			<td width="20%"><?php echo $s[2]['header'][3]; ?></td>
			<td width="20%"><?php echo $s[2]['header'][4]; ?></td>
			<td width="20%"><?php echo $s[2]['header'][5]; ?></td>
		</tr>
		<?php foreach( $s[2]['row'] as $row ) { ?>
			<tr <?php echo helper_alternate_class() ?>>
				<?php echo $row['hidden']; ?>
				<td width="20%"><?php echo $row[1]; ?></td>
				<td width="20%"><?php echo $row[2]; ?></td>
				<td width="20%"><?php echo $row[3]; ?></td>
				<td width="20%"><?php echo $row[4]; ?></td>
				<td width="20%"><?php echo $row[5]; ?></td>
			</tr>
		<?php } ?>
		<tr>
				<td></td><td></td><td></td><td></td>
				<td><?php echo $s[2]['btn_del']; ?></td>
		</tr>
		<tr>
			<td colspan="5" class="center">
				<?php echo $s[2]['btn_upd']; ?>
			</td>
		</tr>
	</table>

	<!-- Form area #3 -->

	<?php if( $s[3] ) { ?>
	<br />
	<table class="width75" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo $s[3]['legend']; ?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category" width="30%">
				<?php echo $s[3]['label']; ?>
			</td>
			<td width="70%">
				<?php echo $s[3]['select']; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<?php echo $s[3]['btn_add']; ?>
			</td>
		</tr>
	</table>
	<?php } ?>
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
<form method="post" action="<?php echo $s['formaction']; ?>">
<?php echo $s['token']; ?>

<!-- Form area #1 -->

<?php if( $s[1] ) {  ?>
	<fieldset>
		<div class="floatright">
			<span><?php echo $s[1]['check']; ?></span>
			<span><?php echo $s[1]['checklabel']; ?></span>
		</div>
		<legend>
			<div>
				<?php echo $s[1]['legend']; ?>
			</div>
			<div class="small-normal">
				<?php echo $s[1]['legendsub']; ?>
			</div>
		</legend>
		<div class="field-container">
			<label class="right">
				<?php echo $s[1]['label']; ?>
			</label>
			<span class="input">
				<?php echo $s[1]['input']; ?>
			</span>
			<span class="label-style"></span>
		</div>
	</fieldset>
	<fieldset>
		<span class="submit-button">
			<?php echo $s[1]['btn_upd']; ?>
		</span>
	</fieldset>

	<hr>
<?php } ?>

<!-- Form area #2 -->

	<fieldset>
		<div class="floatright">
			<span><?php echo $s[2]['check']; ?></span>
			<span><?php echo $s[2]['checklabel']; ?></span>
		</div>
		<legend>
			<div><?php echo $s[2]['legend']; ?></div>
			<div class="small-normal"><?php echo $s[2]['legendsub']; ?></div>
		</legend>
	</fieldset>
	<table>
		<thead>
			<tr class="row-category">
				<td><?php echo $s[2]['header'][1]; ?></td>
				<td><?php echo $s[2]['header'][2]; ?></td>
				<td><?php echo $s[2]['header'][3]; ?></td>
				<td><?php echo $s[2]['header'][4]; ?></td>
				<td><?php echo $s[2]['header'][5]; ?></td>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $s[2]['row'] as $row ) { ?>
				<tr>
					<?php echo $row['hidden']; ?>
					<td><?php echo $row[1]; ?></td>
					<td class="center"><?php echo $row[2]; ?></td>
					<td class="center"><?php echo $row[3]; ?></td>
					<td class="center"><?php echo $row[4]; ?></td>
					<td><?php echo $row[5]; ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td></td><td></td><td></td><td></td>
				<td><?php echo $s[2]['btn_del']; ?></td>
			</tr>
		</tbody>
	</table>
	<fieldset>
		<span class="submit-button">
			<?php echo $s[2]['btn_upd']; ?>
		</span>
	</fieldset>

<!-- Form area #3 -->

	<?php if( $s[3] ) { ?>
	<hr>
	<fieldset>
	<legend>
		<div>
			<?php echo $s[3]['legend']; ?>
		</div>
	</legend>
	<div class="field-container">
		<label>
			<span><?php echo $s[3]['label']; ?></span>
		</label>
		<span class="select">
			<?php echo $s[3]['select']; ?>
		</span>
		<span class="label-style"></span>
	</div>
	</fieldset>
	<fieldset>
		<span class="submit-button">
			<?php echo $s[3]['btn_add']; ?>
		</span>
	</fieldset>
	<?php } ?>
</form>
</div>

<?php
} #end html 1.3

html_page_bottom();
