=== Plugin Name ===
Contributors: Steph, cvedovini
Donate link: http://www.amazon.de/exec/obidos/wishlist/3ZN17IJ7B1XW/
Tags: multilingual, bilingual, translation, language, languages
Requires at least: 2.7
Tested up to: 3.6
Stable tag: 0.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Inserts an extra field which you can use to summarize your post in a second language.

You might want to check [my work on multilingualism online](http://climbtothestars.org/focus/multilingual) to understand what brought me to develop this plugin.

Basically, what it does is add two extra fields to the post editing form:

- Language
- Other Language Excerpt

Where "Language" is a field for the two-letter code of the language the post was written in, and "Other Language Excerpt" is a textarea for a summary (or translation) of the post in a second language.

Both values are stored as postmeta.

The "Other Language Excerpt" is automatically inserted right before the post content, in a div with class "other-excerpt" and the correct language attribute.

Two template tags are provided:

- `bb_the_time()`, which outputs a localized version of the date and time
- `bb_the_language()`, which outputs the two-letter language code (for adding `lang="xx"` attributes to relevant HTML elements)

== Installation ==

1. Upload `basic-bilingual` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. If your languages are other than [fr] and [en], edit the line `$bb_languages=array('en', 'fr');` at the beginning of the plugin file to reflect this.
1. Edit your template with the template tags `bb_the_time()` and `bb_the_language()` if you wish (recommended).

== Changelog ==

= version 0.5 =
- Rewrite plugin to use classes and modern stuff

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

No. Basic Bilingual gives you an extra field in which you can write a short summary or a complete translation of your post.

= Does it work with more than two languages? =

No, the plugin was written for two languages, and it would require some modifications to work with 3 or n languages. I'd be happy to see somebody do it, though!

= Aren't there more complete plugins out there if I want to make all my content available in more than one language? =

Yes, there certainly are. My approach is to keep it simple and minimal, so that it works. I've been blogging bilingually since 2000, using [the method this plugin reproduces](http://climbtothestars.org/archives/2004/07/11/multilingual-weblog/) since 2004.

Translating everything is just too hard. Giving a brief summary allows people who don't understand the language you're writing the post well enough to at least know what it's about. See [my work on multilingualism online](http://climbtothestars.org/focus/multilingual) for more explanations.

= Some of my excerpts are disappearing, help! =

This is a problem up to version 0.3, sorry. See <a href="http://markjaquith.wordpress.com/2007/01/28/authorization-and-intentionorigination-verification-when-using-the-edit_post-hook/">Mark's explanation</a> and download 0.31, which should work.

== Screenshots ==

1. Post editing screen with Basic Bilingual installed. This has changed now, see http://www.flickr.com/photos/bunny/4102736550/.

== Future Development ==

Here's what I'd like this plugin to do, someday:

- complete "post" localisation (ie, French posts get French "furniture", etc.)
- automatic insertion of `lang` attributes
- option to show/hide languages selectively (ie, I want only English, hide that French!), adapting the blog localisation
- more languages...
