<?php
/*
Widget Name: Comic List Dropdown of Current Chapter
Widget URI: http://comiceasel.org/
Description: Display a list of links of the latest comics.
Author: Philip M. Hofer (Frumph)
Version: 1.02
*/

function ceo_list_jump_to_comic($exclude = '', $return = false) {
	global $post;
	ceo_protect();
	$output = '';
	$terms = wp_get_object_terms( $post->ID, 'chapters');
	if (!empty($terms)){
		$term = reset($terms); // only one chapter
		$output = '<form method="get" class="comic-list-dropdown-form">';
		$output .= '<select onchange="document.location.href=this.options[this.selectedIndex].value;">';
		$level = 0;
		$output .= '<option class="level-select" value="">'.__('Jump To','comiceasel').'</option>';
		$post_args = array( 
			'showposts' => -1,
			'post_type' => 'comic',
			'order' => 'ASC', 
			'post_status' => 'publish', 
			'chapters' => $term->slug, 
		);					
		$qposts = get_posts( $post_args );
		foreach($qposts as $qpost) {
			$permalink = get_permalink($qpost->ID);
			if (!empty($permalink)) $output .='<option class="level-0" value="'.esc_url($permalink).'">'.$qpost->post_title.'</option>';
		}
		$output .= '</select>';
		$output .= '<noscript>';
		$output .= '<div><input type="submit" value="View" /></div>';
		$output .= '</noscript>';
		$output .= '</form>';
		if ($return) {
			return $output;
		} else echo $output;
	}
	ceo_unprotect();
}

class ceo_comic_list_dropdown_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			__CLASS__, // Base ID
			__( 'Comic Easel - Comic List Dropdown', 'comiceasel' ), // Name
			array( 'classname' => __CLASS__, 'description' => __( 'Display dropdown list of comics.', 'comiceasel' ), ) // Args
		);
	}
	
	function widget($args, $instance) {
		global $post;

		extract($args, EXTR_SKIP); 
		echo $before_widget;
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }; 
		if ((is_home() || is_front_page()) && !is_paged() && !ceo_pluginfo('disable_comic_on_home_page')) {
			$chapter_on_home = '';
			$chapter_on_home = get_term_by( 'id', ceo_pluginfo('chapter_on_home'), 'chapters');
			$chapter_on_home = (!is_wp_error($chapter_on_home) && !empty($chapter_on_home)) ? '&chapters='.$chapter_on_home->slug : '';
			$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'asc' : 'desc';
			$query_args = 'post_type=comic&showposts=1&order='.$order.$chapter_on_home;
			apply_filters('ceo_display_comic_mininav_home_query', $query_args);
			$comicFrontpage = new WP_Query(); $comicFrontpage->query($query_args);
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
				ceo_list_jump_to_comic($instance['exclude'], false);
			endwhile;
		} elseif (!empty($post)) {
			ceo_list_jump_to_comic($instance['exclude'], false);
		}
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['exclude'] = strip_tags($new_instance['exclude']);
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'exclude' => '', 'unhide' => 1, 'showcount' => 1) );
		$title = $instance['title'];
		$exclude = $instance['exclude'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		</p>
		<?php
	}
}

