<?php
/**
 * Debug Dashboard - Real-time error and log viewer
 * Access via: /wp-content/plugins/docket-onboarding/debug-dashboard.php
 */

// Security check
if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    require_once('../../../wp-load.php');
}

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_logs':
            echo json_encode(get_all_logs());
            break;
        case 'clear_logs':
            clear_all_logs();
            echo json_encode(['success' => true]);
            break;
        case 'get_php_errors':
            echo json_encode(get_php_errors());
            break;
    }
    exit;
}

function get_all_logs() {
    $logs = [];
    
    // Docket error log (our new centralized log)
    $docket_log = WP_CONTENT_DIR . '/docket-errors.log';
    if (file_exists($docket_log)) {
        $logs['docket'] = [
            'title' => 'Docket Error Log (MAIN)',
            'content' => file_get_contents($docket_log),
            'size' => filesize($docket_log),
            'modified' => date('Y-m-d H:i:s', filemtime($docket_log))
        ];
    }
    
    // Template 4 debug log
    $template4_log = WP_CONTENT_DIR . '/esc-template4-debug.log';
    if (file_exists($template4_log)) {
        $logs['template4'] = [
            'title' => 'Template 4 Debug Log (CRITICAL)',
            'content' => file_get_contents($template4_log),
            'size' => filesize($template4_log),
            'modified' => date('Y-m-d H:i:s', filemtime($template4_log))
        ];
    }
    
    // Trello debug log
    $trello_log = WP_CONTENT_DIR . '/trello-debug.log';
    if (file_exists($trello_log)) {
        $logs['trello'] = [
            'title' => 'Trello Debug Log',
            'content' => file_get_contents($trello_log),
            'size' => filesize($trello_log),
            'modified' => date('Y-m-d H:i:s', filemtime($trello_log))
        ];
    }
    
    // WordPress debug log
    $wp_debug_log = WP_CONTENT_DIR . '/debug.log';
    if (file_exists($wp_debug_log)) {
        // Get last 100 lines to avoid huge files
        $lines = file($wp_debug_log);
        $last_lines = array_slice($lines, -100);
        $logs['wordpress'] = [
            'title' => 'WordPress Debug Log (Last 100 lines)',
            'content' => implode('', $last_lines),
            'size' => filesize($wp_debug_log),
            'modified' => date('Y-m-d H:i:s', filemtime($wp_debug_log))
        ];
    }
    
    // PHP error log
    $php_error_log = ini_get('error_log');
    if ($php_error_log && file_exists($php_error_log)) {
        $lines = file($php_error_log);
        $last_lines = array_slice($lines, -50);
        $logs['php'] = [
            'title' => 'PHP Error Log (Last 50 lines)',
            'content' => implode('', $last_lines),
            'size' => filesize($php_error_log),
            'modified' => date('Y-m-d H:i:s', filemtime($php_error_log))
        ];
    }
    
    // Docket form submissions
    $submissions = get_recent_form_submissions();
    if (!empty($submissions)) {
        $logs['submissions'] = [
            'title' => 'Recent Form Submissions',
            'content' => format_submissions($submissions),
            'size' => strlen(format_submissions($submissions)),
            'modified' => date('Y-m-d H:i:s')
        ];
    }
    
    return $logs;
}

function get_recent_form_submissions() {
    global $wpdb;
    
    $submissions = [];
    $options = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} 
         WHERE option_name LIKE 'docket_submission_%' 
         ORDER BY option_id DESC LIMIT 10"
    );
    
    foreach ($options as $option) {
        $data = maybe_unserialize($option->option_value);
        if (is_array($data)) {
            $submissions[] = [
                'id' => $option->option_name,
                'data' => $data,
                'timestamp' => isset($data['timestamp']) ? $data['timestamp'] : 'Unknown'
            ];
        }
    }
    
    return $submissions;
}

function format_submissions($submissions) {
    $output = '';
    foreach ($submissions as $submission) {
        $output .= "=== " . $submission['id'] . " ===\n";
        $output .= "Timestamp: " . $submission['timestamp'] . "\n";
        $output .= "Business: " . ($submission['data']['business_name'] ?? 'N/A') . "\n";
        $output .= "Template: " . ($submission['data']['website_template_selection'] ?? 'N/A') . "\n";
        $output .= "Form Type: " . ($submission['data']['form_type'] ?? 'N/A') . "\n";
        $output .= "Site URL: " . ($submission['data']['new_site_url'] ?? 'N/A') . "\n";
        $output .= "Full Data: " . json_encode($submission['data'], JSON_PRETTY_PRINT) . "\n\n";
    }
    return $output;
}

function clear_all_logs() {
    $logs_to_clear = [
        WP_CONTENT_DIR . '/docket-errors.log',
        WP_CONTENT_DIR . '/esc-template4-debug.log',
        WP_CONTENT_DIR . '/trello-debug.log',
        WP_CONTENT_DIR . '/debug.log'
    ];
    
    foreach ($logs_to_clear as $log) {
        if (file_exists($log)) {
            file_put_contents($log, '');
        }
    }
}

function get_php_errors() {
    $errors = [];
    
    // Check for common error locations
    $error_locations = [
        ini_get('error_log'),
        '/var/log/apache2/error.log',
        '/var/log/nginx/error.log',
        WP_CONTENT_DIR . '/debug.log'
    ];
    
    foreach ($error_locations as $location) {
        if ($location && file_exists($location)) {
            $lines = file($location);
            $recent_lines = array_slice($lines, -20);
            foreach ($recent_lines as $line) {
                if (strpos($line, 'PHP') !== false || strpos($line, 'Fatal') !== false || strpos($line, 'Error') !== false) {
                    $errors[] = trim($line);
                }
            }
        }
    }
    
    return array_unique($errors);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Docket Debug Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f1f1f1;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        .controls {
            margin-bottom: 20px;
        }
        .btn {
            background: #0073aa;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background: #005a87;
        }
        .btn.danger {
            background: #dc3232;
        }
        .btn.danger:hover {
            background: #a00;
        }
        .log-section {
            background: white;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .log-header {
            background: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 5px 5px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .log-meta {
            font-size: 12px;
            opacity: 0.8;
        }
        .log-content {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
            background: #f9f9f9;
            border-radius: 0 0 5px 5px;
        }
        .log-content pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        .error-line {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 5px 10px;
            margin: 2px 0;
        }
        .success-line {
            background: #e8f5e8;
            border-left: 4px solid #4caf50;
            padding: 5px 10px;
            margin: 2px 0;
        }
        .warning-line {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 5px 10px;
            margin: 2px 0;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .status {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 10px 15px;
            border-radius: 3px;
            display: none;
        }
        .auto-refresh {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 25px;
            background: #ccc;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .toggle-switch.active {
            background: #0073aa;
        }
        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 21px;
            height: 21px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.3s;
        }
        .toggle-switch.active::after {
            transform: translateX(25px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Docket Debug Dashboard</h1>
        
        <div class="controls">
            <button class="btn" onclick="refreshLogs()">🔄 Refresh Logs</button>
            <button class="btn danger" onclick="clearLogs()">🗑️ Clear All Logs</button>
            <div class="auto-refresh">
                <span>Auto-refresh:</span>
                <div class="toggle-switch" id="autoRefreshToggle" onclick="toggleAutoRefresh()"></div>
                <span id="autoRefreshStatus">OFF</span>
            </div>
        </div>
        
        <div id="logs-container">
            <div class="loading">Loading logs...</div>
        </div>
        
        <div class="status" id="status"></div>
    </div>

    <script>
        let autoRefreshInterval;
        let autoRefreshEnabled = false;
        
        function showStatus(message, type = 'info') {
            const status = document.getElementById('status');
            status.textContent = message;
            status.style.display = 'block';
            status.style.background = type === 'error' ? '#dc3232' : '#333';
            setTimeout(() => {
                status.style.display = 'none';
            }, 3000);
        }
        
        function refreshLogs() {
            showStatus('Refreshing logs...');
            
            fetch('?action=get_logs')
                .then(response => response.json())
                .then(logs => {
                    displayLogs(logs);
                    showStatus('Logs refreshed');
                })
                .catch(error => {
                    showStatus('Error loading logs: ' + error.message, 'error');
                });
        }
        
        function clearLogs() {
            if (confirm('Are you sure you want to clear all logs?')) {
                showStatus('Clearing logs...');
                
                fetch('?action=clear_logs')
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            showStatus('Logs cleared');
                            refreshLogs();
                        }
                    })
                    .catch(error => {
                        showStatus('Error clearing logs: ' + error.message, 'error');
                    });
            }
        }
        
        function displayLogs(logs) {
            const container = document.getElementById('logs-container');
            container.innerHTML = '';
            
            if (Object.keys(logs).length === 0) {
                container.innerHTML = '<div class="loading">No logs found</div>';
                return;
            }
            
            Object.entries(logs).forEach(([key, log]) => {
                const section = document.createElement('div');
                section.className = 'log-section';
                
                const header = document.createElement('div');
                header.className = 'log-header';
                header.innerHTML = `
                    <span>${log.title}</span>
                    <span class="log-meta">
                        Size: ${formatBytes(log.size)} | 
                        Modified: ${log.modified}
                    </span>
                `;
                
                const content = document.createElement('div');
                content.className = 'log-content';
                
                const pre = document.createElement('pre');
                pre.innerHTML = highlightLogContent(log.content);
                content.appendChild(pre);
                
                section.appendChild(header);
                section.appendChild(content);
                container.appendChild(section);
            });
        }
        
        function highlightLogContent(content) {
            return content
                .replace(/.*ERROR.*|.*FAILED.*|.*Exception.*|.*Fatal.*/gi, '<div class="error-line">$&</div>')
                .replace(/.*SUCCESS.*|.*completed.*|.*created successfully.*/gi, '<div class="success-line">$&</div>')
                .replace(/.*WARNING.*|.*NOTICE.*/gi, '<div class="warning-line">$&</div>');
        }
        
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function toggleAutoRefresh() {
            const toggle = document.getElementById('autoRefreshToggle');
            const status = document.getElementById('autoRefreshStatus');
            
            autoRefreshEnabled = !autoRefreshEnabled;
            
            if (autoRefreshEnabled) {
                toggle.classList.add('active');
                status.textContent = 'ON';
                autoRefreshInterval = setInterval(refreshLogs, 5000); // Refresh every 5 seconds
                showStatus('Auto-refresh enabled');
            } else {
                toggle.classList.remove('active');
                status.textContent = 'OFF';
                clearInterval(autoRefreshInterval);
                showStatus('Auto-refresh disabled');
            }
        }
        
        // Load logs on page load
        document.addEventListener('DOMContentLoaded', refreshLogs);
    </script>
</body>
</html> 