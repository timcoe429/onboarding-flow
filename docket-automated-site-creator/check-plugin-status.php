<?php
/**
 * Plugin Status Checker
 * Upload this to dockethosting5.com root directory and visit it to check plugin status
 */

// Include WordPress
require_once('wp-config.php');
require_once('wp-includes/wp-db.php');
require_once('wp-includes/pluggable.php');
require_once('wp-admin/includes/plugin.php');

echo "<h1>Plugin Status Checker</h1>";

// Check if plugin directory exists
$plugin_dir = WP_PLUGIN_DIR . '/docket-automated-site-creator';
echo "<h2>Plugin Directory Check:</h2>";
if (is_dir($plugin_dir)) {
    echo "✅ Plugin directory exists: $plugin_dir<br>";
    
    // List files in plugin directory
    $files = scandir($plugin_dir);
    echo "Files in plugin directory:<br>";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file<br>";
        }
    }
} else {
    echo "❌ Plugin directory does not exist: $plugin_dir<br>";
}

// Check if main plugin file exists
$main_file = $plugin_dir . '/docket-automated-site-creator.php';
echo "<h2>Main Plugin File Check:</h2>";
if (file_exists($main_file)) {
    echo "✅ Main plugin file exists: $main_file<br>";
    
    // Check file permissions
    if (is_readable($main_file)) {
        echo "✅ Plugin file is readable<br>";
    } else {
        echo "❌ Plugin file is not readable<br>";
    }
} else {
    echo "❌ Main plugin file does not exist: $main_file<br>";
}

// Check if plugin is active
echo "<h2>Plugin Activation Check:</h2>";
if (function_exists('is_plugin_active')) {
    if (is_plugin_active('docket-automated-site-creator/docket-automated-site-creator.php')) {
        echo "✅ Plugin is active<br>";
    } else {
        echo "❌ Plugin is NOT active<br>";
        
        // Try to activate it
        echo "Attempting to activate plugin...<br>";
        $result = activate_plugin('docket-automated-site-creator/docket-automated-site-creator.php');
        if (is_wp_error($result)) {
            echo "❌ Failed to activate plugin: " . $result->get_error_message() . "<br>";
        } else {
            echo "✅ Plugin activated successfully<br>";
        }
    }
} else {
    echo "❌ is_plugin_active function not available<br>";
}

// Check if our class exists
echo "<h2>Class Check:</h2>";
if (class_exists('DocketAutomatedSiteCreator')) {
    echo "✅ DocketAutomatedSiteCreator class exists<br>";
} else {
    echo "❌ DocketAutomatedSiteCreator class does not exist<br>";
    
    // Try to include the file manually
    if (file_exists($main_file)) {
        echo "Attempting to include plugin file manually...<br>";
        include_once($main_file);
        
        if (class_exists('DocketAutomatedSiteCreator')) {
            echo "✅ Class loaded successfully after manual include<br>";
        } else {
            echo "❌ Class still not available after manual include<br>";
        }
    }
}

// Check REST API endpoints
echo "<h2>REST API Check:</h2>";
if (function_exists('rest_get_server')) {
    $rest_routes = rest_get_server()->get_routes();
    $docket_routes = array();
    
    foreach ($rest_routes as $route => $handlers) {
        if (strpos($route, 'docket') !== false) {
            $docket_routes[] = $route;
        }
    }
    
    if (!empty($docket_routes)) {
        echo "✅ Found " . count($docket_routes) . " Docket REST routes:<br>";
        foreach ($docket_routes as $route) {
            echo "  - $route<br>";
        }
    } else {
        echo "❌ No Docket REST routes found<br>";
    }
} else {
    echo "❌ REST API not available<br>";
}

// Show WordPress environment info
echo "<h2>WordPress Environment:</h2>";
echo "WordPress Version: " . get_bloginfo('version') . "<br>";
echo "Site URL: " . site_url() . "<br>";
echo "Plugin Directory: " . WP_PLUGIN_DIR . "<br>";
echo "Is Multisite: " . (is_multisite() ? 'Yes' : 'No') . "<br>";
if (is_multisite()) {
    echo "Current Site ID: " . get_current_blog_id() . "<br>";
    echo "Network Site URL: " . network_site_url() . "<br>";
}

// Show active plugins
echo "<h2>Active Plugins:</h2>";
$active_plugins = get_option('active_plugins', array());
if (is_multisite()) {
    $network_active = get_site_option('active_sitewide_plugins', array());
    $active_plugins = array_merge($active_plugins, array_keys($network_active));
}

if (!empty($active_plugins)) {
    foreach ($active_plugins as $plugin) {
        echo "  - $plugin<br>";
    }
} else {
    echo "No active plugins found<br>";
} 