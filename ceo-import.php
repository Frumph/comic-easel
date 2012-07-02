<?php

function ceo_import_by_namedate($import_date_format = 'Y-m-d', $import_date_mask = '{DATE}*.*', $import_create_post = true) {
	echo '<strong>'.__('Import type: by Filename w/date','comiceasel').'</strong><br />';
}

// Set Defaults or overrides if coming back from $_POST
$import_type = isset($_POST['import-type']) ? esc_attr( $_POST['import-type'] ) : '';
$import_date_format = isset($_POST['import-date-format']) ? esc_attr($_POST['import-date-format']) : 'Y-m-d';
$import_date_mask = isset($_POST['import-date-mask']) ? esc_attr($_POST['import-date-mask']) : '{DATE}*.*';
$import_create_post = (isset($_POST['import-create-post']) && $_POST['import-create-post']) ? true : false;
// Catch the return $_POST and do something with them.
if ( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'comiceasel-import') ) {
	switch ($import_type) {
		case 'namedate':
			ceo_import_by_namedate($import_date_format, $import_date_mask, $import_create_post);
			break;
		default:
			break;
	}
}

?>
<div class="wrap">
<h2><?php _e('Comic Easel - Import','cp2ce'); ?></h2>
<form method="post" id="myForm-import" name="template">
<?php wp_nonce_field('comiceasel-import') ?>
<table class="widefat">
<thead>
	<tr>
	<th colspan="10"><?php _e('Options','cp2ce'); ?></th>
	</tr>
	<tr>
		<td valign="top" align="right" style="width:20px">
			<input name="import-type" class="import-type" type="radio" value="date" disabled="disabled" />
		</td>
		<td valign="top" align="left" colspan="12">
			<?php _e('by Date Stamp of file','comiceasel'); ?>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right">
			<input name="import-type" class="import-type" type="radio" value="namedate" <?php checked($import_type, 'namedate'); ?> />
		</td>
		<td align="left" style="width:180px">
			<?php _e('by Filename w/date','comiceasel'); ?> <cite>(*1)</cite>
		</td>
		<td align="left" style="width:260px">
			<?php _e('Date Format','comiceasel'); ?><br />
			<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank"><?php _e('Documentation on date formats','comiceasel'); ?></a><br />
			<input name="import-date-format" class="import-date-format" value="<?php echo $import_date_format; ?>" />
		</td>
		<td align="left" colspan="8">
			<?php _e('Date Mask','comiceasel'); ?><br />
			{DATE} <?php _e('get\'s replaced with the Date Format','comiceasel'); ?><br />
			<input name="import-date-mask" class="import-date-mask" value="<?php echo $import_date_mask; ?>" />
		</td>
	</tr>
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
	<tr>
		<td align="right">
			<input name="import-create-post" class="import-create-post" type="checkbox" value="1" <?php checked($import_create_post, true); ?> />
		</td>
		<td align="left" colspan="12">
			<?php _e('Create post where applicable.', 'comiceasel'); ?>
		</td>
	</tr>		
</thead>
</table>
<cite>#1</cite> <?php _e('eg, ComicPress style filenames, if Date Format is: Y-m-d and Date Mask is {DATE}*.*','comiceasel'); ?><br />
<?php _e('the filename would be (as example if image is a .jpg):','comiceasel'); ?> <strong><?php _e('2012-01-01-title-of-comic.jpg','comiceasel'); ?></strong><br />
<p class="submit" style="margin-left: 10px;">
	<input type="submit" class="button-primary" value="<?php _e('Import') ?>" />
	<input type="hidden" name="action" value="ceo_import" />
</p>
<br />
<?php
$directory_to_check = ABSPATH.'import/';
echo __('Directory to scan: ','comiceasel').'<strong>'.$directory_to_check.'</strong>'."<br />\r\n";
if (count($results = glob($directory_to_check.'*.*')) > 0) {
	echo count($results).' '.__('Files found.','comiceasel')."<br />\r\n";
	echo "<hr />\r\n";
	natcasesort($results);
	foreach ($results as $filename) {
		echo '<span style="width:320px;display:inline-block;float:left;">'.basename($filename).'</span>';
	}
} else {
	echo "No files found in ".$directory_to_check;
}