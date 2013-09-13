<?php
/*
Plugin Name: Basic Bilingual
Plugin URI: http://climbtothestars.org/wordpress/basic-bilingual/
Description: Makes managing your blog with two languages less cumbersome.
Author: Stephanie Booth
Author URI: http://climbtothestars.org/
Version: 0.5

# The code in this plugin is free software; you can redistribute the code aspects of
# the plugin and/or modify the code under the terms of the GNU Lesser General
# Public License as published by the Free Software Foundation; either
# version 3 of the License, or (at your option) any later version.

# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#
# See the GNU lesser General Public License for more details.
*/


class BasicBilingualPlugin {

	const LANGUAGES = 'en|fr';
	const LANGUAGE_KEY = 'language';
	const OTHER_EXCERPT_KEY = 'other-excerpt';

	function BasicBilingualPlugin() {
		add_action('init', array(&$this, 'init'));
		add_action('admin_init', array(&$this, 'admin_init'));
	}

	function init() {
		// Make plugin available for translation
		// Translations can be filed in the /languages/ directory
		load_plugin_textdomain('basic-bilingual', false, dirname(plugin_basename(__FILE__)) . '/languages/' );

		if (!is_admin()) {
			wp_register_style('basic-bilingual', plugins_url('style.css', __FILE__), false, '0.5');
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
			add_action('the_content', array(&$this, 'filter_content'));
		}
	}

	function admin_init() {
		require_once 'class-admin.php';
		$this->admin = new BasicBilingualAdmin($this);
	}

	function enqueue_scripts() {
		wp_enqueue_style('basic-bilingual');
	}

	function get_the_language() {
		return get_post_meta(get_the_ID(), BasicBilingualPlugin::LANGUAGE_KEY, true);
	}

	function get_the_locale() {
		$language = $this->get_the_language();
		return $language . '_' . strtoupper($language);
	}

	function get_the_other_language() {
		$languages = explode('|', BasicBilingualPlugin::LANGUAGES);
		$others = array_diff($languages, array($this->get_the_language()));
		return array_pop($others);
	}

	function get_the_other_excerpt() {
		return get_post_meta(get_the_ID(), BasicBilingualPlugin::OTHER_EXCERPT_KEY, true);
	}

	function get_the_other_content($before='<div class="other-excerpt" lang="%lg"><p class="oe-first-child">', $after='</p></div>') {
		$the_other_excerpt = $this->get_the_other_excerpt();

		// make sure there is an excerpt to display
		if (!empty($the_other_excerpt)) {
			// this is the excerpt language (easy, because it's bilingual)
			$excerpt_language = $this->get_the_other_language();

			// add a nice little lang attribute where asked for
			$before = str_replace('%lg', $excerpt_language, $before);
			$after = str_replace('%lg', $excerpt_language, $after); // doubt this is needed!

			$content = $before . $the_other_excerpt . $after;

			if (is_feed()) {
				// add separators so that newsreaders which don't get formatting know when the post starts
				$post_language = $this->get_the_language();
				$post_separator_after = "<p class=\"bb-post-separator\"><strong>[$post_language]</strong></p>";
				$post_separator_before = "<p class=\"bb-post-separator\"><strong>[$excerpt_language]</strong></p>";
				$content = $post_separator_before . $content . $post_separator_after;
			}

			return $content;
		}
	}

	function filter_content($content) {
		return $this->get_the_other_content() . $content;
	}

}

global $the_basic_bilingual_plugin;
$the_basic_bilingual_plugin = new BasicBilingualPlugin();


// TEMPLATE TAGS
function bb_the_time($format="%A %d.%m.%Y<br />%Hh%M") {
	global $post, $the_basic_bilingual_plugin;
	$locale = $the_basic_bilingual_plugin->get_the_locale();

	// change locale
	$old_locale = setlocale(LC_ALL,"0");
	setlocale(LC_TIME, $locale);

	// write it out -- this was lifted from the_time() iirc
	$timestamp = strtotime($post->post_date);
	echo strftime($format, $timestamp);

	// make sure to restore it
	setlocale(LC_TIME, $old_locale);
}

// this one outputs the language
function bb_the_language() {
	global $the_basic_bilingual_plugin;
	echo $the_basic_bilingual_plugin->get_the_language();
}

// this outputs the other language excerpt
function bb_get_the_other_excerpt($before='<div class="other-excerpt" lang="%lg"><p class="oe-first-child">', $after='</p></div>') {
	global $the_basic_bilingual_plugin;
	return $the_basic_bilingual_plugin->get_the_other_content($before, $after);
}

// this prints the other language excerpt
function bb_the_other_excerpt() {
	global $the_basic_bilingual_plugin;
	echo $the_basic_bilingual_plugin->get_the_other_content();
}
