<?php
/**
 * Test REST API Endpoints
 * Upload this to dockethosting5.com and visit it directly to test
 */

// Test if our REST endpoints are registered
$rest_routes = rest_get_server()->get_routes();

echo "<h1>REST API Endpoint Test</h1>";
echo "<h2>Available Routes:</h2>";
echo "<pre>";

$docket_routes = array();
foreach ($rest_routes as $route => $handlers) {
    if (strpos($route, 'docket') !== false) {
        $docket_routes[$route] = $handlers;
    }
}

if (empty($docket_routes)) {
    echo "❌ NO DOCKET ROUTES FOUND!\n";
    echo "This means the DocketAutomatedSiteCreator plugin is not loading properly.\n\n";
    
    // Check if plugin file exists
    $plugin_file = WP_PLUGIN_DIR . '/docket-automated-site-creator/docket-automated-site-creator.php';
    if (file_exists($plugin_file)) {
        echo "✅ Plugin file exists at: $plugin_file\n";
        
        // Check if plugin is active
        if (is_plugin_active('docket-automated-site-creator/docket-automated-site-creator.php')) {
            echo "✅ Plugin is active\n";
        } else {
            echo "❌ Plugin is NOT active\n";
        }
    } else {
        echo "❌ Plugin file does not exist at: $plugin_file\n";
    }
} else {
    echo "✅ Found " . count($docket_routes) . " Docket routes:\n";
    foreach ($docket_routes as $route => $handlers) {
        echo "  $route\n";
    }
}

echo "</pre>";

// Test direct access to debug interface
echo "<h2>Direct Debug Interface Test:</h2>";
echo "<p><a href='/wp-json/docket/v1/debug-interface' target='_blank'>Click here to test debug interface</a></p>";

// Test debug logs endpoint
echo "<h2>Debug Logs Test:</h2>";
echo "<p><a href='/wp-json/docket/v1/debug-logs?api_key=docket_automation_key_2025' target='_blank'>Click here to test debug logs</a></p>";

// Show WordPress info
echo "<h2>WordPress Info:</h2>";
echo "<pre>";
echo "WordPress Version: " . get_bloginfo('version') . "\n";
echo "Site URL: " . site_url() . "\n";
echo "Is Multisite: " . (is_multisite() ? 'Yes' : 'No') . "\n";
echo "Current Site ID: " . get_current_blog_id() . "\n";
echo "Network Site URL: " . network_site_url() . "\n";
echo "</pre>"; 