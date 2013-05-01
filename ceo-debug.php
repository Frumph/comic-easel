<?php
// Show Config Variables for right now
// var_dump(ceo_pluginfo());

function ceo_check_directory($dirpath) {
	$output = '';
	if (is_dir(ceo_pluginfo($dirpath))) {
		$output = '<span style="color:green;">'.__('Directory Exists,','comiceasel').'</span>';
		if (is_writable(ceo_pluginfo($dirpath))) {
			$output .= ' <span style="color:green;">'.__('and is writable.','comiceasel').'</span>';
		} else {
			$output .= ' <span style="color:red;">'.__('and is not writable.','comiceasel').'</span>';
		}			
	} else {
		$output = '<span style="color:red;">'.__('Directory does not exist,','comiceasel').'</span>';
	}
	return $output;
}

?>
<div class="wrap">
<h2><?php _e('Comic Easel - Debug','comiceasel'); ?></h2>
<table class="widefat">
<thead>
	<tr>
	<th colspan="3"><?php _e('System Info','comiceasel'); ?></th>
	</tr>
</thead>
<tr><td>error</td><td><?php echo ceo_pluginfo('error'); ?></td></tr>
<tr><td>base_url</td><td><?php echo ceo_pluginfo('base_url'); ?></td></tr>
<tr><td>base_path</td><td><?php echo ceo_pluginfo('base_path'); ?><br /><?php echo ceo_check_directory('base_path'); ?></td></tr>
<tr><td>theme_url</td><td><?php echo ceo_pluginfo('theme_url'); ?></td></tr>
<tr><td>theme_path</td><td><?php echo ceo_pluginfo('theme_path'); ?></td></tr>
<tr><td>style_url</td><td><?php echo ceo_pluginfo('style_url'); ?></td></tr>
<tr><td>style_path</td><td><?php echo ceo_pluginfo('style_path'); ?></td></tr>
<tr><td>plugin_url</td><td><?php echo ceo_pluginfo('plugin_url'); ?></td></tr>
<tr><td>plugin_path</td><td><?php echo ceo_pluginfo('plugin_path'); ?></td></tr>
</table>
</div>
<br />
<table class="widefat">
<thead>
<tr>
<th colspan = "3">
	<?php _e('Variables', 'comiceasel'); ?>
</th>
</tr>
</thead>
<?php 
	$ceo_options = ceo_pluginfo();
	foreach ($ceo_options as $key => $val) { 
	if (!isset($ceo_options[$key]) || !$val) { $val = 'Empty or False'; }
	if ($val == '1') $val = 'True';
?>
		<tr>
			<td><?php echo $key; ?></td>
			<td><?php echo $val; ?></td>
		</tr>
<?php }
?>
</table>