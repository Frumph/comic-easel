<?php
/*
Widget Name: Scheduled Posts
Widget URI: https://github.com/Frumph/comic-easel
Description: Display a list of comic posts that are due to be scheduled.
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/
Version: 1.04
*/

if (!defined('ABSPATH')) exit;

class ceo_scheduled_comics_widget extends WP_Widget {
	
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			__CLASS__, // Base ID
			__( 'Comic Easel - Scheduled Posts', 'comiceasel' ), // Name
			array( 'classname' => __CLASS__, 'description' => __( 'Display a list of comics that are scheduled to be published.', 'comiceasel' ), ) // Args
		);
	}
	
	function widget($args, $instance) {
		extract($args, EXTR_SKIP); 
		echo $before_widget;
		ceo_protect();
		$title = empty($instance['title']) ? __('Scheduled Comics','comiceasel') : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }; 
		$args = array(
				'post_status' => 'future',
				'showposts' => -1,
//				'numberposts' => -1,
				'post_type' => 'comic'
				);
		$scheduled_posts = get_posts($args);
		if (empty($scheduled_posts)) {
			echo '<ul><li>'.esc_html__('None','comiceasel').'</li></ul>';
		} else { ?>
			<ul>
			<?php foreach($scheduled_posts as $post) : ?>
				<li><span class="scheduled-post-date"><?php echo esc_html(date('m/d/Y',strtotime($post->post_date))); ?></span> <span class="scheduled-post-title"><?php echo wp_kses_post($post->post_title); ?></span></li>
			<?php endforeach; ?>
			</ul>
		<?php } 
		echo $after_widget;
		ceo_unprotect();
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = wp_strip_all_tags($new_instance['title']);
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = wp_strip_all_tags($instance['title']);
		?>
		<p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:','comiceasel'); ?> <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<?php
	}
}
