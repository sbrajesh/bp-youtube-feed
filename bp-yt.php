<?php
/*
 * Plugin Name: BP-Youtube Feed
 * Author: Brajesh Singh
 * Plugin URI:http://buddydev.com
 * Author URI:http://buddydev.com/members/sbrajesh
 * Description: allow users to show their latest uploaded youtube videos on their profile and allow group admins to associate youtube account with the group
 * Version: 1.0.2.2
 * Tested with wp 3.5.1+BuddyPress 1.6.4
 * License: GPL
 * Date: February 24, 2013
 */

define("BP_YT_PLUGIN_NAME","bp-yt");
if(!defined("BP_YT_SLUG"))
	define("BP_YT_SLUG","yt");


define("BP_YT_PLUGIN_DIR",  plugin_dir_path(__FILE__));
define("BP_YT_PLUGIN_URL",  plugin_dir_url(__FILE__));

function bp_yt_load(){
    include_once(BP_YT_PLUGIN_DIR."bp-yt-template-tags.php");
    
    if(bp_is_active("groups"))
        include_once(BP_YT_PLUGIN_DIR."yt-group-admin.php");
    include_once(BP_YT_PLUGIN_DIR."bp-yt-css-js.php");
}

add_action("bp_loaded","bp_yt_load");

/*
 * Localization support
 * Put your files into
 * bp-yt/languages/bp-yt-your_local.mo
 */
function bp_yt_load_textdomain() {
        $locale = apply_filters( 'bp_yt_load_textdomain_get_locale', get_locale() );
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%s/languages/%s.mo', BP_YT_PLUGIN_DIR, $locale );
		$mofile = apply_filters( 'bp_yt_load_textdomain_mofile', $mofile_default );
		// make sure file exists, and load it
		if ( file_exists( $mofile ) ) {
			load_textdomain( BP_YT_PLUGIN_NAME, $mofile );
		}
	}
}
add_action ( 'bp_init', 'bp_yt_load_textdomain', 2 );

function bp_yt_setup_globals(){
    global $bp;
    $bp->yt=new stdClass();
    $bp->yt->id="yt";
    $bp->yt->slug=BP_YT_SLUG;
    $bp->active_components[$bp->yt->slug] = $bp->yt->id;
    do_action( 'yt_setup_globals' );
}
add_action("bp_setup_globals","bp_yt_setup_globals");
//setup user navigation

function bp_yt_setup_nav(){
if(!bp_yt_is_enabled_for_members())
    return;
  global $bp;

 bp_core_new_nav_item( array( 'name' =>  sprintf( __( 'Youtube', 'bp-yt' )), 'slug' => $bp->yt->slug, 'position' => 83, 'screen_function' => 'bp_yt_screen_home', 'default_subnav_slug' => 'my-yt', 'item_css_id' => $bp->yt->id ) );
     $yt_link = $bp->loggedin_user->domain . $bp->yt->slug . '/';

    /* Add the subnav items to the gallery nav item */
    bp_core_new_subnav_item( array( 'name' => __( 'My YouTube', 'bp-yt' ), 'slug' => 'my-yt', 'parent_url' => $yt_link, 'parent_slug' => $bp->yt->slug, 'screen_function' => 'bp_yt_screen_home', 'position' => 10, 'item_css_id' => 'yt-my-yt' ) );
    bp_core_new_subnav_item( array( 'name' => __( 'Settings', 'bp-yt' ), 'slug' => 'settings', 'parent_url' => $yt_link, 'parent_slug' => $bp->yt->slug, 'screen_function' => 'bp_yt_screen_settings_user', 'position' => 20, 'user_has_access' => bp_is_my_profile() ) );
   
     do_action( 'yt_setup_nav');
    
}
add_action("bp_setup_nav","bp_yt_setup_nav");
//for home screen
function bp_yt_screen_home(){
    //catch the home screen of bp-yt
    global $bp;
    do_action( 'bp_yt_screen_home' );
    if(bp_is_current_component($bp->yt->slug)){
        $bp->yt->is_home=true;
    }
    if($bp->current_action=="my-yt")
        bp_core_load_template( apply_filters( 'template_yt_my_yt', 'yt/index' ) );
      
    
}

function bp_yt_setup_group_nav(){
  global $bp;
  
if(!bp_is_active("groups")||!bp_yt_is_enabled_for_groups()||!bp_yt_get_group_prefs())
    return;
    $component_link =bp_get_group_permalink($bp->groups->current_group);

    $component_slug=$bp->groups->current_group->slug;
    bp_core_new_subnav_item( array( 'name' =>   __( 'Youtube Feed', 'bp-yt' ), 'slug' => $bp->yt->slug, 'parent_url' => $component_link, 'parent_slug' => $component_slug, 'screen_function' => 'bp_yt_screen_group_home', 'position' => 15, 'user_has_access' => $bp->groups->current_group->user_has_access,'item_css_id' => 'associated-yt-home' ) );
  
}
add_action("groups_setup_nav","bp_yt_setup_group_nav",10);

function bp_yt_screen_group_home(){
    //catch the home screen of bp-yt
    global $bp;
    do_action( 'bp_yt_screen_group_home' );
    if(bp_is_group_single()&&$bp->current_action==$bp->yt->slug){
        $bp->yt->is_home=true;
    }
  
        bp_core_load_template( apply_filters( 'template_yt_group_yt', 'yt/groups/index' ) );


}

//for user settings screen
function bp_yt_screen_settings_user(){
global $bp;
if(bp_is_current_component($bp->yt->slug)&&$bp->current_action=="settings"){

    //settings screen
    if(!empty($_POST['save_settings'])){
        //store in user meta
        check_admin_referer("yt_settings");
        if(empty($_POST['yt_account']))
            bp_core_add_message(__("You must enter your yt account in order to display videos here.",'bp-yt'),'error');
        else{
            update_user_meta($bp->loggedin_user->id, "yt_account", $_POST["yt_account"]);
       
            bp_core_add_message(__("Account updated.",'bp-yt'));
        }
    }
bp_core_load_template(apply_filters('template_yt_settings','yt/index'));
}

}

/*get yt nsid of user from user meta/group meta*/
function bp_yt_get_user_account($obj_id=null){
    global $bp;
    if(bp_is_user()){
        if(empty($obj_id))
            $obj_id=$bp->displayed_user->id;
     
    return get_user_meta($obj_id,"yt_account",true);//get feed
    }
else if(bp_is_active("groups")&&bp_is_group ()){
    if(empty ($obj_id))
        $obj_id=$bp->groups->current_group->id;
 return groups_get_groupmeta($obj_id,"yt_account");
}

}

function bp_yt_is_enabled_for_members(){
    return apply_filters("bp_yt_is_enabled_for_members",true);//use it to disable/enable members feed

}

function bp_yt_is_enabled_for_groups(){
     return apply_filters("bp_yt_is_enabled_for_groups",false);//use this filter to disable/enable groups feed
}

/* allow group admin to enable/disable feed*/
add_action("bp_before_group_settings_admin","bp_yt_group_enable_form");
add_action("bp_before_group_settings_creation_step","bp_yt_group_enable_form");
//check if the group yt is enabled
function bp_yt_group_enable_form(){
    if(!bp_yt_is_enabled_for_groups())
        return;
    ?>
    <div class="checkbox">
	<label><input type="checkbox" name="group-enable-yt" id="group-enable-yt" value="1" <?php if(bp_yt_get_group_prefs()):?> checked="checked"<?php endif;?>/> <?php _e( 'Enable Youtube Feed', 'bp-yt' ) ?></label>
    </div>
<?php

}

add_action("groups_group_settings_edited","bp_yt_save_group_prefs");
add_action("groups_create_group","bp_yt_save_group_prefs");
add_action("groups_update_group","bp_yt_save_group_prefs");

function bp_yt_save_group_prefs($group_id){
      $enable=$_POST["group-enable-yt"];
      groups_update_groupmeta($group_id, "yt_enabled", $enable);
}
//save the group admin settings

function bp_yt_get_group_prefs($group_id=null){
    global $bp;
    if(empty($group_id))
        $group_id=$bp->groups->current_group->id;
    return groups_get_groupmeta($group_id,"yt_enabled");
}


add_action("bp_init","bp_yt_register_extension");
function bp_yt_register_extension(){
 global $bp;
 if(!(bp_is_active('groups')&&bp_yt_is_enabled_for_groups()&&bp_yt_get_group_prefs()))
          return;
bp_register_group_extension( 'YT_Group_Extension' );

}

?>