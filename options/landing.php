<div id="comiceasel-landing">
	<form method="post" id="myForm-general" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>
		<?php 
			if (!isset($ceo_options['enable_chapter_landing'])) $ceo_options['enable_chapter_landing'] = false;
			if (!isset($ceo_options['enable_chapter_landing_first'])) $ceo_options['enable_chapter_landing_first'] = false;
			if (!isset($ceo_options['enable_blog_on_chapter_landing'])) $ceo_options['enable_blog_on_chapter_landing'] = false;
			if (!isset($ceo_options['enable_comments_on_chapter_landing'])) $ceo_options['enable_comments_on_chapter_landing'] = false;
		?>
		<div class="comiceasel-options">
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Landing Pages', 'comiceasel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row"><label for="enable_chapter_landing"><?php _e('Enable chapter landing pages? /chapter/slug-name','comiceasel'); ?></label></th>
					<td>
						<input id="enable_chapter_landing" name="enable_chapter_landing" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_chapter_landing']); ?> />
					</td>
					<td>
						<?php _e('When enabled it displays the comic on the archive page for the chapter.','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="enable_chapter_landing_first"><?php _e('Display first comic of the chapter on the landing page?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_chapter_landing_first" name="enable_chapter_landing_first" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_chapter_landing_first']); ?> />
					</td>
					<td>
						<?php _e('Checking this will have the landing page/archive for the chapter display the first comic. (unchecked will be latest - default)','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="enable_blog_on_chapter_landing"><?php _e('Enable the blog post for the comic show on the landing page?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_blog_on_chapter_landing" name="enable_blog_on_chapter_landing" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_blog_on_chapter_landing']); ?> />
					</td>
					<td>
						<?php _e('Checking this will make the blog post for the comic display on the landing page.','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="enable_comments_on_chapter_landing"><?php _e('Enable comments to appear on the landing page under the blog?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_comments_on_chapter_landing" name="enable_comments_on_chapter_landing" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_comments_on_chapter_landing']); ?> />
					</td>
					<td>
						<?php _e('Checking this will make the comments appear under the blog post (if blog post is enabled to show) on the landing pages for chapters.','comiceasel'); ?>
					</td>
				</tr>
			</table>
			<br />
			<small><?php _e('*Options are specific to the ComicPress theme, this screen will only display if the theme is active.','comiceasel'); ?></small>
		</div>
		<br />

		<div class="ceo-options-save">
			<div class="ceo-major-publishing-actions">
				<div class="ceo-publishing-action">
					<input name="ceo_save_config" type="submit" class="button-primary" value="<?php _e('Save Settings','comiceasel'); ?>" />
					<input type="hidden" name="action" value="ceo_save_landing" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</form>
</div>
