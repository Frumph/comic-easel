<div id="comiceasel-archive">

	<form method="post" id="myForm-archive" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>

		<div class="comiceasel-options">
			<div style='color: #b00;font-size: 16px;text-align:center;background-color: #efefef;padding: 5px;border: solid 2px #000;font-weight: 700;'><?php _e('IMPORTANT - If you change any of the settings on this page from the default, go to settings -> permalink and click SAVE so that the permalink structure can be recognized by WordPress','comiceasel'); ?></div>
			<br />
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
				<tr>
					<?php if (!isset($ceo_options['disable_cal_rewrite_rules'])) $ceo_options['disable_cal_rewrite_rules'] = false; ?>
					<th scope="row"><label for="disable_cal_rewrite_rules"><?php _e('Disable the the rewrite rules so numerical slugs do not get turned into dates?','comiceasel'); ?></label></th>
					<td>
						<input id="disable_cal_rewrite_rules" name="disable_cal_rewrite_rules" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_cal_rewrite_rules']); ?> />
					</td>
					<td>
						<?php _e('This option disables the url line from interpreting numerical numbers as dates. ex. /comic/2014/','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<?php if (!isset($ceo_options['enable_chapter_in_url'])) $ceo_options['enable_chapter_in_url'] = false; ?>
					<th scope="row"><label for="enable_chapter_in_url"><?php _e('Allow the URL to also denote the chapter?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_chapter_in_url" name="enable_chapter_in_url" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_chapter_in_url']); ?> />
					</td>
					<td>
						<?php _e('Allows the chapter that the comic is in to show up in the URL of the comic. ex: /comic/chapter-slug/postname/','comiceasel'); ?>
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
					</td>
				</tr>
				<tr>
					<?php if (empty($ceo_options['chapter_type_slug_name'])) $ceo_options['chapter_type_slug_name'] = 'comic'; ?>
					<th scope="row"><label for="chapter_type_slug_name"><?php _e('Chapter Type slug name?','comiceasel'); ?></label></th>
					<td>
						<input id="chapter_type_slug_name" name="chapter_type_slug_name" type="text" value="<?php echo $ceo_options['chapter_type_slug_name']; ?>" /><br />
<?php 
$check_term = term_exists($ceo_options['chapter_type_slug_name']);
if ($check_term) { ?>
	<span style="font-weight: 700; color: #f00;"><?php _e('This slug already exists and will cause problems.  Change it.','comiceasel'); ?></span>
<?php if ($ceo_options['chapter_type_slug_name'] == 'chapter') { ?>
	<br /><?php _e('This is the default chapter slug - which is already in use on your system.','comiceasel'); ?>
	<?php } 
}
?>
					</td>
					<td>
						<?php _e('Default: "chapter" changing this will modify the permalink name for the /chapter/ how it is addressed in the url.  This is a slug name, no slashes or spaces allowed; only alpha characters and a single word.','comiceasel'); ?><br />
						<br />
					</td>
				</tr>
				<tr class="alternate">
					<?php if (empty($ceo_options['chapter_type_name_plural'])) $ceo_options['chapter_type_name_plural'] = 'chapters'; ?>
					<th scope="row"><label for="chapter_type_name_plural"><?php _e('Chapter name plural form?','comiceasel'); ?></label></th>
					<td>
						<input id="chapter_type_name_plural" name="chapter_type_name_plural" type="text" value="<?php echo $ceo_options['chapter_type_name_plural']; ?>" /><br />
					</td>
					<td>
						<?php _e('Default: "chapters" changing this will modify the description information of the plural form of what is put as the chapters slug.  For example if you change the chapters slug to "story" this would be "stories" - use lowercase.','comiceasel'); ?><br />
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