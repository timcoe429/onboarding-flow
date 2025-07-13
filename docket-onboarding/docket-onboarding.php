<?php
/**
 * Plugin Name: Docket Onboarding
 * Plugin URI: https://yourdocketonline.com
 * Description: Multi-step onboarding flow for Docket website plan selection
 * Version: 1.8.7
 * Author: Frank Castle
 * License: GPL v2 or later
 * Text Domain: docket-onboarding
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DOCKET_ONBOARDING_VERSION', '1.0.0');
define('DOCKET_ONBOARDING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOCKET_ONBOARDING_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class DocketOnboarding {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Load includes
        $this->includes();
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register shortcode
        add_shortcode('docket_onboarding', array($this, 'shortcode_handler'));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Include error logger first
        require_once plugin_dir_path(__FILE__) . 'includes/error-logger.php';
        
        // Include required files
        require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
        require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';
        require_once plugin_dir_path(__FILE__) . 'includes/client-portal/portal-database.php';
        require_once plugin_dir_path(__FILE__) . 'includes/client-portal/portal-functions.php';
        require_once plugin_dir_path(__FILE__) . 'includes/client-portal/portal-display.php';
        require_once plugin_dir_path(__FILE__) . 'includes/trello-sync.php';
        
        // Include NS Cloner integration if available
        if (file_exists(plugin_dir_path(__FILE__) . 'includes/ns-cloner-integration.php')) {
            require_once plugin_dir_path(__FILE__) . 'includes/ns-cloner-integration.php';
        }
        
        // Include Cloner Settings for API configuration
        if (file_exists(plugin_dir_path(__FILE__) . 'includes/cloner-settings.php')) {
            require_once plugin_dir_path(__FILE__) . 'includes/cloner-settings.php';
        }
        
        // Load admin interface only in admin
        if (is_admin() && file_exists(DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/client-portal/portal-admin.php')) {
            require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/client-portal/portal-admin.php';
        }
    }
    
    /**
     * Enqueue CSS and JavaScript files
     */
    public function enqueue_scripts() {
        // Temporarily load on all pages to test
        // Later we can add back the shortcode check
        
        // Enqueue CSS
        wp_enqueue_style(
            'docket-onboarding-css',
            DOCKET_ONBOARDING_PLUGIN_URL . 'assets/onboarding.css',
            array(),
            DOCKET_ONBOARDING_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'docket-onboarding-js',
            DOCKET_ONBOARDING_PLUGIN_URL . 'assets/onboarding.js',
            array('jquery'),
            DOCKET_ONBOARDING_VERSION,
            true
        );
        
        // Localize script for AJAX (if needed later)
        wp_localize_script('docket-onboarding-js', 'docket_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('docket_onboarding_nonce')
        ));
    }
    
    /**
     * Handle the shortcode
     */
    public function shortcode_handler($atts) {
        return docket_onboarding_render_shortcode($atts);
    }
}

// Initialize the plugin
new DocketOnboarding();

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'docket_onboarding_activate');
function docket_onboarding_activate() {
    // Add any activation tasks here
    // For example, create database tables, set default options, etc.
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'docket_onboarding_deactivate');
function docket_onboarding_deactivate() {
    // Add any deactivation tasks here
    // For example, clean up temporary data
}
