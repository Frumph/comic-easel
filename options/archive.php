<?php if (!defined('ABSPATH')) exit; ?>
<div id="comiceasel-archive">

	<form method="post" id="myForm-archive" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>

		<div class="comiceasel-options">
			<div style='color: #b00;font-size: 16px;text-align:center;background-color: #efefef;padding: 5px;border: solid 2px #000;font-weight: 700;'><?php esc_html_e('IMPORTANT - If you change any of the settings on this page from the default, go to settings -> permalink and click SAVE so that the permalink structure can be recognized by WordPress','comic-easel'); ?></div>
			<br />
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php esc_html_e('Archive Options','comic-easel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row"><label for="include_comics_in_blog_archive"><?php esc_html_e('Include the comics in the blog archive?','comic-easel'); ?></label></th>
					<td>
						<input id="include_comics_in_blog_archive" name="include_comics_in_blog_archive" type="checkbox" value="1" <?php checked(true, $ceo_options['include_comics_in_blog_archive']); ?> />
					</td>
					<td>
						<?php esc_html_e('When this is enabled, when you search through the year/date/month and other archiving WordPress functions, the comic will appear with the regular blog posts.  This feature automatically works for all tags already.','comic-easel'); ?>
					</td>
				</tr>
				<tr>
					<?php if (!isset($ceo_options['disable_cal_rewrite_rules'])) $ceo_options['disable_cal_rewrite_rules'] = false; ?>
					<th scope="row"><label for="disable_cal_rewrite_rules"><?php esc_html_e('Disable the the rewrite rules so numerical slugs do not get turned into dates?','comic-easel'); ?></label></th>
					<td>
						<input id="disable_cal_rewrite_rules" name="disable_cal_rewrite_rules" type="checkbox" value="1" <?php checked(true, $ceo_options['disable_cal_rewrite_rules']); ?> />
					</td>
					<td>
						<?php esc_html_e('This option disables the url line from interpreting numerical numbers as dates. ex. /comic/2014/','comic-easel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<?php if (!isset($ceo_options['enable_chapter_in_url'])) $ceo_options['enable_chapter_in_url'] = false; ?>
					<th scope="row"><label for="enable_chapter_in_url"><?php esc_html_e('Allow the URL to also denote the chapter?','comic-easel'); ?></label></th>
					<td>
						<input id="enable_chapter_in_url" name="enable_chapter_in_url" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_chapter_in_url']); ?> />
					</td>
					<td>
						<?php esc_html_e('Allows the chapter that the comic is in to show up in the URL of the comic. ex: /comic/chapter-slug/postname/','comic-easel'); ?>
					</td>
				</tr>
			</table>
			<br />
			<table class="widefat">
			<thead>
				<tr>
					<th colspan="3"><?php esc_html_e('Comic Post Type','comic-easel'); ?></th>
				</tr>
			</thead>
				<tr class="alternate">
					<?php if (empty($ceo_options['custom_post_type_slug_name'])) $ceo_options['custom_post_type_slug_name'] = 'comic'; ?>
					<th scope="row"><label for="custom_post_type_slug_name"><?php esc_html_e('Custom Post Type slug name?','comic-easel'); ?></label></th>
					<td>
						<input id="custom_post_type_slug_name" name="custom_post_type_slug_name" type="text" value="<?php echo esc_attr($ceo_options['custom_post_type_slug_name']); ?>" /><br />
<?php 
$check_term = term_exists($ceo_options['custom_post_type_slug_name']);
if ($check_term) { ?>
	<span style="font-weight: 700; color: #f00;"><?php esc_html_e('This slug already exists and will cause problems.  Change it.','comic-easel'); ?></span>
<?php if ($ceo_options['custom_post_type_slug_name'] == 'comic') { ?>
	<br /><?php esc_html_e('This is the default custom post type slug - which is already in use on your system.  Sometimes people have the chapter name as this slug, that needs to be changed if you want to use the default.','comic-easel'); ?>
	<?php } 
}
?>
					</td>
					<td>
						<?php esc_html_e('Default: "comic" changing this will modify the permalink name for the /comic/ how it is addressed in the url.  This is a slug name, no slashes or spaces allowed; only alpha characters and a single word.','comic-easel'); ?><br />
						<br />
					</td>
				</tr>
				<tr>
					<?php if (empty($ceo_options['chapter_type_slug_name'])) $ceo_options['chapter_type_slug_name'] = 'comic'; ?>
					<th scope="row"><label for="chapter_type_slug_name"><?php esc_html_e('Chapter Type slug name?','comic-easel'); ?></label></th>
					<td>
						<input id="chapter_type_slug_name" name="chapter_type_slug_name" type="text" value="<?php echo esc_attr($ceo_options['chapter_type_slug_name']); ?>" /><br />
<?php 
$check_term = term_exists($ceo_options['chapter_type_slug_name']);
if ($check_term) { ?>
	<span style="font-weight: 700; color: #f00;"><?php esc_html_e('This slug already exists and will cause problems.  Change it.','comic-easel'); ?></span>
<?php if ($ceo_options['chapter_type_slug_name'] == 'chapter') { ?>
	<br /><?php esc_html_e('This is the default chapter slug - which is already in use on your system.','comic-easel'); ?>
	<?php } 
}
?>
					</td>
					<td>
						<?php esc_html_e('Default: "chapter" changing this will modify the permalink name for the /chapter/ how it is addressed in the url.  This is a slug name, no slashes or spaces allowed; only alpha characters and a single word.','comic-easel'); ?><br />
						<br />
					</td>
				</tr>
				<tr class="alternate">
					<?php if (empty($ceo_options['chapter_type_name_plural'])) $ceo_options['chapter_type_name_plural'] = 'chapters'; ?>
					<th scope="row"><label for="chapter_type_name_plural"><?php esc_html_e('Chapter name plural form?','comic-easel'); ?></label></th>
					<td>
						<input id="chapter_type_name_plural" name="chapter_type_name_plural" type="text" value="<?php echo esc_attr($ceo_options['chapter_type_name_plural']); ?>" /><br />
					</td>
					<td>
						<?php esc_html_e('Default: "chapters" changing this will modify the description information of the plural form of what is put as the chapters slug.  For example if you change the chapters slug to "story" this would be "stories" - use lowercase.','comic-easel'); ?><br />
					</td>
				</tr>				

			</table>
			<br />	
		</div>
		
		<br />
		<div class="ceo-options-save">
			<div class="ceo-major-publishing-actions">
				<div class="ceo-publishing-action">
					<input name="ceo_save_config" type="submit" class="button-primary" value="<?php esc_attr_e('Save Settings', 'comic-easel'); ?>" />
					<input type="hidden" name="action" value="ceo_save_archive" />
				</div>
				<div class="clear"></div>
			</div>
		</div>

	</form>

</div>