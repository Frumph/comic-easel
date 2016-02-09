<?php

// Make sure the injections happen at init time instead of before so other plugins are known
add_action('plugins_loaded', 'ceo_init_injections');

function ceo_init_injections() {
	// Injected with a poison.
	add_action('wp_head', 'ceo_version_meta');
	add_action('comic-post-foot', 'ceo_display_edit_link');
	add_action('comic-area', 'ceo_display_comic_area');
	add_action('comic-post-info', 'ceo_display_comic_post_info');
// 	add_action('comic-mini-navigation', 'ceo_inject_mini_navigation');
	if (!ceo_pluginfo('disable_related_comics') && !defined('CEO_FEATURE_DISABLE_RELATED')) 
		add_action('comic-post-extras', 'ceo_display_related_comics');
	if (!defined('CEO_FEATURE_DISABLE_TRANSCRIPT')) 
		add_action('comic-transcript', 'ceo_display_the_transcript_action');
	add_action('wp_head', 'ceo_social_meta');

	// Jetpack Mobile Theme Addition
	if( class_exists('Jetpack') && Jetpack::is_module_active('minileven')) {
		add_action('jetpack_mobile_header_after', 'ceo_display_comic_area');
		add_action('jetpack_mobile_header_after', 'ceo_display_comic_post_home');
	}
}

add_action('comic-blog-area', 'ceo_display_comic_post_home');

function ceo_version_meta() {
	echo apply_filters('ceo_version_meta', '<meta name="Comic-Easel" content="'.ceo_pluginfo('version').'" />'."\r\n");
	echo apply_filters('ceo_version_meta_referrer', '<meta name="Referrer" content="'.ceo_get_referer().'" />'."\r\n");
}

function ceo_display_edit_link() {
	global $post;
	if (($post->post_type == 'comic') && current_user_can('edit_post', $post->ID)) {
		echo '<a href="'.get_edit_post_link().'">'.__('Edit Comic.','comiceasel')."</a><br />\r\n";
	}
}

// We use this type of query so that $post is set, it's already set with is_single - but needs to be set on the home page
function ceo_display_comic_area() {
	global $wp_query, $post;
	if (is_single()) {
		ceo_display_comic_wrapper();
	} else {
		if ((is_home() || is_front_page()) && !is_paged() && !ceo_pluginfo('disable_comic_on_home_page'))  {
			ceo_protect();
			$chapter_on_home = '';
			$chapter_on_home = get_term_by( 'id', ceo_pluginfo('chapter_on_home'), 'chapters');
			$chapter_on_home = (!is_wp_error($chapter_on_home) && !empty($chapter_on_home)) ? $chapter_on_home->slug : '';
			$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'asc' : 'desc';
			$comic_args = array(
					'showposts' => 1,
					'posts_per_page' => 1,
					'post_type' => 'comic',
					'order' => $order,
					'chapters' => $chapter_on_home
					);
			$comicFrontpage = new WP_Query(); $comicFrontpage->query($comic_args);
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
				ceo_display_comic_wrapper();
			endwhile;
			ceo_unprotect();
		} elseif (is_archive() && (isset($wp_query->query_vars['taxonomy']) && isset($wp_query->query_vars['chapters'])) && ($wp_query->query_vars['taxonomy'] == 'chapters') && ceo_pluginfo('enable_chapter_landing') && !is_paged()) {
			ceo_protect();
			$order = (ceo_pluginfo('enable_chapter_landing_first')) ?  'asc' : 'desc';
			$comic_args = array(
					'showposts' => 1,
					'posts_per_page' => 1,
					'post_type' => 'comic',
					'order' => $order,
					'chapters' => $wp_query->query_vars['chapters']
					);
			$comicFrontpage = new WP_Query(); $comicFrontpage->query($comic_args);
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
				ceo_display_comic_wrapper();
			endwhile;
			ceo_unprotect();
		}
	} 
}

function ceo_display_comic_post_info() {
	echo '<div class="comic-post-info">';
	ceo_display_comic_chapters();
	ceo_display_comic_characters();
	ceo_display_comic_locations();
	echo '</div>';
	
}

function ceo_display_comic_chapters() {
	global $post;
	if ($post->post_type == 'comic') {
		$before = '<div class="comic-chapter">'.ucwords(ceo_pluginfo('chapter_type_slug_name')).': ';
		$sep = ', '; 
		$after = '</div>';
		echo apply_filters('ceo_display_comic_chapters', get_the_term_list( $post->ID, 'chapters', $before, $sep, $after ));
	}
}

function ceo_display_comic_navigation() {
	global $post, $wp_query;
	if (ceo_pluginfo('navigate_only_chapters')) {
		$first_comic = ceo_get_first_comic_in_chapter_permalink();
		$last_comic = ceo_get_last_comic_in_chapter_permalink();
		$next_comic = ceo_get_next_comic_in_chapter_permalink();
		$prev_comic = ceo_get_previous_comic_in_chapter_permalink();		
	} else {
		$first_comic = ceo_get_first_comic_permalink();
		$last_comic = ceo_get_last_comic_permalink();
		$next_comic = ceo_get_next_comic_permalink();
		$prev_comic = ceo_get_previous_comic_permalink();
	}
	$first_text = __('&lsaquo;&lsaquo; First','comiceasel');
	$last_text = __('Last &rsaquo;&rsaquo;','comiceasel'); 
	$next_text = __('Next &rsaquo;','comiceasel');
	$prev_text = __('&lsaquo; Prev','comiceasel');
	
	?>
	<table id="comic-nav-wrapper">
		<tr class="comic-nav-container">
			<td class="comic-nav"><?php if ( get_permalink() != $first_comic ) { ?><a href="<?php echo $first_comic ?>" class="comic-nav-base comic-nav-first<?php if ( get_permalink() == $first_comic ) { ?> comic-nav-inactive<?php } ?>"><?php echo $first_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-first comic-nav-void">'.$first_text.'</span>'; } ?></td>
			<td class="comic-nav"><?php if ($prev_comic) { ?><a href="<?php echo $prev_comic ?>" class="comic-nav-base comic-nav-previous<?php if (!$prev_comic) { ?> comic-nav-inactive<?php } ?>"><?php echo $prev_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-previous comic-nav-void ">'.$prev_text.'</span>'; } ?></td>
<?php
		if (ceo_pluginfo('enable_buy_comic') && !wp_is_mobile()) {
			if (strpos(ceo_pluginfo('buy_comic_url'), '?') !== false) {
				$bpsep = '&';
			} else {
				$bpsep = '?';
			}
		?>
		<td class="comic-nav"><a href="<?php echo ceo_pluginfo('buy_comic_url').$bpsep.'id='.$post->ID; ?>" class="comic-nav-base comic-nav-buycomic" title="Buy Comic"><?php _e('Buy!','comiceasel'); ?></a></td>
<?php }
if (ceo_pluginfo('enable_comment_nav') && !wp_is_mobile()) { 
			$commentscount = get_comments_number(); ?>
			<td class="comic-nav"><a href="<?php comments_link(); ?>" class="comic-nav-comments" title="<?php the_title(); ?>"><span class="comic-nav-comment-count"><?php echo sprintf( _n( 'Comment(%d)', 'Comments(%d)', $commentscount, 'comiceasel' ), $commentscount ); ?></span></a></td>
<?php } 
	if (ceo_pluginfo('enable_random_nav') && !wp_is_mobile()) { 
		$stay = '';
		if (ceo_pluginfo('enable_chapter_only_random')) {
			$chapter = get_the_terms($post->ID, 'chapters');
			if (!empty($chapter) && !is_wp_error($chapter)) $stay = '&stay='.reset($chapter)->term_id;
		}
?>
			<td class="comic-nav"><a href="<?php bloginfo('url') ?>?random&nocache=1<?php echo $stay; ?>" class="comic-nav-random" title="Random Comic"><?php _e('Random','comiceasel'); ?></a></td>
<?php } ?>
	<td class="comic-nav"><?php if ($next_comic) { ?><a href="<?php echo $next_comic ?>" class="comic-nav-base comic-nav-next<?php if (!$next_comic) { ?> comic-nav-inactive<?php } ?>"><?php echo $next_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-next comic-nav-void ">'.$next_text.'</span>'; } ?></td>
	<td class="comic-nav"><?php if ( get_permalink() != $last_comic ) { ?><a href="<?php echo $last_comic ?>" class="comic-nav-base comic-nav-last<?php if ( get_permalink() == $last_comic ) { ?> comic-nav-inactive<?php } ?>"><?php echo $last_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-last comic-nav-void ">'.$last_text.'</span>'; } ?></td>
<?php if (ceo_pluginfo('enable_chapter_nav')) { ?>				
			<td class="comic-nav comic-nav-jumpto"><?php ceo_comic_archive_jump_to_chapter(true, '', false, ceo_pluginfo('default_nav_bar_chapter_goes_to_archive')); ?></td>
<?php } 
		if (ceo_pluginfo('enable_comic_nav')) { ?>				
			<td class="comic-nav comic-nav-jumptocomic"><?php ceo_list_jump_to_comic(); ?></td>
<?php } ?>
		</tr>
<?php if (ceo_pluginfo('enable_embed_nav') && !wp_is_mobile()) { ?>
		<tr>
			<td class="comic-nav" colspan="15">
				<?php 
					$post_image_id = get_post_thumbnail_id($post->ID);
					$thumbnail = wp_get_attachment_image_src( $post_image_id, 'full', false);
					if (is_array($thumbnail)) { 
						$thumbnail = reset($thumbnail);
						echo $thumbnail;
					}
				?>
			</td>
		</tr>
<?php } ?> 
	</table>
	<?php
}

// This is used inside ceo_display_comic_area()
if (!function_exists('ceo_display_comic_wrapper')) {
	function ceo_display_comic_wrapper() {
		global $post;
		if ($post->post_type == 'comic') { ?>
			<div id="comic-wrap" class="comic-id-<?php echo $post->ID; ?>">
				<div id="comic-head">
					<?php if (!ceo_pluginfo('disable_default_nav') && ceo_pluginfo('enable_nav_above_comic')) ceo_display_comic_navigation(); ?>
				</div>
				<?php ceo_get_sidebar('over-comic'); ?>
				<div class="comic-table">	
					<?php ceo_get_sidebar('left-of-comic'); ?>
					<div id="comic">
						<?php echo ceo_display_comic(); ?>
					</div>
					<?php ceo_get_sidebar('right-of-comic'); ?>
				</div>				
				<?php ceo_get_sidebar('under-comic'); ?>
				<div id="comic-foot">
					<?php if (!ceo_pluginfo('disable_default_nav')) ceo_display_comic_navigation(); ?>
				</div>
				<div class="clear"></div>
			</div>
		<?php }
	}
}

function ceo_display_comic_locations() {
	global $post;
	if ($post->post_type == 'comic') {
		$before = '<div class="comic-locations">'.__('Location','comiceasel').': ';
		$sep = ', '; 
		$after = '</div>';
		echo get_the_term_list( $post->ID, 'locations', $before, $sep, $after );
	}
}

function ceo_display_comic_characters() {
	global $post;
	if ($post->post_type == 'comic') {
		$before = '<div class="comic-characters">'.__('Characters','comiceasel').': ';
		$sep = ', '; 
		$after = '</div>';
		echo get_the_term_list( $post->ID, 'characters', $before, $sep, $after );
	}
}

// add_action('easel-display-the-content-archive-before', 'ceo_inject_thumbnail_into_archive_posts');
// add_action('easel-display-the-content-before', 'ceo_inject_thumbnail_into_archive_posts');

function ceo_inject_thumbnail_into_archive_posts() {
	global $post;
	if ($post->post_type == 'comic') {
		echo '<p>'. str_replace('alt=', 'class="aligncenter" alt=', ceo_display_comic_thumbnail('medium', $post, true, 320)) . '</p>';
	}
}

// Inject into the menubar some mini navigation
function ceo_inject_mini_navigation() {
	global $post, $wp_query;
	if (!ceo_pluginfo('disable_mininav') && !is_404() && !is_search() && !is_archive()) {
		$next_comic = $prev_comic = '';
		if ((is_home() || is_front_page()) && !is_paged() && !ceo_pluginfo('disable_comic_on_home_page')) {
			$chapter_on_home = '';
			$chapter_on_home = get_term_by( 'id', ceo_pluginfo('chapter_on_home'), 'chapters');
			$chapter_on_home = (!is_wp_error($chapter_on_home) && !empty($chapter_on_home)) ? '&chapters='.$chapter_on_home->slug : '';
			$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'asc' : 'desc';
			$query_args = 'post_type=comic&showposts=1&order='.$order.$chapter_on_home;
			apply_filters('ceo_display_comic_mininav_home_query', $query_args);
			$comicFrontpage = new WP_Query(); $comicFrontpage->query($query_args);
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
				if (ceo_pluginfo('navigate_only_chapters')) {
					$next_comic = ceo_get_next_comic_in_chapter_permalink();
					$prev_comic = ceo_get_previous_comic_in_chapter_permalink();
				} else {
					$next_comic = ceo_get_next_comic_permalink();
					$prev_comic = ceo_get_previous_comic_permalink();		
				}
			endwhile;
		} elseif (!empty($post) && $post->post_type == 'comic') {
			if (ceo_pluginfo('navigate_only_chapters')) {
				$next_comic = ceo_get_next_comic_in_chapter_permalink();
				$prev_comic = ceo_get_previous_comic_in_chapter_permalink();
			} else {
				$next_comic = ceo_get_next_comic_permalink();
				$prev_comic = ceo_get_previous_comic_permalink();		
			}
		}
		if (!empty($next_comic) || !empty($prev_comic)) {
			$next_text = __('&rsaquo;','comiceasel');
			$prev_text = __('&lsaquo;','comiceasel');
			$output = '<div class="mininav-wrapper">'."\r\n";
			if (!empty($prev_comic))
				$output .= '<span class="mininav-prev"><a href="'.$prev_comic.'">'.$prev_text.'</a></span>';
			if (!empty($next_comic))
				$output .= '<span class="mininav-next"><a href="'.$next_comic.'">'.$next_text.'</a></span>';
			$output .= '</div>'."\r\n";
			echo apply_filters('ceo_inject_mini_navigation', $output);
		}
	}
}

function ceo_display_comic_post_home() { 
	global $wp_query, $post;
	if (is_front_page() && !is_paged() && !ceo_pluginfo('disable_comic_blog_on_home_page')) {
		if(class_exists('Jetpack') && Jetpack::is_module_active('minileven') && wp_is_mobile()) {
			echo '<div style="margin: 10px; background: #fff; padding: 10px;">';
		}		
		$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'asc' : 'desc';
		$chapter_on_home = '';
		$chapter_on_home = get_term_by( 'id', ceo_pluginfo('chapter_on_home'), 'chapters');
		$chapter_on_home = (!is_wp_error($chapter_on_home) && !empty($chapter_on_home)) ? '&chapters='.$chapter_on_home->slug : '';
		$query_args = 'post_type=comic&showposts=1&order='.$order.$chapter_on_home;
		apply_filters('ceo_display_comic_post_home_query', $query_args);
		$comicFrontpage = new WP_Query(); $comicFrontpage->query($query_args);
		while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
			if (current_theme_supports('post-formats')) {
				get_template_part('content', 'comic');
			} elseif (function_exists('comicpress_display_post')) {
				comicpress_display_post();
			} elseif (function_exists('easel_display_post')) {
				easel_display_post();
			} elseif (function_exists('comic_easel_custom_display_post')) {
				comic_easel_custom_display_post();
			} else ceo_display_comic_post();
		endwhile;
		if (ceo_pluginfo('enable_comments_on_homepage')) {			
			global $withcomments; $withcomments = true;
			comments_template('', true);
		}
		wp_reset_query();
		if(class_exists('Jetpack') && Jetpack::is_module_active('minileven') && wp_is_mobile()) echo '</div>';
		echo '<div id="blogheader"></div>';
	} elseif (is_archive() && (isset($wp_query->query_vars['taxonomy']) && isset($wp_query->query_vars['chapters'])) && ($wp_query->query_vars['taxonomy'] == 'chapters') && !is_paged()) {
		if (ceo_pluginfo('enable_chapter_landing') && ceo_pluginfo('enable_blog_on_chapter_landing')) {
			$order = (ceo_pluginfo('enable_chapter_landing_first')) ?  'asc' : 'desc';
			$comic_args = array(
					'showposts' => 1,
					'posts_per_page' => 1,
					'post_type' => 'comic',
					'order' => $order,
					'chapters' => $wp_query->query_vars['chapters']
			);
			$comicFrontpage = new WP_Query(); $comicFrontpage->query($comic_args);
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
				if (current_theme_supports('post-formats')) {
					get_template_part('content', 'comic');
				} elseif (function_exists('comicpress_display_post')) {
					comicpress_display_post();
				} elseif (function_exists('easel_display_post')) {
					easel_display_post();
				} elseif (function_exists('comic_easel_custom_display_post')) {
					comic_easel_custom_display_post();
				} else ceo_display_comic_post();
			endwhile;
			if (ceo_pluginfo('enable_comments_on_chapter_landing')) {
				global $withcomments; $withcomments = true;
				comments_template('', true);
			}
			wp_reset_query();
		}
	}
}

function ceo_display_comic_post() {
global $post, $wp_query; ?>
	<div <?php post_class(); ?>>
		<div class="comic-post-head"></div>
		<div class="comic-post-content">
			<div class="comic-post-text post-title">
				<h2 class="comic-post-title entry-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>
			</div>
			<div class="comic-post-info">
				<?php do_action('comic-post-info'); ?>

			</div>
				<div class="clear"></div>
				<div class="entry">
					<?php the_content(); ?>
					<div class="clear"></div>
				</div>
				<?php wp_link_pages(array('before' => '<div class="linkpages"><span class="linkpages-pagetext">Pages:</span> ', 'after' => '</div>', 'next_or_number' => 'number')); ?>
				<div class="clear"></div>
				<div class="comic-post-extras">
					<?php 
						do_action('comic-post-extras');
					?>
					<div class="clear"></div>
				</div>
			</div>
			<div class="comic-post-foot"></div>
		</div>
<?php 
}

function ceo_display_related_comics() {
global $post, $wp_query, $wpdb, $table_prefix;
	if ($post->post_type == 'comic' && !is_feed() && !is_archive() && !is_search()) {
		$do_not_duplicate[] = $post->ID;
		$termarray = array();
		$character_terms = wp_get_post_terms( $post->ID, 'characters' );
		if (is_array($character_terms) && (count($character_terms) > 0) && !is_wp_error($character_terms)) {
			foreach ($character_terms as $term) {
				$termarray[] = $term->term_id;
			}
		}
		$location_terms = wp_get_post_terms( $post->ID, 'locations' );
		if (is_array($location_terms) && (count($location_terms) > 0) && !is_wp_error($location_terms)) {
			foreach ($location_terms as $term) {
				$termarray[] = $term->term_id;
			}
		}
		$post_tag_terms = wp_get_post_terms( $post->ID, 'post_tag' );
		if (is_array($post_tag_terms) && (count($post_tag_terms) > 0) && !is_wp_error($post_tag_terms)) {
			foreach ($post_tag_terms as $term) {
				$termarray[] = $term->term_id;
			}
		}
		if (is_array($termarray) && (count($termarray) > 0)) {
			$termlist = implode(',', $termarray);
			if (!empty($termlist)) {
				if (empty($limit)) $limit = 5;
				// Do the query
				$query = "SELECT p.*, count(tr.object_id) as count
						FROM $wpdb->term_taxonomy AS tt,
						$wpdb->term_relationships AS tr, 
						$wpdb->posts AS p WHERE (tt.taxonomy = 'post_tag' OR tt.taxonomy = 'characters' OR tt.taxonomy = 'locations') 
						AND tt.term_taxonomy_id = tr.term_taxonomy_id 
						AND tr.object_id  = p.ID 
						AND tt.term_id IN ($termlist) AND p.ID != $post->ID
						AND p.post_status = 'publish'
						AND p.post_type = 'comic'
						AND p.post_date_gmt < NOW()
						GROUP BY tr.object_id
						ORDER BY RAND() DESC, p.post_date_gmt DESC
						LIMIT $limit;";
				$related = $wpdb->get_results($query);
				$output = '';
				if (!empty($related)) {
					$output .= '<div class="related-comics">'."\r\n";
					$output .= '<h4 class="related-title">'.__('Related Comics &not;','comiceasel').'</h4>'."\r\n";
					$output .= '<ul class="related-ul">'."\r\n";
					foreach ($related as $post_info) {
//						if (has_post_thumbnail($post_info->ID)) the_post_thumbnail($post_info->ID);
						$output .= 	'<li class="related-comic"><a title="'.wptexturize($post_info->post_title).'" href="'.get_permalink($post_info->ID).'">'.wptexturize($post_info->post_title).'</a></li>'."\r\n";
					}
					$output .= "</ul>\r\n";
					$output .= "</div>\r\n";
				}
				echo $output;
			}
		}
	}
}

function ceo_social_meta() {
	global $post;
	if (!empty($post) && $post->post_type == 'comic') {
//		echo '<meta name="twitter:card" content="summary">'."\r\n";
//		echo '<meta name="twitter:site" content="'.get_bloginfo('name').'">'."\r\n";
		if (is_single() || is_page()) {
			$quick_excerpt = esc_attr(get_the_excerpt());
//			echo '<meta name="twitter:description" content="'.$quick_excerpt.'" />'."\r\n";
//			echo '<meta name="twitter:title" content="'.get_the_title().'" />'."\r\n";
		} else {
//			echo '<meta name="twitter:description" content="'.get_bloginfo('description').'" />'."\r\n";         
//			echo '<meta name="twitter:title" content="" />'."\r\n";
		}
		$post_image_id = get_post_thumbnail_id($post->ID);
		$thumbnail = wp_get_attachment_image_src( $post_image_id, ceo_pluginfo('thumbnail_size_for_facebook'), false);
		if (is_array($thumbnail)) { 
			$thumbnail = reset($thumbnail);
			echo '<meta property="og:image" content="'.$thumbnail.'" />'."\r\n";
//			echo '<meta name="twitter:image" content="'.$thumbnail.'" />'."\r\n";
		}		
	}
}