<?php
/**
 * Plugin Name: Fix REST API Authentication for Multisite
 * Description: Allows API key-based authentication for custom REST endpoints
 * 
 * IMPORTANT: Upload this to /wp-content/mu-plugins/ on dockethosting5.com
 * This ensures it loads before other plugins
 */

// Allow our custom API endpoints to bypass WordPress authentication
add_filter('rest_authentication_errors', function($result) {
    // If there's already an error, pass it through
    if (!empty($result)) {
        return $result;
    }
    
    // Check if this is our custom API endpoint
    $current_route = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // If it's our elementor-site-cloner endpoint, allow it through
    if (strpos($current_route, '/wp-json/elementor-site-cloner/') !== false) {
        return true; // This allows the request to proceed to our custom permission_callback
    }
    
    return $result;
}, 5); // Run early, before default authentication

// Remove any filters that might be blocking REST API
add_action('rest_api_init', function() {
    // Log what's happening
    error_log('REST API Init - Current route: ' . $_SERVER['REQUEST_URI']);
    
    // Remove potential blocking filters
    remove_filter('rest_pre_dispatch', 'rest_handle_options_request', 10);
}, 5); // Run early

// Debug logging for multisite REST issues
add_filter('rest_pre_dispatch', function($result, $server, $request) {
    $route = $request->get_route();
    
    if (strpos($route, '/elementor-site-cloner/') !== false) {
        error_log('REST Pre Dispatch - Route: ' . $route);
        error_log('REST Pre Dispatch - Method: ' . $request->get_method());
        error_log('REST Pre Dispatch - Headers: ' . print_r($request->get_headers(), true));
    }
    
    return $result;
}, 1, 3); // Run very early 