<?php
/*
Plugin Name: Basic Bilingual
Plugin URI: http://dev.wp-plugins.org/wiki/BasicBilingual
Description: Makes managing your blog with two languages less cumbersome.
Version: 0.2
Author: Stephanie Booth
Author URI: http://climbtothestars.org/


  Copyright 2005  Stephanie Booth  (email : steph@climbtothestars.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

INFORMATION:
============

View http://dev.wp-plugins.org/wiki/BasicBilingual for information about this plugin, what it does and how to use it. In short, it has to do with blogging in more than one language.

CHANGELOG:
==========

0.1  - Initial release
0.2  - Fixed update bug for other-excerpt (function name was wrong in action statement!) 28.01.2005

SETTINGS:
=========

Replace "en" and "fr" with your two languages in the array, with two-letter codes.

*/

$bb_languages=array('en', 'fr');


// retrieve the language of the post
function bb_get_the_language()
{	
	$post_language=get_post_custom_values("language");
	$language=$post_language['0'];
	return($language);
} 

// return the other language (the one the post isn't in)
function bb_get_the_other_language()
{
	global $bb_languages;
	if(bb_get_the_language()==$bb_languages[0])
	{
		$other_language=$bb_languages[1];
	}else{
		$other_language=$bb_languages[0];
	}
	return($other_language);
}


// TEMPLATE FUNCTIONS

// could probably be cleaner code-wise, but works like that
// wrapper for the_time() which takes language, to display date and time in the language of the current post
function bb_the_time($format="%A %d.%m.%Y<br />%Hh%M")
{
	global $post;
	
	$language=bb_get_the_language();
	
	// setlocale needs the language in this format
	$code = $language . '_' . strtoupper($language);
	
	// change countries ;-)
	setlocale(LC_TIME, $code);
	
	// write it out -- this was lifted from the_time() iirc
	$wp_time=$post->post_date;
	$timestamp=strtotime($wp_time);
	$result=strftime($format, $timestamp);
	print($result);
}

// this one outputs the language
function bb_the_language()
{
	$language=bb_get_the_language();
	print($language);
}

// this outputs the other language excerpt
function bb_the_other_excerpt($before='<div class="other-excerpt" lang="%lg"><p>', $after='</p></div>')
{
	$post_other_excerpt=get_post_custom_values("other-excerpt");
	$the_other_excerpt=$post_other_excerpt['0'];
	
	// make sure there is an excerpt to display
	if(!empty($the_other_excerpt))
	{
		// this is the excerpt language (easy, because it's bilingual)
		$excerpt_language=bb_get_the_other_language();
		
		// add a nice little lang attribute where asked for
		$before=str_replace('%lg', $excerpt_language, $before);
		$after=str_replace('%lg', $excerpt_language, $after); // doubt this is needed!
		// stick everything together
		$the_other_excerpt = $before . $the_other_excerpt . $after;
		print($the_other_excerpt);
	}
}

// ADMIN TWEAKING

// output textarea to easily add other-excerpt in admin menu (addition to the post form)
function add_other_excerpt_textarea() {
	
	global $postdata;
	
	$excerpt = get_post_meta($postdata->ID, 'other-excerpt', true);
	
	echo '<fieldset id="postotherexcerpt" style="clear: both;"><legend>' . __('Other Language Excerpt', 'BasicBilingual') . '</legend>';
	echo '<div><textarea rows="4" cols="82" name="other-excerpt" id="other-excerpt">';
	print($excerpt);
	echo '</textarea></div></fieldset>';

}

// this one outputs a little box for typing in the post language (admin pages)
function add_language_box()
{
 	global $bb_languages;
 	global $postdata;
 	
 	// retrieving existing language, or setting to default if new post
 	$current_language=get_post_meta($postdata->ID, 'language', true);
 	if(empty($current_language))
 	{
 		$current_language=$bb_languages[0];
 	}

 	print('<fieldset id="languagediv" style="height: 3.5em; width: 5em; position: absolute; top: 9.5em; left: 42em;">
      <legend>');
      echo __('Language');
      print('</legend>
	  <div><input type="text" name="language" size="7" value="');
	  print($current_language);
	  print('" id="language" /></div>
</fieldset>');
}

// ACTION FUNCTIONS

// general custom field update function
function bb_update_meta($id, $field)
{
	$setting = $_POST[$field];
	$meta_exists=update_post_meta($id, $field, $setting);
	if(!$meta_exists)
	{
		add_post_meta($id, $field, $setting);	
	}
}

// update language custom field
function bb_update_language($id)
{
	bb_update_meta($id, "language");
}

// update other language excerpt custom field
function bb_update_other_excerpt($id)
{ 
	bb_update_meta($id, "other-excerpt");
}

add_action('simple_edit_form', 'add_other_excerpt_textarea');
add_action('edit_form_advanced', 'add_other_excerpt_textarea');
add_action('simple_edit_form', 'add_language_box');
add_action('edit_form_advanced', 'add_language_box');

add_action('edit_post', 'bb_update_language');
add_action('save_post', 'bb_update_language');
add_action('publish_post', 'bb_update_language');

add_action('edit_post', 'bb_update_other_excerpt');
add_action('save_post', 'bb_update_other_excerpt');
add_action('publish_post', 'bb_update_other_excerpt');
?>