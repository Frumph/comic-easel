<?php
/*
Widget Name: Comic Archive Dropdown
Widget URI: http://comiceasel.org/
Description: Display a list of links of the latest comics.
Author: Philip M. Hofer (Frumph)
Version: 1.1
*/

function ceo_taxonomy_walker_dropdown_or_list_start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
	
	// echo "<pre>args ";print_r($args); echo "</pre>";
	
    $pad = str_repeat('&nbsp;', $depth * 3);
    $cat_name = apply_filters('list_cats', $category->name, $category);

    if( !isset($args['value'])) {
        $args['value'] = ( $category->taxonomy != 'category' ? 'slug' : 'id' );
    }

    $value = ($args['value']=='slug' ? $category->slug : $category->term_id );

	$permalink = '';
	if (!isset($args['jumptoarchive'])) $args['jumptoarchive'] = false;
	if (!$args['jumptoarchive']) {
		$post_args = array( 
				'numberposts' => 1, 
				'post_type' => 'comic',
				'order' => 'ASC', 
				'post_status' => 'publish', 
				'chapters' => $value, 
				);					
		$qposts = get_posts( $post_args );
		if (is_array($qposts) && !is_wp_error($qposts) && !empty($qposts)) {
			$qposts = reset($qposts);
			$permalink = get_permalink($qposts->ID);
		}
	} else $permalink = get_term_link( $value, $category->taxonomy );		

	$css_classes = array(
		'cat-item',
		'cat-item-' . $category->term_id,
	);
	
	// echo "<pre>current cat ";print_r($args['current_category']); echo "</pre>";
	
	if ( ! empty( $args['current_category'] ) ) {
		$_current_terms = $args['current_category'];
		
		foreach ( $_current_terms as $_current_term ) {
			if ( $category->term_id == $_current_term->term_id ) {
				$css_classes[] = 'current-cat';
			} elseif ( $category->term_id == $_current_term->parent ) {
				$css_classes[] = 'current-cat-parent';
			}
		}
	}
	$css_classes = implode( ' ', apply_filters( 'category_css_class', $css_classes, $category, $depth, $args ) );
	
	if ($args['render_as_list']) {
		
		$output .= "\t<li class=\"level-$depth $css_classes\"><a href=\"".$permalink."\">";
	} else {
        $output .= "\t<option class=\"level-$depth\" value=\"".$permalink."\"";
        if ( $value === (string) $args['selected'] ) {   
            $output .= ' selected="selected"';
        }
       	$output .= '>';
	}
    $output .= $pad.$cat_name;
    if ( $args['show_count'] )
        $output .= '&nbsp;&nbsp;('. $category->count .')';
	$output .= ($args['render_as_list']) ? "</a>" : "</option>\n";
}

class ceo_walker_taxonomy_list extends Walker_Category {

	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$args['render_as_list'] = true;
		ceo_taxonomy_walker_dropdown_or_list_start_el( $output, $category, $depth = 0, $args, $id);
	}
}
class ceo_walker_taxonomy_dropdown extends Walker_CategoryDropdown {

	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$args['render_as_list'] = false;
		ceo_taxonomy_walker_dropdown_or_list_start_el( $output, $category, $depth = 0, $args, $id);
	}
}

function ceo_comic_archive_jump_to_chapter($hide = true, $exclude = '', $showcount = false, $jumptoarchive = false, $echo = true, $render_as_list = false) {
	ceo_protect();

	$output = '';
	if ($render_as_list) {
		global $post;
		$the_terms = get_the_terms( $post->ID, 'chapters');
		// echo "<pre>the terms ";print_r($the_terms); echo "</pre>";
		$args = array(
			'walker'			 => new ceo_walker_taxonomy_list(),
			'orderby'            => 'menu_order', 
			'order'              => 'ASC',
			'show_count'         => $showcount,
			'hide_empty'         => $hide,
			'exclude'            => $exclude,
			'echo'               => false,
			'hierarchical'       => 1,
			'taxonomy'           => 'chapters',
			'current_category'	 => $the_terms,
			'title_li'			 => null,
			'jumptoarchive'	 	 => $jumptoarchive,
			'render_as_list'	 => $render_as_list,
		);
		
		$output .= '<ul class="chapter-select">';
		$output .= wp_list_categories( $args );
		$output .= '</ul>';
	} else {
		$args = array(
			'walker' => new ceo_walker_taxonomy_dropdown(),
			'show_option_all'	=>	__('Select','comiceasel').' '.ucwords(ceo_pluginfo('chapter_type_slug_name')),
			'option_none_value'  => '-1',
			'orderby'            => 'menu_order', 
			'order'              => 'ASC',
			'name'               => ceo_pluginfo('chapter_type_slug_name'),
			'show_count'         => $showcount,
			'hide_empty'         => $hide,
			'exclude'            => $exclude,
			'echo'               => false,
			'hierarchical'       => 1,
			'taxonomy'           => 'chapters',
			'hide_if_empty'      => $hide,
			'value_field'	     => 'slug',	
			'jumptoarchive'	 	 => $jumptoarchive,
			'render_as_list'	 => $render_as_list,
		);
		$output .= '<form id="chapter-select" class="chapter-select" method="get">'."\r\n";
		$select  = wp_dropdown_categories( $args );
		$replace = '<select$1 onchange="document.location.href=this.options[this.selectedIndex].value;">';
		$output  .= preg_replace( '#<select([^>]*)>#', $replace, $select ); 
		$output .= "\t<noscript>\r\n";
		$output .= "\t\t<input type=\"submit\" value=\"View\" />\r\n";
		$output .= "\t</noscript>\r\n";
		$output .= "</form>\r\n";
	}
	ceo_unprotect();
	if ($echo) {
		echo $output;
	} else return $output;	
}

class ceo_comic_archive_dropdown_widget extends WP_Widget {
	
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			__CLASS__, // Base ID
			__( 'Comic Easel - Comic Chapters', 'comiceasel' ), // Name
			array( 'classname' => __CLASS__, 'description' => __( 'Display dropdown list of comic chapters.', 'comiceasel' ), )
		);
	}
	
	function widget($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP); 
		echo $before_widget;
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }; 
		if (!isset($instance['hide'])) $instance['hide'] = 1;
		if (!isset($instance['render_as_list'])) $instance['render_as_list'] = false;
		ceo_comic_archive_jump_to_chapter($instance['hide'], $instance['exclude'], $instance['showcount'], $instance['jumptoarchive'], true, $instance['render_as_list']);
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['exclude'] = strip_tags($new_instance['exclude']);
		$instance['hide'] = ($new_instance['hide']) ? 1:0;
		$instance['showcount'] = ($new_instance['showcount']) ? 1:0;
		$instance['jumptoarchive'] = ($new_instance['jumptoarchive']) ? 1:0;
		$instance['render_as_list'] = ($new_instance['render_as_list']) ? 1:0;
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'exclude' => '', 'hide' => 1, 'showcount' => 1, 'jumptoarchive' => 0, 'render_as_list' => 0) );
		$title = $instance['title'];
		$exclude = $instance['exclude'];
		$hide = ($instance['hide']) ? 1:0;
		$showcount = ($instance['showcount']) ? 1:0;
		$jumptoarchive = ($instance['jumptoarchive']) ? 1:0;
		$render_as_list = ($instance['render_as_list']) ? 1:0;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','comiceasel'); ?>&nbsp;<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Exclude Chapters (comma seperated):','comiceasel'); ?>&nbsp;<input class="widefat" id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo esc_attr($exclude); ?>" /></label><br /></p>
		<p><label for="<?php echo $this->get_field_id('hide'); ?>"><?php _e('Hide empty chapters?','comiceasel'); ?>&nbsp;<input id="<?php echo $this->get_field_id('hide'); ?>" name="<?php echo $this->get_field_name('hide'); ?>" type="checkbox" value="1" <?php checked(1, $hide); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id('showcount'); ?>"><?php _e('Show the comic count in parenthesis?','comiceasel'); ?>&nbsp;<input id="<?php echo $this->get_field_id('showcount'); ?>" name="<?php echo $this->get_field_name('showcount'); ?>" type="checkbox" value="1" <?php checked(1, $showcount); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id('jumptoarchive'); ?>"><?php _e('Jump to archive and not first page?','comiceasel'); ?>&nbsp;<input id="<?php echo $this->get_field_id('jumptoarchive'); ?>" name="<?php echo $this->get_field_name('jumptoarchive'); ?>" type="checkbox" value="1" <?php checked(1, $jumptoarchive); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id('render_as_list'); ?>"><?php _e('Show as a list instead of a dropdown?','comiceasel'); ?>&nbsp;<input id="<?php echo $this->get_field_id('render_as_list'); ?>" name="<?php echo $this->get_field_name('render_as_list'); ?>" type="checkbox" value="1" <?php checked(1, $render_as_list); ?> /></label></p>
		<?php
	}
}

