<?php
/**
 * Debug Utility for Elementor Site Cloner - MAIN DIRECTORY VERSION
 * Provides detailed debugging for Template 4 cloning issues
 * 
 * This file is in the main plugin directory for easy access and transfer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ESC_Debug_Utility {
    
    private static $debug_log = '';
    
    public function __construct() {
        self::$debug_log = WP_CONTENT_DIR . '/esc-template4-debug.log';
    }
    
    /**
     * Log debug information with timestamp and context
     */
    public static function log($message, $context = [], $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $memory = round(memory_get_usage() / 1024 / 1024, 2) . 'MB';
        $peak_memory = round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB';
        
        $log_entry = "[$timestamp] [$level] [Memory: $memory / Peak: $peak_memory] $message";
        
        if (!empty($context)) {
            $log_entry .= " | Context: " . json_encode($context);
        }
        
        $log_entry .= "\n";
        
        // Write to debug log
        file_put_contents(self::$debug_log, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Debug Template 4 specific data
     */
    public static function debug_template4_data($template_site_id) {
        global $wpdb;
        
        self::log("=== TEMPLATE 4 DEBUG START ===", ['template_site_id' => $template_site_id]);
        
        // Check basic site info
        $site = get_site($template_site_id);
        self::log("Template 4 site info", [
            'domain' => $site->domain,
            'path' => $site->path,
            'registered' => $site->registered,
            'last_updated' => $site->last_updated
        ]);
        
        // Switch to Template 4 to gather data
        switch_to_blog($template_site_id);
        
        // Check database tables
        $prefix = $wpdb->get_blog_prefix($template_site_id);
        $tables = $wpdb->get_results("SHOW TABLES LIKE '{$prefix}%'");
        self::log("Template 4 database tables", ['table_count' => count($tables)]);
        
        // Check for large tables that might cause issues
        foreach ($tables as $table) {
            $table_name = array_values((array)$table)[0];
            $row_count = $wpdb->get_var("SELECT COUNT(*) FROM `$table_name`");
            $table_size = $wpdb->get_var("SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'MB' FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '$table_name'");
            
            if ($row_count > 1000 || $table_size > 10) {
                self::log("Large table detected", [
                    'table' => $table_name,
                    'rows' => $row_count,
                    'size_mb' => $table_size
                ]);
            }
        }
        
        // Check posts
        $post_count = wp_count_posts();
        self::log("Template 4 posts", [
            'publish' => $post_count->publish,
            'draft' => $post_count->draft,
            'private' => $post_count->private
        ]);
        
        // Check for problematic post content
        $large_posts = $wpdb->get_results("
            SELECT ID, post_title, CHAR_LENGTH(post_content) as content_length 
            FROM {$prefix}posts 
            WHERE CHAR_LENGTH(post_content) > 100000 
            ORDER BY content_length DESC 
            LIMIT 5
        ");
        
        if (!empty($large_posts)) {
            self::log("Large posts detected", ['posts' => $large_posts]);
        }
        
        // Check Elementor data
        $elementor_posts = $wpdb->get_results("
            SELECT p.ID, p.post_title, CHAR_LENGTH(pm.meta_value) as data_length
            FROM {$prefix}posts p
            JOIN {$prefix}postmeta pm ON p.ID = pm.post_id
            WHERE pm.meta_key = '_elementor_data'
            ORDER BY data_length DESC
            LIMIT 5
        ");
        
        if (!empty($elementor_posts)) {
            self::log("Elementor data found", ['elementor_posts' => count($elementor_posts)]);
            foreach ($elementor_posts as $post) {
                if ($post->data_length > 50000) {
                    self::log("Large Elementor data", [
                        'post_id' => $post->ID,
                        'title' => $post->post_title,
                        'data_size' => $post->data_length
                    ]);
                }
            }
        }
        
        // Check upload directory size
        $upload_dir = wp_upload_dir();
        if (is_dir($upload_dir['basedir'])) {
            $size = self::get_directory_size($upload_dir['basedir']);
            self::log("Upload directory", [
                'path' => $upload_dir['basedir'],
                'size_mb' => round($size / 1024 / 1024, 2)
            ]);
        }
        
        // Check for corrupted options
        $options_count = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}options");
        self::log("Options table", ['option_count' => $options_count]);
        
        // Check for problematic serialized data
        $bad_serialized = $wpdb->get_results("
            SELECT option_name, CHAR_LENGTH(option_value) as value_length
            FROM {$prefix}options 
            WHERE option_value LIKE 'a:%' 
            AND CHAR_LENGTH(option_value) > 10000
            ORDER BY value_length DESC
            LIMIT 5
        ");
        
        if (!empty($bad_serialized)) {
            self::log("Large serialized options", ['options' => $bad_serialized]);
        }
        
        restore_current_blog();
        
        self::log("=== TEMPLATE 4 DEBUG END ===");
    }
    
    /**
     * Get directory size recursively
     */
    private static function get_directory_size($directory) {
        $size = 0;
        if (is_dir($directory)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        return $size;
    }
    
    /**
     * Monitor clone process steps
     */
    public static function log_clone_step($step, $template_id, $status = 'started', $details = []) {
        $context = array_merge([
            'step' => $step,
            'template_id' => $template_id,
            'status' => $status
        ], $details);
        
        self::log("Clone step: $step ($status)", $context);
        
        // Log memory usage for each step
        if ($status === 'completed') {
            $memory_usage = memory_get_usage();
            $peak_memory = memory_get_peak_usage();
            self::log("Memory after $step", [
                'current_mb' => round($memory_usage / 1024 / 1024, 2),
                'peak_mb' => round($peak_memory / 1024 / 1024, 2)
            ]);
        }
    }
    
    /**
     * Clear debug log
     */
    public static function clear_log() {
        if (file_exists(self::$debug_log)) {
            file_put_contents(self::$debug_log, '');
        }
    }
    
    /**
     * Get debug log contents
     */
    public static function get_log() {
        if (file_exists(self::$debug_log)) {
            return file_get_contents(self::$debug_log);
        }
        return '';
    }
}

// Initialize the debug utility
new ESC_Debug_Utility(); 