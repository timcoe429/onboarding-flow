<?php
/**
 * Plugin Name: Elementor Site Cloner
 * Plugin URI: https://github.com/yourusername/elementor-site-cloner
 * Description: A specialized WordPress multisite plugin for cloning Elementor-based template sites quickly and reliably.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Network: true
 * Text Domain: elementor-site-cloner
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ESC_VERSION', '1.0.0');
define('ESC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ESC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ESC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader for our classes
spl_autoload_register(function ($class) {
    $prefix = 'ESC_';
    $base_dir = ESC_PLUGIN_DIR . 'includes/';
    
    // Check if the class uses our prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace underscores with hyphens and convert to lowercase
    $file = $base_dir . 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Include required files
require_once plugin_dir_path( __FILE__ ) . 'includes/class-clone-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-database-cloner.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-url-replacer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-file-cloner.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-elementor-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-interface.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-debug-utility.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-api-endpoint.php';

// Initialize the plugin
add_action('init', 'esc_init');
function esc_init() {
    // Only run on multisite
    if (!is_multisite()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . 
                 __('Elementor Site Cloner requires WordPress Multisite to be enabled.', 'elementor-site-cloner') . 
                 '</p></div>';
        });
        return;
    }
    
    // Check if Elementor is active
    if (!did_action('elementor/loaded')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>' . 
                 __('Elementor Site Cloner requires Elementor to be installed and activated.', 'elementor-site-cloner') . 
                 '</p></div>';
        });
        return;
    }
    
    // Initialize admin interface
    if ( is_admin() ) {
        new ESC_Admin_Interface();
    }
    
    // Initialize API endpoint
    new ESC_API_Endpoint();
}

// Activation hook
register_activation_hook(__FILE__, 'esc_activate');
function esc_activate() {
    // Create database table for clone logs
    global $wpdb;
    
    $table_name = $wpdb->base_prefix . 'esc_clone_logs';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        source_site_id bigint(20) NOT NULL,
        destination_site_id bigint(20) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        started_at datetime DEFAULT CURRENT_TIMESTAMP,
        completed_at datetime DEFAULT NULL,
        error_message text DEFAULT NULL,
        user_id bigint(20) NOT NULL,
        PRIMARY KEY (id),
        KEY source_site_id (source_site_id),
        KEY destination_site_id (destination_site_id),
        KEY status (status)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'esc_deactivate');
function esc_deactivate() {
    // Clean up any scheduled tasks
    wp_clear_scheduled_hook('esc_cleanup_old_logs');
}
