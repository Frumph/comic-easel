<?php
/*
Widget Name: Comic Calendar
Widget URI: http://frumph.net/
Description: Display a calendar of this months posts of comics
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/
Version: 1.03
*/

if (!defined('ABSPATH')) exit;

/**
 * Display calendar with days that have posts as links.
 *
 * The calendar is cached, which will be retrieved, if it exists. If there are
 * no posts for the month, then it will not be displayed.
 *
 * @since 1.0.0
 *
 * @param bool $initial Optional, default is true. Use initial calendar names.
 * @param bool $echo Optional, default is true. Set to false for return.
 */
function ceo_get_calendar($initial = true, $echo = true, $taxonomy = 'post') {
	global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

	if (empty($taxonomy)) $taxonomy = 'post';
	$taxonomy = is_scalar($taxonomy) ? (string)$taxonomy : 'post';
	if (empty($taxonomy)) $taxonomy = 'post';

	$taxonomy_query_value = rawurlencode($taxonomy);
	if ( get_option('permalink_structure') ) { $the_post_type = '?post_type='.$taxonomy_query_value; } else { $the_post_type = '&post_type='.$taxonomy_query_value; }
	
	$cache = array();
	$key = md5( $m . $monthnum . $year );
	if ( $cache = wp_cache_get( 'get_comic_calendar', 'calendar' ) ) {
		if ( is_array($cache) && isset( $cache[ $key ] ) ) {
			if ( $echo ) {
				echo apply_filters( 'get_comic_calendar',  $cache[$key] );
				return;
			} else {
				return apply_filters( 'get_comic_calendar',  $cache[$key] );
			}
		}
	}

	if ( !is_array($cache) )
		$cache = array();

	// Quick check. If we have no posts at all, abort!
	if ( !$posts ) {
		$gotsome = $wpdb->get_var($wpdb->prepare("SELECT 1 as test FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' LIMIT 1", $taxonomy));
		if ( !$gotsome ) {
			$cache[ $key ] = '';
			wp_cache_set( 'get_comic_calendar', $cache, 'calendar' );
			return;
		}
	}

	if ( isset($_GET['w']) && is_scalar($_GET['w']) )
		$w = ''.intval(wp_unslash($_GET['w']));

	// week_begins = 0 stands for Sunday
	$week_begins = intval(get_option('start_of_week'));

	// Let's figure out when we are
	if ( !empty($monthnum) && !empty($year) ) {
		$thismonth = ''.zeroise(intval($monthnum), 2);
		$thisyear = ''.intval($year);
	} elseif ( !empty($w) ) {
		// We need to get the month from MySQL
		$thisyear = ''.intval(substr($m, 0, 4));
		$d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
		$thismonth = $wpdb->get_var($wpdb->prepare("SELECT DATE_FORMAT((DATE_ADD(%s, INTERVAL %d DAY) ), '%%m')", $thisyear . '0101', $d));
	} elseif ( !empty($m) ) {
		$thisyear = ''.intval(substr($m, 0, 4));
		if ( strlen($m) < 6 )
				$thismonth = '01';
		else
				$thismonth = ''.zeroise(intval(substr($m, 4, 2)), 2);
	} else {
		$thisyear = gmdate('Y', current_time('timestamp'));
		$thismonth = gmdate('m', current_time('timestamp'));
	}

	$unixmonth = mktime(0, 0 , 0, $thismonth, 1, $thisyear);
	$calendar_month_start = sprintf('%04d-%02d-01', $thisyear, $thismonth);

	// Get the next and previous month and year with at least one post
	$previous = $wpdb->get_row($wpdb->prepare("SELECT DISTINCT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date < %s
		AND post_type = %s AND post_status = 'publish'
			ORDER BY post_date DESC
			LIMIT 1", $calendar_month_start, $taxonomy));
	$next = $wpdb->get_row($wpdb->prepare("SELECT	DISTINCT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date > %s
		AND MONTH( post_date ) != MONTH( %s )
		AND post_type = %s AND post_status = 'publish'
			ORDER	BY post_date ASC
			LIMIT 1", $calendar_month_start, $calendar_month_start, $taxonomy));

	/* translators: Calendar caption: 1: month name, 2: 4-digit year */
	$calendar_caption = _x('%1$s %2$s', 'calendar caption', 'comiceasel');
	$calendar_output = '<table id="wp-calendar" summary="' . esc_attr__('Calendar','comiceasel') . '">
	<caption>' . sprintf($calendar_caption, $wp_locale->get_month($thismonth), date('Y', $unixmonth)) . '</caption>
	<thead>
	<tr>';

	$myweek = array();

	for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
		$myweek[] = $wp_locale->get_weekday(($wdcount+$week_begins)%7);
	}

	foreach ( $myweek as $wd ) {
		$day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
		$wd = esc_attr($wd);
		$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
	}

	$calendar_output .= '
	</tr>
	</thead>

	<tfoot>
	<tr>';

	if ( $previous ) {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . get_month_link($previous->year, $previous->month) . $the_post_type.'" title="' . sprintf(__('View posts for %1$s %2$s','comiceasel'), $wp_locale->get_month($previous->month), date('Y', mktime(0, 0 , 0, $previous->month, 1, $previous->year))) . '">&laquo; ' . $wp_locale->get_month_abbrev($wp_locale->get_month($previous->month)) . '</a></td>';
	} else {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
	}

	$calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';

	if ( $next ) {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . get_month_link($next->year, $next->month) . $the_post_type.'" title="' . esc_attr( sprintf(__('View posts for %1$s %2$s','comiceasel'), $wp_locale->get_month($next->month), date('Y', mktime(0, 0 , 0, $next->month, 1, $next->year))) ) . '">' . $wp_locale->get_month_abbrev($wp_locale->get_month($next->month)) . ' &raquo;</a></td>';
	} else {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
	}

	$calendar_output .= '
	</tr>
	</tfoot>

	<tbody>
	<tr>';

	// Get days with posts
	$dayswithposts = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT DAYOFMONTH(post_date)
		FROM $wpdb->posts WHERE MONTH(post_date) = %d
		AND YEAR(post_date) = %d
		AND post_type = %s AND post_status = 'publish'
		AND post_date < %s", $thismonth, $thisyear, $taxonomy, current_time('mysql')), ARRAY_N);
	if ( $dayswithposts ) {
		foreach ( (array) $dayswithposts as $daywith ) {
			$daywithpost[] = $daywith[0];
		}
	} else {
		$daywithpost = array();
	}

	$user_agent = isset($_SERVER['HTTP_USER_AGENT']) && is_scalar($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
	if (strpos($user_agent, 'MSIE') !== false || stripos($user_agent, 'camino') !== false || stripos($user_agent, 'safari') !== false)
		$ak_title_separator = "\n";
	else
		$ak_title_separator = ', ';

	$ak_titles_for_day = array();
	$ak_post_titles = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, DAYOFMONTH(post_date) as dom "
		."FROM $wpdb->posts "
		."WHERE YEAR(post_date) = %d "
		."AND MONTH(post_date) = %d "
		."AND post_date < %s "
		."AND post_type = 'post' AND post_status = 'publish'"
	, $thisyear, $thismonth, current_time('mysql')));
	if ( $ak_post_titles ) {
		foreach ( (array) $ak_post_titles as $ak_post_title ) {

				$post_title = esc_attr( apply_filters( 'the_title', $ak_post_title->post_title, $ak_post_title->ID ) );

				if ( empty($ak_titles_for_day['day_'.$ak_post_title->dom]) )
					$ak_titles_for_day['day_'.$ak_post_title->dom] = '';
				if ( empty($ak_titles_for_day["$ak_post_title->dom"]) ) // first one
					$ak_titles_for_day["$ak_post_title->dom"] = $post_title;
				else
					$ak_titles_for_day["$ak_post_title->dom"] .= $ak_title_separator . $post_title;
		}
	}


	// See how much we should pad in the beginning
	$pad = calendar_week_mod(date('w', $unixmonth)-$week_begins);
	if ( 0 != $pad )
		$calendar_output .= "\n\t\t".'<td colspan="'. esc_attr($pad) .'" class="pad">&nbsp;</td>';

	$daysinmonth = intval(date('t', $unixmonth));
	for ( $day = 1; $day <= $daysinmonth; ++$day ) {
		if ( isset($newrow) && $newrow )
			$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
		$newrow = false;

		if ( $day == gmdate('j', current_time('timestamp')) && $thismonth == gmdate('m', current_time('timestamp')) && $thisyear == gmdate('Y', current_time('timestamp')) )
			$calendar_output .= '<td id="today">';
		else
			$calendar_output .= '<td>';

		if ( in_array($day, $daywithpost) ) // any posts today?
		    $calendar_output .= '<a href="' . get_day_link($thisyear, $thismonth, $day) . $the_post_type.'">'.$day.'</a>';
		else
			$calendar_output .= $day;
		$calendar_output .= '</td>';

		if ( 6 == calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins) )
			$newrow = true;
	}

	$pad = 7 - calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins);
	if ( $pad != 0 && $pad != 7 )
		$calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';

	$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

	$cache[ $key ] = $calendar_output;

	wp_cache_set( 'get_comic_calendar', $cache, 'calendar' );

	if ( $echo )
		echo apply_filters( 'get_comic_calendar',  $calendar_output );
	else
		return apply_filters( 'get_comic_calendar',  $calendar_output );

}

/**
 * Purge the cached results of get_calendar.
 *
 * @see get_calendar
 * @since 2.1.0
 */
function ceo_delete_get_calendar_cache() {
	wp_cache_delete( 'get_comic_calendar', 'calendar' );
}
add_action( 'save_post', 'ceo_delete_get_calendar_cache' );
add_action( 'delete_post', 'ceo_delete_get_calendar_cache' );
add_action( 'update_option_start_of_week', 'ceo_delete_get_calendar_cache' );
add_action( 'update_option_gmt_offset', 'ceo_delete_get_calendar_cache' );


class ceo_calendar_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			__CLASS__, // Base ID
			__( 'Comic Easel - Calendar', 'comiceasel' ), // Name
			array( 'classname' => __CLASS__, 'description' => __( 'Display a calendar showing this months posts. (this calendar does not drop lines if there is no title given.', 'comiceasel' ), ) // Args
		);
	}

	function widget($args, $instance) {
		global $post, $wp_query;
		extract($args, EXTR_SKIP);

		echo $before_widget;
		ceo_protect();
		if (!empty($instance)) { extract($instance); } ?>
			<div id="wp-calendar-head"></div>
			<div id="wp-calendar-wrap">
				<?php if (!empty($thumbnail)) { ?>
					<div class="wp-calendar-download">
					<?php if (!empty($link)) { ?>
						<a href="<?php echo esc_url($link); ?>"><img src="<?php echo esc_url($thumbnail); ?>" class="wp-calendar-thumb" alt="" /></a>
					<?php } else { ?>
						<img src="<?php echo esc_url($thumbnail); ?>" class="wp-calendar-thumb" alt="" />
					<?php } ?>
						<div class="wp-calendar-download-links">
							<?php if (!empty($small) || !empty($medium) || !empty($large)) { ?>
								<?php esc_html_e('DOWNLOAD','comiceasel'); ?>
								<?php
								  foreach (array(
								    'small' => array(__('Download Small', 'comiceasel'), __('S', 'comiceasel')),
								    'medium' => array(__('Download Medium', 'comiceasel'), __('M', 'comiceasel')),
								  	'large' => array(__('Download Large', 'comiceasel'), __('L', 'comiceasel'))
								 	) as $field => $text) {
								 		if (!empty(${$field})) {
								 			?><a href="<?php echo esc_url(${$field}); ?>" title="<?php echo esc_attr($text[0]); ?>"><?php echo esc_html($text[1]); ?></a><?php
								 		}
								 	}
							} ?>
						</div>
					</div>
				<?php } ?>
			<?php
				ceo_get_calendar(true, true, 'comic'); 
			?>
			</div>
			<div id="wp-calendar-foot"></div>
		<?php
		echo $after_widget;
		ceo_unprotect();
	}

	function update($new_instance, $old_instance = array()) {
		$instance = array();
		foreach (array('thumbnail', 'small', 'medium', 'large', 'link') as $field) {
			if (isset($new_instance[$field])) {	$instance[$field] = wp_strip_all_tags($new_instance[$field]); }
		}

		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'thumbnail' => '', 'small' => '', 'medium' => '', 'large' => '', 'link' => '') );

		$thumbnail = wp_strip_all_tags($instance['thumbnail']);
		$small = wp_strip_all_tags($instance['small']);
		$medium = wp_strip_all_tags($instance['medium']);
		$large = wp_strip_all_tags($instance['large']);
		$link = $instance['link'];

		foreach (array(
			'thumbnail' => __('Thumbnail URL (178px by 130px):','comiceasel'),
			'link' => array('label' => __('Add link on thumbnails:','comiceasel'), 'after' => '<hr />'),
			'small' => __('Wallpaper URL (Small):','comiceasel'),
			'medium' => __('Wallpaper URL (Medium):','comiceasel'),
			'large' => __('Wallpaper URL (Large):','comiceasel'),
		) as $field => $label) {
			unset($after);
			if (is_array($label)) { extract($label); }
			?><p>
				<label for="<?php echo esc_attr($this->get_field_id($field)); ?>"><?php echo esc_html($label) ;?>
				<input class="widefat"
							 id="<?php echo esc_attr($this->get_field_id($field)); ?>"
							 name="<?php echo esc_attr($this->get_field_name($field)); ?>"
							 type="text"
							 value="<?php echo esc_attr($instance[$field]); ?>" />
				</label>
			</p><?php

			if (isset($after)) { echo $after; }
		}
	}
}
