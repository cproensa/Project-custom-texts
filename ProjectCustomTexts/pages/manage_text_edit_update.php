<?php
//plugin_require_api( 'core/helper.php' );
auth_reauthenticate( );
CPT_ensure_access_level( array( 'edit_all', 'edit_own' ) );
form_security_validate( 'CPT_manage_text_edit_update' );

$t_action = '';
if( null != gpc_get( 'btn_upd', null ) ){
	$t_action = 'UPDATE';
}
if( null != gpc_get( 'btn_addlang', null ) ){
	$t_action = 'ADDLANG';
}
/*
if( null != gpc_get( 'btn_delete', null ) ){
	$t_action = 'DELETE';
}
*/

$f_name = gpc_get( 'txt_name' );
$f_descr = gpc_get( 'txt_descr' );
$f_lang_array = gpc_get( 'txt_lang' );
$f_cont_array = gpc_get( 'txt_cont' );
$f_newlang = gpc_get( 'new_lang', null );

/*
if( $t_action === 'DELETE' ) {
	helper_ensure_confirmed( plugin_lang_get( 'ensure_delete_text' ), plugin_lang_get( 'delete_button' ) );
	CPT_text_delete( $f_name );
	print_successful_redirect( plugin_page( 'manage_text', true ) );
}
 */

if( null === $f_name or $f_name === '' ){
	error_parameters( 'NAME' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$t_data['name'] = $f_name;
$t_data['description'] = $f_descr;
$t_cont = array_combine( $f_lang_array, $f_cont_array );
if( $t_action === 'UPDATE' ) {
	$t_cont = array_diff( $t_cont, array('') );
}
if( $t_action === 'ADDLANG' ) {
	$t_cont[$f_newlang] = '';
}
ksort( $t_cont );
$t_data['contents'] = $t_cont;

$t_obj = new CPT_Text( $t_data );
CPT_text_save( $t_obj, ALL_PROJECTS );

form_security_purge( 'CPT_manage_text_edit_update' );

if( $t_action === 'UPDATE' ) {
	print_successful_redirect( plugin_page( 'manage_text', true ) );
}
if( $t_action === 'ADDLANG' ) {
	print_successful_redirect( plugin_page( 'manage_text_edit', true ) . '&txt_name='. string_url( $f_name ) );
}
