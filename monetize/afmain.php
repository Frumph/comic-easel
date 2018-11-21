<div id="comiceasel-afmain">
	<form method="post" id="myForm-archive" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>
<?php
// 			'bf_adinfo' => '',
//			'bf_vidslider' => false
//
?>
		<div class="comiceasel-options">
			<table class="widefat">
				<tr>
					<td>
						<?php _e('Submit your application!','comiceasel'); ?>
						<br />
					</td>
					<td>
						<a href="https://blindferret.com/sign-up/?affid=frumph" class="butstyled">Apply now!</a>
					</td>
					<td>
						<?php _e("To start monetizing your site you need to be approved by Blind Ferret Ads.  Simply complete the questionaire and we'll be in touch with you soon with your Site ID.  Once you get your Site ID input it in the box below and you'll be good to go!", 'comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="bf_adinfo"><?php _e('Site ID:','comiceasel'); ?></label></th>
					<td>
						<input id="bf_adinfo" name="bf_adinfo" type="text" value="<?php echo $ceo_options['bf_adinfo']; ?>" /><br />
					</td>
				</tr>				
				<tr>
				<tr>
					<th scope="row"><label for="bf_vidslider"><?php _e('Enable Video Slider?','comiceasel'); ?></label></th>
					<td>
						<input id="bf_vidslider" name="bf_vidslider" type="checkbox" value="1" <?php checked(true, $ceo_options['bf_vidslider']); ?> />
					</td>
				</tr>
			</table>
			<br />
		<div class="ceo-options-save">
			<div class="ceo-major-publishing-actions">
				<div class="ceo-publishing-action">
					<input name="ceo_save_config" type="submit" class="button-primary" value="<?php _e('Save Settings', 'comiceasel'); ?>" />
					<input type="hidden" name="action" value="ceo_save_afmain" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<br />
			<table class="widefat">
				<thead>
					<td>
						<?php _e('Notes','comiceasel'); ?>
					</td>
				</thead>
				<tr>
					<td>
							<?php _e('1. When adding your Site ID for the first time, a file ads.txt will be retrieved and placed into the root directory of your WordPress installation, this is not a malware, just a text file.','comiceasel'); ?><br />
							<?php _e('2. The DIV ID for use with the AF Ad widget is custom for each person, the div ID is found on your console of information that you will receive from Blind Ferret Advertisements.','comiceasel'); ?>
					</td>
				</tr>
			</table>
			<br />
		</div>
		<br />
	</form>

</div>
