<?php
/*
Widget Name: Cast Hovercard
Widget URI: http://www.cosmichellcats.com/wp/2012/08/8923
Description: Creates a Hovercard with the bio of a cast member on a comic easel site. Created by Christopher Maverick of <A href="http://www.cosmichellcats.com">Cosmic Hellcats</a> fame. Requires comic-easel by Philip M. Hofer (Frumph)
Version: 1.2
Author: Christoper Maverick
Author URI: http://chrismaverick.com
License: GPL2

    Copyright 2012  Christopher Maverick  (email : mav@cosmichellcats.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Only enqueue if the widget is active
if (is_active_widget(false, false, 'ceo_casthover_reference_widget', true)) {
	add_filter('ceo_display_comic_characters', 'ceo_add_characters_hovercards');
	add_action('wp_enqueue_scripts', 'ceo_casthover_res_init_scripts');
	add_action('wp_print_styles', 'ceo_casthover_res_init_styles');
}

function ceo_casthover_res_init_styles() {
	wp_register_style('casthover-css', plugins_url('comic-easel/css/casthover.css'));
	wp_enqueue_style('casthover-css');
}

function ceo_casthover_res_init_scripts() {
	wp_enqueue_script('casthover-js', plugins_url('comic-easel/js/casthoverfunc.js'), array('jquery'), '1.2', true);
}

function ceo_insert_character_hovercard($character) {
	$ccard = '';
	if ($character) {
		$ccard .= '<div class="casthover-hovercard" id="chc-'.$character.'">';
		$ccard .= do_shortcode('[cast-page character="'.$character.'"]');
		$ccard .= '</div>';
	}
	return $ccard;
}

function ceo_add_characters_hovercards($post_characters) {
	global $post;
	$mychars = '<div class="comic-characters">'.__('Characters', 'comiceasel').': ';
	$terms = get_the_terms( $post->ID, 'characters' );
	$return = '';
	if ( !empty( $terms ) ) {
		$out = array();
		foreach ( $terms as $term ) {
			$out[] = '<span class="casthover-hovercard-hook"><a href="'.get_term_link($term->slug, 'characters').'">'.$term->name.'</a>'.ceo_insert_character_hovercard($term->slug).'</span>';
		}
		$return = join( ', ', $out );
	}
	$mychars .= $return . '</div>';
	return $mychars;
}

//widget code below here
class ceo_casthover_reference_widget extends WP_Widget {
	
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			__CLASS__, // Base ID
			__( 'Comic Easel - Cast Hover', 'comiceasel' ), // Name
			array( 'classname' => __CLASS__, 'description' => __( 'Creates a grid of avatars for characters in the current comic.', 'comiceasel' ), ) // Args
		);
	}
	
	function widget($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP);
		ceo_protect();
		if ((is_single() && ($post->post_type == 'comic')) || is_home() || is_front_page()) {
			if ( ((is_home() || is_front_page()) && (is_paged() || ceo_pluginfo('disable_comic_on_home_page') || is_page())) ) return;
			// This section allows the plugin to work in any sidebar even on home, except (paged) files
			if ((is_home() || is_front_page()) && !is_paged() && !ceo_pluginfo('disable_comic_on_home_page')) {
				$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'asc' : 'desc';
				$args = array(
						'showposts' => 1,
						'posts_per_page' => 1,
						'order' => $order,
						'post_type' => 'comic'
						);
				$posts = get_posts($args);
				$post = reset($posts);
			}

			$post_characters = get_the_terms( $post->ID, 'characters');
			if (!empty($post_characters)) {
				echo $before_widget;
				$title = apply_filters( 'widget_title', $instance['title'] );
				if ($title) {
					echo $before_title . $title . $after_title;
				}				
				?><div class="castrefwidget-wrapper"><?php 
				foreach ( $post_characters as $mychar ) {
					$out[] = '<span class="castrefwidget-line casthover-hovercard-hook"><a href="'.get_term_link($mychar->slug, 'characters').'"><div class="castrefwidget-block character-'.$mychar->slug.'"></div></a>'.ceo_insert_character_hovercard($mychar->slug).'</span>';
				}
				echo join('',$out);
				?></div><?php 
				echo $after_widget;
			}
			ceo_unprotect();
		}
	}
	
	function form($instance) {
		if (isset($instance['title'])) {
			$title = $instance['title'];
		} else {
			$title = __( '', 'comiceasel' );
		} 
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'comiceasel' ); ?></label><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
	<?php }
}
