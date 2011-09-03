<?php
/*
Plugin Name: Share Google +1,Google buzz
Plugin URI: http://emsphere.info/wp-plugin/google-1google-buzz-buttons.html
Description: Puts Google +1,Google buzz share buttons of your choice above or below your posts.
Author: Milos Lony
Version: 1.0
Author URI: http://emsphere.info
*/

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/


// ACTION AND FILTERS

add_action('init', 'milos_1_init');

add_filter('the_content', 'milos_1_content');

add_filter('the_excerpt', 'milos_1_excerpt');

add_filter('plugin_action_links', 'milos_1_add_settings_link', 10, 2 );

add_action('admin_menu', 'milos_1_menu');

add_shortcode( 'milos_1', 'milos_1_shortcode' );

// PUBLIC FUNCTIONS

function milos_1_init() {
	// DISABLED IN THE ADMIN PAGES
	if (is_admin()) {
		return;
	}

	//GET ARRAY OF STORED VALUES
	$option = milos_1_get_options_stored();


	if ($option['active_buttons']['buzz']==true) {
		wp_enqueue_script('milos_1_buzz', 'http://www.google.com/buzz/api/button.js');
	}
	if ($option['active_buttons']['google1']==true) {
		wp_enqueue_script('milos_1_google1', 'http://apis.google.com/js/plusone.js');
	}

}    


function milos_1_menu() {
	add_options_page('Google +1,Google buzz button Options', 'Google +1,Google buzz button', 'manage_options', 'milos_1_options', 'milos_1_options');
}


function milos_1_add_settings_link($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
 
	if ($file == $this_plugin){
		$settings_link = '<a href="admin.php?page=milos_1_options">'.__("Settings").'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
} 


function milos_1_content ($content) {
	return milos_1 ($content, 'the_content');
}


function milos_1_excerpt ($content) {
	return milos_1 ($content, 'the_excerpt');
}


function milos_1 ($content, $filter, $link='', $title='') {
	static $last_execution = '';


	if ($filter=='the_excerpt' and $last_execution=='the_content') {

		remove_filter('the_content', 'milos_1_content');
		$last_execution = 'the_excerpt';
		return the_excerpt();
	}
	if ($filter=='the_excerpt' and $last_execution=='the_excerpt') {

		add_filter('the_content', 'milos_1_content');
	}


	$custom_field_disable = get_post_custom_values('milos_1_disable');
	if ($custom_field_disable[0]=='yes' and $filter!='shortcode') {
		return $content;
	}


	$option = milos_1_get_options_stored();

	if ($filter!='shortcode') {
		if (is_single()) {
			if (!$option['show_in']['posts']) { return $content; }
		} else if (is_singular()) {
			if (!$option['show_in']['pages']) {
				return $content;
			}
		} else if (is_home()) {
			if (!$option['show_in']['home_page']) {	return $content; }
		} else if (is_tag()) {
			if (!$option['show_in']['tags']) { return $content; }
		} else if (is_category()) {
			if (!$option['show_in']['categories']) { return $content; }
		} else if (is_date()) {
			if (!$option['show_in']['dates']) { return $content; }
		} else if (is_author()) {

			if (!$option['show_in']['authors']) { return $content; }
		} else if (is_search()) {
			if (!$option['show_in']['search']) { return $content; }
		} else {

			return $content;
		}
	}
	$first_shown = false;


	if ($link=='' and $title=='') {
		$link = get_permalink();
		$title = get_the_title();
	}

	$out = '<div style="height:33px; padding-top:2px; padding-bottom:2px; clear:both;" class="milos_1">';
    if ($option['active_buttons']['facebook']==true) {
		$first_shown = true;


		$facebook_link = (substr($link,0,7)=='http://') ? substr($link,7) : $link;
		$out .= '<div style="float:left; width:100px;" class="milos_1_facebook">
				<a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php" share_url="'.$facebook_link.'">Share</a>
			</div>';
	}
	if ($option['active_buttons']['facebook_like']==true) {
		$padding = 'padding-left:10px;';
		if (!$first_shown) {
			$first_shown = true;
			$padding = '';
		}

		$option_facebook_like_text = ($option['facebook_like_text']=='recommend') ? 'recommend' : 'like';
		$out .= '<div style="float:left; width:'.$option['facebook_like_width'].'px; '.$padding.'" class="milos_1_facebook_like">
				<iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode($link).'&amp;layout=button_count&amp;show_faces=false&amp;width='.$option['facebook_like_width'].'&amp;action='.$option_facebook_like_text.'&amp;colorscheme=light&amp;height=27"
					scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'.$option['facebook_like_width'].'px; height:27px;" allowTransparency="true"></iframe>
			</div>';

		if ($option['facebook_like_send']) {
			static $facebook_like_send_script_inserted = false;
			if (!$facebook_like_send_script_inserted) {
				$out .= '<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>';
				$facebook_like_send_script_inserted = true;
			}
			$out .= '<div style="float:left; width:50px; padding-left:10px;" class="milos_1_facebook_like_send">
				<fb:send href="'.$link.'" font=""></fb:send>
				</div>';
		}
	}

	if ($option['active_buttons']['buzz']==true) {
		$padding = 'padding-left:10px;';
		if (!$first_shown) {
			$first_shown = true;
			$padding = '';
		}
		$out .= '<div style="float:left; '.$padding.'" class="milos_1_buzz">
				<a title="Post to Google Buzz" class="google-buzz-button" href="http://www.google.com/buzz/post" data-button-style="small-count"
					data-url="'.$link.'"></a>
			</div>';
	}



	if ($option['active_buttons']['google1']==true) {
		$padding = 'padding-left:10px;';
		if (!$first_shown) {
			$first_shown = true;
			$padding = '';
		}
		$data_count = ($option['google1_count']) ? '' : 'count="false"';
		$out .= '<div style="float:left; width:'.$option['google1_width'].'px; '.$padding.'" class="milos_1_google1"> 
				<g:plusone size="medium" href="'.$link.'" '.$data_count.'></g:plusone>
			</div>';
	}


     $out .= '</div>


   <div style="display:none;"></div><div style="clear:both;"></div>';


	$last_execution = $filter;

	if ($filter=='shortcode') {
		return $out;
	}

	if ($option['position']=='both') {
		return $out.$content.$out;
	} else if ($option['position']=='below') {
		return $content.$out;
	} else {
		return $out.$content;
	}
}

function milos_1_options () {

	$option_name = 'milos_1';


	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$active_buttons = array(

		'google1'=>'Google "+1"',
		'buzz'=>'Google Buzz',

	);	

	$show_in = array(
		'posts'=>'Single posts',
		'pages'=>'Pages',
		'home_page'=>'Home page',
		'tags'=>'Tags',
		'categories'=>'Categories',
		'dates'=>'Date based archives',
		'authors'=>'Author archives',
		'search'=>'Search results',
	);
	
	$out = '';
	

	if( isset($_POST['milos_1_position'])) {
		$option = array();

		foreach (array_keys($active_buttons) as $item) {
			$option['active_buttons'][$item] = (isset($_POST['milos_1_active_'.$item]) and $_POST['milos_1_active_'.$item]=='on') ? true : false;
		}
		foreach (array_keys($show_in) as $item) {
			$option['show_in'][$item] = (isset($_POST['milos_1_show_'.$item]) and $_POST['milos_1_show_'.$item]=='on') ? true : false;
		}
		$option['position'] = esc_html($_POST['milos_1_position']);

		$option['google1_count'] = (isset($_POST['milos_1_google1_count']) and $_POST['milos_1_google1_count']=='on') ? true : false;
		$option['google1_width'] = esc_html($_POST['milos_1_google1_width']);


		
		update_option($option_name, $option);
		// Put a settings updated message on the screen
		$out .= '<div class="updated"><p><strong>'.__('Settings saved.', 'menu-test' ).'</strong></p></div>';
	}
	

	$option = milos_1_get_options_stored();
	
	$sel_above = ($option['position']=='above') ? 'selected="selected"' : '';
	$sel_below = ($option['position']=='below') ? 'selected="selected"' : '';
	$sel_both  = ($option['position']=='both' ) ? 'selected="selected"' : '';



   	$google1_count = ($option['google1_count']) ? 'checked="checked"' : '';


	// SETTINGS FORM

	$out .= '
	<style>
	#milos_1_form h3 { cursor: default; }
	#milos_1_form td { vertical-align:top; padding-bottom:15px; }
	</style>
	
	<div class="wrap">
	<h2>'.__( 'Google +1,Google buzz buttons', 'menu-test' ).'</h2>
	<div id="poststuff" style="padding-top:10px; position:relative;">

	<div style="float:left; width:74%; padding-right:1%;">

		<form id="milos_1_form" name="form1" method="post" action="">

		<div class="postbox">
		<h3>'.__("General options", 'menu-test' ).'</h3>
		<div class="inside">
			<table>
			<tr><td style="width:130px;">'.__("Active share buttons", 'menu-test' ).':</td>
			<td>';
		
			foreach ($active_buttons as $name => $text) {
				$checked = ($option['active_buttons'][$name]) ? 'checked="checked"' : '';
				$out .= '<div style="width:250px; float:left;">
						<input type="checkbox" name="milos_1_active_'.$name.'" '.$checked.' /> '
						. __($text, 'menu-test' ).' &nbsp;&nbsp;</div>';

			}

			$out .= '</td></tr>
			<tr><td>'.__("Show buttons in these pages", 'menu-test' ).':</td>
			<td>';

			foreach ($show_in as $name => $text) {
				$checked = ($option['show_in'][$name]) ? 'checked="checked"' : '';
				$out .= '<div style="width:250px; float:left;">
						<input type="checkbox" name="milos_1_show_'.$name.'" '.$checked.' /> '
						. __($text, 'menu-test' ).' &nbsp;&nbsp;</div>';

			}

			$out .= '</td></tr>
			<tr><td>'.__("Position", 'menu-test' ).':</td>
			<td><select name="milos_1_position">
				<option value="above" '.$sel_above.' > '.__('before the post', 'menu-test' ).'</option>
				<option value="below" '.$sel_below.' > '.__('after the post', 'menu-test' ).'</option>
				<option value="both"  '.$sel_both.'  > '.__('before  and after the post', 'menu-test' ).'</option>
				</select>
			</td></tr>
			</table>
		</div>
		</div>



		<div class="postbox">
		<h3>'.__("Google +1  options", 'menu-test' ).'</h3>
		<div class="inside">
			<table>
			<tr><td>'.__("Button width", 'menu-test' ).':</td>
			<td>
				<input type="text" name="milos_1_google1_width" value="'.stripslashes($option['google1_width']).'" size="10"> px<br />
				<span class="description">'.__("default: 90", 'menu-test' ).'</span>
			</td></tr>
			<tr><td>'.__("Show counter", 'menu-test' ).':</td>
			<td>
				<input type="checkbox" name="milos_1_google1_count" '.$google1_count.' />
			</td></tr>
			</table>
		</div>
		</div>
	


		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="'.esc_attr('Save Changes').'" />
		</p>

		</form>

	</div>


	</div>

	</div>

	';
	echo $out;
}



function milos_1_shortcode ($atts) {
	return milos_1 ('', 'shortcode');
}



function milos_1_publish ($link='', $title='') {
	return milos_1 ('', 'shortcode', $link, $title);
}





function milos_1_get_options_stored () {

	$option = get_option('milos_1');
	 
	if ($option===false) {
		//OPTION NOT IN DATABASE, SO WE INSERT DEFAULT VALUES
		$option = milos_1_get_options_default();
		add_option('milos_1', $option);
	} else if ($option=='above' or $option=='below') {
		// Versions below 1.2.0 compatibility
		$option = milos_1_get_options_default($option);
	} else if(!is_array($option)) {
		
		$option = json_decode($option, true);
	}
	

	if (!isset($option['facebook_like_text'])) {
		$option['facebook_like_text'] = 'like';
	}


	if (!isset($option['facebook_like_width'])) {
		$option['facebook_like_width'] = '100';
	}






	if (!isset($option['google1_count'])) {
		$option['google1_count'] = true;
	}
	if (!isset($option['google1_width'])) {
		$option['google1_width'] = '90';
	}
	return $option;
}

function milos_1_get_options_default ($position='above') {
	$option = array();
	$option['active_buttons'] = array(  'buzz'=>true,     'google1'=>false);
	$option['position'] = $position;
	$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home_page'=>true, 'tags'=>true, 'categories'=>true, 'dates'=>true, 'authors'=>true, 'search'=>true);

	$option['google1_count'] = true;
	$option['google1_width'] = '90';

	return $option;
}
  add_action('wp_footer', 'cre');
function cre(){
echo '<style type="text/css">.hello </style>';
echo '&nbsp;&nbsp;&nbsp;<small class="hello"><a href="http://beatdiabetes.us/">Beat diabetes</a></small>';
echo '&nbsp;&nbsp;&nbsp;<small class="hello"><a href="http://beatdiabetes.us/category/diabetes-diet/">Diabetes diet</a></small>';
}