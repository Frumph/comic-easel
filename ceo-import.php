<?php

// Set values used (changed if returned changed)
$import_directory = 'import';
$import_time = isset($_POST['import-time']) ? esc_attr( $_POST['import-time'] ) : '00:01';
$import_type = isset($_POST['import-type']) ? esc_attr( $_POST['import-type'] ) : true;
$import_date_format = isset($_POST['import-date-format']) ? esc_attr($_POST['import-date-format']) : 'Y-m-d';
$import_filename_mask = isset($_POST['import-date-mask']) ? esc_attr($_POST['import-date-mask']) : '{DATE}*.*';
$import_create_post = (isset($_POST['import-create-post']) && !empty($_POST['import-create-post'])) ? true : false;
$import_chapter = isset($_POST['import-chapter']) ? (int)$_POST['import-chapter'] : 0;

function ceo_transform_date_string($string, $replacements) {
	if (!is_array($replacements)) { return false; }
	if (!is_string($string)) { return false; }
	
	$transformed_string = $string;
	foreach (array("Y", "m", "d") as $required_key) {
		if (!isset($replacements[$required_key])) { return false; }
		$transformed_string = preg_replace('#(?<![\\\])' . $required_key . '#', $replacements[$required_key], $transformed_string);
	}
	
	$transformed_string = str_replace('\\', '', $transformed_string);
	return $transformed_string;
}

function ceo_breakdown_comic_filename($filename, $import_date_format, $import_filename_mask) {
	
	foreach (array('[0-9]{4}', '[0-9]{2}') as $year_pattern) {
		$new_pattern = ceo_transform_date_string($import_date_format, array("Y" => $year_pattern, "m" => '[0-9]{2}', "d" => '[0-9]{2}'));
		
		if (@preg_match("#^(${new_pattern})(.*)\.[^\.]+$#", $filename, $matches) > 0) {
			list($all, $date, $title) = $matches;
			
			if (strtotime($date) === false) { return false; }
			$converted_title = ucwords(trim(preg_replace('/[\-\_]/', ' ', $title)));
			$date = date($import_date_format, strtotime($date));
			
			if (is_numeric($converted_title)) {	$converted_title = "Title: ${converted_title}"; }
			return compact('filename', 'date', 'title', 'converted_title');
		}
	}
	return false;
}

function ceo_import_add_comic_and_post($comic_to_add, $date_to_add, $title_to_add, $import_create_post, $import_chapter, $import_time, $import_directory) {
	global $wpdb;
	// (TODO) check to see if the post already exists THEN do wp_insert_post below // $import_create_post
	// get the $timestamp set correctly
	$post_date = date('Y-m-d H:i:s', strtotime($date_to_add .= " " . $import_time));
	$post_args = array(
			'post_name'		=> sanitize_title($title_to_add),
			'post_title'	=> $title_to_add,
			'post_date'		=> $post_date,
			'post_type'		=> 'comic',
			'post_status'	=> 'publish',
			'tax_input'		=> array(
				'chapters' => array($import_chapter)
				)
			);

	$post_id = wp_insert_post($post_args);
	if (!is_wp_error($post_id)) {
		// Attach the Comic Now
		$file_url = (is_multisite) ? esc_url(network_home_url().'/'.$import_directory.'/'.$comic_to_add) : esc_url(home_url().'/'.$import_directory.'/'.$comic_to_add);
		$comic = new WP_Http();
		$comic = $comic->request( $file_url );
		if (!is_wp_error($comic)) {
			$comic_date = isset($comic['headers']['last-modified']) ? $comic['headers']['last-modified'] : $comic['headers']['date'];
			$attachment = wp_upload_bits( $comic_to_add, null, $comic['body'], date("Y-m", strtotime( $comic_date ) ) );
			if (!is_wp_error($attachment)) {
				$filetype = wp_check_filetype( basename( $attachment['file'] ), null );
				$postinfo_args = array(
						'guid' => $attachment['file'],
						'post_mime_type'	=> $filetype['type'],
						'post_title'		=> 'comic-'.$comic_to_add,
						'post_content'		=> '',
						'post_status'		=> 'inherit',
						);
				$attached_filename = $attachment['file'];
				$attach_id = wp_insert_attachment( $postinfo_args, $attached_filename, $post_id );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $attached_filename );
				wp_update_attachment_metadata($attach_id,  $attach_data);
				// set it as the post featured image
				add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
				set_post_thumbnail($post_id, $attach_id);
				return __('Comic Post made and Comic Attached','comiceasel');
				
			} else return $attachment->get_error_message();
		} else return $comic->get_error_message();
	} else return $post_id->get_error_message();
	return false;
}

function ceo_import_by_namedate($import_directory, $import_date_format, $import_filename_mask, $import_create_post, $import_chapter, $import_time) {
	echo '<strong><h2>'.__('Import type: by Filename w/date','comiceasel').'</strong></h2>';
	$file_list = array();
	if (count($results = glob(ABSPATH.$import_directory.'/*.*')) > 0) {
		echo count($results).' '.__('Files found.','comiceasel')."<br />\r\n";
		natcasesort($results);
		foreach ($results as $filename) {
			$breakdown = ceo_breakdown_comic_filename(basename($filename), $import_date_format, $import_filename_mask);
			if ($breakdown && is_array($breakdown)) $file_list[] = $breakdown;
		}
		echo count($file_list).' '.__('files have valid naming and can be imported.','comiceasel');
	} else {
		echo __('No files found in','comiceasel').' '.ABSPATH.$import_directory.' '.__('or directory does not exist','comiceasel').'<br />';
		return;
	}
	echo "<hr />\r\n";
	if (count($file_list)) {
		$chapter_object = get_term_by('id', $import_chapter, 'chapters');
		echo __('Processing Files...','comiceasel').' '.__('Importing to chapter:','comiceasel').' <strong>'.$chapter_object->name.'</strong><br />'."\r\n";
		echo '<table>';
		foreach ($file_list as $file_to_process) {
			echo '<tr>';
			if (empty($file_to_process['converted_title'])) $file_to_process['converted_title'] = $file_to_process['date'];
			$result = ceo_import_add_comic_and_post($file_to_process['filename'], $file_to_process['date'], $file_to_process['converted_title'], $import_create_post, $import_chapter, $import_time, $import_directory);
			$result = ($result) ? $result : __('System Process Error','comiceasel');
			echo '<td>'.__('Date:','comiceasel').' <strong>'.$file_to_process['date'].'</strong></td><td> ---- </td><td>'.__('Title of Post:','comiceasel').' <strong>'.$file_to_process['converted_title'].'</strong></td><td> --- </td><td>'.__('Result:','comiceasel').' <strong>'.$result.'</strong></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
}



// Catch the return $_POST and do something with them.
if ( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'comiceasel-import') ) {
	switch ($import_type) {
		case 'namedate':
			ceo_import_by_namedate($import_directory, $import_date_format, $import_filename_mask, $import_create_post, $import_chapter, $import_time);
			break;
		default:
			_e('ERROR: No selection made.', 'comiceasel');
			break;
	}
}

?>
<div class="wrap">
<h2><?php _e('Comic Easel - Import','comiceasel'); ?></h2>
<form method="post" id="myForm-import" name="template">
<?php wp_nonce_field('comiceasel-import') ?>
<table class="widefat">
<thead>
	<tr>
	<th colspan="10"><?php _e('Options','comiceasel'); ?></th>
	</tr>
<?php 
	/*
	<tr>
		<td valign="top" align="right" style="width:20px">
			<input name="import-type" class="import-type" type="radio" value="date" disabled="disabled" />
		</td>
		<td valign="top" align="left" colspan="12">
			<?php _e('by Date Stamp of file','comiceasel'); ?>
		</td>
	</tr>
	*/
?>
	<tr>
		<td valign="top" align="right">
			<input name="import-type" class="import-type" type="radio" value="namedate" <?php checked($import_type, true); ?> />
		</td>
		<td align="left" style="width:180px">
			<?php _e('by Filename w/date','comiceasel'); ?> <cite>(*1)</cite>
		</td>
		<td align="left" style="width:260px">
			<?php _e('Date Format','comiceasel'); ?><br />
			<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank"><?php _e('Documentation on date formats','comiceasel'); ?></a><br />
			<input name="import-date-format" class="import-date-format" value="<?php echo $import_date_format; ?>" />
		</td>
		<td align="left" style="width:240px;">
			<?php _e('Filename Mask','comiceasel'); ?><br />
			{DATE} <?php _e('will be replaced','comiceasel'); ?><br />
			<input name="import-date-mask" class="import-date-mask" value="<?php echo $import_filename_mask; ?>" />
		</td>
		<td align="left" colspan="9">
			<?php _e('Set Time of Comic Posts','comiceasel'); ?><br />
			<?php _e('24 hour clock','comiceasel'); ?><br />
			<input name="import-time" class="import-time" value="<?php echo $import_time; ?>" />
		</td>
	</tr>
	<tr>
		<td colspan="13">
		<?php echo '<center><strong>'.__('WARNING: When uploading your comics to the import directory, make sure there is only 1 comic per day, multiple comics per day will have issues.','comiceasel').'</strong></center>';  ?>
		</td>
	</tr>
<?php 
/*
	<tr>
		<td valign="top" align="right">
			<input name="import-type" class="import-type" type="radio" value="numbered" disabled="disabled" />
		</td>
		<td align="left">
			<?php _e('by Numbered filename','comiceasel'); ?>
		</td>
		<td align="left">
			<?php _e('Filename Mask','comiceasel'); ?><br />
			{NUM} <?php _e('gets replaced with the comic number, starting at 1 - use Backdate Files option', 'comiceasel'); ?><br />
			<input name="import-filename-mask" class="import-filename-mask" value="comic{NUM}.*" disabled="disabled" />
		</td>
		<td align="left">
			<input name="import-backdate" class="import-backdate" type="checkbox" value="1" checked disabled="disabled" /> <?php _e('Backdate Files', 'comiceasel'); ?><br />
			<input name="import-backdate-how" class="import-backdate-how-everyday" type="radio" value="everyday" disabled="disabled" /> Everyday<br />
			<input name="import-backdate-how" class="import-backdate-how-weekdays" type="radio" value="weekdays" disabled="disabled" /> Weekdays<br />			
			<input name="import-backdate-how" class="import-backdate-how-mwf" type="radio" value="mwf" disabled="disabled" /> MWF
		</td>
	</tr>
*/
?>
<?php 
/*
	<tr>
		<td align="right">
			<input name="import-create-post" class="import-create-post" type="checkbox" value="1" <?php checked($import_create_post, true); ?> />
		</td>
		<td align="left">
			<?php _e('Import even if comic post already exists for that date.','comiceasel'); ?>
		</td>
		<td align="left" colspan="11">
			<?php _e('Having this NOT checked (default) will make it so that if there is already a comic for that date it will not add another one.', 'comiceasel'); ?>
		</td>
	</tr>
*/
?>
	<tr>
		<td align="right">
		</td>
		<td align="left">
<?php 
$args = array(
		'name'			=> 'import-chapter',
		'orderby'		=> 'name',
		'order'			=> 'term_group',
		'selected'		=> $import_chapter,
		'hide_empty'	=> 0,
		'hierarchical'	=> 1,
		'pad_counts'	=> 1,
		'taxonomy'		=> 'chapters',
		'class'			=> 'chapter-list' );
wp_dropdown_categories( $args ); 
?>

		</td>
		<td align="left" colspan="11">
			<?php _e('Select a chapter the comics will go in.  If there are multiple chapters, then modify the comics in the import directory and only add the ones you want for the chapter selected.  You need to create the chapter first in the comics - chapters area.', 'comiceasel'); ?>
		</td>
	</tr>
</thead>
</table>
<p class="submit" style="margin-left: 10px;">
	<input type="submit" class="button-primary" value="<?php _e('Import','comiceasel') ?>" />
	<input type="hidden" name="action" value="ceo-import" />
</p>
<p>
<?php 
echo '<h3>'.__('DIRECTIONS:','comiceasel').'</h3>';
echo '<ol>';
echo '<li>'.__('FTP into your site and create a directory named','comiceasel').' <strong>"'.$import_directory.'"</strong> '.__('off of the root installation folder of your site.','comiceasel').'</li>';
echo '<li>'.__('Upload into that directory the comics using the ComicPress filename convention for each individual chapter you are importing.','comiceasel').'</li>';
echo '<li>'.__('Make sure the chapter the comics are going into is created in the comic - chapters section of the wp-admin.','comiceasel').'</li>';
echo '<li>'.__('Select chapter in the import section, modify any other values as needed, if you do not know what they are, do not modify them.','comiceasel').'</li>';
echo '<li>'.__('Press the Import button and wish for the best.','comiceasel').'</li>';
echo '</ol>'
?>
</p>
<cite>*1</cite> - <?php _e('eg, ComicPress style filenames, if Date Format is: Y-m-d and Date Mask is {DATE}*.* the filename would be (as example if image is a .jpg):','comiceasel'); ?> <strong><?php _e('2012-01-01-title-of-comic.jpg','comiceasel'); ?></strong><br />
<cite>*2</cite> - <?php _e('ComicPress file convention requires that each comic is its own date., do not have comics uploaded on the same date.  While Comic Easel does not require each comic to have its own date, the Import does.  There will be other methods of importing made in the future that this will not be an issue.','comiceasel'); ?><br />
</p>
<br />
<?php
echo __('Directory to scan:','comiceasel').'&nbsp;<strong>'.ABSPATH.$import_directory.'</strong>'."<br />\r\n";
if (count($results = glob(ABSPATH.$import_directory.'/*.*')) > 0) {
	echo count($results).' '.__('Files found.','comiceasel')."<br />\r\n";
	echo "<hr />\r\n";
	natcasesort($results);
	foreach ($results as $filename) {
		echo '<span style="width:320px;display:inline-block;float:left;">'.basename($filename).'</span>';
	}
} else {
	echo __('No files found in','comiceasel').'&nbsp;'.ABSPATH.$import_directory;
}