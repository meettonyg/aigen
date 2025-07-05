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
* Unified BEM CSS methodology with design tokens
* Scalable component architecture
* Cross-generator consistency
* Mobile-first responsive design

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

== Architecture ==

The plugin uses a unified CSS architecture based on BEM methodology combined with design tokens:

* **Base Generator Classes**: `.generator__*` classes provide foundation styling
* **Generator-Specific Classes**: `.topics-generator__*`, `.offers-generator__*`, etc. for unique features
* **Design Tokens**: CSS custom properties for colors, spacing, typography
* **Inheritance System**: Minimal duplication through proper cascade inheritance
* **Mobile-First**: Responsive design optimized for all device sizes

For detailed documentation, see:
* `CSS-ARCHITECTURE.md` - Complete architecture guide
* `GENERATOR-DEVELOPMENT-GUIDE.md` - Developer guidelines

== Changelog ==

= 1.0.0 =
* Initial release
* Unified BEM + design tokens architecture
* Authority Hook centralized service
* Cross-generator visual consistency
* Comprehensive documentation
* Mobile-first responsive design
* Formidable Forms integration
