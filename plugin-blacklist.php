<?php
/*
Plugin Name: Plugin Blacklist
Plugin URI: https://www.littlebizzy.com/plugins/plugin-blacklist
Description: Disallows bad WordPress plugins
Version: 2.0.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
GitHub Plugin URI: littlebizzy/plugin-blacklist
Primary Branch: master
Tested up to: 6.6
Prefix: PLBLST
*/

defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * Load blacklist data from an external INI file manually.
 *
 * @return array Parsed INI file data or an empty array on failure.
 */
function pbm_load_blacklist(): array {
    $file_path = WP_CONTENT_DIR . '/blacklist.txt'; // Path to the externally maintained blacklist file

    if ( ! file_exists( $file_path ) ) {
        pbm_add_admin_notice( 'Blacklist file not found at the expected path: ' . esc_html( $file_path ), 'error' );
        return [];
    }

    if ( ! is_readable( $file_path ) ) {
        pbm_add_admin_notice( 'Blacklist file is not readable. Check file permissions for: ' . esc_html( $file_path ), 'error' );
        return [];
    }

    // Read the file manually and parse it line-by-line
    $blacklist_data   = [];
    $current_section = null;

    $file = fopen( $file_path, 'r' );
    if ( $file ) {
        while ( ( $line = fgets( $file ) ) !== false ) {
            $line = trim( $line );

            // Ignore empty lines and comment lines (starting with ';' or '#')
            if ( empty( $line ) || $line[0] === ';' || $line[0] === '#' ) {
                continue;
            }

            // Remove any inline comments after values and strip spaces (assumes semicolon as the comment character)
            $line = preg_replace( '/\s*;\s*.*$/', '', $line );
            $line = trim( $line ); // Trim any leading or trailing whitespace from the value

            // Check for section headers
            if ( preg_match( '/^\[(.*)\]$/', $line, $matches ) ) {
                $current_section = strtolower( trim( $matches[1] ) );
                $blacklist_data[ $current_section ] = [];
            } elseif ( $current_section && ! empty( $line ) ) {
                // Add to the current section
                $blacklist_data[ $current_section ][] = $line;
            }
        }
        fclose( $file );
    } else {
        pbm_add_admin_notice( 'Failed to open the blacklist file for reading.', 'error' );
        return [];
    }

    // Check if there is any data loaded in the 'blacklist' section
    if ( empty( $blacklist_data['blacklist'] ) ) {
        pbm_add_admin_notice( 'No plugins listed under the [blacklist] section in the blacklist file, or the file is empty.', 'warning' );
    }

    return $blacklist_data;
}

/**
 * Add an admin notice to be displayed in the WordPress dashboard.
 *
 * @param string $message The message to display.
 * @param string $type The type of notice (error, warning, success, info).
 */
function pbm_add_admin_notice( string $message, string $type = 'error' ) {
    static $notices = [];

    $notices[] = [ 'message' => $message, 'type' => $type ];

    add_action( 'admin_notices', function() use ( &$notices ) {
        foreach ( $notices as $notice ) {
            echo '<div class="notice notice-' . esc_attr( $notice['type'] ) . '"><p>' . $notice['message'] . '</p></div>';
        }
        $notices = []; // Clear notices after displaying them
    });
}

/**
 * Force deactivate any active blacklisted plugins.
 */
function pbm_force_deactivate_blacklisted_plugins() {
    if ( ! is_admin() ) {
        return; // Only run this check in the admin dashboard
    }

    $blacklist_data = pbm_load_blacklist();
    $blacklisted_plugins = $blacklist_data['blacklist'] ?? [];

    if ( empty( $blacklisted_plugins ) ) {
        return; // No blacklisted plugins found
    }

    $active_plugins = get_option( 'active_plugins', [] );
    $deactivated_plugins = [];

    foreach ( $active_plugins as $plugin ) {
        if ( pbm_is_plugin_blacklisted( $plugin ) ) {
            deactivate_plugins( $plugin );
            $plugin_slug = dirname( $plugin ); // Get the folder name (namespace) of the plugin
            $deactivated_plugins[] = '<strong>' . esc_html( $plugin_slug ) . '</strong>';
        }
    }

    if ( ! empty( $deactivated_plugins ) ) {
        // Display the admin notice with only the plugin folder names (namespaces) bolded
        pbm_add_admin_notice( 'The following plugins have been deactivated because they are blacklisted: ' . implode( ', ', $deactivated_plugins ), 'error' );
    }
}
add_action( 'admin_init', 'pbm_force_deactivate_blacklisted_plugins' );

/**
 * Enqueue Inline Script to Disable "Install Now" Button for Blacklisted Plugins.
 *
 * @param string $hook_suffix The current admin page.
 */
function pbm_enqueue_admin_scripts( $hook_suffix ) {
    if ( 'plugin-install.php' !== $hook_suffix ) {
        return;
    }

    $blacklist_data = pbm_load_blacklist();
    $blacklisted_plugins = isset( $blacklist_data['blacklist'] ) ? array_values( $blacklist_data['blacklist'] ) : [];

    // If blacklist data is empty after parsing, do nothing
    if ( empty( $blacklisted_plugins ) ) {
        return;
    }

    // Ensure blacklisted_plugins is a flat array of slugs
    $blacklisted_plugins = array_map( 'strtolower', $blacklisted_plugins );

    // Inline script to disable the "Install Now" button for blacklisted plugins
    wp_add_inline_script( 'jquery-core', '
        jQuery(document).ready(function($) {
            var blacklistedPlugins = ' . wp_json_encode( $blacklisted_plugins ) . ';

            $(".install-now").each(function() {
                var pluginSlug = $(this).data("slug");

                if (blacklistedPlugins.includes(pluginSlug)) {
                    $(this).prop("disabled", true)
                           .css({
                               "opacity": "0.5",
                               "color": "#aaa",
                               "border-color": "#aaa",
                               "background-color": "#f7f7f7",
                               "cursor": "default"
                           })
                           .text("Blacklisted")
                           .removeAttr("href")
                           .off("click")
                           .click(function(e) {
                               e.preventDefault();
                               e.stopPropagation();
                           });
                }
            });
        });
    ' );
}
add_action( 'admin_enqueue_scripts', 'pbm_enqueue_admin_scripts' );

/**
 * Prevent activation of blacklisted plugins.
 *
 * @param string $plugin Plugin path.
 */
function pbm_prevent_activation( $plugin ) {
    if ( pbm_is_plugin_blacklisted( $plugin ) ) {
        deactivate_plugins( $plugin );

        // Add admin notice and redirect back to Plugins page
        pbm_add_admin_notice( sprintf( __( 'The plugin %s is blacklisted and cannot be activated.', 'plugin-blacklist-manager' ), '<strong>' . esc_html( dirname( $plugin ) ) . '</strong>' ), 'error' );

        // Redirect back to the Plugins page
        wp_safe_redirect( admin_url( 'plugins.php' ) );
        exit;
    }

    if ( pbm_is_plugin_graylisted( $plugin ) ) {
        pbm_add_admin_notice( sprintf( __( 'Warning: The plugin %s is graylisted and may be blacklisted in the future.', 'plugin-blacklist-manager' ), '<strong>' . esc_html( dirname( $plugin ) ) . '</strong>' ), 'warning' );
    }

    if ( pbm_is_plugin_utility( $plugin ) ) {
        pbm_add_admin_notice( sprintf( __( 'Reminder: The plugin %s is a utility plugin. Deactivate when not in use.', 'plugin-blacklist-manager' ), '<strong>' . esc_html( dirname( $plugin ) ) . '</strong>' ), 'info' );
    }
}
add_action( 'plugins_loaded', function() {
    add_action( 'activate_plugin', 'pbm_prevent_activation' );
});

/**
 * Check if a plugin is blacklisted or if its classes/functions are blacklisted.
 *
 * @param string $plugin Plugin path.
 * @return bool True if the plugin is blacklisted, false otherwise.
 */
function pbm_is_plugin_blacklisted( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = strtolower( dirname( $plugin ) );

    // Check folder names
    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklist_data['blacklist'] ?? [] ) ) {
        return true;
    }

    // Check classes
    foreach ( $blacklist_data['blacklist classes'] ?? [] as $class ) {
        if ( class_exists( $class ) ) {
            return true;
        }
    }

    // Check functions
    foreach ( $blacklist_data['blacklist functions'] ?? [] as $function ) {
        if ( function_exists( $function ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Check if a plugin is graylisted or if its classes/functions are graylisted.
 *
 * @param string $plugin Plugin path.
 * @return bool True if the plugin is graylisted, false otherwise.
 */
function pbm_is_plugin_graylisted( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = strtolower( dirname( $plugin ) );

    // Check folder names
    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklist_data['graylist'] ?? [] ) ) {
        return true;
    }

    // Check classes
    foreach ( $blacklist_data['graylist classes'] ?? [] as $class ) {
        if ( class_exists( $class ) ) {
            return true;
        }
    }

    // Check functions
    foreach ( $blacklist_data['graylist functions'] ?? [] as $function ) {
        if ( function_exists( $function ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Check if a plugin is a utility or if its classes/functions are utilities.
 *
 * @param string $plugin Plugin path.
 * @return bool True if the plugin is a utility, false otherwise.
 */
function pbm_is_plugin_utility( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = strtolower( dirname( $plugin ) );

    // Check folder names
    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklist_data['utility'] ?? [] ) ) {
        return true;
    }

    // Check classes
    foreach ( $blacklist_data['utility classes'] ?? [] as $class ) {
        if ( class_exists( $class ) ) {
            return true;
        }
    }

    // Check functions
    foreach ( $blacklist_data['utility functions'] ?? [] as $function ) {
        if ( function_exists( $function ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Helper function to check if a name (folder) is blacklisted.
 *
 * @param string $plugin_slug Plugin slug.
 * @param array  $list List of blacklisted names.
 * @return bool True if the name is blacklisted, false otherwise.
 */
function pbm_is_name_blacklisted( string $plugin_slug, array $list ): bool {
    foreach ( $list as $item ) {
        $item = strtolower( trim( $item ) );
        // Exact match (wrapped in slashes)
        if ( strpos( $item, '/' ) === 0 && substr( $item, -1 ) === '/' ) {
            if ( trim( $item, '/' ) === $plugin_slug ) {
                return true;
            }
        }
        // Wildcard match
        elseif ( strpos( $plugin_slug, $item ) === 0 ) {
            return true;
        }
    }
    return false;
}

// Ref: ChatGPT
