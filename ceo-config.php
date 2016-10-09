<div class="wrap">
	<div id="ceoadmin-headericon" style="background: url('<?php echo ceo_pluginfo('plugin_url') ?>images/easel_small.png') no-repeat;"></div>
<p class="alignleft">
	<h2><?php _e('Comic Easel - Config','comiceasel'); ?></h2>
</p>
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
		if ($_REQUEST['action'] == 'ceo_save_general') {

			foreach (array(
				'thumbnail_size_for_rss',
				'thumbnail_size_for_direct_rss',
				'thumbnail_size_for_archive',
				'thumbnail_size_for_facebook',
				'custom_post_type_slug_name',
				'chapter_on_home',
				'remove_post_thumbnail'
					) as $key) {
						if (isset($_REQUEST[$key])) {
							$ceo_options[$key] = wp_filter_nohtml_kses($_REQUEST[$key]);
						} elseif (empty($_REQUEST[$key]))
							$ceo_options[$key] = '';
			}

			foreach (array(
				'add_dashboard_frumph_feed_widget',
				'disable_comic_on_home_page',
				'disable_comic_blog_on_home_page',
				'enable_comments_on_homepage',
				'enable_comic_sidebar_locations',
				'disable_related_comics',
				'display_first_comic_on_home_page',
				'disable_style_sheet',
				'enable_transcripts_in_comic_posts',
				'enable_motion_artist_support',
				'enable_hoverbox',
				'allow_comics_to_have_categories'
			) as $key) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = 0;
				$ceo_options[$key] = (bool)( $_REQUEST[$key] == 1 ? true : false );
			}
			
			$tab = 'general';
			update_option('comiceasel-config', $ceo_options);
		}
		
		if ($_REQUEST['action'] == 'ceo_save_navigation') {

			foreach (array(
				'graphic_navigation_directory'
					) as $key) {
							if (isset($_REQUEST[$key])) 
								$ceo_options[$key] = wp_filter_nohtml_kses($_REQUEST[$key]);
			}

			foreach (array(
				'click_comic_next',
				'navigate_only_chapters',
				'enable_chapter_nav',
				'enable_comic_nav',
				'enable_comment_nav',
				'enable_random_nav',
				'enable_embed_nav',
				'disable_default_nav',
				'disable_mininav',
				'enable_nav_above_comic',
				'enable_chapter_only_random',
				'enable_prevnext_chapter_traversing',
				'disable_keynav',
				'default_nav_bar_chapter_goes_to_archive'
			) as $key) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = 0;
				$ceo_options[$key] = (bool)( $_REQUEST[$key] == 1 ? true : false );
			}
			
			$tab = 'navigation';
			update_option('comiceasel-config', $ceo_options);
		}

		if ($_REQUEST['action'] == 'ceo_save_archive') {

			foreach (array(
				'custom_post_type_slug_name',
				'chapter_type_slug_name',
				'chapter_type_name_plural'
					) as $key) {
							if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key])) 
								$ceo_options[$key] = strtolower(wp_filter_nohtml_kses($_REQUEST[$key]));
			}

			foreach (array(
				'include_comics_in_blog_archive',
				'disable_cal_rewrite_rules',
				'enable_chapter_in_url'
			) as $key) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = 0;
				$ceo_options[$key] = (bool)( $_REQUEST[$key] == 1 ? true : false );
			}
			
			$tab = 'archive';
			update_option('comiceasel-config', $ceo_options);
		}
		
		if ($_REQUEST['action'] == 'ceo_save_landing') {
			foreach (array(
				'enable_chapter_landing',
				'enable_chapter_landing_first',
				'enable_blog_on_chapter_landing',
				'enable_comments_on_chapter_landing'
			) as $key) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = 0;
				$ceo_options[$key] = (bool)( $_REQUEST[$key] == 1 ? true : false );
			}
			$tab = 'landing';
			update_option('comiceasel-config', $ceo_options);
		}
		
		if ($_REQUEST['action'] == 'ceo_save_buycomic') {

			foreach (array(
				'buy_comic_email',
				'buy_comic_url',
				'buy_comic_print_amount',
				'buy_comic_orig_amount'
					) as $key) {
						if (isset($_REQUEST[$key])) {
							$ceo_options[$key] = wp_filter_nohtml_kses($_REQUEST[$key]);
						} elseif (empty($_REQUEST[$key]))
							$ceo_options[$key] = '';
			}

			foreach (array(
				'enable_buy_comic',
				'buy_comic_sell_print',
				'buy_comic_sell_original'
			) as $key) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = 0;
				$ceo_options[$key] = (bool)( $_REQUEST[$key] == 1 ? true : false );
			}
			
			$tab = 'buycomic';
			update_option('comiceasel-config', $ceo_options);
		}
		
		if ($tab) { ?>
			<div id="message" class="updated"><p><strong><?php _e('Comic Easel Settings SAVED!','comiceasel'); ?></strong></p></div>
			<script>function hidemessage() { document.getElementById('message').style.display = 'none'; }</script>
		<?php }
	} 
	$ceo_options = get_option('comiceasel-config');
?>
	<div id="poststuff" class="metabox-holder">
		<div id="ceoadmin">
		  <?php
			$tab_info = array(
				'main' => __('Main', 'comiceasel'),
		  		'general' => __('General', 'comiceasel'),
		  		'navigation' => __('Navigation', 'comiceasel'),
				'archive' => __('Archive', 'comiceasel')
		  	);
			if (function_exists('comicpress_themeinfo')) $tab_info['landing'] = __('Landing Pages', 'comiceasel');
			if (!defined('CEO_FEATURE_BUY_COMIC'))
				$tab_info['buycomic'] = __('Buy Comic','comiceasel');
		  	if (empty($tab)) { $tab = 'main'; }

		  	foreach($tab_info as $tab_id => $label) { ?>
		  		<div id="comiceasel-tab-<?php echo $tab_id ?>" class="comiceasel-tab <?php echo ($tab == $tab_id) ? 'on' : 'off'; ?>"><span><?php echo $label; ?></span></div>
		  	<?php }
		  ?>
		</div>

		<div id="comiceasel-options-pages">
		  <?php	foreach (glob(ceo_pluginfo('plugin_path') . 'options/*.php') as $file) { include($file); } ?>
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

