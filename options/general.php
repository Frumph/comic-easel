<div id="comiceasel-general">

	<form method="post" id="myForm-general" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>

		<div class="comiceasel-options">
		
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Configuration','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row"><label for="add_dashboard_frumph_feed_widget"><?php _e('Enable Dashboard Feed to Frumph.NET','comiceasel'); ?></label></th>
					<td>
						<input id="add_dashboard_frumph_feed_widget" name="add_dashboard_frumph_feed_widget" type="checkbox" value="1" <?php checked(true, $ceo_options['add_dashboard_frumph_feed_widget']); ?> />
					</td>
					<td>
						<?php _e('This is a feed that shows what is happening on Frumph.NET','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="disable_comic_on_home_page"><?php _e('Disable Comic on the Home Page?','comiceasel'); ?></label></th>
					<td>
						<input id="disable_comic_on_home_page" name="disable_comic_on_home_page" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_comic_on_home_page']); ?> />
					</td>
					<td>
						<?php _e('Checking this will stop the display of the comic and comic area on the home page','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="disable_comic_blog_on_home_page"><?php _e('Disable the Comic Post on the Home Page?','comiceasel'); ?></label></th>
					<td>
						<input id="disable_comic_blog_on_home_page" name="disable_comic_blog_on_home_page" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_comic_blog_on_home_page']); ?> />
					</td>
					<td>
						<?php _e('Checking this will stop the display of the comic\'s blog on the home page.','comiceasel'); ?>
					</td>
				</tr>
			</table>
				
		</div>
		
		<br />
		<div class="ceo-options-save">
			<div class="ceo-major-publishing-actions">
				<div class="ceo-publishing-action">
					<input name="ceo_save_config" type="submit" class="button-primary" value="Save Settings" />
					<input type="hidden" name="action" value="ceo_save_general" />
				</div>
				<div class="clear"></div>
			</div>
		</div>

	</form>

</div>