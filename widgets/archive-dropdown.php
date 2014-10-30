<?php
/*
Widget Name: Comic Archive Dropdown
Widget URI: http://comiceasel.org/
Description: Display a list of links of the latest comics.
Author: Philip M. Hofer (Frumph)
Version: 1.02
*/

function ceo_comic_archive_jump_to_chapter($unhide = false, $exclude = '', $showcount = false, $jumptoarchive = true, $return = false) {
	ceo_protect();
	
	$args = array(
			'pad_counts' => 1,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'hide_empty' => $unhide,
			'exclude' => $exclude,
			'parent' => 0
	);
	$parent_chapters = get_terms( 'chapters', $args );
	$output = '<form method="get" class="comic-archive-dropdown-form">';
	$output .= '<select onchange="document.location.href=this.options[this.selectedIndex].value;">';
	$level = 0;
	$output .= '<option class="level-select" value="">'.__('Select','comiceasel').' '.ucwords(ceo_pluginfo('chapter_type_slug_name')).'</option>';
	if (!is_null($parent_chapters)) {
		foreach($parent_chapters as $parent_chapter) {
			$thecount = ($showcount) ? '&nbsp;('.$parent_chapter->count.')' : '';
			if ($parent_chapter->count) {
				$permalink = '';
				if (!$jumptoarchive) {
					$parent_args = array( 
							'numberposts' => 1, 
							'post_type' => 'comic',
							'order' => 'ASC', 
							'post_status' => 'publish', 
							'chapters' => $parent_chapter->slug, 
							);					
					$qposts = get_posts( $parent_args );
					if (is_array($qposts) && !is_wp_error($qposts) && !empty($qposts)) {
						$qposts = reset($qposts);
						$permalink = get_permalink($qposts->ID);
					}
				} else $permalink = get_term_link($parent_chapter->slug, 'chapters');
				if (!empty($permalink)) $output .='<option class="level-0" value="'.esc_url($permalink).'">'.$parent_chapter->name.$thecount.'</option>';				
			} elseif (!$unhide) {
				$output .='<option class="level-0" value="" disabled>'.$parent_chapter->name.'</option>';
			}
			
			$child_chapters = get_term_children($parent_chapter->term_id, 'chapters');
			$args = array(
					'pad_counts' => 1,
					'orderby' => 'menu_order',
					'order' => 'ASC',
					'hide_empty' => $unhide,
					'child_of' => $parent_chapter->term_id,
					'exclude' => array($exclude)
			);			
			$child_chapters = get_terms( 'chapters', $args );
			foreach ($child_chapters as $child) {
				$child_term = get_term_by( 'id', $child->term_id, 'chapters' );
				$thecount = 0;
				$thecount = ($showcount) ? '('.$child_term->count.')' : '';
				if ($child_term->count) {
					$child_args = array( 
							'numberposts' => 1, 
							'post_type' => 'comic',
							'order' => 'ASC', 
							'post_status' => 'publish', 
							'chapters' => $child_term->slug 
							);					
					if (!$jumptoarchive) {
						$qcposts = get_posts( $child_args );
						if (is_array($qcposts)) {
							$qcposts = reset($qcposts);
							$permalink = get_permalink($qcposts->ID);
						}
					} else $permalink = get_term_link($child_term->slug, 'chapters');
					if (!empty($permalink)) $output .= '<option class="level-1" value="'.esc_url($permalink).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$child_term->name.' '.$thecount.'</option>';				
				}
			}
		}
	}
	$output .= '</select>';
	$output .= '<noscript>';
	$output .= '<div><input type="submit" value="View" /></div>';
	$output .= '</noscript>';
	$output .= '</form>';
	if ($return) {
		return $output;
	} else echo $output;
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
		ceo_comic_archive_jump_to_chapter($instance['unhide'], $instance['exclude'], $instance['showcount'], $instance['jumptoarchive']);
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['exclude'] = strip_tags($new_instance['exclude']);
		$instance['unhide'] = ($new_instance['unhide']) ? true:false;
		$instance['showcount'] = ($new_instance['showcount']) ? true:false;
		$instance['jumptoarchive'] = ($new_instance['jumptoarchive']) ? true:false;
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'exclude' => '', 'unhide' => 1, 'showcount' => 1, 'jumptoarchive' => 0) );
		$title = $instance['title'];
		$exclude = $instance['exclude'];
		$unhide = ($instance['unhide']) ? 1:0;
		$showcount = ($instance['showcount']) ? 1:0;
		$jumptoarchive = ($instance['jumptoarchive']) ? 1:0;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Exclude Chapters (comma seperated):','comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo esc_attr($exclude); ?>" /></label><br />
		<small><?php _e('NOTE: There is a bug with WordPress with parent-child and /all/ some chapters cannot be excluded without causing this bug.','comiceasel'); ?></small>
		</p>
		<p><label for="<?php echo $this->get_field_id('unhide'); ?>"><?php _e('Show all empty chapters?','comiceasel'); ?> <input id="<?php echo $this->get_field_id('unhide'); ?>" name="<?php echo $this->get_field_name('unhide'); ?>" type="checkbox" value="1" <?php checked(1, $unhide); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id('showcount'); ?>"><?php _e('Show the comic count in parenthesis?','comiceasel'); ?> <input id="<?php echo $this->get_field_id('showcount'); ?>" name="<?php echo $this->get_field_name('showcount'); ?>" type="checkbox" value="1" <?php checked(1, $showcount); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id('jumptoarchive'); ?>"><?php _e('Have the dropdown jump to the archive and not first page of that chapter?','comiceasel'); ?> <input id="<?php echo $this->get_field_id('jumptoarchive'); ?>" name="<?php echo $this->get_field_name('jumptoarchive'); ?>" type="checkbox" value="1" <?php checked(1, $jumptoarchive); ?> /></label></p>
		<?php
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("ceo_comic_archive_dropdown_widget");') );
