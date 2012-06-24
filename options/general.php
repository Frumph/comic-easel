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
					<th scope="row"><label for="add_dashboard_frumph_feed_widget"><?php _e('Enable Dashboard Feed to ComicEasel.com','comiceasel'); ?></label></th>
					<td>
						<input id="add_dashboard_frumph_feed_widget" name="add_dashboard_frumph_feed_widget" type="checkbox" value="1" <?php checked(true, $ceo_options['add_dashboard_frumph_feed_widget']); ?> />
					</td>
					<td>
						<?php _e('This is a feed that shows what is happening on ComicEasel.com','comiceasel'); ?>
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
				<tr>
					<th scope="row"><label for="enable_comments_on_homepage"><?php _e('Enable comments to appear on the home page for comic posts?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_comments_on_homepage" name="enable_comments_on_homepage" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_comments_on_homepage']); ?> />
					</td>
					<td>
						<?php _e('If the blog loop is disabled and the comic post is enabled on the home page, enabling this will allow the comments for the comic post to appear.','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="enable_comic_sidebar_locations"><?php _e('Enable comic sidebar locations?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_comic_sidebar_locations" name="enable_comic_sidebar_locations" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_comic_sidebar_locations']); ?> />
					</td>
					<td>
						<?php _e('Checking this option makes 4 new sidebars appear in the appearance - widgets section, above comic, below comic, left of comic and right of comic.','comiceasel'); ?>
					</td>
				</tr>
			</table>
			<br />
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Thumbnail sizes for locations where used.','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row">
						<label for="thumbnail_size_for_rss"><?php _e('Thumbnail size for main RSS Feed','comiceasel'); ?></label>
						<select name="thumbnail_size_for_rss" id="thumbnail_size_for_rss">
							<option class="level-0" value="none" <?php selected( $ceo_options['thumbnail_size_for_rss'],'none'); ?>><?php _e('None', 'comiceasel'); ?></option>
<?php 
$thumbnail_sizes = get_intermediate_image_sizes();
if (!in_array($ceo_options['thumbnail_size_for_rss'], $thumbnail_sizes) && ($ceo_options['thumbnail_size_for_rss'] != 'none') && ($ceo_options['thumbnail_size_for_rss'] != 'full')) $ceo_options['thumbnail_size_for_rss'] = 'full';
foreach ($thumbnail_sizes as $size) { ?>
							<option class="level-0" value="<?php echo $size; ?>" <?php selected( $ceo_options['thumbnail_size_for_rss'], $size); ?>><?php echo ucfirst($size); ?></option>
<?php } ?>
							<option class="level-0" value="full" <?php selected( $ceo_options['thumbnail_size_for_rss'],'full'); ?>><?php _e('Full', 'comiceasel'); ?></option>
						</select>
					</th>
					<td>
						<?php _e('The thumbnail for the main RSS /feed/','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="thumbnail_size_for_direct_rss"><?php _e('Thumbnail size for the direct comic & chapter RSS Feeds','comiceasel'); ?></label>
						<select name="thumbnail_size_for_direct_rss" id="thumbnail_size_for_direct_rss">
							<option class="level-0" value="none" <?php selected( $ceo_options['thumbnail_size_for_direct_rss'],'none'); ?>><?php _e('None', 'comiceasel'); ?></option>
<?php 
if (!in_array($ceo_options['thumbnail_size_for_direct_rss'], $thumbnail_sizes) && ($ceo_options['thumbnail_size_for_direct_rss'] != 'none') && ($ceo_options['thumbnail_size_for_direct_rss'] != 'full')) $ceo_options['thumbnail_size_for_direct_rss'] = 'full';
foreach ($thumbnail_sizes as $size) { ?>
							<option class="level-0" value="<?php echo $size; ?>" <?php selected( $ceo_options['thumbnail_size_for_direct_rss'], $size); ?>><?php echo ucfirst($size); ?></option>
<?php } ?>
							<option class="level-0" value="full" <?php selected( $ceo_options['thumbnail_size_for_direct_rss'],'full'); ?>><?php _e('Full', 'comiceasel'); ?></option>
						</select>
					</th>
					<td>
						<?php _e('The thumbnail for the direct comic and chapter RSS /comic/feed/ and /chapter/chapter-slug/feed/ ','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row">
						<label for="thumbnail_size_for_archive"><?php _e('Thumbnail size for archive and search','comiceasel'); ?></label>
						<select name="thumbnail_size_for_archive" id="thumbnail_size_for_archive">
							<option class="level-0" value="none" <?php selected( $ceo_options['thumbnail_size_for_archive'],'none'); ?>><?php _e('None', 'comiceasel'); ?></option>
<?php 
if (!in_array($ceo_options['thumbnail_size_for_archive'], $thumbnail_sizes) && ($ceo_options['thumbnail_size_for_archive'] != 'none') && ($ceo_options['thumbnail_size_for_archive'] != 'full')) $ceo_options['thumbnail_size_for_archive'] = 'large';
foreach ($thumbnail_sizes as $size) { ?>
							<option class="level-0" value="<?php echo $size; ?>" <?php selected( $ceo_options['thumbnail_size_for_archive'], $size); ?>><?php echo ucfirst($size); ?></option>
<?php } ?>
							<option class="level-0" value="full" <?php selected( $ceo_options['thumbnail_size_for_archive'],'full'); ?>><?php _e('Full', 'comiceasel'); ?></option>							
						</select>
					</th>
					<td>
						<?php _e('The thumbnail shown inside posts when viewed in the archive and search functions of WordPress','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<td colspan="12">
						<i><?php _e('NOTE: Edit a post, click update on it for the feeds to refresh with new copies; to see changes.','comiceasel'); ?></i>
					</td>	
				</tr>
			</table>
		</div>
		<br />
		<div class="ceo-options-save">
			<div class="ceo-major-publishing-actions">
				<div class="ceo-publishing-action">
					<input name="ceo_save_config" type="submit" class="button-primary" value="<?php _e('Save Settings','comiceasel'); ?>" />
					<input type="hidden" name="action" value="ceo_save_general" />
				</div>
				<div class="clear"></div>
			</div>
		</div>

	</form>

</div>
