<div id="comiceasel-navigation">

	<form method="post" id="myForm-navigation" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>

		<div class="comiceasel-options">
		
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Navigation Options','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row"><label for="click_comic_next"><?php _e('Clicking the comic goes to next comic?','comiceasel'); ?></label></th>
					<td>
						<input id="click_comic_next" name="click_comic_next" type="checkbox" value="1" <?php checked(true, $ceo_options['click_comic_next']); ?> />
					</td>
					<td>
						<?php _e('When this is enabled, when the comic is mouse over and clicked it will go to the next comic in the chapter.','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="disable_mininav"><?php _e('Disable the Menubar Mini-navigation? (if implemented)','comiceasel'); ?></label></th>
					<td>
						<input id="disable_mininav" name="disable_mininav" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_mininav']); ?> />
					</td>
					<td>
						<?php _e('Checking this will disable the mini navigation in the menubar if the theme you are using supports it.','comiceasel'); ?>
					</td>
				</tr>				
			</table>
			<br />
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Navigation Widget','comiceasel'); ?></th>
					</tr>
				</thead>
<?php
$current_gnav_directory = $ceo_options['graphic_navigation_directory'];
if (empty($current_gnav_directory)) $current_gnav_directory = 'default';
$dirs_to_search = array_unique(array(ABSPATH.ceo_get_plugin_path(),get_template_directory(),get_stylesheet_directory()));
$gnav_directories = array();
foreach ($dirs_to_search as $gnav_dir) {
	if (is_dir($gnav_dir . '/images/nav')) {
		$thisdir = null;
		$thisdir = array();
		$thisdir = glob($gnav_dir. '/images/nav/*');
		$gnav_directories = array_merge($gnav_directories, $thisdir); 		
	}
}
				?>
				<tr>
					<th scope="row" colspan="2"><label for="graphic_navigation_directory"><?php _e('Graphic Navigation Set','comiceasel'); ?></label>

							<select name="graphic_navigation_directory" id="graphic_navigation_directory">
<?php
foreach ($gnav_directories as $gnav_dirs) {
	if (is_dir($gnav_dirs)) {
											$gnav_dir_name = basename($gnav_dirs); ?>
											<option class="level-0" value="<?php echo $gnav_dir_name; ?>" <?php selected($current_gnav_directory, $gnav_dir_name); ?>><?php echo $gnav_dir_name; ?></option>
	<?php }
}
								?>
							</select>

					</th>
					<td>
						<?php _e('Choose a directory to get the graphic navigation styling from. To create your own custom graphic navigation menu buttons just create a directory under <i>images/nav/</i> in your child theme and place your image files and navstyle.css file inside of it to determine the style of your navigation display.','comiceasel'); ?>
					</td>
				</tr>
			</table>
			<br />
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Default Navigation','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row"><label for="disable_default_nav"><?php _e('Disable default navigation?','comiceasel'); ?></label></th>
					<td>
						<input id="disable_default_nav" name="disable_default_nav" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_default_nav']); ?> />
					</td>
					<td>
						<?php _e('Checking this will disable the default navigation, you could use and skin the navigation widget.','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="navigate_only_chapters"><?php _e('Navigate through only the chapters and not all comics?','comiceasel'); ?></label></th>
					<td>
						<input id="navigate_only_chapters" name="navigate_only_chapters" type="checkbox" value="1" <?php checked(true, $ceo_options['navigate_only_chapters']); ?> />
					</td>
					<td>
						<?php _e('Enabling this make the navigation only navigate through individual chapters with the default navigation.','comiceasel'); ?>
					</td>
				</tr>				
				<tr class="alternate">
					<th scope="row"><label for="enable_chapter_nav"><?php _e('Enable the chapter navigation drop down in the comic navigation?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_chapter_nav" name="enable_chapter_nav" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_chapter_nav']); ?> />
					</td>
					<td>
						<?php _e('When this is enabled, a drop down archive box will appear in the navigation that lets you go to the start of each chapter','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="enable_random_nav"><?php _e('Enable the random comic link in the comic navigation?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_random_nav" name="enable_random_nav" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_random_nav']); ?> />
					</td>
					<td>
						<?php _e('When this is enabled, a link will appear in the navigation that lets you go to a random comic in all chapters.','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="enable_comment_nav"><?php _e('Enable the comment link in the comic navigation?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_comment_nav" name="enable_comment_nav" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_comment_nav']); ?> />
					</td>
					<td>
						<?php _e('When this is enabled, a link will appear in the navigation that lets you go to the comments section of the current post, it also shows how many comments there currently are.','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="enable_embed_nav"><?php _e('Enable the "embed this comic" textarea in the comic navigation?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_embed_nav" name="enable_embed_nav" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_embed_nav']); ?> />
					</td>
					<td>
						<?php _e('When this is enabled, a textarea will appear under the navigation with the href and image link to have users embed this comic on their site.','comiceasel'); ?>
					</td>
				</tr>				
			</table>
		</div>
		
		<br />
		<div class="ceo-options-save">
			<div class="ceo-major-publishing-actions">
				<div class="ceo-publishing-action">
					<input name="ceo_save_config" type="submit" class="button-primary" value="Save Settings" />
					<input type="hidden" name="action" value="ceo_save_navigation" />
				</div>
				<div class="clear"></div>
			</div>
		</div>

	</form>

</div>