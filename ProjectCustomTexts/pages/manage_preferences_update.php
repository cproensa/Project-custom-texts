<?php
//plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
CPT_ensure_access_level('manage_configuration');
form_security_validate( 'CPT_manage_preferences_update' );

if( null != gpc_get( 'btn_upd', null ) ){
	$f_access_allpr = gpc_get( 'access_allpr' );
	$f_access_pr = gpc_get( 'access_pr' );
	$f_access_alltxt =  gpc_get( 'access_alltxt' );
	$f_access_owntxt = gpc_get ( 'access_owntxt' );

	$t_default = CPT_get_defaults();
	$t_access = $t_default['access_level'];
	$t_access['manage_allprojects'] = $f_access_allpr;
	$t_access['manage_project'] = $f_access_pr;
	$t_access['edit_all'] = $f_access_alltxt;
	$t_access['edit_own'] = $f_access_owntxt;

	plugin_config_set( 'access_level', $t_access, ALL_USERS, ALL_PROJECTS );
}

if( null != gpc_get( 'btn_reset', null ) ){
	helper_ensure_confirmed( lang_get('config_delete_sure') . '<br />' . plugin_lang_get( 'reset_default_permissions_txt' ), plugin_lang_get( 'reset_default_permissions' ) );
	$t_def = CPT_get_defaults();
	plugin_config_set( 'access_level', $t_def['access_level'], ALL_USERS, ALL_PROJECTS );
}

if( null != gpc_get( 'btn_delete', null ) ){
	helper_ensure_confirmed( lang_get('config_delete_sure') . '<br />' . plugin_lang_get( 'delete_all_configuration_txt' ), plugin_lang_get( 'delete_all_configuration' ) );
	$t_def = CPT_get_defaults();
	foreach( array_keys( $t_def ) as $t_key ) {
		plugin_config_delete( $t_key, ALL_USERS, ALL_PROJECTS );
	}
	$t_all = project_get_all_rows();
	foreach( $t_all as $t_row ) {
		plugin_config_delete( 'project', ALL_USERS, $t_row['id'] );
	}
}

form_security_purge( 'CPT_manage_preferences_update' );
print_successful_redirect( plugin_page( 'manage_preferences', true ) );
?>
