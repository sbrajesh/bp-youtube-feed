<?php
//handle everything except the front end display

class YT_Group_Extension extends BP_Group_Extension {
var $visibility = 'public'; // 'public' will show your extension to non-group members, 'private' means you have to be a member of the group to view your extension.

var $enable_create_step =false; // do not enable create step
var $enable_nav_item = false; //do not show in front end
var $enable_edit_item = true; // If your extensi
	function yt_group_extension() {
		$this->name = __('Youtube Feed settings','bp-yt');
		$this->slug = 'yt-feed';

		$this->create_step_position = 21;
		$this->nav_item_position = 31;
	}
//on group crate step
	function create_screen() {
		
	}
//on group create save
	function create_screen_save() {
		
            
                }

	function edit_screen() {
		if ( !bp_is_group_admin_screen( $this->slug ) )
			return false; ?>

        <h2><?php echo esc_attr( $this->name ) ?></h2>
                
			
			<p><?php _e("Please enter your youtube account userid to associate with the group","bp-yt");?>
			<input type="text" name="yt_account" value="<?php echo bp_yt_get_user_account();?>"/>
			</p>
			
			

		
        <?php
                   
        wp_nonce_field( 'groups_edit_save_' . $this->slug );
            ?>
         <p><input type="submit" value="<?php _e( 'Save Changes', 'bp-yt' ) ?> &rarr;" id="save" name="save" /></p>
                <?php
	}

	function edit_screen_save() {
		global $bp;

		if ( !isset( $_POST['save'] ) )
			return false;

		check_admin_referer( 'groups_edit_save_' . $this->slug );


                $group_id=$bp->groups->current_group->id;
		 $yt_user=$_POST["yt_account"];
                         //print_r($cats);
			if ( !groups_update_groupmeta($group_id,"yt_account", $yt_user) ) {
				bp_core_add_message( __( 'There was an error associating the YoutTube Account , please try again.', 'bp-yt' ), 'error' );
			} else {
				bp_core_add_message( __( 'YouTube account succesfully updated.', 'bcg' ) );
			}

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}

	function display() {
		/* Use this function to display the actual content of your group extension when the nav item is selected */
	}

	function widget_display() {
	}
}

?>