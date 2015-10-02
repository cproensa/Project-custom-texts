<?php
auth_reauthenticate( );
CPT_ensure_access_level( array( 'edit_all', 'edit_own' ) );

//plugin_require_api( 'core/helper.php' );
form_security_validate( 'CPT_manage_text_new' );

$f_name = gpc_get( 'newtxt_name' );

if( null === $f_name || $f_name === '' ){
	error_parameters( plugin_lang_get( 'name') );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}
if( CPT_text_name_exists( $f_name ) ) {
	error_parameters( plugin_lang_get( 'name') );
	trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
}

$t_textobj = new CPT_Text( null );
$t_textobj->name = string_normalize( $f_name );
$t_textobj->description = '';
$t_textobj->user = auth_get_current_user_id();
$t_textobj->contents = array();

CPT_text_save( $t_textobj, ALL_PROJECTS );

form_security_purge( 'CPT_manage_text_new' );
print_successful_redirect( plugin_page( 'manage_text', true ) );
