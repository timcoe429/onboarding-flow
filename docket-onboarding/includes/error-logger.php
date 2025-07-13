<?php
/**
 * Centralized Error Logger for Docket Onboarding
 * Provides better error visibility and debugging
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DocketErrorLogger {
    
    private static $log_file;
    private static $instance = null;
    
    public function __construct() {
        self::$log_file = WP_CONTENT_DIR . '/docket-errors.log';
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log an error with context
     */
    public static function log_error($message, $context = [], $level = 'ERROR') {
        $timestamp = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($backtrace[1]) ? $backtrace[1]['function'] : 'unknown';
        $file = isset($backtrace[0]) ? basename($backtrace[0]['file']) : 'unknown';
        $line = isset($backtrace[0]) ? $backtrace[0]['line'] : 'unknown';
        
        $log_entry = "[$timestamp] [$level] [$file:$line] [$caller] $message";
        
        if (!empty($context)) {
            $log_entry .= " | Context: " . json_encode($context);
        }
        
        $log_entry .= "\n";
        
        // Write to our custom log file
        file_put_contents(self::$log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Also log to WordPress error log for backup
        error_log("Docket: $message");
    }
    
    /**
     * Log form submission data
     */
    public static function log_form_submission($form_data, $form_type, $result = null) {
        $context = [
            'form_type' => $form_type,
            'business_name' => $form_data['business_name'] ?? 'Unknown',
            'template' => $form_data['website_template_selection'] ?? 'Unknown',
            'result' => $result
        ];
        
        self::log_error("Form submitted", $context, 'INFO');
    }
    
    /**
     * Log API call details
     */
    public static function log_api_call($url, $data, $response, $success = true) {
        $level = $success ? 'INFO' : 'ERROR';
        $message = $success ? "API call successful" : "API call failed";
        
        $context = [
            'url' => $url,
            'request_data' => $data,
            'response' => $response,
            'success' => $success
        ];
        
        self::log_error($message, $context, $level);
    }
    
    /**
     * Log Trello operations
     */
    public static function log_trello($message, $data = [], $success = true) {
        $level = $success ? 'INFO' : 'ERROR';
        $context = array_merge(['component' => 'trello'], $data);
        
        self::log_error($message, $context, $level);
    }
    
    /**
     * Log site cloning operations
     */
    public static function log_clone($message, $template = null, $site_id = null, $success = true) {
        $level = $success ? 'INFO' : 'ERROR';
        $context = [
            'component' => 'clone',
            'template' => $template,
            'site_id' => $site_id
        ];
        
        self::log_error($message, $context, $level);
    }
    
    /**
     * Get recent logs
     */
    public static function get_recent_logs($lines = 100) {
        if (!file_exists(self::$log_file)) {
            return [];
        }
        
        $file_lines = file(self::$log_file);
        return array_slice($file_lines, -$lines);
    }
    
    /**
     * Clear logs
     */
    public static function clear_logs() {
        if (file_exists(self::$log_file)) {
            file_put_contents(self::$log_file, '');
        }
    }
    
    /**
     * Get log file path
     */
    public static function get_log_file() {
        return self::$log_file;
    }
}

// Initialize the logger
DocketErrorLogger::getInstance();

// Convenience functions
function docket_log_error($message, $context = []) {
    DocketErrorLogger::log_error($message, $context, 'ERROR');
}

function docket_log_info($message, $context = []) {
    DocketErrorLogger::log_error($message, $context, 'INFO');
}

function docket_log_warning($message, $context = []) {
    DocketErrorLogger::log_error($message, $context, 'WARNING');
} 