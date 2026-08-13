<?php if (!defined('ABSPATH')) exit; ?>
<div id="comiceasel-general">
	<form method="post" id="myForm-general" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>
		<div class="comiceasel-options">
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php esc_html_e('Configuration','comic-easel'); ?></th>
					</tr>
				</thead>
				<tr>
					<th scope="row"><label for="disable_comic_on_home_page"><?php esc_html_e('Disable Comic on the Home Page?','comic-easel'); ?></label></th>
					<td>
						<input id="disable_comic_on_home_page" name="disable_comic_on_home_page" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_comic_on_home_page']); ?> />
					</td>
					<td>
						<?php esc_html_e('Checking this will stop the display of the comic and comic area on the home page','comic-easel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="disable_comic_blog_on_home_page"><?php esc_html_e('Disable the Comic Post on the Home Page?','comic-easel'); ?></label></th>
					<td>
						<input id="disable_comic_blog_on_home_page" name="disable_comic_blog_on_home_page" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_comic_blog_on_home_page']); ?> />
					</td>
					<td>
						<?php esc_html_e('Checking this will stop the display of the comic\'s blog on the home page.','comic-easel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="enable_comments_on_homepage"><?php esc_html_e('Enable comments to appear on the home page for comic posts?','comic-easel'); ?></label></th>
					<td>
						<input id="enable_comments_on_homepage" name="enable_comments_on_homepage" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_comments_on_homepage']); ?> />
					</td>
					<td>
						<?php esc_html_e('If the blog loop is disabled and the comic post is enabled on the home page, enabling this will allow the comments for the comic post to appear.','comic-easel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="enable_comic_sidebar_locations"><?php esc_html_e('Enable comic sidebar locations?','comic-easel'); ?></label></th>
					<td>
						<input id="enable_comic_sidebar_locations" name="enable_comic_sidebar_locations" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_comic_sidebar_locations']); ?> />
					</td>
					<td>
						<?php esc_html_e('Checking this option makes 4 new sidebars appear in the appearance - widgets section, above comic, below comic, left of comic and right of comic.','comic-easel'); ?>
					</td>
				</tr>
<?php /*
				<tr>
					<th scope="row"><label for="enable_hoverbox"><?php esc_html_e('Enable Hoverbox?','comic-easel'); ?></label></th>
					<td>
						<input id="enable_hoverbox" name="enable_hoverbox" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_hoverbox']); ?> />
					</td>
					<td>
						<?php esc_html_e('Hoverbox is the equivelant of Rascal in ComicPress, mouse-hover over comic leads to a skinnable section that can be customized for viewing the hovertext.','comic-easel'); ?>
					</td>
				</tr>
*/				?>
				<tr class="alternate">
					<th scope="row"><label for="disable_related_comics"><?php esc_html_e('Disable the displaying of related comics?','comic-easel'); ?></label></th>
					<td>
						<input id="disable_related_comics" name="disable_related_comics" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_related_comics']); ?> />
					</td>
					<td>
						<?php esc_html_e('If you have a theme that has related comics do_action code installed, this will disable it from displaying.','comic-easel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="display_first_comic_on_home_page"><?php esc_html_e('Show the first comic on the home page?','comic-easel'); ?></label></th>
					<td>
						<input id="display_first_comic_on_home_page" name="display_first_comic_on_home_page" type="checkbox" value="1" <?php checked(true, $ceo_options['display_first_comic_on_home_page']); ?> />
					</td>
					<td>
						<?php esc_html_e('Enabling this will make it so that the comic on the home page is the first comic.','comic-easel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="disable_style_sheet"><?php esc_html_e('Disable the default stylesheets. comiceasel.css and navstyle.css','comic-easel'); ?></label></th>
					<td>
						<input id="disable_style_sheet" name="disable_style_sheet" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_style_sheet']); ?> />
					</td>
					<td>
						<?php esc_html_e('Checkmarking this will make it so that the default stylesheets do not load, you would need to add those css elements yourself to your style.css','comic-easel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="allow_comics_to_have_categories"><?php esc_html_e('Allow comics to associate with WordPress categories?','comic-easel'); ?></label></th>
					<td>
						<input id="allow_comics_to_have_categories" name="allow_comics_to_have_categories" type="checkbox" value="1" <?php checked(true, $ceo_options['allow_comics_to_have_categories']); ?> />
					</td>
					<td>
						<?php esc_html_e('For those people who need to allow comics associated with categories as well as chapters. (might cause problems)','comic-easel'); ?>
					</td>
				</tr>
<?php if (!defined('CEO_FEATURE_DISABLE_TRANSCRIPT')) { ?>
				<tr class="alternate">
					<th scope="row"><label for="enable_transcripts_in_comic_posts"><?php esc_html_e('Enable the transcripts to automatically show at the bottom of posts if they exist?','comic-easel'); ?></label></th>
					<td>
						<input id="enable_transcripts_in_comic_posts" name="enable_transcripts_in_comic_posts" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_transcripts_in_comic_posts']); ?> />
					</td>
					<td>
						<?php esc_html_e('Enabling this will make transcripts show at the bottom of comic posts, if the comic has a transcript.','comic-easel'); ?>
					</td>
				</tr>				
<?php } ?>
<?php if (!isset($ceo_options['chapter_on_home'])) $ceo_options['chapter_on_home'] = 0; ?>
				<tr>
					<th scope="row"><label for="chapter_on_home"><?php esc_html_e('What chapter would you like to display on the home page?','comic-easel'); ?></label></th>
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
						<?php echo esc_html($ceo_options['chapter_on_home']); ?>
						<?php esc_html_e('Select which chapter or (all) to display on the home page if you have different stories/chapters.','comic-easel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="remove_post_thumbnail"><?php esc_html_e('Remove featured image in posts on non-ComicPress themes?','comic-easel'); ?></label></th>
					<td>
						<input id="remove_post_thumbnail" name="remove_post_thumbnail" type="checkbox" value="1" <?php checked(true, $ceo_options['remove_post_thumbnail']); ?> />
					</td>
					<td>
						<?php esc_html_e('Try to have Comic Easel automatically remove the featured image in posts on non-ComicPress themes?','comic-easel'); ?>
					</td>
				</tr>
			</table>
			<br />
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php esc_html_e('Thumbnail sizes for locations where used.','comic-easel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row">
						<label for="thumbnail_size_for_rss"><?php esc_html_e('Thumbnail size for main RSS Feed','comic-easel'); ?></label>
						<select name="thumbnail_size_for_rss" id="thumbnail_size_for_rss">
							<option class="level-0" value="none" <?php selected( $ceo_options['thumbnail_size_for_rss'],'none'); ?>><?php esc_html_e('None', 'comic-easel'); ?></option>
<?php 
$thumbnail_sizes = get_intermediate_image_sizes();
if (!in_array($ceo_options['thumbnail_size_for_rss'], $thumbnail_sizes) && ($ceo_options['thumbnail_size_for_rss'] != 'none') && ($ceo_options['thumbnail_size_for_rss'] != 'full')) $ceo_options['thumbnail_size_for_rss'] = 'full';
foreach ($thumbnail_sizes as $size) { ?>
							<option class="level-0" value="<?php echo esc_attr($size); ?>" <?php selected( $ceo_options['thumbnail_size_for_rss'], $size); ?>><?php echo esc_html(ucfirst($size)); ?></option>
<?php } ?>
							<option class="level-0" value="full" <?php selected( $ceo_options['thumbnail_size_for_rss'],'full'); ?>><?php esc_html_e('Full', 'comic-easel'); ?></option>
						</select>
					</th>
					<td>
						<?php esc_html_e('The thumbnail for the main RSS /feed/','comic-easel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="thumbnail_size_for_direct_rss"><?php esc_html_e('Thumbnail size for the direct comic & chapter RSS Feeds','comic-easel'); ?></label>
						<select name="thumbnail_size_for_direct_rss" id="thumbnail_size_for_direct_rss">
							<option class="level-0" value="none" <?php selected( $ceo_options['thumbnail_size_for_direct_rss'],'none'); ?>><?php esc_html_e('None', 'comic-easel'); ?></option>
<?php 
if (!in_array($ceo_options['thumbnail_size_for_direct_rss'], $thumbnail_sizes) && ($ceo_options['thumbnail_size_for_direct_rss'] != 'none') && ($ceo_options['thumbnail_size_for_direct_rss'] != 'full')) $ceo_options['thumbnail_size_for_direct_rss'] = 'full';
foreach ($thumbnail_sizes as $size) { ?>
							<option class="level-0" value="<?php echo esc_attr($size); ?>" <?php selected( $ceo_options['thumbnail_size_for_direct_rss'], $size); ?>><?php echo esc_html(ucfirst($size)); ?></option>
<?php } ?>
							<option class="level-0" value="full" <?php selected( $ceo_options['thumbnail_size_for_direct_rss'],'full'); ?>><?php esc_html_e('Full', 'comic-easel'); ?></option>
						</select>
					</th>
					<td>
						<?php esc_html_e('The thumbnail for the direct comic and chapter RSS /comic/feed/ and /chapter/chapter-slug/feed/','comic-easel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row">
						<label for="thumbnail_size_for_archive"><?php esc_html_e('Thumbnail size for archive and search','comic-easel'); ?></label>
						<select name="thumbnail_size_for_archive" id="thumbnail_size_for_archive">
							<option class="level-0" value="none" <?php selected( $ceo_options['thumbnail_size_for_archive'],'none'); ?>><?php esc_html_e('None', 'comic-easel'); ?></option>
<?php 
if (!in_array($ceo_options['thumbnail_size_for_archive'], $thumbnail_sizes) && ($ceo_options['thumbnail_size_for_archive'] != 'none') && ($ceo_options['thumbnail_size_for_archive'] != 'full')) $ceo_options['thumbnail_size_for_archive'] = 'large';
foreach ($thumbnail_sizes as $size) { ?>
							<option class="level-0" value="<?php echo esc_attr($size); ?>" <?php selected( $ceo_options['thumbnail_size_for_archive'], $size); ?>><?php echo esc_html(ucfirst($size)); ?></option>
<?php } ?>
							<option class="level-0" value="full" <?php selected( $ceo_options['thumbnail_size_for_archive'],'full'); ?>><?php esc_html_e('Full', 'comic-easel'); ?></option>							
						</select>
					</th>
					<td>
						<?php esc_html_e('The thumbnail shown inside posts when viewed in the archive and search functions of WordPress','comic-easel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="thumbnail_size_for_facebook"><?php esc_html_e('Thumbnail size for Facebook images','comic-easel'); ?></label>
						<select name="thumbnail_size_for_facebook" id="thumbnail_size_for_facebook">
							<option class="level-0" value="none" <?php selected( $ceo_options['thumbnail_size_for_facebook'],'none'); ?>><?php esc_html_e('None', 'comic-easel'); ?></option>
<?php 
if (!in_array($ceo_options['thumbnail_size_for_facebook'], $thumbnail_sizes) && ($ceo_options['thumbnail_size_for_facebook'] != 'none') && ($ceo_options['thumbnail_size_for_facebook'] != 'full')) $ceo_options['thumbnail_size_for_facebook'] = 'large';
foreach ($thumbnail_sizes as $size) { ?>
							<option class="level-0" value="<?php echo esc_attr($size); ?>" <?php selected( $ceo_options['thumbnail_size_for_facebook'], $size); ?>><?php echo esc_html(ucfirst($size)); ?></option>
<?php } ?>
							<option class="level-0" value="full" <?php selected( $ceo_options['thumbnail_size_for_facebook'],'full'); ?>><?php esc_html_e('Full', 'comic-easel'); ?></option>							
						</select>
					</th>
					<td>
						<?php esc_html_e('Comic Easel adds an og:image to the head section of the site.  This is the size of the image that is used for the image that facebook recognizes.  If you are having issues where the image is not the one you want, flip this.','comic-easel'); ?>
					</td>
				</tr>
				<tr>
					<td colspan="12">
						<i><?php esc_html_e('NOTE: Edit a post, click update on it for the feeds to refresh with new copies; to see changes.','comic-easel'); ?></i>
					</td>	
				</tr>
			</table>
			<br />
		</div>
		<br />

		<div class="ceo-options-save">
			<div class="ceo-major-publishing-actions">
				<div class="ceo-publishing-action">
					<input name="ceo_save_config" type="submit" class="button-primary" value="<?php esc_attr_e('Save Settings','comic-easel'); ?>" />
					<input type="hidden" name="action" value="ceo_save_general" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</form>
</div>
