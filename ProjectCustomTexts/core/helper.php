<?php

/**
 * Class to encapsulate a TExt object
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
		$str .= '<option'.$selected.'>' . $t_name . ' [' . $t_user . ']</option>';
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
 * Prints option list for accessible projects, still unconfigured
 */
function CPT_get_pending_project_list() {
	$s = null;
	$t_projects = user_get_all_accessible_projects( auth_get_current_user_id(), ALL_PROJECTS );
	foreach( $t_projects as $t_project_id ){
		if( ( user_get_access_level( auth_get_current_user_id(), $t_project_id ) >= CPT_threshold( 'manage_project_threshold' ) )
			&& ( null === @plugin_config_get( 'project', null, null, ALL_USERS, $t_project_id ) ) )
		{
			$t_project_name = project_get_name( $t_project_id );
			$s.= '<option value="' . $t_project_id . '">' . $t_project_name . '</option>';
		}
	}
	return $s;
}

/**
 * Print navigation menu
 * @param string $p_page Page where is called to not hyperlink
 */
function CPT_print_menu( $p_page = '' ) {
	$t_pages = array(
			'manage_config' => CPT_threshold( array( 'manage_allprojects_threshold', 'manage_project_threshold' ) ),
			'manage_text' => CPT_threshold( array( 'edit_all_threshold', 'edit_own_threshold' ) ),
			'manage_preferences' => config_get( 'manage_plugin_threshold' )
	);

	$t_min = min( plugin_config_get( 'access_level', config_get( 'manage_plugin_threshold' ), FALSE, ALL_USERS, ALL_PROJECTS ) );
	if( access_has_project_level( $t_min ) ) {
			echo '<div align="center"><p>';
			foreach( $t_pages as $t_page_name => $t_access_level ) {
				if( access_has_project_level( $t_access_level ) ) {
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
 * @return type
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
				'manage_allprojects_threshold' => $t_manage_plugin_threshold,
				'manage_project_threshold' => $t_manage_plugin_threshold,
				'edit_all_threshold' => $t_manage_plugin_threshold,
				'edit_own_threshold' => $t_manage_plugin_threshold
				)
    );
}

/**
 * return access level configured for $t_perm permission definition
 * see configuration => 'access_level' => keys
 * @param string|array $t_perm
 * @return type
 */
function CPT_threshold( $t_perm ) {
	$t_default = CPT_get_defaults()['access_level'];
	$t_access = plugin_config_get( 'access_level', $t_default , FALSE, ALL_USERS, ALL_PROJECTS );
	if( is_array( $t_perm ) ) {
		$t_min = config_get( 'manage_plugin_threshold' );
		foreach( $t_perm as $t_p ) {
			$tmp = (int)( $t_access[$t_p] );
			$t_min = min( $t_min, (int)( $t_access[$t_p] ) );
		}
		return $t_min;
	}
	else {
		return $t_access[$t_perm];
	}
}

function CPT_print_enum_string_option_list( $p_enum_name, $p_val ){
    ob_start();
    print_enum_string_option_list( $p_enum_name, $p_val );
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

function CPT_print_button( $p_action_page, $p_label, $p_args_to_post, $p_security_token ){
    ob_start();
	print_button( $p_action_page, $p_label, $p_args_to_post, $p_security_token );
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

?>