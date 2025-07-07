<?php
/**
 * Direct Clone Endpoint - Bypasses REST API
 * Access via: https://dockethosting5.com/wp-content/plugins/elementor-site-cloner/clone-endpoint.php
 */

// Load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    die(json_encode(['success' => false, 'message' => 'WordPress not found']));
}
require_once($wp_load_path);

// Check if multisite
if (!is_multisite()) {
    die(json_encode(['success' => false, 'message' => 'Not a multisite installation']));
}

// Handle CORS
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Simple API key check
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? $input['api_key'] ?? '';
$stored_key = get_option('esc_api_key', 'esc_docket_2025_secure_key');

if ($api_key !== $stored_key) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Invalid API key']));
}

// Get parameters
$template = sanitize_text_field($input['template'] ?? '');
$site_name = sanitize_text_field($input['site_name'] ?? '');
$form_data = $input['form_data'] ?? [];

if (empty($template) || empty($site_name)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Missing required parameters']));
}

// Load clone manager
require_once(dirname(__FILE__) . '/includes/class-clone-manager.php');
$clone_manager = new ESC_Clone_Manager();

// Find template site
$template_path = '/' . $template . '/';
$sites = get_sites(['path' => $template_path]);
if (empty($sites)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'Template not found: ' . $template]));
}

$template_site_id = $sites[0]->blog_id;

// Generate site URL
$site_number = time();
$site_path = 'docketsite' . $site_number;
$site_url = 'https://' . get_current_site()->domain . '/' . $site_path . '/';

// Clone the site
$result = $clone_manager->clone_site($template_site_id, $site_name, $site_url);

if (is_wp_error($result)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => $result->get_error_message()]));
}

// Return success
echo json_encode([
    'success' => true,
    'site_id' => $result['site_id'],
    'site_url' => $result['site_url'],
    'admin_url' => $result['admin_url']
]); 