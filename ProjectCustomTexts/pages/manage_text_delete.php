<?php
auth_reauthenticate( );
access_ensure_global_level( CPT_threshold( array( 'edit_all_threshold', 'edit_own_threshold' ) ) );

plugin_require_api( 'core/helper.php' );
form_security_validate( 'CPT_manage_text_delete' );

$f_name = gpc_get( 'txt_name' );

helper_ensure_confirmed( plugin_lang_get( 'ensure_delete_text' ), plugin_lang_get( 'delete_button' ) );
CPT_text_delete( $f_name );

form_security_purge( 'CPT_manage_text_delete' );
print_successful_redirect( plugin_page( 'manage_text', true ) );

