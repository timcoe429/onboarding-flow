<?php
/**
 * Fix the fucking slashes in the database once and for all
 */

require_once('../../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied');
}

global $wpdb;
$table_name = $wpdb->prefix . 'docket_form_content';

// Get all content
$results = $wpdb->get_results("SELECT * FROM {$table_name}");

$fixed = 0;
foreach ($results as $row) {
    // Remove those fucking slashes
    $clean_value = stripslashes($row->content_value);
    
    // Update the database
    $wpdb->update(
        $table_name,
        array('content_value' => $clean_value),
        array('id' => $row->id),
        array('%s'),
        array('%d')
    );
    
    $fixed++;
}

echo "<h1>Fixed {$fixed} records</h1>";
echo "<p>Those fucking slashes are GONE!</p>";
echo "<p><a href='" . admin_url('tools.php?page=docket-form-content') . "'>Go back to Form Content Manager</a></p>";
