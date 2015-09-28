<?php
plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
access_ensure_project_level( CPT_threshold( array( 'manage_allprojects_threshold', 'manage_project_threshold' ) ) );

html_page_top( plugin_lang_get( 'manage_config_title' ) );
print_manage_menu();

$t_this_page = 'manage_config';
CPT_print_menu( $t_this_page );
?>

<form method="post" action="<?php echo plugin_page( 'manage_config_update' ); ?>">
<?php  echo form_security_field( 'CPT_manage_config_update' )?>
<div class="form-container">

<?php if( access_has_project_level( CPT_threshold( 'manage_allprojects_threshold' ), ALL_PROJECTS ) ) {  ?>        
    <fieldset>
        <?php
            $t_active = plugin_config_get( 'enable_allpr', FALSE, null, ALL_USERS, ALL_PROJECTS ); 
            $chk = $t_active ? 'checked="checked"' : '';
        ?>
        <div class="floatright">
            <span><input type="checkbox" name="enable_allpr" <?php echo $chk ?> /></span>
            <span><?php echo plugin_lang_get( 'enabled' ) ?></span>
        </div>
    	<legend>
        <div>
            <?php echo plugin_lang_get( 'all_projects_config' ); ?>
        </div>
        <div class="small-normal">
            <?php echo lang_get( 'config_all_projects' ); ?>
        </div>
        </legend>
        <div class="field-container">
            <label class="right">
                <?php echo plugin_lang_get( 'predefined_text' ); ?>
            </label>
            <span class="input">
                <?php 
                    $t_configitem = plugin_config_get( 'project_all',array(), null, ALL_USERS, ALL_PROJECTS );
                    echo CPT_get_alltext_option_list( ALL_PROJECTS, TRUE, $t_configitem['txt_pred'] ); ?>
            </span>
            <span class="label-style"></span>
        </div>
    </fieldset>
        <hr>
<?php
        }
?>
        <fieldset>
            <?php
                $t_active = plugin_config_get( 'enable_pr', FALSE, null, ALL_USERS, ALL_PROJECTS); 
                $chk = $t_active ? 'checked="checked"' : '';
            ?>
            <div class="floatright">
                <span><input type="checkbox" name="enable_pr" <?php echo $chk; ?> /></span>
                <span><?php echo plugin_lang_get('enabled'); ?></span>
            </div>
            <legend>
        <div><?php echo plugin_lang_get( 'projects_config' ); ?></div>
        <div class="small-normal"><?php echo lang_get( 'colour_project' ); ?></div>
            </legend>
        </fieldset>
	<table>
		<thead>
			<tr class="row-category">
				<td><?php echo plugin_lang_get( 'project_name' ); ?>
				</td>
				<td><?php echo plugin_lang_get( 'show_allpr' ); ?>
				</td>
				<td><?php echo plugin_lang_get( 'show_custom' ); ?>
				</td>
				<td><?php echo plugin_lang_get( 'predefined_text' ); ?>
				</td>
				<td><?php echo plugin_lang_get( 'delete_selection' ); ?>
				</td>                                
			</tr>
		</thead>
		<tbody>
<?php                
                $t_manage_project_threshold = CPT_threshold( array( 'manage_allprojects_threshold', 'manage_project_threshold' ) );
		$t_projects = user_get_accessible_projects( auth_get_current_user_id(), true );
		$t_full_projects = array();
		foreach( $t_projects as $t_project_id ) {
			$t_full_projects[] = project_get_row( $t_project_id );
		}
                $t_projects = $t_full_projects;
		$t_stack = array( $t_projects );

		while( 0 < count( $t_stack ) ) {
			$t_projects = array_shift( $t_stack );
			if( 0 == count( $t_projects ) ) {
				continue;
			}
			$t_project = array_shift( $t_projects );
			$t_project_id = $t_project['id'];
			$t_level = count( $t_stack );

			# only print row if user has project management privileges
                        #and exists the configuration for that project
                        $t_configitem = plugin_config_get( 'project', null, null, ALL_USERS, $t_project_id );
			if( 
                                access_has_project_level( $t_manage_project_threshold, $t_project_id, auth_get_current_user_id() )
                                && isset( $t_configitem )
                            ) { ?>
			<tr>
                                <?php
                                echo '<td>';
				echo str_repeat( '&raquo; ', $t_level ) . string_display( $t_project['name'] ) ;
                                echo '<input type="hidden" name="prid[]" value="' . $t_project_id . '" />' ;
				echo '</td>';

                                $t_flag = $t_configitem['show_all'];
                                $t_set = ( ON == $t_flag ) ? 'checked="checked"' : '';
                                echo '<td class="center"><input type="checkbox" name="flag_all[]" value="' . $t_project_id . '" ' . $t_set . ' /></td>';				
                                $t_flag = $t_configitem['show_custom'];
                                $t_set = ( ON == $t_flag ) ? 'checked="checked"' : '';
                                echo '<td class="center"><input type="checkbox" name="flag_custom[]" value="' . $t_project_id . '" ' . $t_set . ' /></td>';				
                                
                                echo '<td class="center">';
                                echo CPT_get_alltext_option_list( $t_project_id, TRUE, $t_configitem['txt_pred'] );
                                echo '</td>';

                                echo '<td><input type="checkbox" name="cb_delete[' . $t_project_id . ']" /></td>';
                                ?>
			</tr><?php
                            }			
                        $t_subprojects = project_hierarchy_get_subprojects( $t_project_id, true );

			if( 0 < count( $t_projects ) || 0 < count( $t_subprojects ) ) {
				array_unshift( $t_stack, $t_projects );
			}

			if( 0 < count( $t_subprojects ) ) {
				$t_full_projects = array();
				foreach ( $t_subprojects as $t_project_id ) {
					$t_full_projects[] = project_get_row( $t_project_id );
				}
                                $t_subprojects= $t_full_projects;
				array_unshift( $t_stack, $t_subprojects );
			}
		} ?>
                        <tr>
                            <td></td><td></td><td></td><td></td>
                            <td><span><input type="submit" class="button-small" name ="btn_del" value="<?php echo plugin_lang_get( 'delete_button' ); ?>" /><span></td>
                        </tr>
		</tbody>
	</table>
    
        <fieldset>
            <span class="submit-button">
            <input type="submit" name="btn_upd" class="button" value="<?php echo plugin_lang_get( 'update_button' ); ?>" />
            </span>
        </fieldset>
        
    <h2><?php echo plugin_lang_get( 'add_projects' ); ?></h2>
    <fieldset>
        <div class="field-container">
            <label><span><?php echo plugin_lang_get( 'projects_without_config' )  ?></span></label>
            <span class="select">
                <select id="add-user-project-id" name="add_project_id[]" multiple="multiple" size="5">
                                        <?php print_project_list_option(); ?>
                </select>
            </span>
            <span class="label-style"></span>
        </div>
        <span class="submit-button"><input type="submit" class="button" name ="btn_add" value="<?php echo plugin_lang_get( 'add_projects_button' ); ?>" /></span>
    </fieldset>
</div>
</form>

<?php
html_page_bottom();
?>

