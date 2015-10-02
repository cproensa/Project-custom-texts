<?php
auth_reauthenticate( );
CPT_ensure_access_level( array( 'edit_all', 'edit_own' ) );

plugin_require_api( 'core/helper.php' );
form_security_validate( 'CPT_manage_text_delete' );

$f_name = gpc_get( 'txt_name' );

$t_obj = CPT_text_load( $f_name );
if( null == $t_obj ) {
	error_parameters( $f_name );
	trigger_error( ERROR_LANG_STRING_NOT_FOUND, ERROR );
}

if( CPT_access_has_level( 'edit_all' ) || ( CPT_access_has_level( 'edit_own' ) && $t_obj->user == auth_get_current_user_id() ) ) {
	helper_ensure_confirmed( plugin_lang_get( 'ensure_delete_text' ), plugin_lang_get( 'delete_button' ) );
	CPT_text_delete( $f_name );
}
else {
	access_denied();
}

form_security_purge( 'CPT_manage_text_delete' );
print_successful_redirect( plugin_page( 'manage_text', true ) );
