<?php
/*
 * Plugin Name: Future
 * Author URI: http://www.sudvarg.com
 * Plugin URI: http://www.sudvarg.com/wordpress.php
 * Description: The 'Future' plugin allows posts with future scheduled dates to be integrated into a site. This can be useful, for example, with events that have associated dates in the future. Such future posts can, with this plugin, be displayed, both individually and in archive lists. This plugin also adds functionality to the built-in calendar widget. It adds a checkbox to include future posts in the calendar, and it allows the calendar to be configured to show posts from a single category.
 * Author: Marion Sudvarg
 * Version: 1.1.2
 */

/* The following license information applies to this plugin:

Copyright 2013 Sudvarg Digital Solutions (email : msudvarg@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.


*/


/*  The following license information applies to the following block:

Copyright 2009  Show Future Posts on Single Post Templates (email : stanley@dumanig.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.


*/

add_filter('the_posts', 'futurems_show_posts');

function futurems_show_posts($posts)
{
   global $wp_query, $wpdb;

   if(is_single() && $wp_query->post_count == 0)
   {
      $posts = $wpdb->get_results($wp_query->request);
   }

   return $posts;
}

/* End Stanley Dumanig's Show Future Posts on Single Post Templates block */


class Futurems_Widget_Calendar extends WP_Widget {

  function __construct() {
		$widget_ops = array('classname' => 'widget_calendar', 'description' => __( 'A calendar of your site&#8217;s posts') );
		parent::__construct('calendar', __('Calendar'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo '<div id="calendar_wrap">';
		futurems_get_calendar(true, true, $instance['category'], $instance['future']);
		echo '</div>';
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['category'] = $new_instance['category'];
		$instance['future'] = $new_instance['future'];
        if ($instance['future'] == "") { $instance['future'] = 0; }
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'category' => 0, 'future' => 0 ) );
		$title = strip_tags( $instance['title'] );
?>
		<p><label for="<?php echo $this->get_field_id("title"); ?>"><?php _e("Title:"); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id("category"); ?>"><?php _e("Category:"); ?></label>
		<?php wp_dropdown_categories( array( 'name' => $this->get_field_name("category"), 'selected' => $instance['category'], 'orderby' => 'Name' , 'hierarchical' => 1, 'show_option_all' => __("All Categories"), 'hide_empty' => '0' ) ); ?></p>

		<p><label for="<?php echo $this->get_field_id("future"); ?>"><?php _e("Include Future Posts?"); ?></label>
		<input type="checkbox" value="1" id=<?php echo $this->get_field_id("future"); ?> name="<?php echo $this->get_field_name("future"); ?>" <?php if($instance['future'] == 1) { echo "checked"; } ?> /></p>
<?php
	}
}


function futurems_widget_init() {
	unregister_widget('WP_Widget_Calendar');
	register_widget('Futurems_Widget_Calendar');
}
add_action('widgets_init', 'futurems_widget_init');


function futurems_get_calendar($initial = true, $echo = true, $category = 0, $future) {
	global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;
    
    $sql_futurems = "";
    if ($future == 1) { $sql_futurems = "OR post_status = 'future'"; }

	$cache = array();
	$key = md5( $m . $monthnum . $year );
	if ( $cache = wp_cache_get( 'futurems_get_calendar', 'calendar' ) ) {
		if ( is_array($cache) && isset( $cache[ $key ] ) ) {
			if ( $echo ) {
				echo apply_filters( 'futurems_get_calendar',  $cache[$key] );
				return;
			} else {
				return apply_filters( 'futurems_get_calendar',  $cache[$key] );
			}
		}
	}

	if ( !is_array($cache) )
		$cache = array();

	// Quick check. If we have no posts at all, abort!
	if ( !$posts ) {
		$gotsome = $wpdb->get_var("SELECT 1 as test FROM $wpdb->posts WHERE post_type = 'post' AND (post_status = 'publish' $sql_futurems) LIMIT 1");
		if ( !$gotsome ) {
			$cache[ $key ] = '';
			wp_cache_set( 'futurems_get_calendar', $cache, 'calendar' );
			return;
		}
	}

	if ( isset($_GET['w']) )
		$w = ''.intval($_GET['w']);

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
		$thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
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
	$last_day = date('t', $unixmonth);

	// Get the next and previous month and year with at least one post
	$previous = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts " . futurems_single_category_joins($category) . "
		WHERE post_date < '$thisyear-$thismonth-01'" . futurems_category_sql($category) . "
		AND post_type = 'post' AND (post_status = 'publish' $sql_futurems)
			ORDER BY post_date DESC
			LIMIT 1");
	$next = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts " . futurems_single_category_joins($category) . "
		WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:59'" . futurems_category_sql($category) . "
		AND post_type = 'post' AND (post_status = 'publish' $sql_futurems)
			ORDER BY post_date ASC
			LIMIT 1");

	/* translators: Calendar caption: 1: month name, 2: 4-digit year */
	$calendar_caption = _x('%1$s %2$s', 'calendar caption');
	$calendar_output = '<table id="wp-calendar">
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
		$calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . futurems_link(get_month_link($previous->year, $previous->month),$category,$future) . '" title="' . esc_attr( sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($previous->month), date('Y', mktime(0, 0 , 0, $previous->month, 1, $previous->year)))) . '">&laquo; ' . $wp_locale->get_month_abbrev($wp_locale->get_month($previous->month)) . '</a></td>';
	} else {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
	}

	$calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';

	if ( $next ) {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . futurems_link(get_month_link($next->year, $next->month),$category,$future) . '" title="' . esc_attr( sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($next->month), date('Y', mktime(0, 0 , 0, $next->month, 1, $next->year))) ) . '">' . $wp_locale->get_month_abbrev($wp_locale->get_month($next->month)) . ' &raquo;</a></td>';
	} else {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
	}

	$calendar_output .= '
	</tr>
	</tfoot>

	<tbody>
	<tr>';

	// Get days with posts
	$dayswithposts = $wpdb->get_results("SELECT DISTINCT DAYOFMONTH(post_date)
		FROM $wpdb->posts" . futurems_single_category_joins($category) . " WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00'" . futurems_category_sql($category) . "
		AND post_type = 'post' AND (post_status = 'publish' $sql_futurems) 
		AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'", ARRAY_N);
	if ( $dayswithposts ) {
		foreach ( (array) $dayswithposts as $daywith ) {
			$daywithpost[] = $daywith[0];
		}
	} else {
		$daywithpost = array();
	}

	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'camino') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false)
		$ak_title_separator = "\n";
	else
		$ak_title_separator = ', ';

	$ak_titles_for_day = array();
	$ak_post_titles = $wpdb->get_results("SELECT ID, post_title, DAYOFMONTH(post_date) as dom "
		."FROM $wpdb->posts " . futurems_single_category_joins($category)
		."WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00' "
		."AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59' " . futurems_category_sql($category)
		."AND post_type = 'post' AND (post_status = 'publish' $sql_futurems)"
	);
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
				$calendar_output .= '<a href="' . futurems_link(get_day_link( $thisyear, $thismonth, $day ),$category,$future) . '" title="' . esc_attr( $ak_titles_for_day[ $day ] ) . "\">$day</a>";
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
	wp_cache_set( 'futurems_get_calendar', $cache, 'calendar' );

	if ( $echo )
		echo apply_filters( 'futurems_get_calendar',  $calendar_output );
	else
		return apply_filters( 'futurems_get_calendar',  $calendar_output );

}

function futurems_link($link,$category,$future) {
    $arr_params = array();
    if ($category > 0) { $arr_params['cat'] = $category; }
    if ($future == 1) { $arr_params['future'] = "all"; }
    return add_query_arg( $arr_params, $link );
}

function futurems_category_sql($category) {
	global $wpdb;
	if ( $category > 0 ) { return " AND $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id IN($category) "; }
    else { return " "; }
}

function futurems_single_category_joins($category) {
	global $wpdb;
	if ( $category > 0 ) {
		return "
            wposts LEFT JOIN $wpdb->postmeta wpostmeta ON wposts.ID = wpostmeta.post_id 
			LEFT JOIN $wpdb->term_relationships ON (wposts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
        ";
	}
	else { return " "; }
}

add_filter('get_calendar', 'futurems_get_calendar');


function futurems_show_post_list($query) {
    if ( $query->is_main_query() ) {
        $query->set( 'order', 'ASC' );
        $query->set( 'post_status', 'future' );
    }
}

function futurems_show_all_post_list($query) {
    if ( $query->is_main_query() ) {
        $query->set( 'post_status', array('future','publish') );
    }
}

function futurems_get_adjacent_post($sql) {
  return preg_replace("/ AND p.post_status = '[^']*'/", "AND ( p.post_status = 'future' )", $sql);
}

function futurems_get_adjacent_post_all($sql) {
  return preg_replace("/ AND p.post_status = '[^']*'/", "AND ( p.post_status = 'publish' OR p.post_status = 'future' )", $sql);
}

function futurems_permalink($link) {
    $future = $_GET['future'];
    if (preg_match('/\?/',$link)) {
        $link = $link . "&future=$future";
    }
    else { $link = $link . "?future=$future"; }
    return $link;
}



if ( in_array(@$_GET['future'], array('true','all') ) ) {
    add_filter('post_link', 'futurems_permalink');
    if ($_GET['future'] == "true") {
        add_action( 'pre_get_posts', 'futurems_show_post_list' );
        add_filter('get_next_post_where', 'futurems_get_adjacent_post');
        add_filter('get_previous_post_where', 'futurems_get_adjacent_post');
    }
    else {
        add_action( 'pre_get_posts', 'futurems_show_all_post_list' );
        add_filter('get_next_post_where', 'futurems_get_adjacent_post_all');
        add_filter('get_previous_post_where', 'futurems_get_adjacent_post_all');
    }
}

?>
