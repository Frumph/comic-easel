<div class="wrap">
	<div id="ceoadmin-headericon" style="background: url('<?php echo ceo_pluginfo('plugin_url') ?>images/bf.png') no-repeat;"></div>
<div class="clear"></div>
<?php
$tab = '';
if (isset($_GET['tab'])) $tab = wp_filter_nohtml_kses($_GET['tab']);
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'comiceasel_reset') {
	delete_option('comiceasel-config');
	global $ceo_pluginfo;
	$ceo_pluginfo = array();
	ceo_load_options('reset');
	?>
			<div id="message" class="updated"><p><strong><?php _e('Comic Easel Settings RESET!','comiceasel'); ?></strong></p></div>
	<?php
}
		$ceo_options = get_option('comiceasel-config');
		if ( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'update-options') ) {
		if ($_REQUEST['action'] == 'ceo_save_afmain') {
			if (!empty($_REQUEST['bf_adinfo']) && empty($ceo_options['bf_adinf'])) {
				$path = get_home_path();
				$url = 'https://www.lfg.co/ads.txt';
				$permfile = $path.'ads.txt';
				$tmpfile = download_url( $url, $timeout = 300 );
				copy( $tmpfile, $permfile );
				unlink( $tmpfile ); // must unlink afterwards
			}
			foreach (array(
				'bf_adinfo'
					) as $key) {
						if (isset($_REQUEST[$key])) {
							$ceo_options[$key] = wp_filter_nohtml_kses($_REQUEST[$key]);
						} elseif (empty($_REQUEST[$key]))
							$ceo_options[$key] = '';
			}

			foreach (array(
				'bf_vidslider'
			) as $key) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = 0;
				$ceo_options[$key] = (bool)( $_REQUEST[$key] == 1 ? true : false );
			}
			
			$tab = 'afmain';
			update_option('comiceasel-config', $ceo_options);
		}
		
		if ($tab) { ?>
			<div id="message" class="updated"><p><strong><?php _e('Blind Ferret Advertising Settings SAVED!','comiceasel'); ?></strong></p></div>
			<script>function hidemessage() { document.getElementById('message').style.display = 'none'; }</script>
		<?php }
	} 
	$ceo_options = get_option('comiceasel-config');
?>
	<div id="poststuff" class="metabox-holder">
		<div id="ceoadmin">
		  <?php
			$tab_info = array(
				'afmain' => __('Blind Ferret Advertisements', 'comiceasel')
		  	);
		  	if (empty($tab)) { $tab = 'afmain'; }

		  	foreach($tab_info as $tab_id => $label) { ?>
		  		<div id="comiceasel-tab-<?php echo $tab_id ?>" class="comiceasel-tab <?php echo ($tab == $tab_id) ? 'on' : 'off'; ?>"><span><?php echo $label; ?></span></div>
		  	<?php }
		  ?>
		</div>

		<div id="comiceasel-options-pages">
		  <?php	foreach (glob(ceo_pluginfo('plugin_path') . 'monetize/*.php') as $file) { include($file); } ?>
		</div>
	</div>
	<script type="text/javascript">
		(function($) {
			var showPage = function(which) {
				$('#comiceasel-options-pages > div').each(function(i) {
					$(this)[(this.id == 'comiceasel-' + which) ? 'show' : 'hide']();
				});
			};

			$('.comiceasel-tab').click(function() {
				$('#message').animate({height:"0", opacity:0, margin: 0}, 100, 'swing', function() { $(this).remove() });

				showPage(this.id.replace('comiceasel-tab-', ''));
				var myThis = this;
				$('.comiceasel-tab').each(function() {
					var isSame = (this == myThis);
					$(this).toggleClass('on', isSame).toggleClass('off', !isSame);
				});
				return false;
			});

			showPage('<?php echo esc_js($tab); ?>');
		}(jQuery));
	</script>
</div>

	<div class="ceoadmin-footer">
		<br />
		<a href="http://comiceasel.com"><?php _e('Comic Easel','comiceasel'); ?></a> <?php _e('created, developed and maintained by','comiceasel'); ?> <a href="http://frumph.net/">Philip M. Hofer</a> <small>(<a href="http://frumph.net/">Frumph</a>)</small><br />
		<?php _e('If you like the Comic Easel plugin, please donate.  It will help in developing new features and versions.','comiceasel'); ?><br />
		<table style="margin:0 auto;">
			<tr>
				<td style="width:200px;">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick" />
						<input type="hidden" name="hosted_button_id" value="46RNWXBE7467Q" />
						<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" />
						<img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
					</form>
				</td>
				<td style="width:200px;">
					<form method="post" id="myForm" name="template" enctype="multipart/form-data" action="">
						<?php wp_nonce_field('update-options') ?>
						<input name="easel_reset" type="submit" class="button" value="Reset All Settings" />
						<input type="hidden" name="action" value="comiceasel_reset" />
					</form>
				</td>
			</tr>
		</table>
	</div>

