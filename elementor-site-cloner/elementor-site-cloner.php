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
require_once plugin_dir_path( __FILE__ ) . 'includes/class-placeholder-replacer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-file-cloner.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-elementor-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-admin-interface.php';
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

// Add AJAX handler for external cloning (works on frontend too)
add_action('wp_ajax_nopriv_esc_clone_site', 'esc_handle_ajax_clone');
add_action('wp_ajax_esc_clone_site', 'esc_handle_ajax_clone');

function esc_handle_ajax_clone() {
    // Check API key
    $api_key = $_POST['api_key'] ?? '';
    $stored_key = get_option('esc_api_key', 'esc_docket_2025_secure_key');
    
    if ($api_key !== $stored_key) {
        wp_send_json_error(['message' => 'Invalid API key']);
    }
    
    // Get parameters
    $template = sanitize_text_field($_POST['template'] ?? '');
    $site_name = sanitize_text_field($_POST['site_name'] ?? '');
    $form_data = $_POST['form_data'] ?? [];
    
    if (empty($template) || empty($site_name)) {
        wp_send_json_error(['message' => 'Missing required parameters']);
    }
    
    // Load clone manager
    require_once ESC_PLUGIN_DIR . 'includes/class-clone-manager.php';
    $clone_manager = new ESC_Clone_Manager();
    
    // Find template site
    $template_path = '/' . $template . '/';
    $sites = get_sites(['path' => $template_path]);
    if (empty($sites)) {
        wp_send_json_error(['message' => 'Template not found: ' . $template]);
    }
    
    $template_site_id = $sites[0]->blog_id;
    
    // Generate site URL from company name
    $company_name = !empty($form_data['business_name']) ? $form_data['business_name'] : $site_name;
    $site_path = esc_generate_site_path_from_company_name($company_name);
    $site_url = 'https://' . get_current_site()->domain . '/' . $site_path . '/';
    
    // Set appropriate user for API calls (no logged-in user from external site)
    if (!get_current_user_id()) {
        // First check if there's a configured API clone user
        $api_user_id = get_option('esc_api_clone_user_id');
        
        if ($api_user_id && get_user_by('id', $api_user_id)) {
            wp_set_current_user($api_user_id);
        } else {
            // Look for super admins first
            $super_admins = get_super_admins();
            if (!empty($super_admins)) {
                $super_admin = get_user_by('login', $super_admins[0]);
                if ($super_admin) {
                    wp_set_current_user($super_admin->ID);
                }
            } else {
                // Fall back to first regular admin
                $admins = get_users(['role' => 'administrator', 'number' => 1]);
                if (!empty($admins)) {
                    wp_set_current_user($admins[0]->ID);
                }
            }
        }
    }
    
    // Build placeholder replacements from form data
    $placeholders = array();
    if (!empty($form_data)) {
        // Map form fields to placeholders
        if (!empty($form_data['business_name'])) {
            $placeholders['{{company}}'] = $form_data['business_name'];
        }
        if (!empty($form_data['business_email'])) {
            $placeholders['{{email}}'] = $form_data['business_email'];
        }
        if (!empty($form_data['business_phone']) || !empty($form_data['business_phone_number']) || !empty($form_data['phone_number'])) {
            // Handle different possible field names
            $phone = $form_data['business_phone'] ?? $form_data['business_phone_number'] ?? $form_data['phone_number'] ?? '';
            if ($phone) {
                $placeholders['{{phone}}'] = $phone;
            }
        }
        if (!empty($form_data['business_address'])) {
            $placeholders['{{address}}'] = $form_data['business_address'];
        }
        if (!empty($form_data['business_city'])) {
            $placeholders['{{city}}'] = $form_data['business_city'];
        }
        if (!empty($form_data['business_state'])) {
            $placeholders['{{state}}'] = $form_data['business_state'];
        }
    }
    
    // Clone the site with placeholders
    $result = $clone_manager->clone_site($template_site_id, $site_name, $site_url, $placeholders);
    
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }
    
    // Return success
    wp_send_json_success([
        'site_id' => $result['site_id'],
        'site_url' => $result['site_url'],
        'admin_url' => $result['admin_url']
    ]);
}

/**
 * Generate site path from company name
 * Converts company name to clean URL-friendly slug
 */
function esc_generate_site_path_from_company_name($company_name) {
    // Convert to lowercase and remove all non-alphanumeric characters
    $base_path = strtolower($company_name);
    
    // Remove all special characters and spaces, keep only letters and numbers
    $base_path = preg_replace('/[^a-z0-9]/', '', $base_path);
    
    // If empty after cleaning, use a fallback
    if (empty($base_path)) {
        $base_path = 'docketsite' . time();
        return $base_path;
    }
    
    // Check if this path already exists
    $path_to_check = $base_path;
    $counter = 2;
    
    while (esc_site_path_exists($path_to_check)) {
        $path_to_check = $base_path . $counter;
        $counter++;
    }
    
    return $path_to_check;
}

/**
 * Check if a site path already exists
 */
function esc_site_path_exists($path) {
    $sites = get_sites(array(
        'path' => '/' . $path . '/',
        'number' => 1
    ));
    
    return !empty($sites);
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
