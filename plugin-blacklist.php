<?php
/*
Plugin Name: Plugin Blacklist
Plugin URI: https://www.littlebizzy.com/plugins/plugin-blacklist
Description: Disallows bad WordPress plugins
Version: 2.1.2
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
GitHub Plugin URI: littlebizzy/plugin-blacklist
Primary Branch: master
Tested up to: 6.6
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Disable WordPress.org updates for this plugin
add_filter( 'gu_override_dot_org', function( $overrides ) {
    $overrides[] = 'plugin-blacklist/plugin-blacklist.php';
    return $overrides;
});

// Global to store parsed blacklist data
global $pbm_blacklist_data;
$pbm_blacklist_data = null;

// Load blacklist data from external INI file
function pbm_load_blacklist(): array {
    global $pbm_blacklist_data;

    // If already loaded, return the data
    if ( ! is_null( $pbm_blacklist_data ) ) {
        return $pbm_blacklist_data;
    }

    $file_path = WP_CONTENT_DIR . '/blacklist.txt'; // Path to the blacklist file

    // Check if the file exists and is readable
    if ( ! file_exists( $file_path ) ) {
        pbm_add_admin_notice( 'Blacklist file not found: ' . esc_html( $file_path ) . '. Please upload the correct file.', 'error' );
        return [];
    }
    if ( ! is_readable( $file_path ) ) {
        pbm_add_admin_notice( 'Blacklist file is not readable: ' . esc_html( $file_path ) . '. Please check file permissions.', 'error' );
        return [];
    }

    // Parse the file manually
    $blacklist_data = [];
    $current_section = null;

    $file = fopen( $file_path, 'r' );
    if ( $file ) {
        while ( ( $line = fgets( $file ) ) !== false ) {
            $line = trim( $line );
            if ( empty( $line ) || $line[0] === ';' || $line[0] === '#' ) {
                continue; // Ignore comments and empty lines
            }
            // Remove comments after values and trim
            $line = preg_replace( '/\s*;\s*.*$/', '', $line );
            $line = trim( $line );

            // Parse section headers and plugin slugs
            if ( preg_match( '/^\[(.*)\]$/', $line, $matches ) ) {
                $current_section = strtolower( trim( $matches[1] ) );
                $blacklist_data[ $current_section ] = [];
            } elseif ( $current_section && ! empty( $line ) ) {
                $blacklist_data[ $current_section ][] = $line;
            }
        }
        fclose( $file );
    }

    // Handle empty blacklist section
    if ( empty( $blacklist_data['blacklist'] ) ) {
        pbm_add_admin_notice( 'No plugins listed under [blacklist] in the file. Add plugin slugs to prevent their use.', 'warning' );
    }

    $pbm_blacklist_data = $blacklist_data; // Cache blacklist data
    return $pbm_blacklist_data;
}

// Add admin notice
function pbm_add_admin_notice( string $message, string $type = 'error' ) {
    static $notices = [];
    foreach ( $notices as $notice ) {
        if ( $notice['message'] === $message && $notice['type'] === $type ) {
            return; // Avoid duplicate notices
        }
    }
    $notices[] = [ 'message' => $message, 'type' => $type ];
    add_action( 'admin_notices', function() use ( &$notices ) {
        foreach ( $notices as $notice ) {
            echo '<div class="notice notice-' . esc_attr( $notice['type'] ) . '"><p>' . esc_html( $notice['message'] ) . '</p></div>';
        }
        $notices = [];
    });
}

// Force deactivate blacklisted plugins
function pbm_force_deactivate_blacklisted_plugins() {
    $blacklist_data = pbm_load_blacklist();
    $blacklisted_plugins = $blacklist_data['blacklist'] ?? [];

    if ( empty( $blacklisted_plugins ) ) {
        return; // No blacklisted plugins to deactivate
    }

    $active_plugins = get_option( 'active_plugins', [] );
    $deactivated_plugins = [];

    foreach ( $active_plugins as $plugin ) {
        $plugin_slug = pbm_get_plugin_slug( $plugin );
        if ( pbm_is_name_blacklisted( $plugin_slug, $blacklisted_plugins ) ) {
            deactivate_plugins( $plugin ); // Deactivate the plugin
            $deactivated_plugins[] = $plugin_slug;
        }
    }

    // Display notice if any plugins were deactivated
    if ( ! empty( $deactivated_plugins ) ) {
        pbm_add_admin_notice(
            'The following blacklisted plugins have been deactivated: <strong>' . implode( ', ', $deactivated_plugins ) . '</strong>. Please remove them.',
            'error'
        );
    }
}
add_action( 'admin_init', 'pbm_force_deactivate_blacklisted_plugins' );

// Prevent activation of blacklisted plugins
function pbm_prevent_plugin_activation( $plugin ) {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = pbm_get_plugin_slug( $plugin );

    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklist_data['blacklist'] ?? [] ) ) {
        wp_die(
            'The plugin <strong>' . esc_html( $plugin_slug ) . '</strong> is blacklisted and cannot be activated. Please choose another plugin.',
            'Plugin Activation Error',
            [ 'back_link' => true ]
        );
    }
}
add_action( 'activate_plugin', 'pbm_prevent_plugin_activation' );

// Display notices for graylisted and utility plugins
function pbm_display_graylist_and_utility_notices() {
    $blacklist_data = pbm_load_blacklist();
    
    $graylisted_plugins = $blacklist_data['graylist'] ?? [];
    $utility_plugins = $blacklist_data['utility'] ?? [];
    $active_plugins = get_option( 'active_plugins', [] );

    // Notify for graylisted plugins
    $active_graylisted_plugins = array_filter( $active_plugins, function( $plugin ) use ( $graylisted_plugins ) {
        return pbm_is_name_blacklisted( pbm_get_plugin_slug( $plugin ), $graylisted_plugins );
    } );

    if ( ! empty( $active_graylisted_plugins ) ) {
        pbm_add_admin_notice(
            'The following graylisted plugins are active: <strong>' . implode( ', ', array_map( 'pbm_get_plugin_slug', $active_graylisted_plugins ) ) . '</strong>. They may be blacklisted in the future.',
            'warning'
        );
    }

    // Notify for utility plugins
    $active_utility_plugins = array_filter( $active_plugins, function( $plugin ) use ( $utility_plugins ) {
        return pbm_is_name_blacklisted( pbm_get_plugin_slug( $plugin ), $utility_plugins );
    } );

    if ( ! empty( $active_utility_plugins ) ) {
        pbm_add_admin_notice(
            'The following utility plugins are currently active: <strong>' . implode( ', ', array_map( 'pbm_get_plugin_slug', $active_utility_plugins ) ) . '</strong>. Deactivate them when not in use for security reasons.',
            'info'
        );
    }
}
add_action( 'admin_init', 'pbm_display_graylist_and_utility_notices' );

// Modify plugin action links for blacklisted plugins
function pbm_modify_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
    $blacklist_data = pbm_load_blacklist();
    $blacklisted_plugins = $blacklist_data['blacklist'] ?? [];

    $plugin_slug = pbm_get_plugin_slug( $plugin_file );

    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklisted_plugins ) ) {
        $new_actions = [];
        $new_actions['blacklisted'] = '<span style="color: #777;">Blacklisted</span>'; // Show Blacklisted label
        foreach ( $actions as $key => $action ) {
            if ( strpos( $key, 'delete' ) !== false ) {
                $new_actions[ $key ] = $action; // Keep delete action
            }
        }
        return $new_actions;
    }

    return $actions;
}
add_filter( 'plugin_action_links', 'pbm_modify_plugin_action_links', 10, 4 );
add_filter( 'network_admin_plugin_action_links', 'pbm_modify_plugin_action_links', 10, 4 );

// Disable "Install Now" button for blacklisted plugins
function pbm_enqueue_admin_scripts( $hook_suffix ) {
    if ( 'plugin-install.php' !== $hook_suffix ) {
        return;
    }

    $blacklist_data = pbm_load_blacklist();
    $blacklisted_plugins = array_map( 'strtolower', $blacklist_data['blacklist'] ?? [] );

    if ( empty( $blacklisted_plugins ) ) {
        return;
    }

    // Inline script to disable "Install Now" button for blacklisted plugins
    wp_add_inline_script( 'jquery-core', '
    jQuery(document).ready(function($) {
        var blacklistedPlugins = ' . esc_js( wp_json_encode( $blacklisted_plugins ) ) . ';

        function disableInstallButtons() {
            $(".install-now").each(function() {
                var pluginSlug = $(this).data("slug");

                if (pluginSlug) {
                    pluginSlug = pluginSlug.toLowerCase().trim();

                    var isBlacklisted = blacklistedPlugins.some(function(item) {
                        return pluginSlug === item || pluginSlug.startsWith(item);
                    });

                    if (isBlacklisted) {
                        $(this).prop("disabled", true)
                               .css({ "opacity": "0.5", "color": "#777", "border-color": "#ccc", "background-color": "#f7f7f7", "cursor": "default" })
                               .text("Blacklisted")
                               .removeAttr("href")
                               .off("click")
                               .click(function(e) { e.preventDefault(); e.stopPropagation(); });
                    }
                }
            });
        }

        // Initial run on page load
        disableInstallButtons();

        // Listen for AJAX completion and re-run the script
        $(document).ajaxComplete(function() {
            disableInstallButtons();
        });
    });
');
}
add_action( 'admin_enqueue_scripts', 'pbm_enqueue_admin_scripts', 5 );

// Helper function to check if a plugin is blacklisted
function pbm_is_name_blacklisted( string $plugin_slug, array $list ): bool {
    $plugin_slug = strtolower( trim( $plugin_slug ) );

    foreach ( $list as $item ) {
        $item = strtolower( trim( $item ) );

        // Check for exact match (wrapped in slashes)
        if ( preg_match( '/^\/.*\/$/', $item ) && trim( $item, '/' ) === $plugin_slug ) {
            return true;
        }
        // Check for wildcard match (prefix)
        elseif ( strpos( $plugin_slug, $item ) === 0 ) {
            return true;
        }
    }
    return false;
}

// Helper function to extract plugin slug from file path
function pbm_get_plugin_slug( string $plugin_file ): string {
    return dirname( $plugin_file );
}

// Ref: ChatGPT
