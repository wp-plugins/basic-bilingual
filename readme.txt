=== Plugin Name ===
Contributors: Steph
Donate link: http://www.amazon.de/exec/obidos/wishlist/3ZN17IJ7B1XW/
Tags: multilingual, bilingual, translation, language, languages
Requires at least: 2.0
Tested up to: 2.8.6
Stable tag: trunk

Inserts an extra field which you can use to summarize your post in a second language.

== Description ==

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

1. Post editing screen with Basic Bilingual installed. This has changed now, the ugly language box has been replaced by a pretty DBX box in the sidebar.

== Future Development ==

Here's what I'd like this plugin to do, someday:

- complete "post" localisation (ie, French posts get French "furniture", etc.)
- automatic insertion of `lang` attributes
- option to show/hide languages selectively (ie, I want only English, hide that French!), adapting the blog localisation
- more languages...
