# Plugin Blacklist

Disallows bad WordPress plugins

## Changelog

### 2.0.0
- completely refactored plugin to standard WordPress coding using ChatGPT
- no more cron jobs or database usage
- main blacklist now called `blacklist`
- `future` blacklist now called `graylist`
- `pause` blacklist now called `utility`
- renamed other sublists to: `blacklist classes`, `blacklist functions`, `graylist classes`, `graylist functions`, `utility classes`, `utility functions`
- blacklisted plugins force deactivated on each WP Admin page load via `init` if activated (no longer uses cron jobs for this check)
- new feature to gray out blacklisted plugins in the plugin search
- new featured to disable blacklisted plugins action links and replace with the word "Blacklisted"
- trying to circumvent using drop-down menu Activate or direct URL are sent to `wp_die`
- enhanced code for PHP 8.3
- compatible with PHP 7.0, 7.2, 7.4, 8.1
- support for Git Updater (although this is meant to be a MU plugin only)

### 1.1.1
- admin notices code play (incomplete)
- abandoned version

### 1.1.0
- "pause blacklist" feature added (admin notice for utility plugins that should be deactivated when not in use)
- it uses the same technique of the previous sections, checking all plugins on activation/deactivation processes, and also every hour via WP cron. The future and pause plugins info is saved in autoloaded options to avoid extra database queries.
- for clarity I have changed the admin notice color codes, now it shows red border left (error code) for deactivated plugins (yellow before), yellow left border (warning) for future plugins (blue before), and blue (info) for pause plugins.
- added a new section `Pause Blacklist` in the plugin blacklist.txt template ini file.

### 1.0.1
- minor performance improvements during the "by code" plugin detection, avoiding the WP active_plugins options update when no banned plugins detected by functions or classes

### 1.0.0
- initial release
- tested with PHP 7.0
- tested with PHP 7.1
- tested with PHP 7.2
- plugin uses PHP namespaces
- object-oriented codebase
- WP Cron fires 1x hour (de-activates blacklisted plugins)
