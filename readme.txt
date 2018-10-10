=== Plugin Blacklist ===

Contributors: littlebizzy
Donate link: https://www.patreon.com/littlebizzy
Tags: blacklist, disallow, block, plugins, functions
Requires at least: 4.4
Tested up to: 4.9
Requires PHP: 7.2
Multisite support: No
Stable tag: 1.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: PLBLST

Allows web hosts, agencies, or other WordPress site managers to disallow a custom list of plugins from being activated for security or other reasons.

== Description ==

Allows web hosts, agencies, or other WordPress site managers to disallow a custom list of plugins from being activated for security or other reasons.

* [**Join our FREE Facebook group for support!**](https://www.facebook.com/groups/littlebizzy/)
* [Plugin Homepage](https://www.littlebizzy.com/plugins/plugin-blacklist)
* [Plugin GitHub](https://github.com/littlebizzy/plugin-blacklist)

*Our related OSS projects:*

* [SlickStack (LEMP stack automation)](https://slickstack.io)
* [WP Lite boilerplate](https://wplite.org)
* [Starter Theme](https://starter.littlebizzy.com)

#### The Long Version ####

Meant to be used as a Must Use (MU) plugin although it will work as a normal plugin too.

You need to customize the "Active" rules using the sample blacklist.txt file and place directly here:

    `wp-content/blacklist.txt`
    
...otherwise it will not work correctly. After blacklisted plugins (or PHP classes, and PHP functions) are specificied, any matching plugin will be unable to be activated in WordPress and a message will appear (disappears on refresh) explaining to the user that the given plugin is not allowed and thus was not activated.

There is also a section on blacklist.txt for "Future" blacklist to give your users some heads up notice that a certain list of plugins will be disallowed shortly to give them time to find alternative plugins, etc.

Both the Active and Future blacklists have a message that can be customized within the blacklist.txt file. The message for Active blacklist (e.g. "this plugin is not supported and has been disabled") appears only once, upon refresh it disappears. But the message for Future blacklist is non-dismissable and will continue to appear until those plugins are de-activated or deleted.

If for some reason a plugin gets activated but is blacklisted, WP Cron will automatically de-activate it each 1x hour.

Notes from developer 1.0.x:

* there is a special treatment of the blacklist.txt ini file to avoid common format pitfalls at parsing time, but let me know if you see any unexpected behavior.

* there is also support for cron events, doing the same verifications of the plugins activation/deactivation section.

* it only looks for source code from "normal" plugins directory, once the plugin to be deactivated/future has been located, it checks that the plugin path is under the wp-content/plugins directory (using the proper WP constant), so it does not affect to mu-plugins or theme directories/functions/classes.

#### Compatibility ####

This plugin has been designed for use on LEMP (Nginx) web servers with PHP 7.0 and MySQL 5.7 to achieve best performance. All of our plugins are meant for single site WordPress installations only; for both performance and security reasons, we highly recommend against using WordPress Multisite for the vast majority of projects.

Note: Any WordPress plugin may also be loaded as "Must-Use" by using the [Autoloader](https://github.com/littlebizzy/autoloader) script within the `mu-plugins` directory.

#### Defined Constants ####

* n/a

#### Plugin Features ####

* Premium Version: n/a
* Settings Page: No
* PHP Namespaces: Yes
* Object-Oriented Code: Yes
* Includes Media (images, icons, etc): No
* Includes CSS: No
* Database Storage: Yes
  * Transients: No
  * Options: Yes
  * Table Data: Yes
  * Creates New Tables: No
* Database Queries: Backend Only 
  * Query Types: Options API
* Multisite Support: No
* Uninstalls Data: Yes

#### Nag Notices ####

This plugin generates multiple [Admin Notices](https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices) in the WP Admin dashboard. The first is a notice that fires during plugin activation which recommends several related free plugins that we believe will enhance this plugin's features; this notice will re-appear approximately once every 6 months as our code and recommendations evolve. The second is a notice that fires a few days after plugin activation which asks for a 5-star rating of this plugin on its WordPress.org profile page. This notice will re-appear approximately once every 9 months. These notices can be dismissed by clicking the **(x)** symbol in the upper right of the notice box. These notices may annoy or confuse certain users, but are appreciated by the majority of our userbase, who understand that these notices support our free contributions to the WordPress community while providing valuable (free) recommendations for optimizing their website.

If you feel that these notices are too annoying, than we encourage you to consider one or more of our upcoming premium plugins that combine several free plugin features into a single control panel, or even consider developing your own plugins for WordPress, if supporting free plugin authors is too frustrating for you. A final alternative would be to place the defined constant mentioned below inside of your `wp-config.php` file to manually hide this plugin's nag notices:

    define('DISABLE_NAG_NOTICES', true);

Note: This defined constant will only affect the notices mentioned above, and will not affect any other notices generated by this plugin or other plugins, such as one-time notices that communicate with admin-level users.

#### Inspiration ####

* n/a

#### Free Plugins ####

* [404 To Homepage](https://wordpress.org/plugins/404-to-homepage-littlebizzy/)
* [Autoloader](https://github.com/littlebizzy/autoloader)
* [CloudFlare](https://wordpress.org/plugins/cf-littlebizzy/)
* [Delete Expired Transients](https://wordpress.org/plugins/delete-expired-transients-littlebizzy/)
* [Disable Admin-AJAX](https://wordpress.org/plugins/disable-admin-ajax-littlebizzy/)
* [Disable Author Pages](https://wordpress.org/plugins/disable-author-pages-littlebizzy/)
* [Disable Cart Fragments](https://wordpress.org/plugins/disable-cart-fragments-littlebizzy/)
* [Disable Embeds](https://wordpress.org/plugins/disable-embeds-littlebizzy/)
* [Disable Emojis](https://wordpress.org/plugins/disable-emojis-littlebizzy/)
* [Disable Empty Trash](https://wordpress.org/plugins/disable-empty-trash-littlebizzy/)
* [Disable Image Compression](https://wordpress.org/plugins/disable-image-compression-littlebizzy/)
* [Disable jQuery Migrate](https://wordpress.org/plugins/disable-jq-migrate-littlebizzy/)
* [Disable Search](https://wordpress.org/plugins/disable-search-littlebizzy/)
* [Disable WooCommerce Status](https://wordpress.org/plugins/disable-wc-status-littlebizzy/)
* [Disable WooCommerce Styles](https://wordpress.org/plugins/disable-wc-styles-littlebizzy/)
* [Disable XML-RPC](https://wordpress.org/plugins/disable-xml-rpc-littlebizzy/)
* [Download Media](https://wordpress.org/plugins/download-media-littlebizzy/)
* [Download Plugin](https://wordpress.org/plugins/download-plugin-littlebizzy/)
* [Download Theme](https://wordpress.org/plugins/download-theme-littlebizzy/)
* [Duplicate Post](https://wordpress.org/plugins/duplicate-post-littlebizzy/)
* [Enable Subtitles](https://wordpress.org/plugins/enable-subtitles-littlebizzy/)
* [Export Database](https://wordpress.org/plugins/export-database-littlebizzy/)
* [Facebook Pixel](https://wordpress.org/plugins/fb-pixel-littlebizzy/)
* [Force HTTPS](https://wordpress.org/plugins/force-https-littlebizzy/)
* [Force Strong Hashing](https://wordpress.org/plugins/force-strong-hashing-littlebizzy/)
* [Google Analytics](https://wordpress.org/plugins/ga-littlebizzy/)
* [Header Cleanup](https://wordpress.org/plugins/header-cleanup-littlebizzy/)
* [Index Autoload](https://wordpress.org/plugins/index-autoload-littlebizzy/)
* [Maintenance Mode](https://wordpress.org/plugins/maintenance-mode-littlebizzy/)
* [Profile Change Alerts](https://wordpress.org/plugins/profile-change-alerts-littlebizzy/)
* [Remove Category Base](https://wordpress.org/plugins/remove-category-base-littlebizzy/)
* [Remove Query Strings](https://wordpress.org/plugins/remove-query-strings-littlebizzy/)
* [Server Status](https://wordpress.org/plugins/server-status-littlebizzy/)
* [StatCounter](https://wordpress.org/plugins/sc-littlebizzy/)
* [View Defined Constants](https://wordpress.org/plugins/view-defined-constants-littlebizzy/)
* [Virtual Robots.txt](https://wordpress.org/plugins/virtual-robotstxt-littlebizzy/)

#### Special Thanks ####

* [Alex Georgiou](https://www.alexgeorgiou.gr)
* [Automattic](https://automattic.com)
* [Brad Touesnard](https://bradt.ca)
* [Daniel Auener](http://www.danielauener.com)
* [Delicious Brains](https://deliciousbrains.com)
* [Greg Rickaby](https://gregrickaby.com)
* [Matt Mullenweg](https://ma.tt)
* [Mika Epstein](https://halfelf.org)
* [Mike Garrett](https://mikengarrett.com)
* [Samuel Wood](http://ottopress.com)
* [Scott Reilly](http://coffee2code.com)
* [Jan Dembowski](https://profiles.wordpress.org/jdembowski)
* [Jeff Starr](https://perishablepress.com)
* [Jeff Chandler](https://jeffc.me)
* [Jeff Matson](https://jeffmatson.net)
* [Jeremy Wagner](https://jeremywagner.me)
* [John James Jacoby](https://jjj.blog)
* [Leland Fiegel](https://leland.me)
* [Paul Irish](https://www.paulirish.com)
* [Rahul Bansal](https://profiles.wordpress.org/rahul286)
* [Roots](https://roots.io)
* [rtCamp](https://rtcamp.com)
* [Ryan Hellyer](https://geek.hellyer.kiwi)
* [WP Chat](https://wpchat.com)
* [WP Tavern](https://wptavern.com)

#### Disclaimer ####

We released this plugin in response to our managed hosting clients asking for better access to their server, and our primary goal will remain supporting that purpose. Although we are 100% open to fielding requests from the WordPress community, we kindly ask that you keep the above-mentioned goals in mind... thanks!

== Installation ==

1. Upload to `/wp-content/mu-plugins/plugin-blacklist-littlebizzy`
2. Upload and customize `/wp-content/blacklist.txt`
3. Activate via WP Admin > Plugins
4. Test plugin is working

== Frequently Asked Questions ==

= How can I change this plugin's settings? =

There is a settings page where you can exclude certain types of query strings.

= I have a suggestion, how can I let you know? =

Please avoid leaving negative reviews in order to get a feature implemented. Instead, we kindly ask that you post your feedback on the wordpress.org support forums by tagging this plugin in your post. If needed, you may also contact our homepage.

== Changelog ==

= 1.0.1 =
* minor performance improvements during the "by code" plugin detection, avoiding the WP active_plugins options update when no banned plugins detected by functions or classes

= 1.0.0 =
* initial release
* tested with PHP 7.0
* tested with PHP 7.1
* tested with PHP 7.2
* plugin uses PHP namespaces
* object-oriented codebase
* WP Cron fires 1x hour (de-activates blacklisted plugins)
