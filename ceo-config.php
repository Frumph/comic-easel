<?php
// Show Config Variables for right now
// var_dump(ceo_pluginfo());
?>
<div class="wrap">
<h2><?php _e('Comic Easel - Config','comiceasel'); ?></h2>
<table class="widefat">
<thead>
	<tr>
	<th colspan="3"><?php _e('Variables','comiceasel'); ?></th>
	</tr>
</thead>
<tr><td>error</td><td><?php echo ceo_pluginfo('error'); ?></td></tr>
<tr><td>base_url</td><td><?php echo ceo_pluginfo('base_url'); ?></td></tr>
<tr><td>base_path</td><td><?php echo ceo_pluginfo('base_path'); ?></td></tr>
<tr><td>theme_url</td><td><?php echo ceo_pluginfo('theme_url'); ?></td></tr>
<tr><td>theme_path</td><td><?php echo ceo_pluginfo('theme_path'); ?></td></tr>
<tr><td>style_url</td><td><?php echo ceo_pluginfo('style_url'); ?></td></tr>
<tr><td>style_path</td><td><?php echo ceo_pluginfo('style_path'); ?></td></tr>
<tr><td>plugin_url</td><td><?php echo ceo_pluginfo('plugin_url'); ?></td></tr>
<tr><td>plugin_path</td><td><?php echo ceo_pluginfo('plugin_path'); ?></td></tr>
<tr><td>comic_url</td><td><?php echo ceo_pluginfo('comic_url'); ?></td></tr>
<tr><td>comic_path</td><td><?php echo ceo_pluginfo('comic_path'); ?></td></tr>
<tr><td>thumbnail_medium_url</td><td><?php echo ceo_pluginfo('thumbnail_medium_url'); ?></td></tr>
<tr><td>thumbnail_medium_path</td><td><?php echo ceo_pluginfo('thumbnail_medium_path'); ?></td></tr>
<tr><td>thumbnail_small_url</td><td><?php echo ceo_pluginfo('thumbnail_small_url'); ?></td></tr>
<tr><td>thumbnail_small_path</td><td><?php echo ceo_pluginfo('thumbnail_small_path'); ?></td></tr>
<tr><td>comic_folder</td><td><?php echo ceo_pluginfo('comic_folder'); ?></td></tr>
<tr><td>comic_folder_medium</td><td><?php echo ceo_pluginfo('comic_folder_medium'); ?></td></tr>
<tr><td>comic_folder_small</td><td><?php echo ceo_pluginfo('comic_folder_small'); ?></td></tr>
<tr><td>medium_comic_width</td><td><?php echo ceo_pluginfo('medium_comic_width'); ?></td></tr>
<tr><td>small_comic_width</td><td><?php echo ceo_pluginfo('small_comic_width'); ?></td></tr>
<tr><td>add_dashboard_frumph_feed_widget</td><td><?php echo ceo_pluginfo('add_dashboard_frumph_feed_widget'); ?> (1 true, 0 false)</td></tr>
</table>
</div>