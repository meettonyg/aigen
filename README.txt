=== Media Kit Content Generator ===
Contributors: Guestify
Plugin Name: Media Kit Content Generator
Plugin URI: https://guestify.com
Description: Unified content generator for biography, offers, topics, and interview questions using AI
Author: Guestify
Version: 1.0.0
Author URI: https://guestify.com
Text Domain: media-kit-content-generator
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL2

== Description ==

Generate professional content for your media kit including:
* Professional biographies (short, medium, long)
* Service offers and packages
* Interview topics that showcase expertise
* Podcast interview questions

Features:
* Authority Hook builder with live preview
* AI-powered content generation
* Formidable Forms integration
* Responsive, professional design
* BEM CSS methodology

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/media-kit-content-generator/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Add your OpenAI API key to wp-config.php: `define('OPENAI_API_KEY', 'your-key-here');`
4. Use shortcodes to add generators to pages: `[mkcg_biography]`, `[mkcg_offers]`, etc.

== Frequently Asked Questions ==

= Do I need an OpenAI API key? =
Yes, you need an OpenAI API key for the AI content generation to work.

= Can I customize the styling? =
Yes, the plugin uses BEM CSS methodology making it easy to customize.

= Does it work with Formidable Forms? =
Yes, there's built-in integration for saving generated content to Formidable entries.

== Shortcodes ==

* `[mkcg_biography]` - Biography generator
* `[mkcg_offers]` - Offers generator  
* `[mkcg_topics]` - Topics generator
* `[mkcg_questions]` - Questions generator

== Changelog ==

= 1.0.0 =
* Initial release
* Unified architecture for all generators
* Authority Hook component
* BEM CSS methodology
* Formidable Forms integration
