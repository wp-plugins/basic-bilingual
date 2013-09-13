<?php

class BasicBilingualAdmin {

	function BasicBilingualAdmin($plugin) {
		$this->plugin = $plugin;
		$this->add_settings();
		add_action('do_meta_boxes', array(&$this, 'customize_meta_boxes'));

		/* Use the save_post action to do something with the data entered */
		add_action('save_post', array(&$this, 'save_post_data'));
	}

	function customize_meta_boxes() {
		remove_meta_box('postexcerpt', 'post', 'normal');
		remove_meta_box('postexcerpt', 'page', 'normal');
		add_meta_box('bb-post-language', __( 'Language', 'basic-bilingual' ), array(&$this, 'meta_box_post_language'), 'post', 'side');
		add_meta_box('bb-post-language', __( 'Language', 'basic-bilingual' ), array(&$this, 'meta_box_post_language'), 'page', 'side');
		add_meta_box('bb-post-excerpts', __( 'Excerpts', 'basic-bilingual' ), array(&$this, 'meta_box_post_excerpts'), 'post', 'normal');
		add_meta_box('bb-post-excerpts', __( 'Excerpts', 'basic-bilingual' ), array(&$this, 'meta_box_post_excerpts'), 'page', 'normal');
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

	/**
	 * Prints the inner fields for the language post/page section
	 */
	function meta_box_post_language() {
		$site_languages = $this->plugin->get_site_languages();
		$all_languages = $this->get_all_languages();
		$default_language = $this->get_default_language();

		// retrieving existing language, or setting to default if new post
		$post_language = $this->plugin->get_post_language();
		if (empty($post_language)) {
			$post_language = $default_language;
		}

		// Use nonce for verification
		echo '<input type="hidden" name="bb_noncename" id="bb_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';

		echo '<select name="' . BB_POST_LANGUAGE . '" id="' . BB_POST_LANGUAGE . '">';
		foreach ($site_languages as $lang) {
			echo '<option value="' . $lang . '" ' . selected($current_language, $lang, false) . '>' . $all_languages[$lang] . '</option>';
		}
		echo '</select>';	}

	/**
	 * Prints the inner fields for the other excerpt_box post/page section
	 */
	function meta_box_post_excerpts() {
		$site_languages = $this->plugin->get_site_languages();
		$all_languages = $this->get_all_languages();
		$excerpts = $this->plugin->get_post_excerpts(); ?>

		<style>
			.excerpts-panel { display: none; background-color: white; border: 1px solid #dfdfdf; }
			.excerpts-panel textarea { width: 98%; padding: 12px 8px; border: none; }
		</style>
		<input type="hidden" name="bb_noncename" id="bb_noncename" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />
		<div id="excerpts">
			<ul class="category-tabs ">
				<?php foreach ($site_languages as $lang): ?>
		    	<li><a href="#tab-<?php echo $lang; ?>"><?php echo $all_languages[$lang]; ?></a></li>
		    	<?php endforeach; ?>
			</ul>
			<?php foreach ($site_languages as $lang): ?>
			<div id="tab-<?php echo $lang; ?>" class="excerpts-panel"><textarea rows="10" cols="80" name="excerpt-<?php echo $lang; ?>"
				id="excerpt-<?php echo $lang; ?>"><?php echo isset($excerpts[$lang]) ? $excerpts[$lang] : ''; ?></textarea></div>
	    	<?php endforeach; ?>
		</div>
		<script>jQuery(document).ready(function($) {
				$('#excerpts li:first').addClass('tabs');
				$('#excerpts .excerpts-panel:first').show();
				$('#excerpts a').click(function(){
					var t = $(this).attr('href');
					$(this).parent().addClass('tabs').siblings('li').removeClass('tabs');
					$('.excerpts-panel').hide();
					$(t).show();
					return false;
				});
			});</script>
		<p><?php _e('Write an excerpt of your post in the different languages you use on your blog. Short and sweet, or long and detailed.', 'basic-bilingual'); ?></p><?php
	}

	/**
	 * Adds a settings
	 */
	function add_settings() {
		add_submenu_page('options-general.php', __('Basic Bilingual Options', 'basic-bilingual'), __('Basic Bilingual', 'basic-bilingual'), 'manage_options', 'basic-bilingual', array(&$this, 'options_page'));
		add_filter('plugin_action_links_basic-bilingual/basic-bilingual.php', array(&$this, 'add_settings_link'));
	}

	function add_settings_link($links) {
		$url = site_url('/wp-admin/options-writing.php?basic-bilingual');
		$links[] = '<a href="' . $url . '">' . __('Settings') . '</a>';
		return $links;
	}

	function options_page() { ?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e('Basic Bilingual Options', 'basic-bilingual'); ?></h2>
			<div id="main-container" class="postbox-container metabox-holder" style="width:75%;"><div style="margin:0 8px;">
				<div class="postbox">
					<h3 style="cursor:default;"><span><?php _e('Options', 'basic-bilingual'); ?></span></h3>
					<div class="inside">
						<style>
							#languages-list { width: 25em; height: 16em; padding: 0.5em 0.8em; border: 1px solid #dfdfdf;
								background-color: white; overflow: auto; border-radius: 3px; -moz-border-radius: 3px; }
							#site-languages { border-bottom: 1px solid #dfdfdf; padding-bottom: 2px; }
						</style>
						<form method="post" action="options.php"><?php wp_nonce_field('update-options'); ?>
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="page_options" value="<?php echo BB_SITE_LANGUAGES . ',' . BB_USE_ACCEPT_HEADER;?>" />
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><?php _e('Site languages', 'basic-bilingual'); ?>:</th>
								<td><?php
									$site_languages = $this->plugin->get_site_languages();
									$all_languages = $this->get_all_languages();
									$default_language = $this->get_default_language();

									// Just make sure it's there...
									if (!in_array($default_language, $site_languages)) {
										$site_languages[] = $default_language;
									} ?>

									<div id="languages-list"><div id="site-languages">
									<?php foreach ($site_languages as $lang):
										$name = $all_languages[$lang];
										if ($lang == $default_language): ?>
											<div>
												<input type="hidden" name="<?php echo BB_SITE_LANGUAGES;?>[]" value="<?php echo $lang; ?>" />
												<label style="color:darkgray"><input type="checkbox" name="" value="" checked="true"
													disabled="disabled" />&nbsp;<?php echo $name; ?> (<?php _e('default site language', 'basic-bilingual'); ?>)</label>
											</div>
										<?php else: ?>
											<div>
												<label><input type="checkbox" name="<?php echo BB_SITE_LANGUAGES;?>[]"
													value="<?php echo $lang; ?>" checked="checked" />&nbsp;<?php echo $name; ?></label>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
									</div>
									<?php foreach ($all_languages as $lang => $name): ?>
										<?php if (!in_array($lang, $site_languages)): ?>
											<div>
												<label><input type="checkbox" name="<?php echo BB_SITE_LANGUAGES;?>[]"
													value="<?php echo $lang; ?>" />&nbsp;<?php echo $name; ?></label>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
									</div>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('Use Accept-Language header', 'basic-bilingual'); ?>:</th>
								<td>
									<label><input type="checkbox" name="<?php echo BB_USE_ACCEPT_HEADER;?>"
										value="1" <?php checked($this->plugin->get_use_accept_header()); ?> />&nbsp;
										<?php _e('Use the Accept-Language header sent by the visitor\'s browser to decide what excerpts to show.', 'basic-bilingual') ?></label>
								</td>
							</tr>
						</table>
						<?php submit_button(); ?>
						</form>
					</div> <!-- .inside -->
				</div> <!-- .postbox -->
			</div></div> <!-- #main-container -->

			<div id="side-container" class="postbox-container metabox-holder" style="width:24%;"><div style="margin:0 8px;">
				<div class="postbox">
					<h3 style="cursor:default;"><span><?php _e('Do you like this Plugin?', 'basic-bilingual'); ?></span></h3>
					<div class="inside">
						<p><?php _e('Please consider offering something to Stephanie from her <a href="http://www.amazon.de/exec/obidos/wishlist/3ZN17IJ7B1XW/">Amazon wishlist</a> or click on the Paypal button below to make a donation.', 'basic-bilingual'); ?></p>
						<div style="text-align:center">
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
							<input type="image" src="https://www.paypalobjects.com/en_US/CH/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
							</form>
						</div>
					</div> <!-- .inside -->
				</div> <!-- .postbox -->
				<div>
					<a class="twitter-timeline" href="https://twitter.com/cvedovini/basic-bilingual" data-widget-id="378520771070414848">Tweets from @cvedovini/basic-bilingual</a>
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
				</div>
			</div></div> <!-- #side-container -->

		</div><?php
		}

	/**
	 * When the post is saved, saves our custom data
	 */
	function save_post_data($post_id) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if (!wp_verify_nonce($_POST['bb_noncename'], plugin_basename(__FILE__))) {
			return $post_id;
		}

		// Check permissions
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return $post_id;
			}
		}

		// update language and other language excerpts custom fields
		$post_language = $this->get_request_data(BB_POST_LANGUAGE);
		$site_languages = $this->plugin->get_site_languages();
		$other_excerpts = array();

		foreach ($site_languages as $lang) {
			$excerpt = $this->get_request_data('excerpt-' . $lang);
			if ($excerpt) {
				$other_excerpts[$lang] = $excerpt;
				if ($post_language == $lang) {
					remove_action('save_post', array(&$this, 'save_post_data'));
					wp_update_post(array('ID' => $post_id, 'post_excerpt' => $excerpt));
					add_action('save_post', array(&$this, 'save_post_data'));
				}
			}
		}

		update_post_meta($post_id, BB_POST_LANGUAGE, $post_language);
		update_post_meta($post_id, BB_POST_EXCERPTS, $other_excerpts);
	}

	// general custom field update function
	function get_request_data($field) {
		if (isset($_REQUEST[$field])) {
			return stripslashes($_REQUEST[$field]);
		}

		return false;
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