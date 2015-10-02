<?php

/**
 * Class to encapsulate a Text object
 */
class CPT_Text {
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $description;
	/**
	 * @var integer
	 */
	public $user;
	/**
	 * @var array Array of (language => string)
	 */
	public $contents;

	/**
	* Constrcutor
	* @param array $p_data Text data
	*/
	public function __construct( array $p_data = null ) {
		$this->name = $p_data['name'];
		$this->description = $p_data['description'];
		$this->user = $p_data['user'];
		$this->contents = $p_data['contents'];
	}

	/**
	* Returns an array of the languages which have content defined
	* @return array
	*/
	public function get_langs() {
		return array_keys( $this->contents );
	}

	/**
	 * Returns text content for specified language. If none use current lang
	 * If no text is found for language, return text for fallback language
	 * @global type $g_active_language
	 * @param string $p_lang
	 * @return type
	 */
	public function get_localized_txt( $p_lang= null ) {
		global $g_active_language;
		if( !$p_lang ) {
			$p_lang = $g_active_language;
		}
		if( isset( $this->contents[$p_lang]) ) {
			return $this->contents[$p_lang];
		}
		else return $this->contents[config_get( 'fallback_language' )];
	}
}

/**
 * Checks if a Text of given name already exists
 * @param type $p_name	name of text
 * @param type $p_project_id	*not implemented*
 * @return type
 */
function CPT_text_name_exists( $p_name, $p_project_id = ALL_PROJECTS ) {
	$t_all = plugin_config_get( 'CPT_texts', array(), ALL_USERS, $p_project_id );
	return isset( $t_all[$p_name] );
}

/**
 * Returns an array of all Text objects stored in db
 * @return array Array of Text objects (as array)
 */
function CPT_get_all_texts( ) {
	return plugin_config_get( 'CPT_texts', array(), ALL_USERS, ALL_PROJECTS );
}

/**
 * Creates html option list for all text stored in db
 * @param integer	$p_project_id	project id refered, for indexing names
 * @param bool		$include_none	TRUE if a "none" selection muyst be included
 * @param string	$defaulted		match to get defaulted option
 * @return string
 */
function CPT_get_alltext_option_list( $p_project_id, $include_none = TRUE, $defaulted = null) {
	$t_all = CPT_get_all_texts( );
	ksort( $t_all );
	$str ='<select name="sel_txt[' . $p_project_id . ']">';
	if( $include_none ) {
		$str .= '<option value="">[' . lang_get( 'none' ) . ']</option>';
	}
	foreach( $t_all as $i_name => $i_data ) {
		$t_name = $i_data['name'];
		$t_user =  user_get_name($i_data['user']);
		$selected = ( $t_name == $defaulted ) ? ' selected="selected"' : '';
		$str .= '<option value="' . $t_name . '" '.$selected.'>' . $t_name . ' [' . $t_user . ']</option>';
	}
	$str .= '</select>';
	return $str;
}

/**
 * Retrieves a text object by name from db
 * @param string $p_name	Name of text
 * @return \CPT_Text
 */
function CPT_text_load( $p_name ) {
	$t_all = plugin_config_get( 'CPT_texts', array(), ALL_USERS, ALL_PROJECTS );
	$t_txt = isset($t_all[$p_name]) ? $t_all[$p_name] : null;
	if( null == $t_txt ) {
		return null;
	}
	else {
		return new CPT_Text( $t_txt );
	}
}

/**
 * Deletes a text from db
 * @param string $p_name	Name of text
 */
function CPT_text_delete( $p_name ) {
	$t_all = plugin_config_get( 'CPT_texts', array(), ALL_USERS, ALL_PROJECTS );
	unset( $t_all[$p_name] );
	plugin_config_set( 'CPT_texts', $t_all, ALL_USERS, ALL_PROJECTS );
}

/**
 * Stores a Text in db
 * @param CPT_Text $t	Text object
 */
function CPT_text_save( CPT_Text $t ) {
	$t_item['name'] = $t->name;
	$t_item['description'] = $t->description;
	$t_item['user'] = auth_get_current_user_id();
	$t_item['contents'] = $t->contents;

	$t_all = CPT_get_all_texts( );
	$t_all[$t->name] = $t_item;
	ksort( $t_all );
	plugin_config_set( 'CPT_texts', $t_all, ALL_USERS, ALL_PROJECTS );
}

/**
 * Recursive helper for project option list
 */
function CPT_get_subproject_option_list( $p_parent_id, $p_project_id = null, $p_parents = Array() ) {
	if( null === $p_parents ) $p_parents = Array();
	array_push( $p_parents, $p_parent_id );
	$t_project_ids = current_user_get_accessible_subprojects( $p_parent_id );
	project_cache_array_rows( $t_project_ids );
	$s = '';
	foreach( $t_project_ids as $t_id ){
		if( CPT_access_has_level('manage_project', $t_id)
			&& ( null === @plugin_config_get( 'project', null, null, ALL_USERS, $t_id ) ) )
		{
			$s .= "<option value=\"";
			$s .= $t_id . '"';
			$s .= '>' . str_repeat( '&#160;', count( $p_parents ) ) . str_repeat( '&raquo;', count( $p_parents ) ) . ' ' . string_attribute( project_get_field( $t_id, 'name' ) ) . '</option>' . "\n";
		}
		$s .= CPT_get_subproject_option_list( $t_id, $p_project_id, $p_parents );
	}
	return $s;
}

/**
 * Prints option list for accessible projects, those projects still unconfigured
 */
function CPT_get_pending_project_list() {
	$t_project_ids = current_user_get_accessible_projects();
	project_cache_array_rows( $t_project_ids );
	$s = '';
	foreach( $t_project_ids as $t_id ){
		if( CPT_access_has_level('manage_project', $t_id)
			&& ( null === @plugin_config_get( 'project', null, null, ALL_USERS, $t_id ) ) )
		{
			$s.= '<option value="' . $t_id . '"';
			$s.= '>' . string_attribute( project_get_field( $t_id, 'name' ) ) . '</option>' . "\n";
		}
		$s.= CPT_get_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, Array() );
	}
	return $s;
}

/**
 * Gets a plugin page that is accesible by the user current access level
 * to avoid linking to a restricted page when user has limited access
 * @return string Name of the page selected
 */
function CPT_get_first_accesible_page() {
	$t_pages = CPT_get_menu_pages();
	foreach( $t_pages as $t_page_name => $t_access_has_level) {
		if( $t_access_has_level ){
			return $t_page_name;
		}
	}
	return null;
}

/**
 * Defines pages listed as menu entries, with required access checks
 * @return array Array of page=>(bool)has_acess_level
 */
function CPT_get_menu_pages() {
	return array(
			'manage_config' => CPT_access_has_level( array( 'manage_allprojects', 'manage_project' ) ),
			'manage_text' => CPT_access_has_level( array( 'edit_all', 'edit_own' ) ),
			'manage_preferences' => CPT_access_has_level( 'manage_configuration' )
	);
}

/**
 * Print navigation menu
 * @param string $p_page Page where is called to not hyperlink
 */
function CPT_print_menu( $p_page = '' ) {
	$t_pages = CPT_get_menu_pages();

	$t_any = $t_pages['manage_config'] || $t_pages['manage_text'] || $t_pages['manage_preferences'];
	if( $t_any ) {
			echo '<div align="center"><p>';
			foreach( $t_pages as $t_page_name => $t_access_has_level ) {
				if( $t_access_has_level ) {
					$title = plugin_lang_get( $t_page_name.'_title' );
					$t_page = ( ( $p_page !== $t_page_name ) ? plugin_page( $t_page_name ) : NULL );
					print_bracket_link( $t_page, $title );
				}
			}
			echo '</p></div>';
	}
}

/**
 * get defaults for plugin initialization
 * @return array Configuration array for default values
 */
function CPT_get_defaults () {
    $t_manage_plugin_threshold = config_get( 'manage_plugin_threshold', null, ALL_USERS, ALL_PROJECTS);
    return array(
	    'enable_allpr' => TRUE,
	    'project_all' => array(),
	    'enable_pr' => TRUE,
	    'project' => null,
	    'CPT_texts' => array(),
	    'access_level' => array (
				'manage_allprojects' => $t_manage_plugin_threshold,
				'manage_project' => $t_manage_plugin_threshold,
				'edit_all' => $t_manage_plugin_threshold,
				'edit_own' => $t_manage_plugin_threshold,
				'manage_configuration' => $t_manage_plugin_threshold
				)
    );
}

/**
 * Check access level against one or any of several authorization string keys
 * If access level is not satisfied at least by une key, deny access and show error
 * @param string|array $p_perm
 * @param integer $p_project_id Project id if (if applicable)
 * @param integer $p_user_id User id if (if applicable)
 */
function CPT_ensure_access_level( $p_perm, $p_project_id =  null, $p_user_id = null ) {
	if( !CPT_access_has_level( $p_perm, $p_project_id, $p_user_id ) ) {
		access_denied();
	}
}

/**
 * Checks access level against one or more access string keys for this plugin
 * string keys are those defined in configuration array
 * @param string|array $p_perm Single or array of authorization string keys
 * @param integer $p_project_id Project id if (if applicable)
 * @param integer $p_user_id User id if (if applicable)
 * @return type
 */
function CPT_access_has_level( $p_perm, $p_project_id = null, $p_user_id = null) {
	if( !is_array( $p_perm ) ){
		$t_perms= array( $p_perm );
	}
	else {
		$t_perms = $p_perm;
	}
	$t_access = FALSE;
	foreach( $t_perms as $t_p ){
		$t_access = $t_access || CPT_access_level( $t_p, $p_project_id, $p_user_id );
	}
	return $t_access;
}

/**
 * Checks access level against one access string key for this plugin
 * string keys are those defined in configuration array
 * @param string $p_perm String name for access key to check
 * @param integer $p_project_id Project id if (if applicable)
 * @param integer $p_user_id User id if (if applicable)
 * @return boolean
 */
function CPT_access_level( $p_perm, $p_project_id = null, $p_user_id = null) {
	$t_default = CPT_get_defaults()['access_level'];
	$t_access = plugin_config_get( 'access_level', $t_default , FALSE, ALL_USERS, ALL_PROJECTS );
	switch ($p_perm) {
		case 'manage_allprojects':
		case 'edit_all':
		case 'manage_configuration':
			return access_has_global_level( $t_access[$p_perm], $p_user_id );
		case 'manage_project':
		case 'edit_own':
			return access_has_project_level($t_access[$p_perm], $p_project_id, $p_user_id );
		default:
			return FALSE;
	}
}

/**
 * Rewrite core function to get string instead of printing directly
 * @param type $p_enum_name
 * @param type $p_val
 * @return type
 */
function CPT_get_enum_string_option_list( $p_enum_name, $p_val ){
    ob_start();
    print_enum_string_option_list( $p_enum_name, $p_val );
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

/**
 * Rewrite core function to get string instead of printing directly
 * @param type $p_action_page
 * @param type $p_label
 * @param type $p_args_to_post
 * @param type $p_security_token
 * @return type
 */
function CPT_get_button( $p_action_page, $p_label, $p_args_to_post, $p_security_token ){
    ob_start();
	print_button( $p_action_page, $p_label, $p_args_to_post, $p_security_token );
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}
