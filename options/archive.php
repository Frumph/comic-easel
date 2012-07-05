<div id="comiceasel-archive">

	<form method="post" id="myForm-archive" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>

		<div class="comiceasel-options">
		
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Archive Options','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row"><label for="include_comics_in_blog_archive"><?php _e('Include the comics in the blog archive?','comiceasel'); ?></label></th>
					<td>
						<input id="include_comics_in_blog_archive" name="include_comics_in_blog_archive" type="checkbox" value="1" <?php checked(true, $ceo_options['include_comics_in_blog_archive']); ?> />
					</td>
					<td>
						<?php _e('When this is enabled, when you search through the year/date/month and other archiving WordPress functions, the comic will appear with the regular blog posts.  This feature automatically works for all tags already.','comiceasel'); ?>
					</td>
				</tr>
			</table>
			<br />
		</div>
		
		<br />
		<div class="ceo-options-save">
			<div class="ceo-major-publishing-actions">
				<div class="ceo-publishing-action">
					<input name="ceo_save_config" type="submit" class="button-primary" value="<?php _e('Save Settings', 'comiceasel'); ?>" />
					<input type="hidden" name="action" value="ceo_save_archive" />
				</div>
				<div class="clear"></div>
			</div>
		</div>

	</form>

</div>