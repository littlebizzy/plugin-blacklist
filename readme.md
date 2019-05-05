# Plugin Blacklist

Allows web hosts, agencies, or other WordPress site managers to disallow a custom list of plugins from being activated for security or other reasons.

* [Plugin Homepage](https://www.littlebizzy.com/plugins/plugin-blacklist)
* [**Become A LittleBizzy.com Member Today!**](https://www.littlebizzy.com/members)

### Defined Constants

    /** Plugin Meta */
    define('DISABLE_NAG_NOTICES', true);
    
    /** Plugin Blacklist Functions */
    define('PLUGIN_BLACKLIST', true); // default = true

### Compatibility

This plugin has been designed for use on [SlickStack](https://slickstack.io) web servers with PHP 7.2 and MySQL 5.7 to achieve best performance. All of our plugins are meant for single site WordPress installations only — for both performance and usability reasons, we strongly recommend avoiding WordPress Multisite for the vast majority of your projects.

Any of our WordPress plugins may also be loaded as "Must-Use" plugins (meaning that they load first, and cannot be deactivated) by using our free [Autoloader](https://github.com/littlebizzy/autoloader) script in the `mu-plugins` directory.

### Support Issues

Please do not submit Pull Requests. Instead, kindly create a new Issue with relevant information if you are an experienced developer, otherwise you may become a [**LittleBizzy.com Member**](https://www.littlebizzy.com/members) if your company requires official support.
