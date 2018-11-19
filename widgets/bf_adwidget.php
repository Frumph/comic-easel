<?php
/*
Widget Name: BF_adwidget
Description: Display an advertisement for BF based on code, will not display if no-code
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/
Version: 1.0
*/
	
class ceo_bf_adwidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			__CLASS__, // Base ID
			__( 'Comic Easel - BF Ad Widget', 'comiceasel' ), // Name
			array( 'classname' => __CLASS__, 'description' => __( 'Display a BF Advertisement based on dropdown selection.', 'comiceasel' ), ) // Args
		);
	}
	
	function widget($args, $instance) {
		global $post, $wp_query;		
		extract($args, EXTR_SKIP);
		$center = (isset($instance['center'])) ? $instance['center'] : '0';
		$divID = (isset($instance['divID'])) ? $instance['divID'] : '';
		
		if ($divID) {
			echo $before_widget;
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']); 
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
			if ($center) echo '<center>';
				if ($divID) echo '<div id="'.$divID.'"></div>';
			if ($center) echo '</center>';
			echo $after_widget;
		}
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['divID'] = strip_tags($new_instance['divID']);
		$instance['center'] =  (bool)( $new_instance['center'] == 1 ? true : false );
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'divID' => '', 'center' => false) );
		$title = strip_tags($instance['title']);
		$divID = (isset($instance['divID'])) ? $instance['divID'] : '';
		$center = (isset($instance['center'])) ? $instance['center'] : '0';
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'comiceasel'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('divID'); ?>"><?php _e('div ID:','comiceasel'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('divID'); ?>" name="<?php echo $this->get_field_name('divID'); ?>" type="text" value="<?php echo esc_attr($divID); ?>" /></label><br />
		<?php _e('*Found on the customer portal page for the adsize you want to use.'); ?></p>
		<p><label for="<?php echo $this->get_field_id('center'); ?>"><?php _e('Add Centering?','comiceasel'); ?> <input id="<?php echo $this->get_field_id('center'); ?>" name="<?php echo $this->get_field_name('center'); ?>" type="checkbox" value="1" <?php checked(true, $center); ?> /></label></p>		
		<br />
	<?php
	}
}

