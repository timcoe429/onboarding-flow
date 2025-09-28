<?php
/**
 * Check what's actually in the database
 */

require_once('../../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied');
}

global $wpdb;
$table_name = $wpdb->prefix . 'docket_form_content';

// Get the saved content for Fast Build Step 1
$result = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_name} WHERE form_type = %s AND step_number = %d AND content_key = %s",
    'fast-build', 1, 'content'
));

echo "<h1>Database Check</h1>";
echo "<pre>";

if ($result) {
    echo "Found in database:\n";
    echo "Form Type: " . $result->form_type . "\n";
    echo "Step: " . $result->step_number . "\n";
    echo "Content Key: " . $result->content_key . "\n";
    echo "Content Value (first 200 chars): " . substr($result->content_value, 0, 200) . "...\n\n";
    
    // Check if it has "WebsiteSS"
    if (strpos($result->content_value, 'WebsiteSS') !== false) {
        echo "✅ Found 'WebsiteSS' in database content!\n";
    } else {
        echo "❌ 'WebsiteSS' NOT found in database content\n";
    }
} else {
    echo "❌ NO CONTENT FOUND IN DATABASE for fast-build step 1!\n";
    echo "This means the form is using the default hardcoded values.\n";
}

echo "</pre>";

// Also check all content in the table
echo "<h2>All Database Content:</h2>";
$all = $wpdb->get_results("SELECT form_type, step_number, content_key, LEFT(content_value, 50) as preview FROM {$table_name}");
echo "<pre>";
print_r($all);
echo "</pre>";
