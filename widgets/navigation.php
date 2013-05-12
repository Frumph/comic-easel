<?php
/*
Widget Name: Graphical Navigation
Widget URI: http://comiceasel.com/
Description: You can place graphical navigation buttons on your comic.
Author: Philip M. Hofer (Frumph) (mods by Chris Maverick)
Author URI: http://frumph.net/
Version: 1.1m
*/

class ceo_comic_navigation_widget extends WP_Widget {
	
	function ceo_comic_navigation_widget() {
		$widget_ops = array('classname' => 'ceo_comic_navigation_widget', 'description' => __('Displays navigation links for navigating your comics.  This widget works best in the above-comic and under-comic sidebars if you set them in the theme you use.','comiceasel') );
		$this->WP_Widget('ceo_comic_navigation', __('Comic Easel - Navigation','comiceasel'), $widget_ops);
	}
	
	function display_comic_nav_wrapper($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP);
		$prev_in_comic = $next_in_comic = $first_in_comic = $last_in_comic = $prev_comic = $next_comic = $first_comic = $last_comic = $previous_chap = $next_chap = false;
		if ($instance['previous_in']) $prev_in_comic = ceo_get_previous_comic_in_chapter_permalink();
		if ($instance['next_in']) $next_in_comic = ceo_get_next_comic_in_chapter_permalink();
		if ($instance['first_in']) $first_in_comic = ceo_get_first_comic_in_chapter_permalink();
		if ($instance['last_in']) $last_in_comic = ceo_get_last_comic_in_chapter_permalink();      
		if ($instance['previous']) $prev_comic = ceo_get_previous_comic_permalink();
		if ($instance['next']) $next_comic = ceo_get_next_comic_permalink();
		if ($instance['first']) $first_comic = ceo_get_first_comic_permalink();
		if ($instance['last']) $last_comic = ceo_get_last_comic_permalink();
		if ($instance['previous_chap']) $previous_chap = ceo_get_previous_chapter();
		if ($instance['next_chap']) $next_chap = ceo_get_next_chapter();
		$this_permalink = get_permalink($post->ID);
		//      echo $before_widget;
		?>
		<div class="comic_navi_wrapper">
		<table class="comic_navi">
		<?php if (!isset($instance['comicchapter'])) $instance['comicchapter'] = false; ?>
		<?php if (($instance['comicchapter']) || ($instance['comictitle'])) { ?>
			<tr>
			<td class="comic_navi_title" colspan="3">
			<?php if ($instance['comicchapter']) { $post_category = get_the_term_list ($post->ID,'chapters', '', ', ', ''); ?>
				<span class="navi-comicchapter"><?php echo $post_category ?></span>
				<?php if ($instance['comictitle']) { echo '- '; }
			}
			if ($instance['comictitle']) { ?>
				<span class="navi-comictitle"><a href="<?php the_permalink(); ?>" class="comic-nav-title">"<?php the_title(); ?>"</a></span>
			<?php } ?>
			</td>
			</tr>
		<?php } ?>
		<tr>
		<td class="comic_navi_left">
		<?php 
		if ($instance['first']) {
			if (!empty($first_comic) && ($first_comic !== $this_permalink)) { ?>
				<a href="<?php echo $first_comic; ?>" class="navi navi-first" title="<?php echo $instance['first_title']; ?>"><?php echo $instance['first_title']; ?></a>
			<?php } else { ?>
				<span class="navi navi-first navi-void"><?php echo $instance['first_title']; ?></span>
			<?php } 
		}
		if ($instance['first_in']) {
			if (!empty($first_in_comic) && ($first_in_comic !== $this_permalink)) { ?>
				<a href="<?php echo $first_in_comic; ?>" class="navi navi-first-in" title="<?php echo $instance['first_in_title']; ?>"><?php echo $instance['first_in_title']; ?></a>
			<?php } else { ?>
				<span class="navi navi-first-in navi-void"><?php echo $instance['first_in_title']; ?></span>
			<?php } 
		}
		if ($instance['previous']) {
			if (!empty($prev_comic)) { ?>
				<a href="<?php echo $prev_comic; ?>" class="navi navi-prev" title="<?php echo $instance['previous_title']; ?>"><?php echo $instance['previous_title']; ?></a>
			<?php } else { ?>
				<span class="navi navi-prev navi-void"><?php echo $instance['previous_title']; ?></span>
			<?php }
		}
		if ($instance['previous_in']) {
			if (!empty($prev_in_comic)) { ?>
				<a href="<?php echo $prev_in_comic; ?>" class="navi navi-prev-in" title="<?php echo $instance['previous_in_title']; ?>"><?php echo $instance['previous_in_title']; ?></a>
			<?php } else { ?>
				<span class="navi navi-prev-in navi-void"><?php echo $instance['previous_in_title']; ?></span>
			<?php }
		}
		if ($instance['previous_chap']) {
			if (!empty($previous_chap)) { ?>
				<a href="<?php echo $previous_chap; ?>" class="navi navi-prev-chap" title="<?php echo $instance['previous_chap_title']; ?>"><?php echo $instance['previous_chap_title']; ?></a>
			<?php } else { ?>
				<span class="navi navi-prev-chap navi-void"><?php echo $instance['previous_chap_title']; ?></span>
			<?php } 
		} 
		?>
		</td>
		<td class="comic_navi_center">
		<?php
		if ($instance['archives'] && !empty($instance['archive_path'])) { ?>
			<a href="<?php echo $instance['archive_path']; ?>" class="navi navi-archives navi-archive" title="<?php echo $instance['archives_title']; ?>"><?php echo $instance['archives_title']; ?></a>
		<?php } 
		if ($instance['random']) { ?>
			<a href="<?php echo home_url(); ?>/?random&amp;nocache=1" class="navi navi-random" title="<?php echo $instance['random_title']; ?>"><?php echo $instance['random_title']; ?></a>
		<?php }
		do_action('inside-comic-navigation');
		if ($instance['comments']) { ?>
			<a href="<?php the_permalink(); ?>#comment" class="navi navi-comments" title="<?php echo $instance['comments_title']; ?>"><span class="navi-comments-count"><?php comments_number('0', '1', '%'); ?></span><?php echo $instance['comments_title']; ?></a>
		<?php }
		if ($instance['subscribe']) { ?>
			<a href="<?php bloginfo('rss2_url') ?>" class="navi navi-subscribe" title="<?php echo $instance['subscribe_title']; ?>"><?php echo $instance['subscribe_title']; ?></a>
		<?php } 
		?>
		</td>
		<td class="comic_navi_right">
		<?php
		if ($instance['next_chap']) {
			if (!empty($next_chap)) { ?>
				<a href="<?php echo $next_chap; ?>" class="navi navi-next-chap" title="<?php echo $instance['next_chap_title']; ?>"><?php echo $instance['next_chap_title']; ?></a>
			<?php } else { ?>
				<span class="navi navi-next-chap navi-void"><?php echo $instance['next_chap_title']; ?></span>
			<?php } 
		}
		if ($instance['next_in']) {
			if (!empty($next_in_comic)) { ?>
				<a href="<?php echo $next_in_comic; ?>" class="navi navi-next-in" title="<?php echo $instance['next_in_title']; ?>"><?php echo $instance['next_in_title']; ?></a>
			<?php } else { ?>
				<span class="navi navi-next-in navi-void"><?php echo $instance['next_in_title']; ?></span>
			<?php }
		}
		if ($instance['next']) {
			if (!empty($next_comic)) { ?>
				<a href="<?php echo $next_comic; ?>" class="navi navi-next" title="<?php echo $instance['next_title']; ?>"><?php echo $instance['next_title']; ?></a>
			<?php } else { ?>
				<span class="navi navi-next navi-void"><?php echo $instance['next_title']; ?></span>
			<?php }
		}
		if ($instance['last_in']) {
			if (!empty($last_in_comic) && ($last_in_comic !== $this_permalink)) { ?>
				<a href="<?php echo $last_in_comic; ?>" class="navi navi-last-in" title="<?php echo $instance['last_in_title']; ?>"><?php echo $instance['last_in_title']; ?></a>                  
			<?php } else { ?>
				<span class="navi navi-last-in navi-void"><?php echo $instance['last_in_title']; ?></span>
			<?php }
		}
		if ($instance['last']) {
			if (!empty($last_comic) && ($last_comic !== $this_permalink)) {
				if (isset($instance['lastgohome']) && $instance['lastgohome']) { ?>
					<a href="/" class="navi navi-last" title="<?php echo $instance['last_title']; ?>"><?php echo $instance['last_title']; ?></a>
				<?php } else { ?>
					<a href="<?php echo $last_comic; ?>" class="navi navi-last" title="<?php echo $instance['last_title']; ?>"><?php echo $instance['last_title']; ?></a>                  
				<?php } ?>
			<?php } else { ?>
				<span class="navi navi-last navi-void"><?php echo $instance['last_title']; ?></span>
			<?php }
		} ?>
		</td>
		</tr>
		<?php if ($instance['imageurl']) { ?>
			<tr>
			<td class="comic_navi_href" colspan="3">
			<?php 
			$post_image_id = get_post_thumbnail_id($post->ID);
			$thumbnail = wp_get_attachment_image_src( $post_image_id, 'full', false);
			if (is_array($thumbnail) && !empty($thumbnail)) { 
				$thumbnail = reset($thumbnail);
				echo '<span class="comic-navi-href-info">'.__('Image URL (for hotlinking/embedding): ','comiceasel').$thumbnail.'</span>';
			}
			?>
			</td>
			</tr>
		<?php } ?>
		</table>
		</div>
		<?php
		//      echo $after_widget;
	}
	
	function widget($args, $instance) {
		global $post;
		if (is_home() || is_front_page()) {
			$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'asc' : 'desc';
			$comic_args = array(
					'showposts' => 1,
					'posts_per_page' => 1,
					'post_type' => 'comic',
					'order' => $order
					);
			$comicFrontpage = new WP_Query(); $comicFrontpage->query($comic_args);
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();         
				$this->display_comic_nav_wrapper($args, $instance);
			endwhile;
		} else { 
			$this->display_comic_nav_wrapper($args, $instance);
		}
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		foreach (array(
					'previous_chap',
					'next_chap',
					'first_in',
					'last_in',
					'previous_in',
					'next_in',         
					'first',
					'last',
					'previous',
					'next',
					'random',
					'archives',
					'comments',
					'subscribe',
					'comictitle',
					'comicchapter',
					'imageurl'
					) as $key) {
			$instance[$key] = (bool)( $new_instance[$key] == 1 ? true : false );
		}
		foreach (array(
					'previous_chap_title',
					'next_chap_title',
					'first_in_title',
					'last_in_title',
					'previous_in_title',
					'next_in_title',         
					'first_title',
					'last_title',
					'previous_title',
					'next_title',
					'random_title',
					'archives_title',
					'comments_title',
					'subscribe_title',
					) as $key) {
			$instance[$key] = $new_instance[$key];
		}
		$instance['archive_path'] = $new_instance['archive_path'];
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array(
					'previous_chap' => false,
					'next_chap' => false,
					'first_in' => false,
					'last_in' => false,
					'previous_in' => false,
					'next_in' => false,
					'first' => true,
					'last' => true,
					'previous' => true,
					'next' => true,
					'random' => false,
					'archives' => false,
					'comments' => false,
					'archive_path' => '',
					'previous_chap_title' => '&lt;[ Prev Chapter',
					'next_chap_title' => 'Next chapter ]&gt;',
					'first_in_title' => '&lt;&lt; First',
					'last_in_title' => 'Last &gt;&gt;',
					'previous_in_title' => '&lt; Prev',
					'next_in_title' => 'Next &gt;',
					'first_title' => '&lt;&lt; First',
					'last_title' => 'Last &gt;&gt;',
					'previous_title' => '&lt; Prev',
					'next_title' => 'Next &gt;',
					'random_title' => 'Random', 
					'archives_title' => 'Archives', 
					'comments_title' => 'Comments', 
					'buyprint_title' => 'Buy Print',
					'comictitle' => false,      
					'comicchapter' => false,      
					'subscribe' => 'Subscribe',
					'subscribe_path' => '',
					'subscribe_title' => 'SubScribe',
					'imageurl' => false
					) );
		
		?>
		<input id="<?php echo $this->get_field_id('first'); ?>" name="<?php echo $this->get_field_name('first'); ?>" type="checkbox" value="1" <?php checked(true, $instance['first']); ?> /> <label for="<?php echo $this->get_field_id('first'); ?>"><strong><?php _e('First','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('first_title'); ?>" name="<?php echo $this->get_field_name('first_title'); ?>" type="text" value="<?php echo esc_attr($instance['first_title']); ?>" /><br />   
		<br />
		
		<input id="<?php echo $this->get_field_id('last'); ?>" name="<?php echo $this->get_field_name('last'); ?>" type="checkbox" value="1" <?php checked(true, $instance['last']); ?> /> <label for="<?php echo $this->get_field_id('last'); ?>"><strong><?php _e('Last','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('last_title'); ?>" name="<?php echo $this->get_field_name('last_title'); ?>" type="text" value="<?php echo esc_attr($instance['last_title']); ?>" /><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('previous'); ?>" name="<?php echo $this->get_field_name('previous'); ?>" type="checkbox" value="1" <?php checked(true, $instance['previous']); ?> /> <label for="<?php echo $this->get_field_id('previous'); ?>"><strong><?php _e('Previous','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('previous_title'); ?>" name="<?php echo $this->get_field_name('previous_title'); ?>" type="text" value="<?php echo esc_attr($instance['previous_title']); ?>" /><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('next'); ?>" name="<?php echo $this->get_field_name('next'); ?>" type="checkbox" value="1" <?php checked(true, $instance['next']); ?> /> <label for="<?php echo $this->get_field_id('next'); ?>"><strong><?php _e('Next','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('next_title'); ?>" name="<?php echo $this->get_field_name('next_title'); ?>" type="text" value="<?php echo esc_attr($instance['next_title']); ?>" /><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('first_in'); ?>" name="<?php echo $this->get_field_name('first_in'); ?>" type="checkbox" value="1" <?php checked(true, $instance['first_in']); ?> /> <label for="<?php echo $this->get_field_id('first_in'); ?>"><strong><?php _e('First in Chapter','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('first_in_title'); ?>" name="<?php echo $this->get_field_name('first_in_title'); ?>" type="text" value="<?php echo esc_attr($instance['first_in_title']); ?>" /><br />   
		<br />
		
		<input id="<?php echo $this->get_field_id('last_in'); ?>" name="<?php echo $this->get_field_name('last_in'); ?>" type="checkbox" value="1" <?php checked(true, $instance['last_in']); ?> /> <label for="<?php echo $this->get_field_id('last_in'); ?>"><strong><?php _e('Last in Chapter','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('last_in_title'); ?>" name="<?php echo $this->get_field_name('last_in_title'); ?>" type="text" value="<?php echo esc_attr($instance['last_in_title']); ?>" /><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('previous_in'); ?>" name="<?php echo $this->get_field_name('previous_in'); ?>" type="checkbox" value="1" <?php checked(true, $instance['previous_in']); ?> /> <label for="<?php echo $this->get_field_id('previous_in'); ?>"><strong><?php _e('Previous in Chapter','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('previous_in_title'); ?>" name="<?php echo $this->get_field_name('previous_in_title'); ?>" type="text" value="<?php echo esc_attr($instance['previous_in_title']); ?>" /><br />   
		<br />
		
		<input id="<?php echo $this->get_field_id('next_in'); ?>" name="<?php echo $this->get_field_name('next_in'); ?>" type="checkbox" value="1" <?php checked(true, $instance['next_in']); ?> /> <label for="<?php echo $this->get_field_id('next_in'); ?>"><strong><?php _e('Next in Chapter','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('next_in_title'); ?>" name="<?php echo $this->get_field_name('next_in_title'); ?>" type="text" value="<?php echo esc_attr($instance['next_in_title']); ?>" /><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('previous_chap'); ?>" name="<?php echo $this->get_field_name('previous_chap'); ?>" type="checkbox" value="1" <?php checked(true, $instance['previous_chap']); ?> /> <label for="<?php echo $this->get_field_id('previous_chap'); ?>"><strong><?php _e('Previous Chapter','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('previous_chap_title'); ?>" name="<?php echo $this->get_field_name('previous_chap_title'); ?>" type="text" value="<?php echo esc_attr($instance['previous_chap_title']); ?>" /><br />   
		<br />
		
		<input id="<?php echo $this->get_field_id('next_chap'); ?>" name="<?php echo $this->get_field_name('next_chap'); ?>" type="checkbox" value="1" <?php checked(true, $instance['next_chap']); ?> /> <label for="<?php echo $this->get_field_id('next_chap'); ?>"><strong><?php _e('Next Chapter','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('next_chap_title'); ?>" name="<?php echo $this->get_field_name('next_chap_title'); ?>" type="text" value="<?php echo esc_attr($instance['next_chap_title']); ?>" /><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('comictitle'); ?>" name="<?php echo $this->get_field_name('comictitle'); ?>" type="checkbox" value="1" <?php checked(true, $instance['comictitle']); ?> /> <label for="<?php echo $this->get_field_id('comictitle'); ?>"><strong><?php _e('Comic Title','comiceasel'); ?></strong><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('comicchapter'); ?>" name="<?php echo $this->get_field_name('comicchapter'); ?>" type="checkbox" value="1" <?php checked(true, $instance['comicchapter']); ?> /> <label for="<?php echo $this->get_field_id('comicchapter'); ?>"><strong><?php _e('Comic Chapter','comiceasel'); ?></strong><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('archives'); ?>" name="<?php echo $this->get_field_name('archives'); ?>" type="checkbox" value="1" <?php checked(true, $instance['archives']); ?> /> <label for="<?php echo $this->get_field_id('archives'); ?>"><strong><?php _e('Archives','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('archives_title'); ?>" name="<?php echo $this->get_field_name('archives_title'); ?>" type="text" value="<?php echo esc_attr($instance['archives_title']); ?>" /><br />   
		Archive URL: <input class="widefat" id="<?php echo $this->get_field_id('archive_path'); ?>" name="<?php echo $this->get_field_name('archive_path'); ?>" type="text" value="<?php echo esc_attr($instance['archive_path']); ?>" /><br />
		<br />
		
		<input id="<?php echo $this->get_field_id('comments'); ?>" name="<?php echo $this->get_field_name('comments'); ?>" type="checkbox" value="1" <?php checked(true, $instance['comments']); ?> /> <label for="<?php echo $this->get_field_id('comments'); ?>"><strong><?php _e('Comments','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('comments_title'); ?>" name="<?php echo $this->get_field_name('comments_title'); ?>" type="text" value="<?php echo esc_attr($instance['comments_title']); ?>" /><br />   
		<br />
		
		<input id="<?php echo $this->get_field_id('random'); ?>" name="<?php echo $this->get_field_name('random'); ?>" type="checkbox" value="1" <?php checked(true, $instance['random']); ?> /> <label for="<?php echo $this->get_field_id('random'); ?>"><strong><?php _e('Random','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('random_title'); ?>" name="<?php echo $this->get_field_name('random_title'); ?>" type="text" value="<?php echo esc_attr($instance['random_title']); ?>" /><br />   
		<br />
		
		<input id="<?php echo $this->get_field_id('subscribe'); ?>" name="<?php echo $this->get_field_name('subscribe'); ?>" type="checkbox" value="1" <?php checked(true, $instance['subscribe']); ?> /> <label for="<?php echo $this->get_field_id('subscribe'); ?>"><strong><?php _e('Subscribe','comiceasel'); ?></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('subscribe_title'); ?>" name="<?php echo $this->get_field_name('subscribe_title'); ?>" type="text" value="<?php echo esc_attr($instance['subscribe_title']); ?>" /><br />   
		<br />
		
		<input id="<?php echo $this->get_field_id('imageurl'); ?>" name="<?php echo $this->get_field_name('imageurl'); ?>" type="checkbox" value="1" <?php checked(true, $instance['imageurl']); ?> /> <label for="<?php echo $this->get_field_id('imageurl'); ?>"><strong><?php _e('ImageURL','comiceasel'); ?></strong>
		<?php
	}
}

function ceo_register_widget_comic_navigation() {
	register_widget('ceo_comic_navigation_widget');
}

add_action( 'widgets_init', 'ceo_register_widget_comic_navigation'); 

?>