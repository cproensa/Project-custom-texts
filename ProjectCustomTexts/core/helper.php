<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CPT_Text {    
	public $name;
	public $description;
	public $user;
	public $contents;

	public function CPT_Text( $p_data ) {
		$this->name = $p_data['name'];
		$this->description = $p_data['description'];
		$this->user = $p_data['user'];
		$this->contents = $p_data['contents'];
	}

	public function get_langs() {
		return array_keys( $this->contents );
	}

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

function CPT_text_name_exists( $p_name, $p_project_id = ALL_PROJECTS ) {
	$t_all = plugin_config_get( 'CPT_texts', array(), ALL_USERS, $p_project_id );
	return isset( $t_all[$p_name] );
}

function CPT_get_all_texts( $p_project_id ) {
	return plugin_config_get( 'CPT_texts', array(), ALL_USERS, $p_project_id );
}

function CPT_get_alltext_option_list( $p_project_id, $include_none = TRUE, $defaulted = null) {
	$t_all = plugin_config_get( 'CPT_texts', array(), ALL_USERS, ALL_PROJECTS );
	$str ='<select name="sel_txt[' . $p_project_id . ']">';
	if( $include_none ) {
		$str .= '<option value="">[' . lang_get( 'none' ) . ']</option>';
	}
	foreach( $t_all as $i_name => $i_data ) {
		$t_name = $i_data['name'];
		$selected = ( $t_name == $defaulted ) ? ' selected="selected"' : '';
		$str .= '<option'.$selected.'>' . $t_name . '</option>';
	}
	$str .= '</select>';
	return $str;
}

function CPT_text_load( $p_name, $p_project_id ) {
	$t_all = plugin_config_get( 'CPT_texts', array(), ALL_USERS, $p_project_id );
	$t_txt = $t_all[$p_name];
	if( null == $t_txt ) {
		return null;
	}
	else {
		return new CPT_Text( $t_txt );
	}
}

function CPT_text_delete( $p_name, $p_project_id ) {
	$t_all = plugin_config_get( 'CPT_texts', array(), ALL_USERS, $p_project_id );
	unset( $t_all[$p_name] );
	plugin_config_set( 'CPT_texts', $t_all, ALL_USERS, $p_project_id );
}

function CPT_text_save( CPT_Text $t, $p_project_id ) {
	$t_item['name'] = $t->name;
	$t_item['description'] = $t->description;
	$t_item['user'] = auth_get_current_user_id();
	$t_item['contents'] = $t->contents;

	$t_all = CPT_get_all_texts( $p_project_id );
	$t_all[$t->name] = $t_item;
	ksort( $t_all );
	plugin_config_set( 'CPT_texts', $t_all, ALL_USERS, $p_project_id );
}

function print_project_list_option() {
	$t_projects = user_get_all_accessible_projects( auth_get_current_user_id(), ALL_PROJECTS );
	foreach( $t_projects as $t_project_id ){
		if( ( user_get_access_level( auth_get_current_user_id(), $t_project_id ) >= CPT_threshold( 'manage_project_threshold' ) )
			&& ( null === plugin_config_get( 'project', null, null, ALL_USERS, $t_project_id ) ) )
		{
			$t_project_name = project_get_name( $t_project_id );
			echo '<option value="' . $t_project_id . '">' . $t_project_name . '</option>';
		}
	}
}

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

function CPT_get_defaults () {
	return array(
		'enable_allpr' => TRUE,
		'project_all' => array(),
		'enable_pr' => TRUE,
		'project' => null,
		'CPT_texts' => array(),
		'access_level' => array (
							'manage_allprojects_threshold' => config_get( 'manage_plugin_threshold' ),
							'manage_project_threshold' => config_get( 'manage_plugin_threshold' ),
							'edit_all_threshold' => config_get( 'manage_plugin_threshold' ),
							'edit_own_threshold' => config_get( 'manage_plugin_threshold' )
							)
	);
}

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

?>