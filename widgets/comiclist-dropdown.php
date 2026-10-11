<?php
/*
Widget Name: Comic List Dropdown of Current Chapter
Widget URI: https://github.com/Frumph/comic-easel
Description: Display a list of links of the latest comics.
Author: Philip M. Hofer (Frumph)
Version: 1.02
*/

if (!defined('ABSPATH')) exit;

function ceo_list_jump_to_comic($exclude = '', $return = false) {
	global $post;
	ceo_protect();
	$output = '';
	$terms = wp_get_object_terms( $post->ID, 'chapters');
	if (!empty($terms)){
		$term = reset($terms); // only one chapter
		$output = '<form method="get" class="comic-list-dropdown-form">';
		// js/jump-menu.js goes to the chosen comic when a select with this class changes.
		ceo_enqueue_jump_menu();
		$output .= '<select class="ceo-jump-menu">';
		$level = 0;
		$output .= '<option class="level-select" value="">'.esc_html__('Jump To','comic-easel').'</option>';
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
			if (!empty($permalink)) $output .='<option class="level-0" value="'.esc_url($permalink).'">'.esc_html($qpost->post_title).'</option>';
		}
		$output .= '</select>';
		$output .= '<noscript>';
		$output .= '<div><input type="submit" value="View" /></div>';
		$output .= '</noscript>';
		$output .= '</form>';
		if ($return) {
			ceo_unprotect();
			return $output;
		} else echo wp_kses($output, ceo_comic_list_dropdown_allowed_html());
	}
	ceo_unprotect();
}

/**
 * The tags and attributes the comic dropdown prints, for wp_kses().
 *
 * wp_kses_post() would strip the form controls, so the dropdown is filtered against exactly
 * what it emits.
 */
function ceo_comic_list_dropdown_allowed_html() {
	return array(
		'form'     => array( 'method' => true, 'class' => true ),
		'select'   => array( 'class' => true ),
		'option'   => array( 'class' => true, 'value' => true ),
		'noscript' => array(),
		'div'      => array(),
		'input'    => array( 'type' => true, 'value' => true ),
	);
}

class ceo_comic_list_dropdown_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			__CLASS__, // Base ID
			__( 'Comic Easel - Comic List Dropdown', 'comic-easel' ), // Name
			array( 'classname' => __CLASS__, 'description' => __( 'Display dropdown list of comics.', 'comic-easel' ), ) // Args
		);
	}
	
	/**
	 * Every setting the widget reads, with the value a newly added widget starts with.
	 *
	 * widget() and update() apply these, not just form(): a widget that has never been saved
	 * (a Legacy Widget block left untouched) has no settings at all.
	 */
	private static function defaults() {
		return array( 'title' => '', 'exclude' => '' );
	}
	
	function widget($args, $instance) {
		global $post;

		extract($args, EXTR_SKIP); 
		$instance = wp_parse_args( (array) $instance, self::defaults() );
		echo wp_kses_post($before_widget);
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo wp_kses_post($before_title . $title . $after_title); }; 
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
		echo wp_kses_post($after_widget);
	}
	
	function update($new_instance, $old_instance) {
		$new_instance = wp_parse_args( (array) $new_instance, self::defaults() );
		$instance = $old_instance;
		$instance['title'] = wp_strip_all_tags($new_instance['title']);
		$instance['exclude'] = wp_strip_all_tags($new_instance['exclude']);
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, self::defaults() );
		$title = $instance['title'];
		$exclude = $instance['exclude'];
		?>
		<p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:','comic-easel'); ?> <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		</p>
		<?php
	}
}
