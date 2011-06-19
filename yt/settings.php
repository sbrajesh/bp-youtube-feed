<div class="item-list-tabs no-ajax" id="subnav">
	<ul>
		<?php bp_get_options_nav() ?>
	</ul>
</div><!-- .item-list-tabs -->

<?php do_action( 'bp_before_member_yt_content' ) ?>
<div class="yt myyt-settings">
	<?php do_action( 'bp_before_yt_settings' ) ?>

	<form class="standard-form" method="post" action="">

		<fieldset>
			<legend>Youtube Account Settings</legend>
			<p>Please enter your youtube account userid
			<input type="text" name="yt_account" value="<?php echo bp_yt_get_user_account();?>"/>
			</p>
			<?php wp_nonce_field( 'yt_settings') ?>
			<input type="submit" name="save_settings" value="Save" />
			
		</fieldset>

	</form>






<?php do_action( 'bp_after_yt_settings' ) ?>
	
</div><!-- yt -->

<?php do_action( 'bp_after_member_yt_content' ) ?>
