<?php
/**
 * Elementor Site Cloner Integration Helper
 * 
 * This file provides helper functions for integrating the Elementor Site Cloner
 * with other plugins like Docket Onboarding.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if Elementor Site Cloner is available
 */
function esc_is_available() {
    return class_exists('ESC_Clone_Manager');
}

/**
 * Clone a site using Elementor Site Cloner
 * 
 * @param int $template_site_id The ID of the template site to clone
 * @param string $site_name The name for the new site
 * @param string $site_url The URL for the new site (optional - will generate if not provided)
 * @return array|WP_Error Array with site details on success, WP_Error on failure
 */
function esc_clone_site($template_site_id, $site_name, $site_url = null) {
    if (!esc_is_available()) {
        return new WP_Error('esc_not_available', 'Elementor Site Cloner is not available');
    }
    
    // Generate site URL if not provided
    if (empty($site_url)) {
        $site_number = esc_get_next_site_number();
        $site_url = 'https://' . get_current_site()->domain . '/site' . $site_number . '/';
    }
    
    // Create clone manager instance
    $clone_manager = new ESC_Clone_Manager();
    
    // Clone the site
    return $clone_manager->clone_site($template_site_id, $site_name, $site_url);
}

/**
 * Get the next available site number
 */
function esc_get_next_site_number($prefix = 'site') {
    $sites = get_sites(array(
        'path__like' => '/' . $prefix . '%',
        'number' => 1000
    ));
    
    $highest_number = 0;
    foreach ($sites as $site) {
        if (preg_match('/\/' . $prefix . '(\d+)\//', $site->path, $matches)) {
            $number = intval($matches[1]);
            if ($number > $highest_number) {
                $highest_number = $number;
            }
        }
    }
    
    return $highest_number + 1;
}

/**
 * Get template site ID by path or name
 */
function esc_get_template_site_id($template_identifier) {
    // If it's already a numeric ID, return it
    if (is_numeric($template_identifier)) {
        return intval($template_identifier);
    }
    
    // Try to find by path
    $path = '/' . trim($template_identifier, '/') . '/';
    $sites = get_sites(array('path' => $path));
    
    if (!empty($sites)) {
        return $sites[0]->blog_id;
    }
    
    // Try to find by domain if subdomain install
    if (is_subdomain_install()) {
        $sites = get_sites(array('domain' => $template_identifier . '.' . get_current_site()->domain));
        if (!empty($sites)) {
            return $sites[0]->blog_id;
        }
    }
    
    return null;
}

/**
 * Integration hook for form submissions
 * Other plugins can use this filter to modify the clone parameters
 */
add_filter('esc_before_clone', function($params) {
    // Allow other plugins to modify clone parameters
    return $params;
}, 10, 1);

/**
 * Integration hook after clone completion
 * Other plugins can use this action to perform additional tasks
 */
add_action('esc_after_clone', function($new_site_id, $template_site_id, $result) {
    // Allow other plugins to perform actions after cloning
}, 10, 3); 