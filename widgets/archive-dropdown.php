<?php
/*
Widget Name: Comic Archive Dropdown
Widget URI: http://comiceasel.org/
Description: Display a list of links of the latest comics.
Author: Philip M. Hofer (Frumph)
Version: 1.02
*/

function ceo_comic_archive_jump_to_chapter($unhide = false, $exclude = '', $showcount = true) {
	ceo_protect();
	$args = array(
			'pad_counts' => 1,
			'orderby' => 'menu_order',
			'order' => 'DESC',
			'hide_empty' => $unhide,
			'parent' => 0,
			'exclude' => array($exclude)
	);
	$parent_chapters = get_terms( 'chapters', $args );
	$output = '<form method="get">';
	$output .= '<select onchange="document.location.href=this.options[this.selectedIndex].value;">';
	$level = 0;
	$output .= '<option class="level-select" value="">'.__('Select Story','comiceasel').'</option>';
	if (!is_null($parent_chapters)) {
		foreach($parent_chapters as $parent_chapter) {
			$count = '';
			$parent_args = array( 
					'numberposts' => 1, 
					'post_type' => 'comic', 
					'orderby' => 'post_date', 
					'order' => 'ASC', 
					'post_status' => 'publish', 
					'chapters' => $parent_chapter->slug, 
					);					
			$qposts = get_posts( $parent_args );
			if (is_array($qposts) && !is_wp_error($qposts) && !empty($qposts)) {
				$qposts = reset($qposts);
				if ($parent_chapter->count) $count = ' ('.$parent_chapter->count.') ';
				$output .='<option class="level-0" value="'.get_permalink($qposts->ID).'">'.$parent_chapter->name.$count.'</option>';
			}
			$child_chapters = get_term_children( $parent_chapter->term_id, 'chapters' );
			foreach ($child_chapters as $child) {
				$child_term = get_term_by( 'id', $child, 'chapters' );
				if ($child_term->count) {
					$child_args = array( 
							'numberposts' => 1, 
							'post_type' => 'comic',
							'orderby' => 'post_date', 
							'order' => 'ASC', 
							'post_status' => 'publish', 
							'chapters' => $child_term->slug 
							);					
					$qcposts = get_posts( $child_args );
					if (is_array($qcposts)) {
						$qcposts = reset($qcposts);
						$thecount = '';
						if ($showcount) $thecount = ' ('.$child_term->count.')';
						$output .= '<option class="level-1" value="' . get_permalink($qcposts->ID) . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $child_term->name . $thecount. '</option>';
					}
				}
			}
		}
	}
	$output .= '</select>';
	$output .= '<noscript>';
	$output .= '<div><input type="submit" value="View" /></div>';
	$output .= '</noscript>';
	$output .= '</form>';
	echo $output;
	ceo_unprotect();
}

class ceo_comic_archive_dropdown_widget extends WP_Widget {
	
	function ceo_comic_archive_dropdown_widget($skip_widget_init = false) {
		if (!$skip_widget_init) {
			$widget_ops = array('classname' => __CLASS__, 'description' => __('Display dropdown list of comic chapters.', 'comiceasel') );
			$this->WP_Widget(__CLASS__, __('Comic Easel - Comic Chapters','comiceasel'), $widget_ops);
		}
	}
	
	function widget($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP); 
		echo $before_widget;
		$title = empty($instance['title']) ? __('Comic Chapters','comiceasel') : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }; 
		ceo_comic_archive_jump_to_chapter($instance['unhide'], $instance['exclude']);
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['exclude'] = strip_tags($new_instance['exclude']);
		$instance['unhide'] = ($new_instance['unhide']) ? true : false;
		$instance['showcount'] = ($new_isntance['showcount']) ? true : false;
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'exclude' => '', 'unhide' => true, 'showcount' => true) );
		$title = strip_tags($instance['title']);
		$exclude = strip_tags($instance['exclude']);
		$unhide = $instance['unhide'] ? true : false;
		$showcount = $instance['showcount'] ? true:false;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Exclude Chapters (comma seperated):','comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo esc_attr($exclude); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('unhide'); ?>"><?php _e('Show all empty chapters?','comiceasel'); ?> <input id="<?php echo $this->get_field_id('unhide'); ?>" name="<?php echo $this->get_field_name('unhide'); ?>" type="checkbox" value="true" <?php checked(true, $unhide); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id('showcount'); ?>"><?php _e('Show the comic count in parenthesis?','comiceasel'); ?> <input id="<?php echo $this->get_field_id('showcount'); ?>" name="<?php echo $this->get_field_name('showcount'); ?>" type="checkbox" value="true" <?php checked(true, $showcount); ?> /></label></p>
		<?php
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("ceo_comic_archive_dropdown_widget");') );
