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
Prefix: PLBLST
*/

defined( 'ABSPATH' ) || exit; // Prevent direct access

// Load blacklist, graylist, and utility list from external file
function pbm_load_blacklist() {
    $file_path = WP_CONTENT_DIR . '/blacklist.txt'; // Path to the externally maintained blacklist file

    if ( ! file_exists( $file_path ) ) {
        return [];
    }

    $blacklist_data = @parse_ini_file( $file_path, true, INI_SCANNER_TYPED );

    if ( $blacklist_data === false ) {
        // Handle error gracefully by logging or notifying admin
        if ( is_admin() ) {
            add_action( 'admin_notices', 'pbm_show_ini_error_notice' );
        }
        return [];
    }

    return $blacklist_data;
}

// Display an admin notice if the INI file is not properly formatted
function pbm_show_ini_error_notice() {
    echo '<div class="notice notice-error">';
    echo '<p>' . esc_html__( 'Error: The blacklist.txt file is not properly formatted. Please check for syntax errors.', 'plugin-blacklist-manager' ) . '</p>';
    echo '</div>';
}

// Check if a plugin is blacklisted
function pbm_is_plugin_blacklisted( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = dirname( $plugin );

    foreach ( $blacklist_data['blacklist'] ?? [] as $blacklisted_item ) {
        // Exact match (wrapped in slashes)
        if ( strpos( $blacklisted_item, '/' ) === 0 && substr( $blacklisted_item, -1 ) === '/' ) {
            if ( trim( $blacklisted_item, '/' ) === $plugin_slug ) {
                return true;
            }
        } 
        // Namespace match
        else if ( strpos( $plugin_slug, $blacklisted_item ) === 0 ) {
            return true;
        }
    }

    return false;
}

// Check if a plugin is graylisted
function pbm_is_plugin_graylisted( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = dirname( $plugin );

    foreach ( $blacklist_data['graylist'] ?? [] as $graylisted_item ) {
        // Exact match (wrapped in slashes)
        if ( strpos( $graylisted_item, '/' ) === 0 && substr( $graylisted_item, -1 ) === '/' ) {
            if ( trim( $graylisted_item, '/' ) === $plugin_slug ) {
                return true;
            }
        } 
        // Namespace match
        else if ( strpos( $plugin_slug, $graylisted_item ) === 0 ) {
            return true;
        }
    }

    return false;
}

// Check if a plugin is in the utility list
function pbm_is_plugin_utility( string $plugin ): bool {
    $blacklist_data = pbm_load_blacklist();
    $plugin_slug    = dirname( $plugin );

    foreach ( $blacklist_data['utility'] ?? [] as $utility_item ) {
        // Exact match (wrapped in slashes)
        if ( strpos( $utility_item, '/' ) === 0 && substr( $utility_item, -1 ) === '/' ) {
            if ( trim( $utility_item, '/' ) === $plugin_slug ) {
                return true;
            }
        } 
        // Namespace match
        else if ( strpos( $plugin_slug, $utility_item ) === 0 ) {
            return true;
        }
    }

    return false;
}

// Prevent activation of blacklisted plugins
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

// Display a warning for graylisted plugins
function pbm_show_graylist_warning(): void {
    echo '<div class="notice notice-warning is-dismissible">';
    echo '<p>' . esc_html__( 'A graylisted plugin has been activated. Please be aware that it may be blacklisted in the future.', 'plugin-blacklist-manager' ) . '</p>';
    echo '</div>';
}

// Display an error for blacklisted plugins
function pbm_show_blacklist_error( string $plugin ): void {
    echo '<div class="notice notice-error is-dismissible">';
    echo '<p>' . sprintf( esc_html__( 'The plugin %s has been deactivated because it is blacklisted.', 'plugin-blacklist-manager' ), esc_html( $plugin ) ) . '</p>';
    echo '</div>';
}

// Display an info notice for utility plugins
function pbm_show_utility_notice(): void {
    echo '<div class="notice notice-info is-dismissible">';
    echo '<p>' . esc_html__( 'A utility plugin is active. Please deactivate it when not in use.', 'plugin-blacklist-manager' ) . '</p>';
    echo '</div>';
}

// Deactivate blacklisted plugins on every page load
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

// Prevent installation of blacklisted plugins
function pbm_prevent_installation( $result, $hook_extra ) {
    $plugin_slug = $hook_extra['slug'] ?? '';
    if ( empty( $plugin_slug ) ) {
        return $result;
    }

    $blacklist_data = pbm_load_blacklist();
    foreach ( $blacklist_data['blacklist'] ?? [] as $blacklisted_item ) {
        // Exact match
        if ( strpos( $blacklisted_item, '/' ) === 0 && substr( $blacklisted_item, -1 ) === '/' ) {
            if ( trim( $blacklisted_item, '/' ) === $plugin_slug ) {
                $result['error'] = new WP_Error( 'plugin_blacklisted', __( 'This plugin is blacklisted and cannot be installed.', 'plugin-blacklist-manager' ) );
            }
        } 
        // Namespace match
        else if ( strpos( $plugin_slug, $blacklisted_item ) === 0 ) {
            $result['error'] = new WP_Error( 'plugin_blacklisted', __( 'This plugin is blacklisted and cannot be installed.', 'plugin-blacklist-manager' ) );
        }
    }

    return $result;
}
add_filter( 'upgrader_pre_install', 'pbm_prevent_installation', 10, 2 );

// Enqueue Admin Scripts to Gray Out "Install Now" Button
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
