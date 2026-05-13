=== RB2B WordPress Plugin ===
Contributors: RB2B
Tags: RB2B, tracking, analytics, b2b
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.2
Requires PHP: 7.4
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easily add your RB2B tracking snippet to your WordPress site.

== Description ==

RB2B's WordPress plugin allows you to add your RB2B tracking snippet via a simple settings screen — no manual code edits required.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/rb2b/`, or install via the WordPress plugin screen.
2. Activate the plugin.
3. Go to **RB2B > Settings** and enter your Account ID.

== Changelog ==

= 1.2 =
* Tested up to WordPress 6.8
* Use wp_enqueue_scripts / wp_add_inline_script for tracking snippet (replaces direct echo)
* Use wp_add_inline_style for admin icon CSS (replaces direct echo in admin_head)
* Use plugin-specific plugin_action_links filter for efficiency
* Escape all output with esc_url, esc_html, esc_attr, esc_js
* Updated register_setting to use array form with explicit type and sanitize_callback
* Added ABSPATH guard
* Fixed stray </td> in settings page HTML
* Bumped minimum WordPress requirement to 5.0

= 1.0 =
* Initial release
