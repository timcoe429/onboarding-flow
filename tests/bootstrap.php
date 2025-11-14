<?php
/**
 * Test Bootstrap
 * Sets up WordPress function mocks for unit testing
 * 
 * This file initializes the test environment by:
 * 1. Defining WordPress constants
 * 2. Loading Brain Monkey for function mocking
 * 3. Loading WordPress function mocks
 * 4. Loading the plugin code
 */

// Prevent direct access
if (php_sapi_name() !== 'cli') {
    die('Tests can only be run from the command line.');
}

// Define WordPress constants if not already defined
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__ . '/../wordpress/wp-content/');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', __DIR__ . '/../wordpress/wp-content/plugins/');
}

if (!defined('WP_PLUGIN_URL')) {
    define('WP_PLUGIN_URL', 'http://localhost/wp-content/plugins/');
}

if (!defined('DOCKET_ONBOARDING_VERSION')) {
    define('DOCKET_ONBOARDING_VERSION', '1.0.9');
}

if (!defined('DOCKET_ONBOARDING_PLUGIN_DIR')) {
    define('DOCKET_ONBOARDING_PLUGIN_DIR', __DIR__ . '/../docket-onboarding/');
}

if (!defined('DOCKET_ONBOARDING_PLUGIN_URL')) {
    define('DOCKET_ONBOARDING_PLUGIN_URL', 'http://localhost/wp-content/plugins/docket-onboarding/');
}

// Load Composer autoloader
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    die("ERROR: Composer dependencies not installed. Run 'composer install' first.\n");
}
require_once $autoloader;

// Initialize Brain Monkey for WordPress function mocking
Brain\Monkey\setUp();

// Load WordPress function mocks BEFORE loading plugin
// This ensures all WordPress functions are mocked when plugin code runs
require_once __DIR__ . '/mocks/wordpress-functions.php';

// Suppress output during plugin loading (plugins may echo/print)
ob_start();

// Load the plugin files
// We load files individually to have better control
$plugin_dir = DOCKET_ONBOARDING_PLUGIN_DIR;

// Load core plugin file
require_once $plugin_dir . 'docket-onboarding.php';

// Load includes that are required for testing
require_once $plugin_dir . 'includes/error-logger.php';
require_once $plugin_dir . 'includes/form-content-helpers.php';
require_once $plugin_dir . 'includes/class-form-content-manager.php';
require_once $plugin_dir . 'includes/form-handler.php';
require_once $plugin_dir . 'includes/shortcode.php';
require_once $plugin_dir . 'includes/cloner-settings.php';
require_once $plugin_dir . 'includes/trello-sync.php';

// Clear any output from plugin loading
ob_end_clean();

// Reset test globals
global $wp_test_options, $wp_test_http_responses, $wp_test_file_writes;
$wp_test_options = [];
$wp_test_http_responses = [];
$wp_test_file_writes = [];

