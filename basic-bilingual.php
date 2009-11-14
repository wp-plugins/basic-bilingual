<?php
/*
Plugin Name: Basic Bilingual
Plugin URI: http://climbtothestars.org/wordpress/basic-bilingual/
Description: Makes managing your blog with two languages less cumbersome.
Version: 0.4
Author: Stephanie Booth
Author URI: http://climbtothestars.org/


  Copyright 2005-2009  Stephanie Booth  (email : steph@climbtothestars.org)

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

View http://climbtothestars.org/wordpress/basic-bilingual/ for information about this plugin, what it does and how to use it. In short, it has to do with blogging in more than one language.

CHANGELOG:
==========

0.1  - Initial release
0.2  - Fixed update bug for other-excerpt (function name was wrong in action statement!) 28.01.2005
0.21 - Fixed for WP 2.0 by replacing $postdata->ID with $post->ID (31.12.2005)
	 - Cosmetic changes to the edit form (03.01.2006)
	 - added hooks to deal with pages (03.01.2006)
0.3  - Added stripslashes to get rid of slash problem
     - No need to add template tag anymore for other-excerpt -- added automagically (30.11.2007)
     - Added class to excerpt first-child
0.31 - Attempted to fix vanishing excerpts problem -- see http://markjaquith.wordpress.com/2007/01/28/
       authorization-and-intentionorigination-verification-when-using-the-edit_post-hook/
0.32 - Replaced the ugly "language box" in the admin section with a pretty DBX box. Drag it to the top of the page!
0.33 - Fixed a bunch of stuff, code provided kindly by Tim Isenheim http://www.freshlabs.de/journal/
	 - Half-arsed attempt to make the interface look prettier (30.01.2009)
0.4  - Modified the be fully compatible with the last WordPress versions: drag the language and other-excerpt boxes
       -- code provided by Luca Palli http://video.monte-ceneri.org/ (27.09.2009)
	 - Add the "Allow empty language" option on the new Options page (27.09.2009, Luca Palli)


SETTINGS:
=========

Replace "en" and "fr" with your two languages in the array below, with two-letter codes.

*/

$bb_languages = array('en', 'fr');

$bb_language_field = 'language';
$bb_other_excerpt_field = 'other-excerpt';
$bb_allow_empty_language_option = 'bb_allow_empty_language';

/*

CSS:
====

You might want to define CSS rules similar to these for your stylesheet:

.other-excerpt {
	font-style: italic;
	background: #fff;
	padding-left: 1em;
	padding-right: 1em;
	border: 1px solid #ccc;
}

.other-excerpt:lang(fr) p.oe-first-child:before {
	content: "[fr] ";
	font-weight: bold;
}

.other-excerpt:lang(en) p.oe-first-child:before {
	content: "[en] ";
	font-weight: bold;
}

.bb-post-separator {
	display: none;
}

div.hentry:lang(fr) .entry-title:after {
  	content: " [fr] ";
  	vertical-align: middle;
  	font-size: 80%;
  	color: #bbb;
}

div.hentry:lang(en) .entry-title:after {
  	content: " [en] ";
  	vertical-align: middle;
  	font-size: 80%;
  	color: #bbb;
}

*/

// retrieve the language of the post
function bb_get_the_language()
{
	global $bb_language_field;
	$post_language = get_post_custom_values($bb_language_field);
	$language = $post_language['0'];
	return $language;
}

// return the other language (the one the post isn't in)
function bb_get_the_other_language()
{
	global $bb_languages;
	if (bb_get_the_language() == $bb_languages[0])
	{
		$other_language = $bb_languages[1];
	} else {
		$other_language = $bb_languages[0];
	}
	return $other_language;
}

// TEMPLATE FUNCTIONS

// could probably be cleaner code-wise, but works like that
// wrapper for the_time() which takes language, to display date and time in the language of the current post
function bb_the_time($format="%A %d.%m.%Y<br />%Hh%M")
{
	global $post;
	
	$language = bb_get_the_language();
	
	// setlocale needs the language in this format
	$code = $language . '_' . strtoupper($language);
	
	// change countries ;-)
	setlocale(LC_TIME, $code);
	
	// write it out -- this was lifted from the_time() iirc
	$wp_time = $post->post_date;
	$timestamp = strtotime($wp_time);
	$result = strftime($format, $timestamp);
	print($result);
}

// this one outputs the language
function bb_the_language()
{
	$language = bb_get_the_language();
	print($language);
}

// this outputs the other language excerpt
function bb_get_the_other_excerpt($before='<div class="other-excerpt" lang="%lg"><p class="oe-first-child">', $after='</p></div>')
{
	global $bb_other_excerpt_field;
	
	$post_other_excerpt = get_post_custom_values($bb_other_excerpt_field);
	$the_other_excerpt = $post_other_excerpt['0'];
	
	// make sure there is an excerpt to display
	if(!empty($the_other_excerpt))
	{
		// this is the excerpt language (easy, because it's bilingual)
		$excerpt_language = bb_get_the_other_language();
		
		// add a nice little lang attribute where asked for
		$before = str_replace('%lg', $excerpt_language, $before);
		$after = str_replace('%lg', $excerpt_language, $after); // doubt this is needed!
		
		// add separators so that newsreaders which don't get formatting know when the post starts
		$post_language = bb_get_the_language();
		$post_separator_after = "<p class=\"bb-post-separator\"><strong>[$post_language]</strong></p>";
		$post_separator_before = "<p class=\"bb-post-separator\"><strong>[$excerpt_language]</strong></p>";
		// stick everything together
		$the_other_excerpt = $post_separator_before . $before . $the_other_excerpt . $after . $post_separator_after;
		return $the_other_excerpt;
	}
}

// this prints the other language excerpt
function bb_the_other_excerpt()
{
	print(bb_get_the_other_excerpt());
}

// automatic insertion of other-excerpt
function bb_embed_other_excerpt($content) {
	$content = bb_get_the_other_excerpt() . $content;
	return $content;
}

// automatic insertion of lang attribute on post div
/* function bb_embed_lang($buffer) {
	ob_start('bb_insert_lang_attribute');
}

function bb_insert_lang_attribute($buffer) {
 	$search='/(class="(.)*hentry(.)*")/';
 	$replace=\\1 . ' lang="' . bb_get_the_language() . '"';
 	return preg_replace($search, $replace, $buffer);	
} not sure this will work, and depends on the template */

// ADMIN TWEAKING

/**
 * Adds a custom section to the Post and Page edit screens
 */
function bb_add_custom_boxes()
{
	if ( function_exists('add_meta_box'))
	{
		add_meta_box('bb_language', __( 'Language', 'bb_textdomain' ), 'bb_language_box', 'post', 'side');
		add_meta_box('bb_language', __( 'Language', 'bb_textdomain' ), 'bb_language_box', 'page', 'side');
		
		add_meta_box('bb_other_excerpt', __( 'Other Language Excerpt', 'bb_textdomain' ), 'bb_other_excerpt_box', 'post', 'normal');
		add_meta_box('bb_other_excerpt', __( 'Other Language Excerpt', 'bb_textdomain' ), 'bb_other_excerpt_box', 'page', 'normal');
	} else {
		add_action('dbx_page_sidebar', 'bb_language_old_box');
		add_action('dbx_post_sidebar', 'bb_language_old_box');
		
		add_action('simple_edit_form', 'bb_other_excerpt_old_box');
		add_action('edit_form_advanced', 'bb_other_excerpt_old_box');
		add_action('edit_page_form', 'bb_other_excerpt_old_box');
	}
}

/**
 * Prints the inner fields for the language post/page section
 */
function bb_language_box()
{
	global $post;
	global $bb_languages;
	global $bb_language_field;
	global $bb_allow_empty_language_option;
	
	// retrieving existing language, or setting to default if new post
	$current_language = get_post_meta($post->ID, $bb_language_field, true);
	if (empty($current_language) && !get_option($bb_allow_empty_language_option))
	{
		$current_language=$bb_languages[0]; // LP: to support empty language
	}
	
	// Use nonce for verification
	echo '<input type="hidden" name="bb_noncename" id="bb_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
	
	// The actual fields for data entry
	echo '<input type="text" name="' . $bb_language_field . '" value="' . $current_language . '" size="7" id="' . $bb_language_field . '" /> ';
	echo '<label for="bb_language">' . __("2-letter code", 'bb_textdomain' ) . '</label>';
}

/**
 * Prints the edit form for pre-WordPress 2.5 post/page
 */
function bb_language_old_box()
{
	echo '<div id="post-lang" class="postbox"><h3>' . __('Language') . '</h3>';
	echo '<div class="inside">' . bb_language_box() . '</div></div>';
}

/**
 * Prints the inner fields for the other excerpt_box post/page section
 */
function bb_other_excerpt_box()
{
	global $post;
	global $bb_other_excerpt_field;
	
	$excerpt = get_post_meta($post->ID, $bb_other_excerpt_field, true);
	
	// Use nonce for verification
	echo '<input type="hidden" name="bb_noncename" id="bb_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
	
	// The actual fields for data entry
	echo '<textarea rows="10" cols="80" name="' . $bb_other_excerpt_field . '" id="' . $bb_other_excerpt_field . '" style="width: 100%;">' . $excerpt . '</textarea>';
	echo '<p>Write an excerpt of your post in the other language you use on your blog. Short and sweet, or long and detailed.</p>';
}

/**
 * Prints the edit form for pre-WordPress 2.5 post/page
 */
function bb_other_excerpt_old_box()
{
	echo '<div id="post-lang" class="postbox"><h3>' . __('Other Language Excerpt') . '</h3>';
	echo '<div class="inside">' . bb_other_excerpt_box() . '</div></div>';
}

/** 
 * Adds a custom submenu to the Options
 */
function bb_add_options()
{
	// Add a new submenu under Options
	add_options_page('Basic Bilingual', 'Basic Bilingual', 'administrator', 'bb_options', 'bb_options_page');
}

/**
 * Displays the page content for the Basic Bilingual Options submenu
 */
function bb_options_page()
{
	global $bb_allow_empty_language_option;
	
	// variables for the field and option names
	$opt_name = $bb_allow_empty_language_option;
	$hidden_field_name = 'bb_submit_hidden';
	
	// Read in existing option value from database
	$opt_val = get_option($opt_name);
	
	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if ($_POST[$hidden_field_name] == 'Y')
	{
		// Read their posted value
		$opt_val = $_POST[$opt_name];
		
		// Save the posted value in the database
		update_option($opt_name, $opt_val);
		
		// Put an options updated message on the screen
		echo '<div class="updated"><p><strong>' . __('Options saved.', 'bb_textdomain') . '</strong></p></div>';
	}
	
	// Now display the options editing screen
	echo '<div class="wrap">';
	// header
	echo "<h2>" . __( 'Basic Bilingual', 'bb_textdomain' ) . "</h2>";
	// options form
	echo '<form name="form1" method="post" action="">';
	echo '<input type="hidden" name="' . $hidden_field_name . '" value="Y">';
	echo '<p>' . __("Allow empty language:", 'bb_textdomain');
	echo '<input type="checkbox" name="' . $opt_name . '" value="true"';
	if ($opt_val) 
	{
		echo ' checked="checked"';
	}//'"' . $opt_val .'"
	echo ' />';
	echo '</p><hr />';
	echo '<p class="submit"><input type="submit" name="Submit" value="' . __('Update Options', 'bb_textdomain') . '" /></p>';
	echo '</form>';
	echo '</div>';
}

// ACTION FUNCTIONS

/**
 * When the post is saved, saves our custom data
 */
function bb_save_postdata($id)
{	
	global $bb_language_field;
	global $bb_other_excerpt_field;
	
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if (!wp_verify_nonce($_POST['bb_noncename'], plugin_basename(__FILE__)))
	{
		return $id;
	}
	
	// verify if this is an auto save routine. If it is our form has not been
	// submitted, so we dont want to do anything
	/*if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
	{
		return $post_id;
	}*/
	
	// Check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can( 'edit_page', $id ))
		{
			return $id;
		}
	} else {
		if (!current_user_can( 'edit_post', $id ))
		{
			return $id;
		}
	}
	
	// update language and other language excerpt custom fields
	bb_update_meta($id, $bb_language_field);
	bb_update_meta($id, $bb_other_excerpt_field);
}

// general custom field update function
function bb_update_meta($id, $field)
{
	$setting = stripslashes($_POST[$field]);
	$meta_exists = update_post_meta($id, $field, $setting);
	if (!$meta_exists)
	{
		add_post_meta($id, $field, $setting);
	}
}

add_action('the_content', 'bb_embed_other_excerpt');

/* Use the admin_menu action to define the custom boxes */
add_action('admin_menu', 'bb_add_custom_boxes');

/* Use the save_post action to do something with the data entered */
add_action('edit_post', 'bb_save_postdata', 1, 2);
add_action('save_post', 'bb_save_postdata', 1, 2);
add_action('publish_post', 'bb_save_postdata', 1, 2);

// Use the admin_menu action to define the admin menus
add_action('admin_menu', 'bb_add_options');

/* not sure this will work
function bb_got_hentry()
{
	return false;
}
if(bb_got_hentry())
{
	add_action('template_redirect', 'bb_embed_lang');
} */
?>