<?php
/**
 * Template 4 Debug Log Viewer
 * Access via: /wp-content/plugins/elementor-site-cloner/template4-debug.php
 */

// Load WordPress if not already loaded
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

$debug_log_file = WP_CONTENT_DIR . '/esc-template4-debug.log';

// Handle actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'clear' && file_exists($debug_log_file)) {
        file_put_contents($debug_log_file, '');
        echo '<div style="color: green; font-weight: bold; margin: 20px;">Debug log cleared!</div>';
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Template 4 Debug Log</title>
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
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .controls {
            padding: 20px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }
        .btn {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn:hover {
            background: #005a87;
        }
        .btn.danger {
            background: #dc3545;
        }
        .btn.danger:hover {
            background: #c82333;
        }
        .log-content {
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
            background: #1e1e1e;
            color: #f8f8f2;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        .log-content pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .error-line {
            background: rgba(255, 0, 0, 0.2);
            padding: 2px 5px;
            border-left: 3px solid #ff4444;
        }
        .success-line {
            background: rgba(0, 255, 0, 0.2);
            padding: 2px 5px;
            border-left: 3px solid #44ff44;
        }
        .info-line {
            background: rgba(0, 150, 255, 0.2);
            padding: 2px 5px;
            border-left: 3px solid #4488ff;
        }
        .no-log {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .file-info {
            padding: 15px 20px;
            background: #e9ecef;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        .auto-refresh {
            float: right;
        }
        .status {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Template 4 Debug Log</h1>
            <p>Real-time debugging for Template 4 cloning issues</p>
        </div>
        
        <div class="controls">
            <a href="?" class="btn">🔄 Refresh</a>
            <a href="?action=clear" class="btn danger" onclick="return confirm('Clear debug log?')">🗑️ Clear Log</a>
            <div class="auto-refresh">
                <label>
                    <input type="checkbox" id="autoRefresh"> Auto-refresh (5s)
                </label>
            </div>
        </div>
        
        <?php if (file_exists($debug_log_file)): ?>
            <div class="file-info">
                <strong>File:</strong> <?php echo $debug_log_file; ?> |
                <strong>Size:</strong> <?php echo number_format(filesize($debug_log_file)); ?> bytes |
                <strong>Modified:</strong> <?php echo date('Y-m-d H:i:s', filemtime($debug_log_file)); ?>
            </div>
            
            <div class="log-content">
                <pre><?php
                    $content = file_get_contents($debug_log_file);
                    if (empty($content)) {
                        echo '<div class="no-log">Debug log is empty. Try cloning Template 4 to generate debug data.</div>';
                    } else {
                        // Highlight different log levels
                        $content = preg_replace('/^.*\[ERROR\].*$/m', '<div class="error-line">$0</div>', $content);
                        $content = preg_replace('/^.*\[INFO\].*$/m', '<div class="info-line">$0</div>', $content);
                        $content = preg_replace('/^.*SUCCESS.*$/m', '<div class="success-line">$0</div>', $content);
                        echo $content;
                    }
                ?></pre>
            </div>
        <?php else: ?>
            <div class="no-log">
                <h3>Debug log file not found</h3>
                <p>The debug log will be created when you try to clone Template 4.</p>
                <p><strong>Expected location:</strong> <?php echo $debug_log_file; ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="status" id="status"></div>
    
    <script>
        let autoRefreshInterval;
        
        document.getElementById('autoRefresh').addEventListener('change', function() {
            if (this.checked) {
                autoRefreshInterval = setInterval(function() {
                    location.reload();
                }, 5000);
                showStatus('Auto-refresh enabled');
            } else {
                clearInterval(autoRefreshInterval);
                showStatus('Auto-refresh disabled');
            }
        });
        
        function showStatus(message) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.style.display = 'block';
            setTimeout(() => {
                status.style.display = 'none';
            }, 2000);
        }
        
        // Auto-scroll to bottom on load
        const logContent = document.querySelector('.log-content');
        if (logContent) {
            logContent.scrollTop = logContent.scrollHeight;
        }
    </script>
</body>
</html> 