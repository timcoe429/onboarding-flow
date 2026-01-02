# WordPress Debug Log Rotation Setup

## Problem
The WordPress `debug.log` file can grow very large (99MB+) if not managed, making it difficult to debug issues.

## Solution: Add Log Rotation to wp-config.php

Add this code to your `wp-config.php` file **before** the line that says `/* That's all, stop editing! Happy publishing. */`

### For Live Server

1. Access your WordPress installation via FTP/SFTP or hosting file manager
2. Edit `wp-config.php`
3. Add this code before the "That's all, stop editing!" comment:

```php
// Rotate debug.log when it reaches 10MB
if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    $debug_log = WP_CONTENT_DIR . '/debug.log';
    if (file_exists($debug_log) && filesize($debug_log) > 10485760) { // 10MB in bytes
        $archived_log = WP_CONTENT_DIR . '/debug-' . date('Y-m-d-His') . '.log';
        rename($debug_log, $archived_log);
        error_log('Debug log rotated: ' . basename($archived_log));
    }
}
```

### For Docker Local Development

If you're using Docker, you can add this to `wp-config.php` inside the container:

1. Access the container: `docker-compose exec wordpress bash`
2. Edit wp-config.php: `nano /var/www/html/wp-config.php`
3. Add the same code above

## How It Works

- Checks if debug logging is enabled
- Checks if `debug.log` exists and is larger than 10MB
- Renames the current log to `debug-YYYY-MM-DD-HHMMSS.log`
- WordPress automatically creates a new `debug.log` file
- Old logs are preserved with timestamps for reference

## Optional: Auto-delete Old Logs

If you want to automatically delete logs older than 30 days, add this after the rotation code:

```php
// Delete debug logs older than 30 days
if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    $log_dir = WP_CONTENT_DIR;
    $files = glob($log_dir . '/debug-*.log');
    $now = time();
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file)) > (30 * 24 * 60 * 60)) {
            unlink($file);
        }
    }
}
```

## Testing

After adding the code:
1. Create a large test log entry
2. Check that when `debug.log` exceeds 10MB, it gets rotated
3. Verify a new `debug.log` is created automatically

