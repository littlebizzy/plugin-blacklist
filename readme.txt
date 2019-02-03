=== Plugin Blacklist ===

Contributors: littlebizzy
Donate link: https://www.patreon.com/littlebizzy
Tags: blacklist, disallow, block, plugins, functions
Requires at least: 4.4
Tested up to: 4.9
Requires PHP: 7.2
Multisite support: No
Stable tag: 1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: PLBLST

Allows web hosts, agencies, or other WordPress site managers to disallow a custom list of plugins from being activated for security or other reasons.

== Description ==

Allows web hosts, agencies, or other WordPress site managers to disallow a custom list of plugins from being activated for security or other reasons.

* [**Join our FREE Facebook group for support!**](https://www.facebook.com/groups/littlebizzy/)
* [Plugin Homepage](https://www.littlebizzy.com/plugins/plugin-blacklist)
* [Plugin GitHub](https://github.com/littlebizzy/plugin-blacklist)

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

#### Inspiration ####

* n/a

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

= 1.1.0 =
* "pause blacklist" feature added (admin notice for utility plugins that should be deactivated when not in use)
* it uses the same technique of the previous sections, checking all plugins on activation/deactivation processes, and also every hour via WP cron. The future and pause plugins info is saved in autoloaded options to avoid extra database queries.
* for clarity I have changed the admin notice color codes, now it shows red border left (error code) for deactivated plugins (yellow before), yellow left border (warning) for future plugins (blue before), and blue (info) for pause plugins.
* added a new section `Pause Blacklist` in the plugin blacklist.txt template ini file.

= 1.0.1 =
* minor performance improvements during the "by code" plugin detection, avoiding the WP active_plugins options update when no banned plugins detected by functions or classes

= 1.0.0 =
* initial release
* tested with PHP 7.0
* tested with PHP 7.1
* tested with PHP 7.2
* plugin uses PHP namespaces
* object-oriented codebase
* WP Cron fires 1x hour (de-activates blacklisted plugins, admin notice for future blacklist plugins)
