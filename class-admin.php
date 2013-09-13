<?php

class BasicBilingualAdmin {

	const ALLOW_EMPTY_LANG_KEY = 'bb_allow_empty_language';

	function BasicBilingualAdmin($plugin) {
		$this->plugin = $plugin;
		$this->add_custom_boxes();
		$this->add_settings();

		/* Use the save_post action to do something with the data entered */
		add_action('save_post', array(&$this, 'save_post_data'));
	}

	function get_allow_empty_language() {
		return get_option(BasicBilingualAdmin::ALLOW_EMPTY_LANG_KEY, true);
	}

	function set_allow_empty_language($allow_empty_language) {
		return update_option(BasicBilingualAdmin::ALLOW_EMPTY_LANG_KEY, $allow_empty_language);
	}

	/**
	 * Adds a custom section to the Post and Page edit screens
	 */
	function add_custom_boxes() {
		add_meta_box('bb_language', __( 'Language', 'basic-bilingual' ), array(&$this, 'meta_box_language'), 'post', 'side');
		add_meta_box('bb_language', __( 'Language', 'basic-bilingual' ), array(&$this, 'meta_box_language'), 'page', 'side');
		add_meta_box('bb_other_excerpt', __( 'Other Language Excerpt', 'basic-bilingual' ), array(&$this, 'meta_box_other_excerpt'), 'post', 'normal');
		add_meta_box('bb_other_excerpt', __( 'Other Language Excerpt', 'basic-bilingual' ), array(&$this, 'meta_box_other_excerpt'), 'page', 'normal');
	}

	/**
	 * Prints the inner fields for the language post/page section
	 */
	function meta_box_language() {
		$languages = explode('|', BasicBilingualPlugin::LANGUAGES);

		// retrieving existing language, or setting to default if new post
		$current_language = $this->plugin->get_the_language();
		if (empty($current_language) && !$this->get_allow_empty_language()) {
			$current_language = $languages[0];
		}

		// Use nonce for verification
		echo '<input type="hidden" name="bb_noncename" id="bb_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';

		echo '<select name="' . BasicBilingualPlugin::LANGUAGE_KEY . '" id="' . BasicBilingualPlugin::LANGUAGE_KEY . '">';
		if ($this->get_allow_empty_language()) {
			echo '<option value="">' . __('&lt;none&gt;', 'basic-bilingual') . '</option>';
		}
		foreach ($languages as $lang) {
			echo '<option value="' . $lang . '" ' . selected($current_language, $lang, false) . '>' . $lang . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Prints the inner fields for the other excerpt_box post/page section
	 */
	function meta_box_other_excerpt() {
		$excerpt = $this->plugin->get_the_other_excerpt();

		// Use nonce for verification
		echo '<input type="hidden" name="bb_noncename" id="bb_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';

		// The actual fields for data entry
		echo '<textarea rows="10" cols="80" name="' . BasicBilingualPlugin::OTHER_EXCERPT_KEY . '" id="' . BasicBilingualPlugin::OTHER_EXCERPT_KEY . '" style="width: 100%;">' . $excerpt . '</textarea>';
		echo '<p>' . __('Write an excerpt of your post in the other language you use on your blog. Short and sweet, or long and detailed.', 'basic-bilingual') . '</p>';
	}

	/**
	 * Adds a settings
	 */
	function add_settings() {
		add_settings_section('basic-bilingual', __('Basic Bilingual', 'basic-bilingual'), array(&$this, 'settings_section_bb_writing'), 'writing');
		add_settings_field(BasicBilingualAdmin::ALLOW_EMPTY_LANG_KEY, __("Allow empty language:", 'basic-bilingual'),
				array(&$this, 'settings_field_allow_empty_lang'), 'writing', 'basic-bilingual');
		register_setting('writing', BasicBilingualAdmin::ALLOW_EMPTY_LANG_KEY);
		add_filter('plugin_action_links_basic-bilingual/basic-bilingual.php', array(&$this, 'add_settings_link'));
	}

	function add_settings_link($links) {
		$url = site_url('/wp-admin/options-writing.php');
		$links[] = '<a href="' . $url . '">' . __('Settings') . '</a>';
		return $links;
	}

	function settings_section_bb_writing() {
	}

	function settings_field_allow_empty_lang() {
		echo '<input name="' . BasicBilingualAdmin::ALLOW_EMPTY_LANG_KEY . '" id="' . BasicBilingualAdmin::ALLOW_EMPTY_LANG_KEY;
		echo '" type="checkbox" value="1" class="code" ' . checked( 1, $this->get_allow_empty_language(), true) . ' />';
	}

	/**
	 * When the post is saved, saves our custom data
	 */
	function save_post_data($id) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if (!wp_verify_nonce($_POST['bb_noncename'], plugin_basename(__FILE__))) {
			return $id;
		}

		// Check permissions
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $id)) {
				return $id;
			}
		} else {
			if (!current_user_can('edit_post', $id)) {
				return $id;
			}
		}

		// update language and other language excerpt custom fields
		$this->update_post_meta($id, BasicBilingualPlugin::LANGUAGE_KEY);
		$this->update_post_meta($id, BasicBilingualPlugin::OTHER_EXCERPT_KEY);
	}

	// general custom field update function
	function update_post_meta($id, $field) {
		$setting = stripslashes($_POST[$field]);
		return update_post_meta($id, $field, $setting);
	}

}