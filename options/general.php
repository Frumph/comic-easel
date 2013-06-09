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
				<tr>
					<th scope="row"><label for="disable_related_comics"><?php _e('Disable the displaying of related comics?','comiceasel'); ?></label></th>
					<td>
						<input id="disable_related_comics" name="disable_related_comics" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_related_comics']); ?> />
					</td>
					<td>
						<?php _e('If you have a theme that has related comics do_action code installed, this will disable it from displaying.','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="display_first_comic_on_home_page"><?php _e('Show the first comic on the home page?','comiceasel'); ?></label></th>
					<td>
						<input id="display_first_comic_on_home_page" name="display_first_comic_on_home_page" type="checkbox" value="1" <?php checked(true, $ceo_options['display_first_comic_on_home_page']); ?> />
					</td>
					<td>
						<?php _e('Enabling this will make it so that the comic on the home page is the first comic.','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="disable_style_sheet"><?php _e('Disable the default stylesheets. comiceasel.css and navstyle.css','comiceasel'); ?></label></th>
					<td>
						<input id="disable_style_sheet" name="disable_style_sheet" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_style_sheet']); ?> />
					</td>
					<td>
						<?php _e('Checkmarking this will make it so that the default stylesheets do not load, you would need to add those css elements yourself to your style.css','comiceasel'); ?>
					</td>
				</tr>
<?php if (!defined('CEO_FEATURE_DISABLE_TRANSCRIPT')) { ?>
				<tr>
					<th scope="row"><label for="enable_transcripts_in_comic_posts"><?php _e('Enable the transcripts to automatically show at the bottom of posts if they exist?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_transcripts_in_comic_posts" name="enable_transcripts_in_comic_posts" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_transcripts_in_comic_posts']); ?> />
					</td>
					<td>
						<?php _e('Enabling this will make transcripts show at the bottom of comic posts, if the comic has a transcript.','comiceasel'); ?>
					</td>
				</tr>
<?php } ?>
			</table>
			<br />
<?php 
if (!defined('CEO_FEATURE_DISABLE_MOTION_ARTIST')) { 
	if (!isset($ceo_options['enable_motion_artist_support'])) $ceo_options['enable_motion_artist_support'] = false;
?>
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Motion Artist Comics','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row"><label for="enable_motion_artist_support"><?php _e('Enable support for Motion Artist Comics','comiceasel'); ?></label></th>
					<td>
						<input id="enable_motion_artist_support" name="enable_motion_artist_support" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_motion_artist_support']); ?> />
					</td>
					<td>
						<?php _e('When enabled, this will provide a drop down box that you can select which directory to use for the motion comic for that post.','comiceasel'); ?>
					</td>
				</tr>			
			</table>
			<br />
<?php } ?>
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
			<br />
			<table class="widefat">
			<thead>
				<tr>
					<th colspan="3"><?php _e('Comic Post Type','comiceasel'); ?></th>
				</tr>
			</thead>
				<tr class="alternate">
					<?php if (empty($ceo_options['custom_post_type_slug_name'])) $ceo_options['custom_post_type_slug_name'] = 'comic'; ?>
					<th scope="row"><label for="custom_post_type_slug_name"><?php _e('Custom Post Type slug name?','comiceasel'); ?></label></th>
					<td>
						<input id="custom_post_type_slug_name" name="custom_post_type_slug_name" type="text" value="<?php echo $ceo_options['custom_post_type_slug_name']; ?>" /><br />
<?php 
$check_term = term_exists($ceo_options['custom_post_type_slug_name']);
if ($check_term) { ?>
	<span style="font-weight: 700; color: #f00;"><?php _e('This slug already exists and will cause problems.  Change it.','comiceasel'); ?></span>
<?php if ($ceo_options['custom_post_type_slug_name'] == 'comic') { ?>
	<br /><?php _e('This is the default custom post type slug - which is already in use on your system.  Sometimes people have the chapter name as this slug, that needs to be changed if you want to use the default.','comiceasel'); ?>
	<?php } 
}
?>
					</td>
					<td>
						<?php _e('Default: "comic" changing this will modify the permalink name for the /comic/ how it is addressed in the url.  This is a slug name, no slashes or spaces allowed; only alpha characters and a single word.','comiceasel'); ?><br />
						<br />
						<span style='color: #b00;'><?php _e('IMPORTANT - If you change this from the default remember to go to settings -> permalink and click SAVE so that the permalink structure can be recognized by WordPress','comiceasel'); ?></span>
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
