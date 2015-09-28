<?php
auth_reauthenticate( );
access_ensure_project_level( CPT_threshold( array( 'manage_allprojects_threshold', 'manage_project_threshold' ) ) );
form_security_validate( 'CPT_manage_config_update' );

$f_projects = gpc_get( 'prid', array() );
$f_flags_all = gpc_get( 'flag_all', array() );
$f_flags_custom = gpc_get( 'flag_custom', array() );
$f_delete_arr = gpc_get( 'cb_delete',array() );
$f_sel_txt = gpc_get( 'sel_txt',array() );

$f_allpr_active = gpc_get( 'enable_allpr',null );
$f_pr_active = gpc_get( 'enable_pr',null );

if( access_has_project_level( CPT_threshold( 'manage_allprojects_threshold' ), ALL_PROJECTS ) ) {
	$t_configitem = plugin_config_get( 'project_all', array(), null, ALL_USERS, ALL_PROJECTS );
	$t_configitem['txt_pred'] = $f_sel_txt[0];
	plugin_config_set( 'project_all', $t_configitem, ALL_USERS, ALL_PROJECTS );
	plugin_config_set( 'enable_allpr',( $f_allpr_active? TRUE : FALSE ), ALL_USERS, ALL_PROJECTS );
	plugin_config_set( 'enable_pr', ( $f_pr_active ? TRUE : FALSE ) , ALL_USERS, ALL_PROJECTS );
}

foreach( $f_projects as $t_project ) {
	if( access_has_project_level( CPT_threshold( 'manage_project_threshold' ), $t_project ) ) {
		$t_configitem = plugin_config_get( 'project', array(), null, ALL_USERS, $t_project);
		if( in_array( $t_project, $f_flags_all ) ){
			$t_configitem['show_all'] = ON;
		}
		else {
			$t_configitem['show_all'] = OFF;
		}
		if( in_array( $t_project, $f_flags_custom ) ){
			$t_configitem['show_custom'] = ON;
		}
		else {
			$t_configitem['show_custom'] = OFF;
		}
		$t_configitem['txt_pred'] = $f_sel_txt[$t_project];
		plugin_config_set ('project', $t_configitem, ALL_USERS, $t_project);
	}
}

if( gpc_get( 'btn_add', null ) ) {
	$f_add_projects = gpc_get( 'add_project_id', array() );
	foreach( $f_add_projects as $t_project_id ){
		if( access_has_project_level( CPT_threshold( 'manage_project_threshold'), $t_project ) ) {
			$t_configitem = plugin_config_get( 'project', null, null, ALL_USERS, $t_project_id );
			if( !$t_configitem ) {
				$t_configitem = array();
				$t_configitem['show_all'] = ON;
				$t_configitem['show_custom'] = ON;
				$t_configitem['txt_pred'] = null;
				$t_configitem['txt_anon'] = null;
				plugin_config_set( 'project', $t_configitem, ALL_USERS, $t_project_id );
			}
		}
	}
}

if( gpc_get( 'btn_del', null ) ) {
	helper_ensure_confirmed( plugin_lang_get( 'ensure_delete_projects' ), plugin_lang_get( 'delete_button' ) );
	foreach( $f_delete_arr as $t_id => $sel ) {
		if( access_has_project_level( CPT_threshold( 'manage_project_threshold' ), $t_id ) ) {
			plugin_config_delete( 'project', ALL_USERS, $t_id );
		}
	}
}

form_security_purge( 'CPT_manage_config_update' );
print_successful_redirect( plugin_page( 'manage_config', true ) );
?>