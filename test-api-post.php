<?php
/**
 * Test API POST Request
 * Upload this to yourdocketonline.com to test the API call
 */

require_once('wp-load.php');

echo "<h2>Testing Elementor Site Cloner API POST Request</h2>";

// Get API settings
$api_url = get_option('docket_cloner_api_url', 'https://dockethosting5.com');
$api_key = get_option('docket_cloner_api_key', 'esc_docket_2025_secure_key');

echo "<p><strong>API URL:</strong> " . esc_html($api_url) . "</p>";
echo "<p><strong>API Key:</strong> " . esc_html($api_key) . "</p>";

// Test data
$test_data = array(
    'template' => 'template1',
    'site_name' => 'Test Site ' . time(),
    'form_data' => array(
        'test' => 'data'
    )
);

echo "<h3>Sending test POST request...</h3>";
echo "<pre>Request data: " . print_r($test_data, true) . "</pre>";

// Make the request
$response = wp_remote_post($api_url . '/wp-json/elementor-site-cloner/v1/clone', array(
    'timeout' => 60,
    'headers' => array(
        'Content-Type' => 'application/json',
        'X-API-Key' => $api_key
    ),
    'body' => json_encode($test_data),
    'sslverify' => false
));

// Check response
if (is_wp_error($response)) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $response->get_error_message() . "</p>";
} else {
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $headers = wp_remote_retrieve_headers($response);
    
    echo "<p><strong>Response Code:</strong> " . $code . "</p>";
    echo "<p><strong>Response Headers:</strong></p>";
    echo "<pre>" . print_r($headers, true) . "</pre>";
    echo "<p><strong>Response Body:</strong></p>";
    echo "<pre>" . htmlspecialchars($body) . "</pre>";
    
    // Try to decode JSON
    $json = json_decode($body, true);
    if ($json) {
        echo "<p><strong>Decoded JSON:</strong></p>";
        echo "<pre>" . print_r($json, true) . "</pre>";
    }
}

echo "<p style='margin-top: 40px; color: #666;'>Remember to delete this file when done!</p>";
?> 