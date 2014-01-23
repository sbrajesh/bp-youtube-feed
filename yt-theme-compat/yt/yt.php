<?php do_action( 'bp_before_member_yt_content' ) ?>

<div class="yt myyt">
<?php do_action( 'bp_before_yt_loop' ) ?>

<?php if ( bp_yt_has_items() ) : ?>

	<div class="pagination no-ajax">

		<div class="pag-count" >
			<?php bp_yt_pagination_count() ?>
		</div>

		<div class="pagination-links no-ajax" >
			<?php  bp_yt_pagination_links() ?>
		</div>

	</div>

	<div id="yt-list" >
	<?php while ( bp_yt_items() ) : bp_yt_the_item(); ?>

			<div class="yt-item">
				<h5><?php bp_yt_item_title();?></h5>
                                <?php bp_yt_show_video(300,200);?>
                            
			<div class="clear"></div>
		
			</div>

			
			
	<?php endwhile; ?>
	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, there were no items found in the stream.', 'bp-yt' ) ?></p>
	</div>

<?php endif; ?>

</div><!-- yt-list -->

<?php do_action( 'bp_after_member_yt_content' ) ?>
