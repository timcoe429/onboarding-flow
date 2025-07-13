<?php
/**
 * Simple Error Log Viewer
 * Upload to yourdocketonline.com and visit directly
 */

// Get logs from multiple sources
$log_files = [
    'Main Error Log' => ini_get('error_log'),
    'WordPress Debug' => WP_CONTENT_DIR . '/debug.log',
    'Trello Debug' => WP_CONTENT_DIR . '/trello-debug.log'
];

echo '<h1>Error Logs</h1>';
echo '<style>body{font-family:monospace;} .log{background:#f5f5f5;padding:10px;margin:10px 0;border-left:4px solid #0073aa;} .error{border-left-color:#dc3232;background:#ffe6e6;}</style>';

foreach ($log_files as $name => $file) {
    echo "<h2>$name</h2>";
    
    if (!$file || !file_exists($file)) {
        echo "<p>File not found: $file</p>";
        continue;
    }
    
    $lines = file($file);
    if (!$lines) {
        echo "<p>Could not read file</p>";
        continue;
    }
    
    // Show last 50 lines
    $recent_lines = array_slice($lines, -50);
    
    foreach (array_reverse($recent_lines) as $line) {
        $class = (stripos($line, 'error') !== false) ? 'log error' : 'log';
        echo "<div class='$class'>" . htmlspecialchars($line) . "</div>";
    }
}
?> 