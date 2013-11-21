<?php
/*
Plugin Name: Basic Bilingual
Plugin URI: http://climbtothestars.org/wordpress/basic-bilingual/
Description: Makes managing your blog with two languages less cumbersome.
Author: Stephanie Booth
Author URI: http://climbtothestars.org/
Version: 1.1

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
define('BB_POSTFIX_TITLES', 'bb-postfix-titles');
define('BB_BEFORE_EXCERPT', 'bb-before-excerpt');
define('BB_AFTER_EXCERPT', 'bb-after-excerpt');
define('BB_AUTO_FILTER_CONTENT', 'bb-auto-filter-content');


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
			wp_register_style('basic-bilingual', plugins_url('style.css', __FILE__), false, '1.0');
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

			add_action('wp_loaded', array(&$this, 'flush_rules'));
			add_filter('rewrite_rules_array', array(&$this, 'add_rewrite_rules'));
			add_filter('query_vars', array(&$this, 'query_vars'));
			add_action('pre_get_posts', array(&$this, 'posts_by_language'));
			add_filter('template_include', array(&$this, 'template_include'));

			if ($this->get_auto_filter_content()) {
				add_action('the_content', array(&$this, 'filter_the_content'));
				add_filter('the_title', array(&$this, 'filter_the_title'));
			}

			// Won't work as expected...
			// add_filter('locale', array(&$this, 'filter_the_locale'));
		}

		// Must flush rules on deactivation
		register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
	}

	function admin_init() {
		require_once 'class-admin.php';
		$this->admin = new BasicBilingualAdmin($this);
	}

	function deactivate() {
		flush_rewrite_rules();
	}

	// flush_rules() if our rules are not yet included
	function flush_rules(){
		$rules = get_option('rewrite_rules');

		if (!isset($rules['language/([a-z]{2})/?$'])) {
			flush_rewrite_rules();
		}
	}

	function add_rewrite_rules($rules) {
		$newrules = array();
		$newrules['language/([a-z]{2})/?$'] = 'index.php?bb-lang=$matches[1]';
		$newrules['language/([a-z]{2})/page/(\d+)/?$'] = 'index.php?bb-lang=$matches[1]&paged=$matches[2]';
		return $newrules + $rules;
	}

	function query_vars($vars) {
		$vars[] = 'bb-lang';
		return $vars;
	}

	function posts_by_language($query) {
		if (isset($query->query_vars['bb-lang'])) {
			$query->query_vars["meta_key"] = BB_POST_LANGUAGE;
			$query->query_vars["meta_value"] = $query->query_vars['bb-lang'];
		}

		return $query;
	}

	function template_include($template) {
		if (get_query_var('bb-lang')) {
			$templates = array(
					'language-' . get_query_var('bb-lang') . '.php',
					'language.php',
					'archive.php');
			if (is_paged()) $templates[] = 'paged.php';
			$templates[] = 'index.php';
			return locate_template($templates);
		}

		return $template;
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

	function get_postfix_titles() {
		return get_option(BB_POSTFIX_TITLES, true);
	}

	function get_before_excerpt() {
		return get_option(BB_BEFORE_EXCERPT, '<blockquote class="other-excerpt" lang="%lg">');
	}

	function get_after_excerpt() {
		return get_option(BB_AFTER_EXCERPT, '</blockquote>');
	}

	function get_auto_filter_content() {
		return get_option(BB_AUTO_FILTER_CONTENT, true);
	}

	function get_default_language() {
		$all_languages = $this->get_all_languages();
		$wp_language = explode('_', get_locale());
		$wp_language = $wp_language[0];

		if (isset($all_languages[$wp_language])) {
			return $wp_language;
		}

		return 'en';
	}

	function get_post_language($default=false) {
		$post_language = get_post_meta(get_the_ID(), BB_POST_LANGUAGE, true);
		if (empty($post_language)) $post_language = ($default) ? $default : $this->get_default_language();
		return $post_language;
	}

	function get_post_excerpts() {
		$excerpts = get_post_meta(get_the_ID(), BB_POST_EXCERPTS, true);
		if (!is_array($excerpts)) $excerpts = array();
		$excerpts[$this->get_post_language()] = get_post()->post_excerpt;
		return $excerpts;
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

	function the_excerpts($before=false, $after=false) {
		$post_language = $this->get_post_language();
		$excerpts = $this->get_post_excerpts();
		$content = '';

		if (!empty($excerpts)) {
			if (!$before) $before = $this->get_before_excerpt();
			if (!$after) $after = $this->get_after_excerpt();

			if ($this->get_use_accept_header() && !is_feed()) {
				$excerpts = $this->filter_excerpts($excerpts, $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			}

			foreach ($excerpts as $lang => $excerpt) {
				if (!empty($excerpt) && $lang != $post_language) {
					// Add some paragraphs
					$excerpt = '<span class="bb-lang">[' . $lang . ']</span> ' . $excerpt;
					$excerpt = wpautop($excerpt);

					// add a nice little lang attribute where asked for
					$the_before = str_replace('%lg', $lang, $before);
					$the_after = str_replace('%lg', $lang, $after);

					$content .= $the_before . $excerpt . $the_after;

					// backward compatibility, removing extra <p>
					$content = preg_replace('|<p([^>]*)>\s*<p>|im', '<p$1>', $content);
					$content = preg_replace('|</p>\s*</p>|im', '</p>', $content);
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

			// Check that we actually have a language and that we did not already add our
			// stuff, because this filter is called several times sometimes.
			if (!empty($lang) && (strpos($title, "<span lang='$lang'>") === false)) {
				$postfix = ($this->get_postfix_titles()) ? " <span class='bb-lang'>[$lang]</span>" : '';
				$title = "<span lang='$lang'>$title$postfix</span>";
			}
		}

		return $title;
	}

	function filter_the_locale($lang) {
		if (is_singular()) {
			return $this->get_post_language($lang);
		}

		return $lang;
	}

	function get_all_languages() {
		static $languages;

		if (!isset($languages)) {
			$languages = array(
					'ar' => __('Arabic', 'basic-bilingual'),
					'az' => __('Azerbaijani', 'basic-bilingual'),
					'bg' => __('Bulgarian', 'basic-bilingual'),
					'bn' => __('Bengali', 'basic-bilingual'),
					'bs' => __('Bosnian', 'basic-bilingual'),
					'ca' => __('Catalan', 'basic-bilingual'),
					'cs' => __('Czech', 'basic-bilingual'),
					'cy' => __('Welsh', 'basic-bilingual'),
					'da' => __('Danish', 'basic-bilingual'),
					'de' => __('German', 'basic-bilingual'),
					'el' => __('Greek', 'basic-bilingual'),
					'en' => __('English', 'basic-bilingual'),
					'eo' => __('Esperanto', 'basic-bilingual'),
					'es' => __('Spanish', 'basic-bilingual'),
					'et' => __('Estonian', 'basic-bilingual'),
					'eu' => __('Basque', 'basic-bilingual'),
					'fa' => __('Persian', 'basic-bilingual'),
					'fi' => __('Finnish', 'basic-bilingual'),
					'fr' => __('French', 'basic-bilingual'),
					'fy' => __('Frisian', 'basic-bilingual'),
					'ga' => __('Irish', 'basic-bilingual'),
					'gl' => __('Galician', 'basic-bilingual'),
					'he' => __('Hebrew', 'basic-bilingual'),
					'hi' => __('Hindi', 'basic-bilingual'),
					'hr' => __('Croatian', 'basic-bilingual'),
					'hu' => __('Hungarian', 'basic-bilingual'),
					'id' => __('Indonesian', 'basic-bilingual'),
					'is' => __('Icelandic', 'basic-bilingual'),
					'it' => __('Italian', 'basic-bilingual'),
					'ja' => __('Japanese', 'basic-bilingual'),
					'ka' => __('Georgian', 'basic-bilingual'),
					'kk' => __('Kazakh', 'basic-bilingual'),
					'km' => __('Khmer', 'basic-bilingual'),
					'kn' => __('Kannada', 'basic-bilingual'),
					'ko' => __('Korean', 'basic-bilingual'),
					'lt' => __('Lithuanian', 'basic-bilingual'),
					'lv' => __('Latvian', 'basic-bilingual'),
					'mk' => __('Macedonian', 'basic-bilingual'),
					'ml' => __('Malayalam', 'basic-bilingual'),
					'mn' => __('Mongolian', 'basic-bilingual'),
					'nb' => __('Norwegian Bokmal', 'basic-bilingual'),
					'ne' => __('Nepali', 'basic-bilingual'),
					'nl' => __('Dutch', 'basic-bilingual'),
					'nn' => __('Norwegian Nynorsk', 'basic-bilingual'),
					'pa' => __('Punjabi', 'basic-bilingual'),
					'pl' => __('Polish', 'basic-bilingual'),
					'pt' => __('Portuguese', 'basic-bilingual'),
					'ro' => __('Romanian', 'basic-bilingual'),
					'ru' => __('Russian', 'basic-bilingual'),
					'sk' => __('Slovak', 'basic-bilingual'),
					'sl' => __('Slovenian', 'basic-bilingual'),
					'sq' => __('Albanian', 'basic-bilingual'),
					'sr' => __('Serbian', 'basic-bilingual'),
					'sv' => __('Swedish', 'basic-bilingual'),
					'sw' => __('Swahili', 'basic-bilingual'),
					'ta' => __('Tamil', 'basic-bilingual'),
					'te' => __('Telugu', 'basic-bilingual'),
					'th' => __('Thai', 'basic-bilingual'),
					'tr' => __('Turkish', 'basic-bilingual'),
					'tt' => __('Tatar', 'basic-bilingual'),
					'uk' => __('Ukrainian', 'basic-bilingual'),
					'ur' => __('Urdu', 'basic-bilingual'),
					'vi' => __('Vietnamese', 'basic-bilingual'),
					'zh' => __('Chinese', 'basic-bilingual')
			);
			asort($languages);
		}

		return $languages;
	}

}

global $the_basic_bilingual_plugin;
$the_basic_bilingual_plugin = new BasicBilingualPlugin();


// TEMPLATE TAGS
function bb_the_time($format="%A %d.%m.%Y<br />%Hh%M") {
	global $post, $the_basic_bilingual_plugin;
	$language = $the_basic_bilingual_plugin->get_post_language();
	$locale = $language . '_' . strtoupper($language);

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
	echo $the_basic_bilingual_plugin->get_post_language();
}

// this outputs the other language excerpt
function bb_get_the_other_excerpt($before=false, $after=false) {
	global $the_basic_bilingual_plugin;
	return $the_basic_bilingual_plugin->the_excerpts($before, $after);
}

// this prints the other language excerpt
function bb_the_other_excerpt() {
	global $the_basic_bilingual_plugin;
	echo $the_basic_bilingual_plugin->get_the_other_content();
}
