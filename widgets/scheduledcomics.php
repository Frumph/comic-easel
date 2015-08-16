<?php
/*
Widget Name: Scheduled Posts
Widget URI: http://comiceasel.com/
Description: Display a list of comic posts that are due to be scheduled.
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/
Version: 1.04
*/

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
			echo '<ul><li>'.__('None','comiceasel').'</li></ul>';
		} else { ?>
			<ul>
			<?php foreach($scheduled_posts as $post) : ?>
				<li><span class="scheduled-post-date"><?php echo date('m/d/Y',strtotime($post->post_date)); ?></span> <span class="scheduled-post-title"><?php echo $post->post_title; ?></span></li>
			<?php endforeach; ?>
			</ul>
		<?php } 
		echo $after_widget;
		ceo_unprotect();
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<?php
	}
}

