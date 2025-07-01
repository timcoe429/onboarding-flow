<?php
/**
 * Test script for Trello integration
 * Place this in your WordPress root and access via browser to test
 */

// Load WordPress
require_once('wp-config.php');
require_once(ABSPATH . 'wp-settings.php');

// Include the Trello sync class
require_once('wp-content/plugins/docket-onboarding/includes/trello-sync.php');

echo "<h1>Trello Integration Test</h1>";

// Create instance
$trello = new DocketTrelloSync();

echo "<h2>1. Testing Trello API Connection</h2>";

// Test board lists
echo "<h3>Board Lists:</h3>";
$reflection = new ReflectionClass($trello);
$method = $reflection->getMethod('get_board_lists');
$method->setAccessible(true);

$lists = $method->invoke($trello);

if ($lists) {
    echo "<p>✅ Successfully connected to Trello board!</p>";
    echo "<ul>";
    foreach ($lists as $list) {
        echo "<li><strong>{$list['name']}</strong> (ID: {$list['id']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ Failed to connect to Trello board</p>";
}

echo "<h2>2. Testing Status Mapping</h2>";
$property = $reflection->getProperty('status_mapping');
$property->setAccessible(true);
$mapping = $property->getValue($trello);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Trello Column</th><th>Project Status</th></tr>";
foreach ($mapping as $trello_name => $status) {
    echo "<tr><td>{$trello_name}</td><td>{$status}</td></tr>";
}
echo "</table>";

echo "<h2>3. Testing Database Connection</h2>";
global $wpdb;

$projects_table = $wpdb->prefix . 'docket_client_projects';
$timeline_table = $wpdb->prefix . 'docket_project_timeline';

// Check if tables exist
$projects_exists = $wpdb->get_var("SHOW TABLES LIKE '{$projects_table}'") == $projects_table;
$timeline_exists = $wpdb->get_var("SHOW TABLES LIKE '{$timeline_table}'") == $timeline_table;

echo "<p>Projects table exists: " . ($projects_exists ? "✅" : "❌") . "</p>";
echo "<p>Timeline table exists: " . ($timeline_exists ? "✅" : "❌") . "</p>";

if ($projects_exists) {
    $project_count = $wpdb->get_var("SELECT COUNT(*) FROM {$projects_table}");
    echo "<p>Total projects: {$project_count}</p>";
}

echo "<h2>4. Manual Sync Test</h2>";
echo "<p>Running sync...</p>";

try {
    $results = $trello->sync_all_projects();
    echo "<p>✅ Sync completed successfully!</p>";
    echo "<p>Updated " . count($results) . " projects:</p>";
    
    if (count($results) > 0) {
        echo "<ul>";
        foreach ($results as $result) {
            echo "<li><strong>{$result['business_name']}</strong>: {$result['old_status']} → {$result['new_status']} (from {$result['trello_list']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No projects needed updating.</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Sync failed: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Current Project Status</h2>";
if ($projects_exists) {
    $projects = $wpdb->get_results("SELECT * FROM {$projects_table} ORDER BY created_at DESC LIMIT 5");
    
    if ($projects) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Business Name</th><th>Form Type</th><th>Current Step</th><th>Created</th><th>Portal URL</th></tr>";
        foreach ($projects as $project) {
            $portal_url = home_url("/project-status/{$project->client_uuid}/");
            echo "<tr>";
            echo "<td>{$project->business_name}</td>";
            echo "<td>{$project->form_type}</td>";
            echo "<td>{$project->current_step}</td>";
            echo "<td>" . date('M j, Y', strtotime($project->created_at)) . "</td>";
            echo "<td><a href='{$portal_url}' target='_blank'>View Portal</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No projects found.</p>";
    }
}

echo "<p><strong>Test completed!</strong></p>";
echo "<p>To test the system fully:</p>";
echo "<ol>";
echo "<li>Submit a form on your website</li>";
echo "<li>Check that a Trello card was created via email BCC</li>";
echo "<li>Move the card to different columns in Trello</li>";
echo "<li>Go to WordPress Admin → Tools → Trello Sync and click 'Sync Now'</li>";
echo "<li>Check the client portal to see updated status</li>";
echo "</ol>";
?> 