<div id="comiceasel-buycomic">

	<form method="post" id="myForm-archive" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options') ?>

		<div class="comiceasel-options">
		
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Buy Comic','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<th scope="row"><label for="enable_buy_comic"><?php _e('Enable the Buy Comic code?','comiceasel'); ?></label></th>
					<td>
						<input id="enable_buy_comic" name="enable_buy_comic" type="checkbox" value="1" <?php checked(true, $ceo_options['enable_buy_comic']); ?> />
					</td>
					<td>
						<?php _e('Once enabled and saved, more options will appear in the Navigation [tab] for the default navigation and the Navigation Widget.','comiceasel'); ?>
					</td>
				</tr>
			</table>
			<br />
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Buy Comic Info','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr>
					<th scope="row" colspan="2">
						<label for="buy_comic_email"><?php _e('Paypal email address','comiceasel'); ?></label>
						<input type="text" size="25" name="buy_comic_email" id="buy_comic_email" value="<?php echo $ceo_options['buy_comic_email']; ?>" />
					</th>
					<td>
						<span style="color: #d54e21;"><?php _e('* This must be correct, you do not want other people getting your money.','comiceasel'); ?></span><br />
						<?php _e('The Email address you registered with Paypal and that your store is associated with.','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"colspan="2">
						<label for="buy_comic_url"><?php _e('FULL URL of where the buy comic shortcode is. (required)','comiceasel'); ?></label>
						<input type="text" size="25" name="buy_comic_url" id="buy_comic_url" value="<?php echo $ceo_options['buy_comic_url']; ?>" />
					</th>
					<td>
						<span style="color: #d54e21;"><?php _e('* This must be correct, the form needs some place to go.','comiceasel'); ?></span><br />
						<?php _e('The FULL URL address to which you associated the buy comic shortcode.','comiceasel'); ?><br />
						<em>
							<?php _e('Examples:','comiceasel'); ?>
							"http://yourdomain.com/?p=233",
							"http://yourdomain.com/shop/",
						</em>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="buy_comic_sell_print"><?php _e('Are you selling prints?','comiceasel'); ?></label></th>
					<td>
						<input id="buy_comic_sell_print" name="buy_comic_sell_print" type="checkbox" value="1" <?php checked(true, $ceo_options['buy_comic_sell_print']); ?> />
					</td>
					<td>
						<?php _e('<strong>NOTE: If you want to add shipping you will have to do that from your profile on paypal.</strong>','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="buy_comic_print_amount"><?php _e('Print Cost','comiceasel'); ?></label></th>
					<td>
						<input type="text" size="7" name="buy_comic_print_amount" id="buy_comic_print_amount" value="<?php echo $ceo_options['buy_comic_print_amount']; ?>" />
					</td>
					<td>
						<?php _e('How much does a print cost?','comiceasel'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="buy_comic_sell_original"><?php _e('Are you selling the original?','comiceasel'); ?></label></th>
					<td>
						<input id="buy_comic_sell_original" name="buy_comic_sell_original" type="checkbox" value="1" <?php checked(true, $ceo_options['buy_comic_sell_original']); ?> />
					</td>
					<td>
						<?php _e('<strong>NOTE: If you want to add shipping you will have to do that from your profile on paypal.</strong>','comiceasel'); ?>
					</td>
				</tr>
				<tr class="alternate">
					<th scope="row"><label for="buy_comic_orig_amount"><?php _e('Original Cost','comiceasel'); ?></label></th>
					<td>
						<input type="text" size="7" name="buy_comic_orig_amount" id="buy_comic_orig_amount" value="<?php echo $ceo_options['buy_comic_orig_amount']; ?>" />
					</td>
					<td>
						<?php _e('How much are you selling the Original for? (Default price, can set individual prices in each comic post)','comiceasel'); ?>
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
					<input type="hidden" name="action" value="ceo_save_buycomic" />
				</div>
				<div class="clear"></div>
			</div>
		</div>

	</form>

</div>