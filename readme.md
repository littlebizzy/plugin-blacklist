# Plugin Blacklist

Disallows bad WordPress plugins

## Description

Plugin Blacklist helps administrators prevent unwanted WordPress plugins from being used while also flagging plugins that may require review or only temporary activation.

The plugin reads its rules from `wp-content/blacklist.txt`. Entries under `[blacklist]` are enforced throughout the WordPress admin area: matching active plugins are deactivated, activation attempts are blocked, and the Plugins screen keeps only a Blacklisted label and the delete action. Matching Install Now buttons are also disabled in the WordPress.org plugin installer, including results loaded through AJAX.

These restrictions do not delete plugin files or prevent plugins from being uploaded by other methods, but matching plugins cannot remain active.

Entries under `[graylist]` are allowed to remain active but generate an admin warning because they may be prohibited in the future. Entries under `[utility]` generate an informational reminder to deactivate those plugins when they are not being actively used.

Blacklist entries use prefix matching by default. For example, an entry of `plugin-name` also matches plugin slugs beginning with `plugin-name`, such as `plugin-name-pro`. To target only one exact plugin slug, wrap it in slashes, such as `/plugin-name/`.

Plugin Blacklist does not use a settings screen, database options, or scheduled jobs. The blacklist file is read directly and cached only for the current request, with admin notices shown when the file is missing, unreadable, or contains no enforced blacklist entries.

## Changelog

### 2.2.1
- standardized code comments and improved formatting for readability

### 2.2.0
- added `Tested up to:` header set at 6.9
- added `Update URI: false` header
- added `Text Domain: plugin-blacklist` header

### 2.1.6
- hardened plugin slug normalization for cross-platform filesystem compatibility

### 2.1.5
- added `Requires PHP` plugin header

### 2.1.4
- improved disabling "Install Now" button on exact match slugs like `/jetpack/`

### 2.1.3
- tweaked disable wordpress.org snippet
- replaced `esc_html()` with `wp_kses_post()` wherever messages are being output in admin notices

### 2.1.2
- added `ajaxComplete` handler to ensure "Install Now" button is properly disabled even after AJAX events
- minor code cleanup and security and performance enhancements

### 2.1.1
- changed `pbm_enqueue_admin_scripts` action to priority `25` to avoid conflicts with Repo Man

### 2.1.0
- minor refactoring to fix support for exact-match `/plugin-slug/` using slashes (without slashes is prefix match only)
- tweaks to formatting, comments, etc.

### 2.0.2
- add `gu_override_dot_org` (fixed) snippet

### 2.0.1
- new global variable `$pbm_blacklist_data`
- improved `pbm_add_admin_notice()` to prevent duplicates

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