<?php
/*
Plugin Name: Widget Flickr Gallery
Plugin URI: http://www.exilius.net
Description: Display up your latest Flickr submissions or Favories in your sidebar.
Author: Valentin Van Meeuwen
Version: 1.1
Author URI: http://www.exilius.net

/* License

    Widget Flickr Gallery
    Copyright (C) 2009 Konstantin Kovshenin (kovshenin@live.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
*/

/** For compatibility **/
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . DIRECTORY_SEPARATOR . 'wp-content'); // full url - WP_CONTENT_DIR is defined further up

if ( !defined('WP_PLUGIN_DIR') )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' ); // full path, no trailing slash

if ( !defined('WP_PLUGIN_URL') )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . DIRECTORY_SEPARATOR . 'plugins' ); // full url, no trailing slash
	
define('WFG_FOLDER', basename(dirname(__FILE__)));
define('WFG_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WFG_FOLDER );

// Load translations
load_plugin_textdomain('widget-flickr-gallery', str_replace( ABSPATH, '', WFG_DIR ) . DIRECTORY_SEPARATOR . 'languages', false);

function widget_wfg($args, $widget_args = 1) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );
	
	$options = get_option('widget_wfg');
	if ( !isset($options[$number]) )
		return;
		
	extract($options[$number], EXTR_SKIP);
		
		
	?>
	<!-- Widget Flickr Gallery - DotClear - Exilius.net - Start -->
	<div class="widgetwflickrgallery">
		<script type="text/javascript">
		wfg[<?php echo $number; ?>] = new oWFG("<?php echo $userid; ?>", "<?php echo $feed; ?>", <?php echo $nbphotos; ?>, <?php echo $dsptype; ?>, <?php echo $nbpicsline; ?>)
		</script>
		<?php echo ($title ? "<h2>".$title."</h2>\n" : '') ?>
		<div id="wflickrgallery-<?php echo $number; ?>"></div>
		<div class="imageholderend-<?php echo $number; ?>"></div>
		<?php echo ($link ? "<p><a href=\"http://www.exilius.net\" target=\"_blank\">wFlickrGallery - Exilius.net</a></p>\n":''); ?>
	</div>
	<!-- Widget Flickr Gallery - DotClear - Exilius.net - End -->
	<?php 
}

function widget_wfg_control($widget_args) {
	global $wp_registered_widgets;
	
	static $updated = false; // Whether or not we have already updated the data after a POST submit

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );
	

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_wfg');
	if ( !is_array($options) )
		$options = array();
	
	//Mise à jour des donn�es
	if ( !$updated && !empty($_POST['sidebar']) ) {
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		
		if ( isset($sidebars_widgets[$sidebar]) ) {
			$this_sidebar =& $sidebars_widgets[$sidebar];
		} else {
			$this_sidebar = array();
		}

		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( 'disp_sm_feed' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "wfg-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed. "many-$widget_number" is "{id_base}-{widget_number}
					unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['wfg'] as $widget_number => $wfg_instance ) {
			// compile data from $widget_random_image_instance
			$title = strip_tags(stripslashes( $wfg_instance['title']));
			$userid = strip_tags(stripslashes( $wfg_instance['userid']));
			$feed = strip_tags(stripslashes( $wfg_instance['feed']));
			$nbphotos = strip_tags(stripslashes( $wfg_instance['nbphotos']));
			$dsptype = strip_tags(stripslashes( $wfg_instance['dsptype']));
			$nbpicsline = strip_tags(stripslashes( $wfg_instance['nbpicsline']));
			$link = strip_tags(stripslashes( $wfg_instance['link']));
			
			//$options[$widget_number] = array( 'title' => $title );  // Even simple widgets should store stuff in array, rather than in scalar
		
			$options[$widget_number] = compact('title', 'userid', 'feed', 'nbphotos', 'dsptype', 'nbpicsline', 'link');
		}
		
		update_option('widget_wfg', $options);

		$updated = true; // So that we don't go through this more than once
	}
	
	// Here we echo out the form
	if ( $number == -1 ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$title = 'Mes Photos';
		$userid = '?';
		$feed = null;
		$nbphotos = 10;
		$dsptype = 1;
		$nbpicsline = 2;
		$link = true;
		$number = '%i%';
	} 
	else {
		$title = attribute_escape($options[$number]['title']);
		$userid = attribute_escape($options[$number]['userid']);
		$feed = attribute_escape($options[$number]['feed']);
		$nbphotos = attribute_escape($options[$number]['nbphotos']);
		$dsptype = attribute_escape($options[$number]['dsptype']);
		$nbpicsline = attribute_escape($options[$number]['nbpicsline']);
		
		$link =attribute_escape($options[$number]['link']);
	}

	?>
	<p><label for="wfg-title-<?php echo $number; ?>"><?php _e('Title:','widget-flickr-gallery'); ?> <input class="widefat" id="wfg-title-<?php echo $number; ?>" name="wfg[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" /></label></p>
	<p><label for="wfg-userid-<?php echo $number; ?>"><?php _e('Your Flickr Id:','widget-flickr-gallery'); ?> <input class="widefat" id="wfg-userid-<?php echo $number; ?>" name="wfg[<?php echo $number; ?>][userid]" type="text" value="<?php echo $userid; ?>" /></label></p>
	<p><label for="wfg-feed-<?php echo $number; ?>"><?php _e('Feed?','widget-flickr-gallery'); ?>
			<select class="widefat" id="wfg-feed-<?php echo $number; ?>" name="wfg[<?php echo $number; ?>][feed]">
			<option value="gal" <?=($feed=="gal" ? "selected=\"selected\"" : "");?>><?php echo _e('Your Gallery','widget-flickr-gallery')?></option>
			<option value="fav" <?=($feed=="fav" ? "selected=\"selected\"" : "");?>><?php echo _e('Your Favories','widget-flickr-gallery')?></option>
		</select>
	</label></p>
	<p><label for="wfg-dsptype-<?php echo $number; ?>"><?php _e('Display type?','widget-flickr-gallery'); ?>
			<select class="widefat" id="wfg-dsptype-<?php echo $number; ?>" name="wfg[<?php echo $number; ?>][dsptype]">
			<option value="1" <?=($dsptype=="1" ? "selected=\"selected\"" : "");?>><?php echo _e('by vertical accordion','widget-flickr-gallery')?></option>
			<option value="2" <?=($dsptype=="2" ? "selected=\"selected\"" : "");?>><?php echo _e('by squarre inline','widget-flickr-gallery')?></option>
		</select>
	</label></p>
	<p><label for="wfg-nbpicsline-<?php echo $number; ?>"><?php _e('Number photos display by line:','widget-flickr-gallery'); ?> <input class="widefat" id="wfg-nbpicsline-<?php echo $number; ?>" name="wfg[<?php echo $number; ?>][nbpicsline]" type="text" value="<?php echo $nbpicsline; ?>" /></label> (<?php echo _e('2 minimums','widget-flickr-gallery')?>)</p>
	<p><label for="wfg-nbphotos-<?php echo $number; ?>"><?php _e('Number photos display:','widget-flickr-gallery'); ?> <input class="widefat" id="wfg-nbphotos-<?php echo $number; ?>" name="wfg[<?php echo $number; ?>][nbphotos]" type="text" value="<?php echo $nbphotos; ?>" /></label></p>
	<p><label for="wfg-link-<?php echo $number; ?>"><input id="wfg-link-<?php echo $number; ?>" name="wfg[<?php echo $number; ?>][link]" type="checkbox" value="checked" <?php echo $link; ?> /> <?php _e('Link Exilius.net','widget-flickr-gallery'); ?></label></p>
	<input type="hidden" id="wfg-submit-<?php echo $number; ?>" name="wfg-submit-<?php echo $number; ?>" value="1" />
	<?php 

}

function wfg_register() {
	
	if ( !$options = get_option('widget_wfg') )
		$options = array();
	
	$widget_ops = array('classname' => 'widget_many', 'description' => __('Displays random images from RSS photo feeds'));
	$control_ops = array('width' => 600, 'height' => 315, 'id_base' => 'wfg');
	$name = __('Flickr Gallery Widget');
		
	$registered = false;
	foreach ( array_keys($options) as $o ) {
	// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) )
			continue;
		
		// $id should look like {$id_base}-{$o}
		$id = "wfg-$o"; // Never never never translate an id
		$registered = true;
		
		wp_register_widget_control($id ,$name, 'widget_wfg_control', $control_ops, array('number' => $o));
		wp_register_sidebar_widget($id, $name, 'widget_wfg', $widget_ops, array('number' => $o));
	}
	
	if ( !$registered ) {
		wp_register_widget_control('wfg-1' ,$name, 'widget_wfg_control', $control_ops, array('number' => -1));
		wp_register_sidebar_widget('wfg-1', $name, 'widget_wfg', $widget_ops, array('number' => -1));
	}
	
	
	$wfg_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
	if (! is_admin()) {
		wp_enqueue_script('wfg_jquery', $wfg_plugin_url.'/js/jquery-1.4.2.min.js');
		//wp_enqueue_script('jquery');
	}
	wp_enqueue_script('wfg_widget_js', $wfg_plugin_url.'/js/wflickrgallery.js');
	wp_enqueue_script('wfg_widget_lightbox_js', $wfg_plugin_url.'/js/jquery.lightbox-0.5.min.js');
	wp_enqueue_style('wfg_widget_lightbox_css', $wfg_plugin_url.'/css/jquery.lightbox-0.5.css', array(), '0.1', screen);
	wp_enqueue_style('wfg_widget_css', $wfg_plugin_url.'/css/wflickrgallery.css', array(), '0.1', screen);
	
}

add_action('widgets_init', 'wfg_register'');
?>