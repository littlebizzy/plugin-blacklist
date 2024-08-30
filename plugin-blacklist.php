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
 * Load blacklist, graylist, and utility list from external file.
 *
 * @return array Parsed INI file data or an empty array on failure.
 */
function pbm_load_blacklist(): array {
    $file_path = WP_CONTENT_DIR . '/blacklist.txt'; // Path to the externally maintained blacklist file

    if ( ! file_exists( $file_path ) ) {
        return [];
    }

    // Manually read and clean up the INI file
    $lines = file( $file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    $cleaned_content = '';

    foreach ( $lines as $line ) {
        $line = trim( $line );

        // Skip full-line comments and empty lines
        if ( $line === '' || $line[0] === ';' || $line[0] === '#' ) {
            continue;
        }

        // Remove trailing comments after values
        if ( strpos( $line, ';' ) !== false ) {
            $line = explode( ';', $line, 2 )[0];
        }

        // Remove trailing comments after values using hash
        if ( strpos( $line, '#' ) !== false ) {
            $line = explode( '#', $line, 2 )[0];
        }

        $line = trim($line); // Trim again to remove any spaces left after removing comments

        if (!empty($line)) {
            $cleaned_content .= $line . PHP_EOL; // Add cleaned line to content
        }
    }

    // Use parse_ini_string instead of parse_ini_file
    $blacklist_data = parse_ini_string( $cleaned_content, true, INI_SCANNER_TYPED );

    if ( $blacklist_data === false ) {
        // Handle error gracefully by logging or notifying admin
        if ( is_admin() ) {
            add_action( 'admin_notices', 'pbm_show_ini_error_notice' );
        }
        return [];
    }

    return $blacklist_data;
}

/**
 * Display an admin notice if the INI file is not properly formatted.
 */
function pbm_show_ini_error_notice(): void {
    echo '<div class="notice notice-error">';
    echo '<p>' . esc_html__( 'Error: The blacklist.txt file is not properly formatted. Please check for syntax errors.', 'plugin-blacklist-manager' ) . '</p>';
    echo '</div>';
}

/**
 * Check if a plugin is blacklisted by folder, class, or function name.
 *
 * @param string $plugin Plugin path.
 * @return bool True if the plugin is blacklisted, false otherwise.
 */
function pbm_is_plugin_blacklisted( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = dirname( $plugin );

    // Check folder names
    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklist_data['blacklist'] ?? [] ) ) {
        return true;
    }

    // Check class names
    if ( pbm_is_class_or_function_blacklisted( $blacklist_data['blacklist classes'] ?? [], 'class_exists' ) ) {
        return true;
    }

    // Check function names
    if ( pbm_is_class_or_function_blacklisted( $blacklist_data['blacklist functions'] ?? [], 'function_exists' ) ) {
        return true;
    }

    return false;
}

/**
 * Check if a plugin is graylisted by folder, class, or function name.
 *
 * @param string $plugin Plugin path.
 * @return bool True if the plugin is graylisted, false otherwise.
 */
function pbm_is_plugin_graylisted( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = dirname( $plugin );

    // Check folder names
    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklist_data['graylist'] ?? [] ) ) {
        return true;
    }

    // Check class names
    if ( pbm_is_class_or_function_blacklisted( $blacklist_data['graylist classes'] ?? [], 'class_exists' ) ) {
        return true;
    }

    // Check function names
    if ( pbm_is_class_or_function_blacklisted( $blacklist_data['graylist functions'] ?? [], 'function_exists' ) ) {
        return true;
    }

    return false;
}

/**
 * Check if a plugin is in the utility list by folder, class, or function name.
 *
 * @param string $plugin Plugin path.
 * @return bool True if the plugin is in the utility list, false otherwise.
 */
function pbm_is_plugin_utility( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = dirname( $plugin );

    // Check folder names
    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklist_data['utility'] ?? [] ) ) {
        return true;
    }

    // Check class names
    if ( pbm_is_class_or_function_blacklisted( $blacklist_data['utility classes'] ?? [], 'class_exists' ) ) {
        return true;
    }

    // Check function names
    if ( pbm_is_class_or_function_blacklisted( $blacklist_data['utility functions'] ?? [], 'function_exists' ) ) {
        return true;
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
        // Exact match (wrapped in slashes)
        if ( strpos( $item, '/' ) === 0 && substr( $item, -1 ) === '/' ) {
            if ( trim( $item, '/' ) === $plugin_slug ) {
                return true;
            }
        }
        // Namespace match
        else if ( strpos( $plugin_slug, $item ) === 0 ) {
            return true;
        }
    }
    return false;
}

/**
 * Helper function to check if a class or function is blacklisted.
 *
 * @param array  $items List of class or function names.
 * @param string $check_type Type of check ('class_exists' or 'function_exists').
 * @return bool True if any class or function is blacklisted, false otherwise.
 */
function pbm_is_class_or_function_blacklisted( array $items, string $check_type ): bool {
    foreach ( $items as $name ) {
        if ( $check_type( $name ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Prevent activation of blacklisted plugins.
 *
 * @param string $plugin Plugin path.
 */
function pbm_prevent_activation( string $plugin ): void {
    if ( pbm_is_plugin_blacklisted( $plugin ) ) {
        deactivate_plugins( $plugin );
        wp_die( sprintf( __( 'The plugin %s is blacklisted and cannot be activated.', 'plugin-blacklist-manager' ), esc_html( $plugin ) ) );
    }

    if ( pbm_is_plugin_graylisted( $plugin ) ) {
        add_action( 'admin_notices', 'pbm_show_graylist_warning' );
    }

    if ( pbm_is_plugin_utility( $plugin ) ) {
        add_action( 'admin_notices', 'pbm_show_utility_notice' );
    }
}
add_action( 'activate_plugin', 'pbm_prevent_activation' );

/**
 * Display a warning for graylisted plugins.
 */
function pbm_show_graylist_warning(): void {
    echo '<div class="notice notice-warning is-dismissible">';
    echo '<p>' . esc_html__( 'A graylisted plugin has been activated. Please be aware that it may be blacklisted in the future.', 'plugin-blacklist-manager' ) . '</p>';
    echo '</div>';
}

/**
 * Display an error for blacklisted plugins.
 *
 * @param string $plugin Plugin path.
 */
function pbm_show_blacklist_error( string $plugin ): void {
    echo '<div class="notice notice-error is-dismissible">';
    echo '<p>' . sprintf( esc_html__( 'The plugin %s has been deactivated because it is blacklisted.', 'plugin-blacklist-manager' ), esc_html( $plugin ) ) . '</p>';
    echo '</div>';
}

/**
 * Display an info notice for utility plugins.
 */
function pbm_show_utility_notice(): void {
    echo '<div class="notice notice-info is-dismissible">';
    echo '<p>' . esc_html__( 'A utility plugin is active. Please deactivate it when not in use.', 'plugin-blacklist-manager' ) . '</p>';
    echo '</div>';
}

/**
 * Deactivate blacklisted plugins on every page load.
 */
function pbm_check_and_deactivate_blacklisted_plugins(): void {
    $active_plugins = get_option( 'active_plugins', [] );

    foreach ( $active_plugins as $plugin ) {
        if ( pbm_is_plugin_blacklisted( $plugin ) ) {
            deactivate_plugins( $plugin );

            // Show admin notice only in admin area
            if ( is_admin() ) {
                add_action( 'admin_notices', function() use ( $plugin ) {
                    pbm_show_blacklist_error( $plugin );
                });
            }
        }

        if ( pbm_is_plugin_graylisted( $plugin ) && is_admin() ) {
            add_action( 'admin_notices', 'pbm_show_graylist_warning' );
        }

        if ( pbm_is_plugin_utility( $plugin ) && is_admin() ) {
            add_action( 'admin_notices', 'pbm_show_utility_notice' );
        }
    }
}
add_action( 'init', 'pbm_check_and_deactivate_blacklisted_plugins' );

/**
 * Prevent installation of blacklisted plugins.
 *
 * @param array $result Result of the upgrade process.
 * @param array $hook_extra Additional hook arguments.
 * @return array Modified result array.
 */
function pbm_prevent_installation( $result, $hook_extra ) {
    $plugin_slug = $hook_extra['slug'] ?? '';
    if ( empty( $plugin_slug ) ) {
        return $result;
    }

    $blacklist_data = pbm_load_blacklist();
    if ( pbm_is_name_blacklisted( $plugin_slug, $blacklist_data['blacklist'] ?? [] ) ) {
        $result['error'] = new WP_Error( 'plugin_blacklisted', __( 'This plugin is blacklisted and cannot be installed.', 'plugin-blacklist-manager' ) );
    }

    return $result;
}
add_filter( 'upgrader_pre_install', 'pbm_prevent_installation', 10, 2 );

/**
 * Enqueue Admin Scripts to Gray Out "Install Now" Button.
 *
 * @param string $hook_suffix The current admin page.
 */
function pbm_enqueue_admin_scripts( string $hook_suffix ): void {
    if ( 'plugin-install.php' !== $hook_suffix ) {
        return;
    }

    // Pass the blacklist to JavaScript
    wp_localize_script(
        'jquery',
        'pbmBlacklistData',
        [ 'blacklist' => pbm_load_blacklist()['blacklist'] ?? [] ]
    );

    // Inline script to disable the "Install Now" button
    wp_add_inline_script( 'jquery', '
        jQuery(document).ready(function($) {
            var blacklistedPlugins = pbmBlacklistData.blacklist || [];

            $(".plugin-card").each(function() {
                var pluginSlug = $(this).data("slug");

                for (var i = 0; i < blacklistedPlugins.length; i++) {
                    var blacklistedItem = blacklistedPlugins[i];
                    if (blacklistedItem.startsWith("/") && blacklistedItem.endsWith("/")) {
                        if (blacklistedItem.replace(/\//g, "") === pluginSlug) {
                            $(this).find(".install-now").prop("disabled", true).css("opacity", "0.5").text("Blacklisted");
                            break;
                        }
                    } else if (pluginSlug.startsWith(blacklistedItem)) {
                        $(this).find(".install-now").prop("disabled", true).css("opacity", "0.5").text("Blacklisted");
                        break;
                    }
                }
            });
        });
    ' );
}
add_action( 'admin_enqueue_scripts', 'pbm_enqueue_admin_scripts' );

// Ref: ChatGPT
