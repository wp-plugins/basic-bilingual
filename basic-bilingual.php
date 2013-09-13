<?php
/*
Plugin Name: Basic Bilingual
Plugin URI: http://climbtothestars.org/wordpress/basic-bilingual/
Description: Makes managing your blog with two languages less cumbersome.
Author: Stephanie Booth
Author URI: http://climbtothestars.org/
Version: 1.0

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

define('BB_POST_LANGUAGE', 'bb-post-language');
define('BB_POST_EXCERPTS', 'bb-post-excerpts');
define('BB_SITE_LANGUAGES', 'bb-site-languages');
define('BB_USE_ACCEPT_HEADER', 'bb-use-accept-header');


class BasicBilingualPlugin {

	function BasicBilingualPlugin() {
		add_action('init', array(&$this, 'init'));
		add_action('admin_menu', array(&$this, 'admin_init'));
	}

	function init() {
		// Make plugin available for translation
		// Translations can be filed in the /languages/ directory
		load_plugin_textdomain('basic-bilingual', false, dirname(plugin_basename(__FILE__)) . '/languages/' );

		if (!is_admin()) {
			wp_register_style('basic-bilingual', plugins_url('style.css', __FILE__), false, '0.5');
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
			add_action('the_content', array(&$this, 'filter_the_content'));
			add_filter('the_title', array(&$this, 'filter_the_title'));
		}
	}

	function admin_init() {
		require_once 'class-admin.php';
		$this->admin = new BasicBilingualAdmin($this);
	}

	function enqueue_scripts() {
		wp_enqueue_style('basic-bilingual');
	}

	function get_site_languages() {
		return get_option(BB_SITE_LANGUAGES, array('en', 'fr'));
	}

	function get_use_accept_header() {
		return get_option(BB_USE_ACCEPT_HEADER, false);
	}

	function get_post_language() {
		return get_post_meta(get_the_ID(), BB_POST_LANGUAGE, true);
	}

	function get_post_excerpts() {
		return get_post_meta(get_the_ID(), BB_POST_EXCERPTS, true);
	}

	function filter_excerpts($excerpts, $languages) {
		//remove spaces
		$languages = preg_replace('/\s+/', '', $languages);
		// remove quality values
		$languages = preg_replace('/;q=\d+(\.\d+)?/i', '', $languages);
		// remove country codes
		$languages = preg_replace('/-[a-z]+/i', '', $languages);
		$languages = array_unique(explode(',', $languages));
		$filtered = array();

		foreach ($languages as $lang) {
			if (key_exists($lang, $excerpts)) $filtered[$lang] = $excerpts[$lang];
		}

		return $filtered;
	}

	function the_excerpts($before='<div class="other-excerpt" lang="%lg"><p>', $after='</p></div>') {
		$post_language = $this->get_post_language();
		$excerpts = $this->get_post_excerpts();
		$content = '';

		if (is_array($excerpts)) {
			if ($this->get_use_accept_header() && !is_feed()) {
				$excerpts = $this->filter_excerpts($excerpts, $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			}

			foreach ($excerpts as $lang => $excerpt) {
				if (!empty($excerpt) && $lang != $post_language) {
					$the_excerpt = '<span class="bb-lang">[' . $lang . ']</span> ' . $excerpt;

					// add a nice little lang attribute where asked for
					$the_before = str_replace('%lg', $lang, $before);
					$the_after = str_replace('%lg', $lang, $after);

					$content .= $the_before . $the_excerpt . $the_after;
				}
			}
		}

		return $content;
	}

	function filter_the_content($content) {
		$lang = $this->get_post_language();

		if (!empty($lang)) {
			// If we are in the feed then add a prefix like in the excerpt.
			$prefix = (is_feed()) ? "[$lang] " : '';
			$content = "<div lang='$lang'>$prefix$content</div>";
		}

		return $this->the_excerpts() . $content;
	}

	function filter_the_title($title) {
		if (in_the_loop()) {
			$lang = $this->get_post_language();

			if (!empty($lang)) {
				$title = "<span lang='$lang'>$title <span class='bb-lang'>[$lang]</span></span>";
			}
		}

		return $title;
	}

}

global $the_basic_bilingual_plugin;
$the_basic_bilingual_plugin = new BasicBilingualPlugin();
