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
 * Deactivate any active plugins that are found on the blacklist.
 */
function pbm_force_deactivate_blacklisted_plugins() {
    $blacklist_data = pbm_load_blacklist();
    $blacklisted_plugins = $blacklist_data['blacklist'] ?? [];

    if ( empty( $blacklisted_plugins ) ) {
        return; // No blacklisted plugins to deactivate
    }

    $active_plugins = get_option( 'active_plugins', [] );
    $deactivated_plugins = [];

    foreach ( $active_plugins as $plugin ) {
        $plugin_slug = dirname( $plugin ); // Get the folder name (namespace) of the plugin
        if ( in_array( $plugin_slug, $blacklisted_plugins, true ) ) {
            deactivate_plugins( $plugin );
            $deactivated_plugins[] = $plugin_slug;
        }
    }

    // Show an admin notice if any plugins were deactivated
    if ( ! empty( $deactivated_plugins ) ) {
        pbm_add_admin_notice(
            'The following plugins have been deactivated because they are blacklisted: <strong>' . implode( ', ', $deactivated_plugins ) . '</strong>',
            'error'
        );
    }
}
add_action( 'admin_init', 'pbm_force_deactivate_blacklisted_plugins' );

/**
 * Prevent activation of blacklisted plugins using wp_die() for restricted activation paths.
 *
 * @param string $plugin Plugin path.
 */
function pbm_prevent_plugin_activation( $plugin ) {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = dirname( $plugin );

    if ( in_array( $plugin_slug, $blacklist_data['blacklist'] ?? [], true ) ) {
        // Use wp_die() to stop activation attempt via dropdown or direct URL manipulation
        wp_die(
            'The plugin <strong>' . esc_html( $plugin_slug ) . '</strong> is blacklisted and cannot be activated.',
            'Plugin Activation Error',
            array( 'back_link' => true )
        );
    }
}
add_action( 'activate_plugin', 'pbm_prevent_plugin_activation' );

/**
 * Display notices for graylisted and utility plugins.
 */
function pbm_display_graylist_and_utility_notices() {
    $blacklist_data = pbm_load_blacklist();
    
    $graylisted_plugins = $blacklist_data['graylist'] ?? [];
    $utility_plugins = $blacklist_data['utility'] ?? [];

    // Active plugins list
    $active_plugins = get_option( 'active_plugins', [] );

    // Check active graylisted plugins and show a notice
    $active_graylisted_plugins = array_filter( $active_plugins, function( $plugin ) use ( $graylisted_plugins ) {
        return in_array( dirname( $plugin ), $graylisted_plugins, true );
    } );

    if ( ! empty( $active_graylisted_plugins ) ) {
        pbm_add_admin_notice(
            'The following plugins are on the graylist and may be blacklisted in the future: <strong>' . implode( ', ', array_map( 'dirname', $active_graylisted_plugins ) ) . '</strong>',
            'warning'
        );
    }

    // Check utility plugins and show a notice if they are active
    $active_utility_plugins = array_filter( $active_plugins, function( $plugin ) use ( $utility_plugins ) {
        return in_array( dirname( $plugin ), $utility_plugins, true );
    } );

    if ( ! empty( $active_utility_plugins ) ) {
        pbm_add_admin_notice(
            'The following utility plugins are currently active but should be deactivated when not in use: <strong>' . implode( ', ', array_map( 'dirname', $active_utility_plugins ) ) . '</strong>',
            'info'
        );
    }
}
add_action( 'admin_init', 'pbm_display_graylist_and_utility_notices' );

/**
 * Disable all action links except "Delete" for blacklisted plugins.
 *
 * @param array  $actions An array of plugin action links.
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 * @param array  $plugin_data An array of plugin data.
 * @param string $context The plugin context. Defaults are 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', 'search'.
 * @return array Modified array of plugin action links.
 */
function pbm_modify_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
    $blacklist_data = pbm_load_blacklist();
    $blacklisted_plugins = $blacklist_data['blacklist'] ?? [];

    $plugin_slug = dirname( $plugin_file ); // Get the folder name (namespace) of the plugin

    if ( in_array( $plugin_slug, $blacklisted_plugins, true ) ) {
        // Only keep the "Delete" link and replace others with "Blacklisted"
        $new_actions = [];

        // Add "Blacklisted" text
        $new_actions['blacklisted'] = '<span style="color: #777;">Blacklisted</span>'; // Set text color to gray

        foreach ( $actions as $key => $action ) {
            if ( strpos( $key, 'delete' ) !== false ) {
                $new_actions[ $key ] = $action;
            }
        }

        return $new_actions;
    }

    return $actions;
}
add_filter( 'plugin_action_links', 'pbm_modify_plugin_action_links', 10, 4 );
add_filter( 'network_admin_plugin_action_links', 'pbm_modify_plugin_action_links', 10, 4 );

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
                               "color": "#777", // Set text color to gray
                               "border-color": "#ccc",
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
 * Helper function to check if a name (folder) is blacklisted.
 *
 * @param string $plugin_slug Plugin slug.
 * @param array  $list List of blacklisted names.
 * @return bool True if the name is blacklisted, false otherwise.
 */
function pbm_is_name_blacklisted( string $plugin_slug, array $list ): bool {
    $plugin_slug = strtolower( trim( $plugin_slug ) ); // Optimize by trimming and lowering case once
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
