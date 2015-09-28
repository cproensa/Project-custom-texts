<?php
plugin_require_api( 'core/helper.php' );

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'manage_preferences_title' ) );
print_manage_menu();

$t_this_page = 'manage_preferences';
CPT_print_menu( $t_this_page );

$t_access = plugin_config_get( 'access_level', array(), FALSE, ALL_USERS, ALL_PROJECTS );
$t_token_field = form_security_field( 'CPT_manage_preferences_update' );
?>

<form method="post" action="<?php echo plugin_page( 'manage_preferences_update' ); ?>">
<?php echo $t_token_field; ?>    
<div class="form-container">
    <fieldset>
        <legend>
        <div>
            <?php echo plugin_lang_get( 'permissions_config' ); ?>
        </div>
        </legend>        
        <div class="field-container">
            <label>
                <?php echo plugin_lang_get( 'configure_all_projects' ); ?>
            </label>
            <span class="input">
                <?php
		echo '<select name="access_allpr">';
		print_enum_string_option_list( 'access_levels', $t_access['manage_allprojects_threshold'] );
		echo '</select>';
                ?>
            </span>
            <span class="label-style"></span>                        
        </div>        
        <div class="field-container">
            <label>
                <?php echo plugin_lang_get( 'configure_own_projects' ); ?>
            </label>
            <span class="input">
                <?php
		echo '<select name="access_pr">';
		print_enum_string_option_list( 'access_levels', $t_access['manage_project_threshold'] );
		echo '</select>';
                ?>
            </span>
            <span class="label-style"></span>                        
        </div>        
        <div class="field-container">
            <label>
                <?php echo plugin_lang_get( 'edit_all_texts' ); ?>
            </label>
            <span class="input">
                <?php
		echo '<select name="access_alltxt">';
		print_enum_string_option_list( 'access_levels', $t_access['edit_all_threshold'] );
		echo '</select>';
                ?>
            </span>
            <span class="label-style"></span>                        
        </div>        
        <div class="field-container">
            <label>
                <?php echo plugin_lang_get( 'edit_own_texts' ); ?>
            </label>
            <span class="input">
                <?php
		echo '<select name="access_owntxt">';
		print_enum_string_option_list( 'access_levels', $t_access['edit_own_threshold'] );
		echo '</select>';
                ?>
            </span>
            <span class="label-style"></span>                        
        </div>        
    </fieldset>
    <fieldset>
        <span class="submit-button">
        <input type="submit" name="btn_upd" class="button" name="btn_update" value="<?php echo plugin_lang_get( 'update_button' ); ?>" />
        </span>
    </fieldset>
</div>
</form>

<form method="post" action="<?php echo plugin_page( 'manage_preferences_update' ); ?>">
<?php  echo $t_token_field; ?>      
    <div class="form-container">
        <fieldset>
            <legend>
            <div>
                <?php echo plugin_lang_get( 'saved_configurations' ); ?>
            </div>
            </legend> 
            <div class="field-container">
                <label class="right">
                <input type="submit" name="btn_reset" value="<?php echo plugin_lang_get( 'reset_default_permissions' ); ?>">
                </label>
                <span class="input"><?php echo plugin_lang_get( 'reset_default_permissions_txt' ); ?></span>
            </div>
            <div class="spacer">
            </div>
            <div class="field-container">
                <label class="right">
                <input type="submit" name="btn_delete" value="<?php echo plugin_lang_get( 'delete_all_configuration' ); ?>">
                </label>
                <span class="input"><?php echo plugin_lang_get( 'delete_all_configuration_txt' ); ?></span>
            </div>        
        </fieldset>
    </div>
</form>

<?php    
html_page_bottom();
?>