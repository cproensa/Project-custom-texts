<?php


$t_version_core = substr(
		MANTIS_VERSION,
		0,
		strpos( MANTIS_VERSION, '.', strpos( MANTIS_VERSION, '.' ) + 1 )
	);

if( $t_version_core === '1.3' ) define( 'GET_VER', '1.3');
else if( $t_version_core === '1.2' ) define( 'GET_VER', '1.2');



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
    /*
        function init() {
            plugin_require_api('core/helper.php');
        }
     * 
     */

        function register() {
            plugin_require_api( 'core/helper.php' );
                $this->name = 'ProjectCustomTexts';    # Proper name of plugin
                $this->description = 'Manage Custom Texts for projects';    # Short description of the plugin
                $this->page = 'manage_config';           # Default plugin page
                $this->version = '1.1';     # Plugin version string

				if( '1.3' === GET_VER ) {
					$this->requires = array( "MantisCore" => "1.3" );
				}
				else if( '1.2' === GET_VER ) {
					$this->requires = array( "MantisCore" => "1.2" );
				}

                $this->author = 'Carlos Proensa';         # Author/team name
                $this->contact = '';        # Author/team e-mail address
                $this->url = '';            # Support webpage
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
//		$t_min = min(plugin_config_get( ('access_level'), config_get( 'manage_plugin_threshold' ), FALSE, ALL_USERS, ALL_PROJECTS ) );
/*
		$t_min = CPT_threshold( array( 'manage_allprojects_threshold', 'manage_project_threshold', 'edit_all_threshold', 'edit_own_threshold' ) );
		if( access_has_project_level( $t_min ) ) {
			return array( '<a href="' . plugin_page( 'manage_config' ) . '">' . plugin_lang_get( 'manage_CPT' ) . '</a>' );
		}
*/
		$t_perm = array( 'manage_allprojects', 'manage_project', 'edit_all', 'edit_own' );
		if( CPT_access_has_level( $t_perm ) ){
			return array( '<a href="' . plugin_page( 'manage_config' ) . '">' . plugin_lang_get( 'manage_CPT' ) . '</a>' );
		}
	}

	function reportBugFormTop( $p_event, $p_project_id ) {
		//global $g_active_language;

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
					echo '<tr class="' . helper_alternate_class( 1 ) . '"><td colspan="2">';
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
					echo '<tr class="' . helper_alternate_class( 1 ) . '"><td colspan="2">';
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
        
?>        
        
