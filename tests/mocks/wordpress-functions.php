<?php
/**
 * WordPress Function Mocks
 * Provides comprehensive mock implementations of WordPress functions for testing
 * 
 * This file mocks all WordPress functions used by the Docket Onboarding plugin
 * to enable local testing without a full WordPress installation.
 */

use Brain\Monkey\Functions;

// Mock WP_Error class for testing
if (!class_exists('WP_Error')) {
    class WP_Error {
        public $errors = [];
        public $error_data = [];
        
        public function __construct($code = '', $message = '', $data = '') {
            if (empty($code)) {
                return;
            }
            $this->errors[$code][] = $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }
        
        public function get_error_code() {
            $codes = array_keys($this->errors);
            return !empty($codes) ? $codes[0] : '';
        }
        
        public function get_error_message($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            if (isset($this->errors[$code])) {
                return $this->errors[$code][0];
            }
            return '';
        }
    }
}

// Global variables that WordPress uses
global $wpdb;

// Create a proper mock wpdb class that supports method calls
if (!class_exists('MockWPDB')) {
    class MockWPDB {
        public $prefix = 'wp_';
        public $last_error = '';
        public $last_query = '';
        public $insert_id = 0;
        
        public function get_charset_collate() {
            return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }
        
        public function get_var($query) {
            global $wp_test_db;
            if (!isset($wp_test_db)) {
                $wp_test_db = [];
            }
            // Simple mock - return 0 for table checks, or stored value
            if (strpos($query, 'SHOW TABLES') !== false) {
                return null; // Table doesn't exist by default
            }
            if (strpos($query, 'SELECT COUNT') !== false) {
                return 0; // No rows by default
            }
            // For form content queries, return default content
            if (strpos($query, 'docket_form_content') !== false && strpos($query, 'content_value') !== false) {
                return null; // Will use default from docket_get_form_content
            }
            return isset($wp_test_db[$query]) ? $wp_test_db[$query] : null;
        }
        
        public function get_row($query) {
            global $wp_test_db;
            if (!isset($wp_test_db)) {
                $wp_test_db = [];
            }
            return isset($wp_test_db[$query]) ? $wp_test_db[$query] : null;
        }
        
        public function get_results($query) {
            global $wp_test_db;
            if (!isset($wp_test_db)) {
                $wp_test_db = [];
            }
            return isset($wp_test_db[$query]) ? $wp_test_db[$query] : [];
        }
        
        public function insert($table, $data) {
            $this->insert_id = rand(1, 1000);
            return true;
        }
        
        public function update($table, $data, $where) {
            return true;
        }
        
        public function replace($table, $data) {
            $this->insert_id = rand(1, 1000);
            return true;
        }
        
        public function prepare($query, ...$args) {
            // Simple mock - just return the query
            return $query;
        }
    }
}

// Mock wpdb with more complete functionality
if (!isset($wpdb)) {
    $wpdb = new MockWPDB();
}

// Security & Nonce Functions
Functions\when('wp_verify_nonce')->alias(function($nonce, $action) {
    // In tests, accept 'test_nonce' or any nonce that matches the action
    return ($nonce === 'test_nonce' || !empty($nonce));
});
Functions\when('wp_create_nonce')->alias(function() {
    return 'test_nonce';
});

// AJAX Response Functions
Functions\when('wp_send_json_success')->alias(function($data = null) {
    // Capture output for testing
    global $wp_test_json_response;
    $wp_test_json_response = ['success' => true, 'data' => $data];
    return $wp_test_json_response;
});
Functions\when('wp_send_json_error')->alias(function($data = null) {
    global $wp_test_json_response;
    $wp_test_json_response = ['success' => false, 'data' => $data];
    return $wp_test_json_response;
});
Functions\when('wp_die')->justReturn();

// Sanitization Functions
Functions\when('sanitize_text_field')->alias(function($str) {
    return is_string($str) ? strip_tags($str) : $str;
});
Functions\when('wp_unslash')->alias(function($value) {
    return is_string($value) ? stripslashes($value) : $value;
});
Functions\when('esc_attr')->returnArg();
Functions\when('esc_url')->returnArg();
Functions\when('esc_html')->returnArg();
Functions\when('esc_js')->returnArg();
Functions\when('wp_kses_post')->alias(function($data) {
    return strip_tags($data, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>');
});

// URL & Path Functions
Functions\when('admin_url')->alias(function($path = '') {
    return 'http://localhost/wp-admin/' . ltrim($path, '/');
});
Functions\when('plugin_dir_path')->alias(function($file) {
    // Handle both absolute and relative paths
    if (strpos($file, DOCKET_ONBOARDING_PLUGIN_DIR) === 0) {
        return DOCKET_ONBOARDING_PLUGIN_DIR;
    }
    return dirname($file) . '/';
});
Functions\when('plugin_dir_url')->alias(function($file) {
    return DOCKET_ONBOARDING_PLUGIN_URL;
});

// Options API
Functions\when('get_option')->alias(function($option, $default = false) {
    global $wp_test_options;
    if (!isset($wp_test_options)) {
        $wp_test_options = [];
    }
    return isset($wp_test_options[$option]) ? $wp_test_options[$option] : $default;
});
Functions\when('update_option')->alias(function($option, $value) {
    global $wp_test_options;
    if (!isset($wp_test_options)) {
        $wp_test_options = [];
    }
    $wp_test_options[$option] = $value;
    return true;
});
Functions\when('delete_option')->alias(function($option) {
    global $wp_test_options;
    if (isset($wp_test_options[$option])) {
        unset($wp_test_options[$option]);
    }
    return true;
});

// Hooks & Filters
Functions\when('add_action')->justReturn();
Functions\when('add_filter')->justReturn();
Functions\when('add_shortcode')->justReturn();
Functions\when('remove_action')->justReturn();
Functions\when('remove_filter')->justReturn();
Functions\when('do_action')->justReturn();
Functions\when('apply_filters')->alias(function($filter, $value) {
    // In tests, filters just pass through
    return $value;
});
Functions\when('did_action')->alias(function($action) {
    global $wp_test_actions;
    if (!isset($wp_test_actions)) {
        $wp_test_actions = [];
    }
    return isset($wp_test_actions[$action]) && $wp_test_actions[$action] > 0;
});

// Shortcodes
Functions\when('do_shortcode')->alias(function($content) {
    // Mock - just return content as-is
    return $content;
});

// User & Capabilities
Functions\when('is_admin')->alias(function() {
    return false;
});
Functions\when('current_user_can')->alias(function($capability) {
    // In tests, allow all capabilities by default
    return true;
});

// HTTP Functions
Functions\when('wp_remote_post')->alias(function($url, $args = []) {
    global $wp_test_http_responses;
    if (!isset($wp_test_http_responses)) {
        $wp_test_http_responses = [];
    }
    
    // Check if we have a custom response for this URL
    if (isset($wp_test_http_responses[$url])) {
        return $wp_test_http_responses[$url];
    }
    
    // Default success response
    return [
        'body' => json_encode([
            'success' => true,
            'data' => [
                'site_id' => 123,
                'site_url' => 'http://test-site.local',
                'admin_url' => 'http://test-site.local/wp-admin'
            ]
        ]),
        'response' => ['code' => 200, 'message' => 'OK'],
        'headers' => ['content-type' => 'application/json']
    ];
});
Functions\when('wp_remote_get')->alias(function($url, $args = []) {
    global $wp_test_http_responses;
    if (!isset($wp_test_http_responses)) {
        $wp_test_http_responses = [];
    }
    
    if (isset($wp_test_http_responses[$url])) {
        return $wp_test_http_responses[$url];
    }
    
    return [
        'body' => json_encode(['success' => true]),
        'response' => ['code' => 200],
        'headers' => []
    ];
});
Functions\when('wp_remote_retrieve_body')->alias(function($response) {
    if (is_wp_error($response)) {
        return '';
    }
    return is_array($response) && isset($response['body']) ? $response['body'] : '';
});
Functions\when('wp_remote_retrieve_response_code')->alias(function($response) {
    if (is_wp_error($response)) {
        return 0;
    }
    return is_array($response) && isset($response['response']['code']) ? $response['response']['code'] : 200;
});
Functions\when('is_wp_error')->alias(function($thing) {
    return $thing instanceof \WP_Error;
});

// File Functions
Functions\when('file_put_contents')->alias(function($file, $data, $flags = 0) {
    // Ensure directory exists before writing
    $dir = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    // In tests, track what would be written AND actually write it (for error logging)
    global $wp_test_file_writes;
    if (!isset($wp_test_file_writes)) {
        $wp_test_file_writes = [];
    }
    $wp_test_file_writes[$file] = $data;
    // Actually write the file (needed for error logger)
    return \file_put_contents($file, $data, $flags);
});
Functions\when('file_exists')->alias(function($file) {
    // Mock - return true for common WordPress files and step files
    if (strpos($file, 'wp-admin') !== false || strpos($file, 'wp-content') !== false) {
        return true;
    }
    // Return true for step files (they're included in the form renderer)
    if (strpos($file, 'step-') !== false && strpos($file, '.php') !== false) {
        return true;
    }
    // Return true for plugin files
    if (strpos($file, DOCKET_ONBOARDING_PLUGIN_DIR) === 0) {
        return true;
    }
    return false;
});

// Upload Functions
Functions\when('wp_handle_upload')->alias(function($file, $overrides = []) {
    if (!is_array($file) || !isset($file['name'])) {
        return ['error' => 'Invalid file'];
    }
    
    return [
        'file' => '/tmp/test-' . basename($file['name']),
        'url' => 'http://localhost/wp-content/uploads/test-' . basename($file['name']),
        'type' => isset($file['type']) ? $file['type'] : 'image/jpeg',
        'error' => false
    ];
});

// Script & Style Functions
Functions\when('wp_enqueue_style')->justReturn();
Functions\when('wp_enqueue_script')->justReturn();
Functions\when('wp_localize_script')->justReturn();
Functions\when('wp_register_script')->justReturn();
Functions\when('wp_register_style')->justReturn();

// Shortcode Functions
Functions\when('shortcode_atts')->alias(function($pairs, $atts) {
    return array_merge($pairs, array_intersect_key($atts, $pairs));
});

// Time Functions
Functions\when('current_time')->alias(function($type = 'mysql') {
    return date('Y-m-d H:i:s');
});

// Class & Function Checks
// Note: These return defaults without calling themselves to avoid infinite recursion
Functions\when('class_exists')->alias(function($class) {
    // Return false by default - classes don't exist unless explicitly needed in tests
    return false;
});
Functions\when('function_exists')->alias(function($function) {
    // Return true for WordPress functions we've mocked, false for others
    // This prevents infinite recursion while still allowing function checks
    $mocked_functions = [
        'wp_create_nonce', 'wp_verify_nonce', 'wp_nonce_field',
        'wp_kses_post', 'apply_filters', 'do_action',
        'wp_send_json', 'wp_send_json_error', 'wp_send_json_success',
        'admin_url', 'wp_upload_bits', 'wp_handle_upload', 'is_wp_error',
        'current_time', 'file_exists', 'file_put_contents'
    ];
    return in_array($function, $mocked_functions, true);
});

// Database Functions
Functions\when('dbDelta')->alias(function($sql) {
    // Mock dbDelta - just return success
    return true;
});

// Register Activation Hook (for testing)
Functions\when('register_activation_hook')->justReturn();
Functions\when('register_deactivation_hook')->justReturn();

// Initialize test storage globals
if (!isset($GLOBALS['wp_test_options'])) {
    $GLOBALS['wp_test_options'] = [];
}
if (!isset($GLOBALS['wp_test_http_responses'])) {
    $GLOBALS['wp_test_http_responses'] = [];
}
if (!isset($GLOBALS['wp_test_file_writes'])) {
    $GLOBALS['wp_test_file_writes'] = [];
}
if (!isset($GLOBALS['wp_test_actions'])) {
    $GLOBALS['wp_test_actions'] = [];
}
if (!isset($GLOBALS['wp_test_json_response'])) {
    $GLOBALS['wp_test_json_response'] = null;
}

