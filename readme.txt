=== Plugin Name ===
Contributors: Steph, cvedovini
Donate link: http://www.amazon.de/exec/obidos/wishlist/3ZN17IJ7B1XW/
Tags: multilingual, bilingual, translation, language, languages
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: 0.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==


Allows you to set language for individual posts and pages and to summarize your posts and pages in different languages.

You might want to check [my work on multilingualism online](http://climbtothestars.org/focus/multilingual) to understand what brought me to develop this plugin.

The excerpts in other languages than the post's are automatically inserted right before the post content, in a div with class "other-excerpt" and the correct language attribute. The correct language attribute is also set on the post titles and original content.


== Installation ==

1. Upload `basic-bilingual` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Select your site languages in the plugin's settings

If you were using this plugin prior to version 1.0 you will have to migrate your posts and pages.
1. Make sure your back-up your database
1. Select the same 2 languages you were previously using in the "Site languages" option
1. Press the "Migrate" button on the settings page


== Changelog ==

= version 1.0 =
- Excerpts in multiple languages
- Option to filter excerpts based on Accept-Language HTTP header
- Migration from prior versions
- Deprecated template tags

= version 0.5 =
- Rewrite plugin to use classes and modern WP features

= version 0.4 =
- Modified the be fully compatible with the last WordPress versions: drag the language and other-excerpt boxes -- code provided by Luca Palli http://video.monte-ceneri.org/ (27.09.2009)
- Add the "Allow empty language" option on the new Options page (27.09.2009, Luca Palli)

= version 0.33 =
- Fixed a bunch of stuff, code provided kindly by Tim Isenheim http://www.freshlabs.de/journal/
- Half-arsed attempt to make the interface look prettier (30.01.2009)

= version 0.32 =
- Replaced the ugly "language box" in the admin section with a pretty DBX box. Drag it to the top of the page!

= version 0.31 =
- Attempted to fix vanishing excerpts problem -- see http://markjaquith.wordpress.com/2007/01/28/authorization-and-intentionorigination-verification-when-using-the-edit_post-hook/

= version 0.3 =
- Added stripslashes to get rid of slash problem
- No need to add template tag anymore for other-excerpt -- added automagically (30.11.2007)
- Added class to excerpt first-child

= version 0.21 =
- Fixed for WP 2.0 by replacing $postdata->ID with $post->ID (31.12.2005)
- Cosmetic changes to the edit form (03.01.2006)
- added hooks to deal with pages (03.01.2006)

= version 0.2 =
- Fixed update bug for other-excerpt (function name was wrong in action statement!) 28.01.2005

= version 0.1 =
- Initial release


== Frequently Asked Questions ==

= Will the plugin translate my posts? =

No. Basic Bilingual gives you the possibility to write short summaries, or a complete translation of your posts and pages, in other languages.

= Does it work with more than two languages? =

YES!

= Aren't there more complete plugins out there if I want to make all my content available in more than one language? =

Yes, there certainly are. My approach is to keep it simple and minimal, so that it works. I've been blogging bilingually since 2000, using [the method this plugin reproduces](http://climbtothestars.org/archives/2004/07/11/multilingual-weblog/) since 2004.

Translating everything is just too hard. Giving a brief summary allows people who don't understand the language you're writing the post well enough to at least know what it's about. See [my work on multilingualism online](http://climbtothestars.org/focus/multilingual) for more explanations.

= Some of my excerpts are disappearing, help! =

This is a problem up to version 0.3, sorry. See <a href="http://markjaquith.wordpress.com/2007/01/28/authorization-and-intentionorigination-verification-when-using-the-edit_post-hook/">Mark's explanation</a> and download 0.31, which should work.

= Is the plugin working with cache plugins? =

Yes, unless you are using the "Use Language-Accept header' option. In that case you'll need to use template tags and enclose them between `mfunc` tags (see you cache plugin's documentation about how to use those tags)


== Screenshots ==

1. Post editing screen with Basic Bilingual installed.
2. Basic Bilingual settings page.
