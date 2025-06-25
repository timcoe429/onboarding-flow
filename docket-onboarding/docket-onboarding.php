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
        
        // Add action to enqueue form-specific CSS when forms are loaded via AJAX
        add_action('wp_ajax_docket_load_fast_build_form', array($this, 'enqueue_fast_build_css'), 5);
        add_action('wp_ajax_nopriv_docket_load_fast_build_form', array($this, 'enqueue_fast_build_css'), 5);
        add_action('wp_ajax_docket_load_standard_build_form', array($this, 'enqueue_standard_build_css'), 5);
        add_action('wp_ajax_nopriv_docket_load_standard_build_form', array($this, 'enqueue_standard_build_css'), 5);
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Include shortcode handler if it exists
        if (file_exists(DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/shortcode.php')) {
            require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/shortcode.php';
        }
        
        // Include form handler if it exists
        if (file_exists(DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/form-handler.php')) {
            require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/form-handler.php';
        }
    }
    
    /**
     * Enqueue CSS and JavaScript files
     */
    public function enqueue_scripts() {
        // Check if we're on a page with the shortcode or if forms are being loaded
        $load_assets = has_shortcode(get_post_field('post_content', get_the_ID()), 'docket_onboarding') || 
                       (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('docket_load_fast_build_form', 'docket_load_standard_build_form')));
        
        if ($load_assets) {
            // Enqueue main CSS
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
            
            // Localize script for AJAX
            wp_localize_script('docket-onboarding-js', 'docket_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('docket_onboarding_nonce')
            ));
        }
    }
    
    /**
     * Enqueue Fast Build Form CSS
     */
    public function enqueue_fast_build_css() {
        // Register and enqueue the fast build form CSS
        wp_register_style(
            'docket-fast-build-form-css',
            DOCKET_ONBOARDING_PLUGIN_URL . 'assets/fast-build-form.css',
            array(),
            DOCKET_ONBOARDING_VERSION
        );
        
        // Add inline style tag to the form response
        add_filter('docket_fast_build_form_response', array($this, 'add_fast_build_css_inline'));
    }
    
    /**
     * Enqueue Standard Build Form CSS
     */
    public function enqueue_standard_build_css() {
        // Register and enqueue the standard build form CSS
        wp_register_style(
            'docket-standard-build-form-css',
            DOCKET_ONBOARDING_PLUGIN_URL . 'assets/standard-build-form.css',
            array(),
            DOCKET_ONBOARDING_VERSION
        );
        
        // Add inline style tag to the form response
        add_filter('docket_standard_build_form_response', array($this, 'add_standard_build_css_inline'));
    }
    
    /**
     * Add Fast Build CSS inline
     */
    public function add_fast_build_css_inline($form_html) {
        $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'assets/fast-build-form.css?ver=' . DOCKET_ONBOARDING_VERSION;
        $css_tag = '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
        return $css_tag . $form_html;
    }
    
    /**
     * Add Standard Build CSS inline
     */
    public function add_standard_build_css_inline($form_html) {
        $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'assets/standard-build-form.css?ver=' . DOCKET_ONBOARDING_VERSION;
        $css_tag = '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
        return $css_tag . $form_html;
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

?>
