<?php
/**
 * Debug Log Viewer for Docket Onboarding Plugin
 * Upload this to yourdocketonline.com/wp-content/plugins/docket-onboarding/debug-logs.php
 * Then visit: https://yourdocketonline.com/wp-content/plugins/docket-onboarding/debug-logs.php
 */

// Security check
if (!defined('ABSPATH')) {
    // Include WordPress if not already loaded
    $wp_config_path = dirname(__FILE__) . '/../../../../wp-config.php';
    if (file_exists($wp_config_path)) {
        require_once($wp_config_path);
    } else {
        die('WordPress not found');
    }
}

// Check if user is logged in and has admin privileges
if (!current_user_can('manage_options')) {
    die('Access denied. Please log in as an administrator.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Docket Onboarding - Debug Logs</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .header { 
            background: #0073aa; 
            color: white; 
            padding: 20px; 
            border-radius: 8px 8px 0 0; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 24px; 
        }
        .controls { 
            padding: 20px; 
            border-bottom: 1px solid #eee; 
        }
        .btn { 
            background: #0073aa; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 4px; 
            cursor: pointer; 
            margin-right: 10px;
        }
        .btn:hover { background: #005a87; }
        .logs { 
            padding: 20px; 
            height: 600px; 
            overflow-y: auto; 
            background: #1e1e1e; 
            color: #d4d4d4; 
            font-family: "Courier New", monospace; 
            font-size: 13px; 
            line-height: 1.4;
        }
        .log-line { 
            margin-bottom: 5px; 
            padding: 3px 0;
        }
        .log-line.trello { 
            background: #2a2a2a; 
            color: #4ec9b0; 
            padding: 5px;
            border-left: 3px solid #4ec9b0;
        }
        .log-line.error { color: #f48771; }
        .log-line.success { color: #4ec9b0; }
        .timestamp { color: #808080; }
        .empty-state { 
            text-align: center; 
            color: #666; 
            padding: 40px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Docket Onboarding - Debug Logs</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Trello integration and form submission debugging</p>
        </div>
        
        <div class="controls">
            <button class="btn" onclick="refreshLogs()">🔄 Refresh Logs</button>
            <button class="btn" onclick="clearLogs()">🗑️ Clear Logs</button>
            <label>
                <input type="checkbox" id="autoRefresh" checked onchange="toggleAutoRefresh()"> 
                Auto-refresh (5s)
            </label>
        </div>
        
        <div class="logs" id="logsContainer">
            <div class="empty-state">Loading logs...</div>
        </div>
    </div>

    <script>
        let autoRefreshInterval;
        
        function refreshLogs() {
            // Get the error log content
            fetch(window.location.href + '?action=get_logs')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('logsContainer');
                    
                    if (data.error) {
                        container.innerHTML = `<div class="empty-state">❌ ${data.error}</div>`;
                        return;
                    }
                    
                    if (!data.logs || data.logs.length === 0) {
                        container.innerHTML = `<div class="empty-state">📝 No logs yet. Submit a form to see debug info!</div>`;
                        return;
                    }
                    
                    let html = '';
                    data.logs.forEach(line => {
                        if (line.trim() === '') return;
                        
                        let className = 'log-line';
                        if (line.includes('Trello Debug')) className += ' trello';
                        else if (line.includes('ERROR')) className += ' error';
                        else if (line.includes('SUCCESS')) className += ' success';
                        
                        // Highlight timestamps
                        line = line.replace(/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/g, '<span class="timestamp">$1</span>');
                        
                        html += `<div class="${className}">${line}</div>`;
                    });
                    
                    container.innerHTML = html;
                    container.scrollTop = container.scrollHeight;
                })
                .catch(error => {
                    document.getElementById('logsContainer').innerHTML = `<div class="empty-state">❌ Error loading logs: ${error.message}</div>`;
                });
        }
        
        function clearLogs() {
            if (!confirm('Are you sure you want to clear all debug logs?')) return;
            
            fetch(window.location.href + '?action=clear_logs')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('logsContainer').innerHTML = `<div class="empty-state">✅ Logs cleared! Submit a form to see new debug info.</div>`;
                    } else {
                        alert('Error clearing logs: ' + (data.error || 'Unknown error'));
                    }
                });
        }
        
        function toggleAutoRefresh() {
            const checkbox = document.getElementById('autoRefresh');
            
            if (checkbox.checked) {
                autoRefreshInterval = setInterval(refreshLogs, 5000);
            } else {
                clearInterval(autoRefreshInterval);
            }
        }
        
        // Initialize
        refreshLogs();
        toggleAutoRefresh();
    </script>
</body>
</html>

<?php
// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'get_logs') {
        // Try to find the WordPress error log
        $log_locations = array(
            WP_CONTENT_DIR . '/debug.log',
            ini_get('error_log'),
            '/tmp/error_log',
            WP_CONTENT_DIR . '/error.log'
        );
        
        $logs = array();
        $log_file = null;
        
        foreach ($log_locations as $location) {
            if ($location && file_exists($location)) {
                $log_file = $location;
                break;
            }
        }
        
        if ($log_file) {
            $lines = file($log_file);
            if ($lines) {
                // Get recent lines that contain our debug info
                $recent_lines = array_slice($lines, -200); // Last 200 lines
                
                // Filter for relevant lines
                foreach ($recent_lines as $line) {
                    if (strpos($line, 'Trello Debug') !== false || 
                        strpos($line, 'Docket Onboarding') !== false ||
                        strpos($line, 'form submission') !== false) {
                        $logs[] = trim($line);
                    }
                }
            }
        }
        
        echo json_encode(array(
            'success' => true,
            'logs' => $logs,
            'log_file' => $log_file
        ));
        exit;
    }
    
    if ($_GET['action'] === 'clear_logs') {
        // This is more complex - we can't easily clear just our logs from the main error log
        // Instead, we'll create a custom log file for our plugin
        $custom_log = WP_CONTENT_DIR . '/docket-onboarding-debug.log';
        file_put_contents($custom_log, '');
        
        echo json_encode(array(
            'success' => true,
            'message' => 'Custom debug log cleared'
        ));
        exit;
    }
}
?> 