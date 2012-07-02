<?php
// Start of the importer.
?>
<div class="wrap">
<h2><?php _e('Comic Easel - Import','cp2ce'); ?></h2>
<form method="post" id="myForm-import" name="template">
<?php wp_nonce_field('form-values') ?>
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
			<input name="import-type" class="import-type" type="radio" value="namedate" checked />
		</td>
		<td align="left" style="width:180px">
			<?php _e('by Filename w/date','comiceasel'); ?> <cite>(*1)</cite>
		</td>
		<td align="left" style="width:260px">
			<?php _e('Date Format','comiceasel'); ?><br />
			<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank"><?php _e('Documentation on date formats','comiceasel'); ?></a><br />
			<input name="import-date-format" class="import-date-format" value="Y-m-d" />
		</td>
		<td align="left" colspan="8">
			<?php _e('Date Mask','comiceasel'); ?><br />
			{DATE} <?php _e('get\'s replaced with the Date Format','comiceasel'); ?><br />
			<input name="import-date-mask" class="import-date-mask" value="{DATE}*.*" />
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
			<input name="import-create-post" class="import-create-post" type="checkbox" value="1" checked disabled="disabled" />
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
		echo '<span style="width:120px;display:inline-block;float:left;">'.basename($filename).'</span>';
	}
} else {
	echo "No files found in ".$directory_to_check;
}