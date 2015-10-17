<?php
# Copyright (c) 2015 Carlos Proensa

# "Project Custom Texts" for MantisBT is free software:
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation,
# either version 2 of the License, or (at your option) any later version.
#
# "Project Custom Texts" plugin for MantisBT is distributed in the hope
# that it will be useful, but WITHOUT ANY WARRANTY; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Inline column configuration plugin for MantisBT.
# If not, see <http://www.gnu.org/licenses/>.


/*
 * Get version of Mantis core, used for proper HTML output
 */
if( !defined( 'GET_VER') ) {
	$t_version_core = substr(
		MANTIS_VERSION,
		0,
		strpos( MANTIS_VERSION, '.', strpos( MANTIS_VERSION, '.' ) + 1 )
		);
	if( $t_version_core === '1.3' ) define( 'GET_VER', '1.3');
	else if( $t_version_core === '1.2' ) define( 'GET_VER', '1.2');
}


/*
 * Compatibility functions
 * Borrowed from "EmailReportingPlugin"
 * Thanks @authors
 */

// Needed for MantisBT 1.2.x.
// Not needed for MantisBT 1.3.x
require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );

// New function in MantisBT 1.3.x
// Included for compatibility with 1.2.x
if ( !function_exists( 'plugin_require_api' ) )
{
	function plugin_require_api( $p_file, $p_basename = null ) {
		if( is_null( $p_basename ) ) {
			$t_current = plugin_get_current();
		} else {
			$t_current = $p_basename;
		}

		$t_path = config_get_global( 'plugin_path' ) . $t_current . '/';
		//$t_path = '/var/www/src/mantisbt-1.2.19/plugins/' . $t_current . '/';

		require_once( $t_path . $p_file );
	}
}

// New function in MantisBT 1.3.x
// Included for compatibility with 1.2.x
if ( !function_exists( 'require_api' ) )
{
	function require_api( $p_api_name ) {
		static $s_api_included;
		global $g_core_path;
		if( !isset( $s_api_included[$p_api_name] ) ) {
			require_once( $g_core_path . $p_api_name );
			$t_new_globals = array_diff_key( get_defined_vars(), $GLOBALS, array( 't_new_globals' => 0 ) );
			foreach ( $t_new_globals as $t_global_name => $t_global_value ) {
				$GLOBALS[$t_global_name] = $t_global_value;
			}
			$s_api_included[$p_api_name] = 1;
		}
	}
}




class ProjectCustomTextsPlugin extends MantisPlugin {

	function register() {
		plugin_require_api( 'core/helper.php' );
			$this->name = 'ProjectCustomTexts';
			$this->description = 'Manage Custom Texts for project bug reporting';
			$this->page = 'manage_config';
			$this->version = '1.0-beta.1';

			if( '1.3' === GET_VER ) {
				$this->requires = array( "MantisCore" => "1.3" );
			}
			else if( '1.2' === GET_VER ) {
				$this->requires = array( "MantisCore" => "1.2" );
			}

			$this->author = 'Carlos Proensa';         # Author/team name
			$this->contact = '';        # Author/team e-mail address
			$this->url = 'https://github.com/cproensa/Project-custom-texts';
	}


	function config() {
            return CPT_get_defaults();
	}

	function hooks( )
	{
		$hooks = array(
			'EVENT_MENU_MANAGE' => 'CPT_get_manage_menu',
			'EVENT_MENU_FILTER' => 'CPT_get_manage_menu_alt',
			'EVENT_REPORT_BUG_FORM_TOP' => 'reportBugFormTop'
		);
		return $hooks;
	}

	/**
	 * If user cant have the general "Administration" menu, print link in the view_all_bugs options menu
	 * @return string
	 */
	function CPT_get_manage_menu_alt() {
		if( !access_has_global_level( config_get( 'manage_site_threshold' ) ) ) {
			return $this->CPT_get_manage_menu();
		}
	}

	/**
	 * Prints link inside "Administration" menu
	 * @return type
	 */
	function CPT_get_manage_menu( ) {
		$t_page = CPT_get_first_accesible_page();
		if( $t_page ){
			return array( '<a href="' . plugin_page( $t_page ) . '">' . plugin_lang_get( 'manage_CPT' ) . '</a>' );
		}
}

	/**
	 * Print the defined texts in the bug report form
	 * @param type $p_event
	 * @param type $p_project_id
	 */
	function reportBugFormTop( $p_event, $p_project_id ) {
		$t_enable_pr = plugin_config_get( 'enable_pr', FALSE, null, ALL_USERS, ALL_PROJECTS );
		$t_enable_allpr = plugin_config_get( 'enable_allpr', FALSE, null, ALL_USERS, ALL_PROJECTS );
		$t_pr_cfg = plugin_config_get( 'project', null, null, ALL_USERS, $p_project_id );

		$showall = FALSE;
		$showpr = FALSE;

		if( $t_enable_allpr ) {
			$showall = TRUE;                
		}

		if( $t_enable_pr && $t_pr_cfg ) {
			$showall = ( ON == $t_pr_cfg['show_all'] );
		}

		if( $t_enable_pr && $t_pr_cfg ) {
			$showpr = ( ON == $t_pr_cfg['show_custom'] );                
		}

		if( $showall ) {
			$txt = plugin_config_get( 'project_all', array(), null, ALL_USERS, ALL_PROJECTS );
			$t_obj = CPT_text_load( $txt['txt_pred'] );
			if( $t_obj ) {
				# HTML for mantis 1.2
				if( '1.2' === GET_VER ) {
					echo '<tr class="' . helper_alternate_class() . '"><td colspan="2">';
					echo string_nl2br( $t_obj->get_localized_txt() );
					echo '</td></tr>';
				}
				# HTML for mantis 1.3
				else{
					echo '<div><span>';
					echo string_nl2br( $t_obj->get_localized_txt() );
					echo '</span></div>';
				}
			}
		}
		if( $showpr ) {
			$txt = plugin_config_get( 'project', array(), null, ALL_USERS, $p_project_id );
			$t_obj = CPT_text_load( $txt['txt_pred'] );
			if( $t_obj ) {
				# HTML for mantis 1.2
				if( '1.2' === GET_VER ) {
					echo '<tr class="' . helper_alternate_class() . '"><td colspan="2">';
					echo string_nl2br( $t_obj->get_localized_txt() );
					echo '</td></tr>';
				}
				# HTML for mantis 1.3
				else{
					echo '<div><span>';
					echo string_nl2br( $t_obj->get_localized_txt() );
					echo '</span></div>';
				}
			}
		}
	}
}        
        