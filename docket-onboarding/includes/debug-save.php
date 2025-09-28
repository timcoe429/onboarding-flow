<?php
/**
 * Debug the save process
 */

require_once('../../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied');
}

global $wpdb;
$table_name = $wpdb->prefix . 'docket_form_content';

// Test saving directly
echo "<h1>Direct Save Test</h1>";

$test_content = '<h2>Fast Build WebsiteSS TEST SAVE</h2>
<p>Let\'s start by reviewing the terms and checking your WordPress experience</p>

<div class="terms-box">
    <div class="terms-content">
        <div class="terms-section">
            <h5>What You\'re Getting</h5>
            <p>TEST CONTENT - This is a test save!</p>
        </div>
    </div>
</div>';

// Try to save directly
$result = $wpdb->replace(
    $table_name,
    array(
        'form_type' => 'fast-build',
        'step_number' => 1,
        'content_key' => 'content',
        'content_value' => $test_content
    ),
    array('%s', '%d', '%s', '%s')
);

echo "<pre>";
if ($result !== false) {
    echo "✅ Direct save successful! Rows affected: " . $result . "\n";
    
    // Check if it saved
    $check = $wpdb->get_var("SELECT content_value FROM {$table_name} WHERE form_type = 'fast-build' AND step_number = 1 AND content_key = 'content'");
    
    if (strpos($check, 'TEST SAVE') !== false) {
        echo "✅ Content verified in database!\n";
    } else {
        echo "❌ Content NOT found in database after save!\n";
    }
} else {
    echo "❌ Direct save FAILED!\n";
    echo "Last error: " . $wpdb->last_error . "\n";
}
echo "</pre>";

// Check table structure
echo "<h2>Table Structure Check</h2>";
$columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
echo "<pre>";
print_r($columns);
echo "</pre>";

echo "<p><a href='" . admin_url('tools.php?page=docket-form-content') . "'>Back to Form Content Manager</a></p>";
