<?php
/*
Plugin Name: Basic Bilingual
Plugin URI: http://climbtothestars.org/archives/2007/11/30/basic-bilingual-03-for-multilingual-blogging/Description: Makes managing your blog with two languages less cumbersome.
Version: 0.33
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

View http://climbtothestars.org/archives/2007/11/30/basic-bilingual-03-for-multilingual-blogging/ for information about this plugin, what it does and how to use it. In short, it has to do with blogging in more than one language.

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
0.32 - Replaced the ugly "language box" in the admin section with a pretty DBX box. Drag it to the top
	   of the page!  
0.33 - Fixed a bunch of stuff, code provided kindly by Tim Isenheim http://www.freshlabs.de/journal/	
	 - Half-arsed attempt to make the interface look prettier (if you know how to make 
	   pretty posboxes let me know) -- 30.01.2009   
	      

SETTINGS:
=========

Replace "en" and "fr" with your two languages in the array below, with two-letter codes.

*/

$bb_languages=array('en', 'fr');

/*

CSS:
====

You might want define CSS rules similar to these for your stylesheet:

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
function bb_get_the_other_excerpt($before='<div class="other-excerpt" lang="%lg"><p class="oe-first-child">', $after='</p></div>')
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
		
		// add separators so that newsreaders which don't get formatting know when the post starts
		$post_language=bb_get_the_language();
		$post_separator_after="<p class=\"bb-post-separator\"><strong>[$post_language]</strong></p>";
		$post_separator_before="<p class=\"bb-post-separator\"><strong>[$excerpt_language]</strong></p>";
		// stick everything together
		$the_other_excerpt = $post_separator_before . $before . $the_other_excerpt . $after . $post_separator_after;
		return($the_other_excerpt);
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
} not sure this will work */


// ADMIN TWEAKING

// output textarea to easily add other-excerpt in admin menu (addition to the post form)
// deprecated
function add_other_excerpt_textarea() {
	
	global $post;
	
	$excerpt = get_post_meta($post->ID, 'other-excerpt', true);
	
	echo '<fieldset id="postotherexcerpt" style="clear: both;"><legend>' . __('Other Language Excerpt', 'BasicBilingual') . '</legend>';
	echo '<div><textarea rows="4" cols="80" name="other-excerpt" id="other-excerpt">';
	print($excerpt);
	echo '</textarea></div></fieldset>';
	// hidden field to avoid vanishing meta
 echo '<input type="hidden" name="bunny-key" id="bunny-key" value="' . wp_create_nonce('bunny') . '" />'; 
}

// new function, to make the textarea prettier and more 2.7-compatible

function add_elegant_other_excerpt_textarea() {
	
	global $post;
	
	$excerpt = get_post_meta($post->ID, 'other-excerpt', true);
echo '
<div id="postother-excerpt" class="postbox">
<h3>Other Language Excerpt</h3>
<div class="inside">

<label class="hidden" for="other-excerpt">Other Language Excerpt</label><textarea rows="10" cols="80" name="other-excerpt" id="other-excerpt">';
print($excerpt);
echo '</textarea>
<p>Write an excerpt of your post in the other language you use on your blog. Short and sweet, or long and detailed.</p>
</div>
</div>';

// hidden field to avoid vanishing meta
 echo '<input type="hidden" name="bunny-key" id="bunny-key" value="' . wp_create_nonce('bunny') . '" />'; 
}








// DEPRECATED this one outputs a little box for typing in the post language (admin pages)
function add_language_box()
{
 	global $bb_languages;
 	global $post;
 	
 	// retrieving existing language, or setting to default if new post
 	$current_language=get_post_meta($post->ID, 'language', true);
 	if(empty($current_language))
 	{
 		$current_language=$bb_languages[0];
 	}

 	print('<fieldset id="languagediv" style="height: 3.5em; width: 5em; position: absolute; top: 10em; left: 42em;">
      <legend>');
      echo __('Language');
      print('</legend>
	  <div><input type="text" name="language" size="7" value="');
	  print($current_language);
	  print('" id="language" /></div>
</fieldset>');
}

// this outputs a little box for typing in the post language, in the admin pages sidebar (posts and pages)
function bb_add_dbx_language_box()
{
 	global $bb_languages;
 	global $post;

	// retrieving existing language, or setting to default if new post
 	$current_language=get_post_meta($post->ID, 'language', true);
 	if(empty($current_language))
 	{
 		$current_language=$bb_languages[0];
 	}
 	
 	print('<div id="post-lang" class="postbox">
 	<h3>');
      echo __('Language');
      print('</h3>
	  <div class="inside"><input type="text" name="language" size="7" value="');
	  print($current_language);
	  print('" id="language" /> <label for="language">2-letter code</label></div>
</div>');
}



// ACTION FUNCTIONS

// general custom field update function
function bb_update_meta($id, $field)
{
	// authorization to avoid vanishing meta    if ( !current_user_can('edit_post', $id) )        return $id;    // origination and intention to avoid vanishing meta    if ( !wp_verify_nonce($_POST['bunny-key'], 'bunny') )        return $id;
	$setting = stripslashes($_POST[$field]);
	$meta_exists=update_post_meta($id, $field, $setting);
	if(!$meta_exists)
	{
		add_post_meta($id, $field, $setting);	
	}
}


// update language custom field
function bb_update_language($id, $post)
{
	if ($post->post_type == 'revision') {
		return;
	}
	bb_update_meta($id, "language");
}

// update other language excerpt custom field
function bb_update_other_excerpt($id, $post)
{
	if ($post->post_type == 'revision') {
		return;
	}
	bb_update_meta($id, "other-excerpt");
}

//add_action('simple_edit_form', 'add_other_excerpt_textarea');
//add_action('edit_form_advanced', 'add_other_excerpt_textarea');

add_action('simple_edit_form', 'add_elegant_other_excerpt_textarea');
add_action('edit_form_advanced', 'add_elegant_other_excerpt_textarea');



// add_action('simple_edit_form', 'add_language_box');
// add_action('edit_form_advanced', 'add_language_box');
// add_action('edit_page_form', 'add_language_box');
add_action('edit_page_form', 'add_other_excerpt_textarea');

add_action('dbx_page_sidebar', 'bb_add_dbx_language_box');
add_action('dbx_post_sidebar', 'bb_add_dbx_language_box');


add_action('edit_post', 'bb_update_language', 1, 2);
add_action('save_post', 'bb_update_language', 1, 2);
add_action('publish_post', 'bb_update_language', 1, 2);

add_action('edit_post', 'bb_update_other_excerpt', 1, 2);
add_action('save_post', 'bb_update_other_excerpt', 1, 2);
add_action('publish_post', 'bb_update_other_excerpt', 1, 2);

add_action('the_content', 'bb_embed_other_excerpt');

/* not sure this will work
function bb_got_hentry()
{
	return false;
	}
if(bb_got_hentry())
{	add_action('template_redirect', 'bb_embed_lang');
} */
?>