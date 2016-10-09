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
<?php /*
				<tr>
					<th scope="row"><label for="enable_hoverbox"><?php _e('Enable Hoverbox?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_hoverbox" name="enable_hoverbox" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_hoverbox']); ?> />
					</td>
					<td>
						<?php _e('Hoverbox is the equivelant of Rascal in ComicPress, mouse-hover over comic leads to a skinnable section that can be customized for viewing the hovertext.','comiceasel'); ?>
					</td>
				</tr>
*/				?>
				<tr class="alternate">
					<th scope="row"><label for="disable_related_comics"><?php _e('Disable the displaying of related comics?','comiceasel'); ?></label></th>
					<td>
						<input id="disable_related_comics" name="disable_related_comics" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_related_comics']); ?> />
					</td>
					<td>
						<?php _e('If you have a theme that has related comics do_action code installed, this will disable it from displaying.','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="display_first_comic_on_home_page"><?php _e('Show the first comic on the home page?','comiceasel'); ?></label></th>
					<td>
						<input id="display_first_comic_on_home_page" name="display_first_comic_on_home_page" type="checkbox" value="1" <?php checked(true, $ceo_options['display_first_comic_on_home_page']); ?> />
					</td>
					<td>
						<?php _e('Enabling this will make it so that the comic on the home page is the first comic.','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="disable_style_sheet"><?php _e('Disable the default stylesheets. comiceasel.css and navstyle.css','comiceasel'); ?></label></th>
					<td>
						<input id="disable_style_sheet" name="disable_style_sheet" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_style_sheet']); ?> />
					</td>
					<td>
						<?php _e('Checkmarking this will make it so that the default stylesheets do not load, you would need to add those css elements yourself to your style.css','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="allow_comics_to_have_categories"><?php _e('Allow comics to associate with WordPress categories?','comiceasel'); ?></label></th>
					<td>
						<input id="allow_comics_to_have_categories" name="allow_comics_to_have_categories" type="checkbox" value="1" <?php checked(true, $ceo_options['allow_comics_to_have_categories']); ?> />
					</td>
					<td>
						<?php _e('For those people who need to allow comics associated with categories as well as chapters. (might cause problems)','comiceasel'); ?>
					</td>
				</tr>
<?php if (!defined('CEO_FEATURE_DISABLE_TRANSCRIPT')) { ?>
				<tr class="alternate">
					<th scope="row"><label for="enable_transcripts_in_comic_posts"><?php _e('Enable the transcripts to automatically show at the bottom of posts if they exist?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_transcripts_in_comic_posts" name="enable_transcripts_in_comic_posts" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_transcripts_in_comic_posts']); ?> />
					</td>
					<td>
						<?php _e('Enabling this will make transcripts show at the bottom of comic posts, if the comic has a transcript.','comiceasel'); ?>
					</td>
				</tr>				
<?php } ?>
<?php if (!isset($ceo_options['chapter_on_home'])) $ceo_options['chapter_on_home'] = 0; ?>
				<tr>
					<th scope="row"><label for="chapter_on_home"><?php _e('What chapter would you like to display on the home page?','comiceasel'); ?></label></th>
					<td>
<?php $args = array(
		'show_option_all'	=> 'All Chapters',
		'orderby'			=> 'menu_order', 
		'order'				=> 'ASC',
		'selected'			=> $ceo_options['chapter_on_home'],
		'name'				=> 'chapter_on_home',
		'id'				=> 'chapter_on_home',
		'class'				=> 'postform',
		'taxonomy'			=> 'chapters',
		'hide_if_empty'		=> false,
		'heirarchel'		=> 1
); 
wp_dropdown_categories($args);
?>					
					</td>
					<td>
						<?php echo $ceo_options['chapter_on_home']; ?>
						<?php _e('Select which chapter or (all) to display on the home page if you have different stories/chapters.','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="remove_post_thumbnail"><?php _e('Remove featured image in posts on non-ComicPress themes?','comiceasel'); ?></label></th>
					<td>
						<input id="remove_post_thumbnail" name="remove_post_thumbnail" type="checkbox" value="1" <?php checked(true, $ceo_options['remove_post_thumbnail']); ?> />
					</td>
					<td>
						<?php _e('Try to have Comic Easel automatically remove the featured image in posts on non-ComicPress themes?','comiceasel'); ?>
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
						<?php _e('The thumbnail for the direct comic and chapter RSS /comic/feed/ and /chapter/chapter-slug/feed/','comiceasel'); ?>
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
					<th scope="row">
						<label for="thumbnail_size_for_facebook"><?php _e('Thumbnail size for Facebook images','comiceasel'); ?></label>
						<select name="thumbnail_size_for_facebook" id="thumbnail_size_for_facebook">
							<option class="level-0" value="none" <?php selected( $ceo_options['thumbnail_size_for_facebook'],'none'); ?>><?php _e('None', 'comiceasel'); ?></option>
<?php 
if (!in_array($ceo_options['thumbnail_size_for_facebook'], $thumbnail_sizes) && ($ceo_options['thumbnail_size_for_facebook'] != 'none') && ($ceo_options['thumbnail_size_for_facebook'] != 'full')) $ceo_options['thumbnail_size_for_facebook'] = 'large';
foreach ($thumbnail_sizes as $size) { ?>
							<option class="level-0" value="<?php echo $size; ?>" <?php selected( $ceo_options['thumbnail_size_for_facebook'], $size); ?>><?php echo ucfirst($size); ?></option>
<?php } ?>
							<option class="level-0" value="full" <?php selected( $ceo_options['thumbnail_size_for_facebook'],'full'); ?>><?php _e('Full', 'comiceasel'); ?></option>							
						</select>
					</th>
					<td>
						<?php _e('Comic Easel adds an og:image to the head section of the site.  This is the size of the image that is used for the image that facebook recognizes.  If you are having issues where the image is not the one you want, flip this.','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<td colspan="12">
						<i><?php _e('NOTE: Edit a post, click update on it for the feeds to refresh with new copies; to see changes.','comiceasel'); ?></i>
					</td>	
				</tr>
			</table>
			<br />
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
