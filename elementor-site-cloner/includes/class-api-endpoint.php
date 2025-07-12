<?php
/**
 * API Endpoint for Elementor Site Cloner
 * Allows external sites to trigger cloning via REST API
 */
class ESC_API_Endpoint {
    
    /**
     * API version
     */
    const API_VERSION = 'v1';
    
    /**
     * API namespace
     */
    const API_NAMESPACE = 'elementor-site-cloner';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
        
        // Add CORS headers to REST API responses
        add_action('rest_pre_serve_request', array($this, 'add_cors_headers'), 10, 3);
        
        // Handle preflight OPTIONS requests
        add_action('init', array($this, 'handle_preflight_request'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Clone endpoint
        register_rest_route(self::API_NAMESPACE . '/' . self::API_VERSION, '/clone', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_clone_request'),
            'permission_callback' => array($this, 'verify_api_key'),
            'args' => array(
                'template' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return !empty($param);
                    }
                ),
                'site_name' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return !empty($param);
                    }
                ),
                'site_path' => array(
                    'required' => false,
                    'validate_callback' => function($param) {
                        return true;
                    }
                ),
                'form_data' => array(
                    'required' => false,
                    'validate_callback' => function($param) {
                        return true;
                    }
                ),
            ),
            'show_in_index' => false,
        ));
        
        // Status endpoint  
        register_rest_route(self::API_NAMESPACE . '/' . self::API_VERSION, '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_status_request'),
            'permission_callback' => '__return_true',  // Allow public access for status checks
            'show_in_index' => false,
        ));
    }
    
    /**
     * Verify API key
     */
    public function verify_api_key($request) {
        error_log('ESC API: verify_api_key called');
        
        // TEMPORARY BYPASS FOR TESTING
        $bypass = get_site_option('esc_auth_bypass_test', 0);
        if ($bypass && (time() - $bypass < 300)) {
            error_log('ESC API: AUTH BYPASS ACTIVE - Allowing request');
            return true;
        }
        
        // Try different methods to get the API key
        $api_key = $request->get_header('X-API-Key');
        error_log('ESC API: Header X-API-Key: ' . ($api_key ? $api_key : 'EMPTY'));
        
        // Try lowercase
        if (empty($api_key)) {
            $api_key = $request->get_header('x-api-key');
            error_log('ESC API: Header x-api-key: ' . ($api_key ? $api_key : 'EMPTY'));
        }
        
        // Try from $_SERVER directly
        if (empty($api_key) && isset($_SERVER['HTTP_X_API_KEY'])) {
            $api_key = $_SERVER['HTTP_X_API_KEY'];
            error_log('ESC API: $_SERVER HTTP_X_API_KEY: ' . $api_key);
        }
        
        // Try from request params
        if (empty($api_key)) {
            $api_key = $request->get_param('api_key');
            error_log('ESC API: Param API Key: ' . ($api_key ? $api_key : 'EMPTY'));
        }
        
        // Debug all headers
        error_log('ESC API: All headers: ' . print_r($request->get_headers(), true));
        
        // Get the stored API key
        $stored_key = get_option('esc_api_key', 'esc_docket_2025_secure_key');
        error_log('ESC API: Stored API Key: ' . $stored_key);
        
        $result = $api_key === $stored_key;
        error_log('ESC API: Authentication result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        
        if (!$result) {
            error_log('ESC API: Key comparison - Received: "' . $api_key . '" vs Stored: "' . $stored_key . '"');
        }
        
        return $result;
    }
    
    /**
     * Handle clone request
     */
    public function handle_clone_request($request) {
        // Log the request
        error_log('ESC API: Clone request received');
        
        // Get parameters
        $template = sanitize_text_field($request->get_param('template'));
        $site_name = sanitize_text_field($request->get_param('site_name'));
        $site_path = sanitize_text_field($request->get_param('site_path'));
        $form_data = $request->get_param('form_data');
        
        // Validate template against whitelist
        $allowed_templates = $this->get_allowed_templates();
        if (!empty($allowed_templates) && !in_array($template, $allowed_templates)) {
            error_log('ESC API: Template not allowed - ' . $template);
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Template not allowed: ' . $template,
                'allowed_templates' => $allowed_templates,
            ), 403);
        }
        
        // Find template site ID
        $template_path = '/' . $template . '/';
        $template_site_id = null;
        
        $sites = get_sites(array('path' => $template_path));
        if (!empty($sites)) {
            $template_site_id = $sites[0]->blog_id;
        }
        
        if (!$template_site_id) {
            error_log('ESC API: Template not found - ' . $template);
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Template site not found: ' . $template,
            ), 404);
        }
        
        // Generate site URL if not provided
        if (empty($site_path)) {
            $site_number = $this->get_next_site_number();
            $site_path = 'docketsite' . $site_number;
        }
        
        $site_url = 'https://' . get_current_site()->domain . '/' . $site_path . '/';
        
        error_log('ESC API: Creating site - Name: ' . $site_name . ', URL: ' . $site_url . ', Template: ' . $template);
        
        // Build placeholder replacements from form data
        $placeholders = array();
        if (!empty($form_data)) {
            // Map form fields to placeholders
            if (!empty($form_data['business_name'])) {
                $placeholders['{{company}}'] = $form_data['business_name'];
            }
            if (!empty($form_data['business_email'])) {
                $placeholders['{{email}}'] = $form_data['business_email'];
            }
            if (!empty($form_data['business_phone']) || !empty($form_data['business_phone_number']) || !empty($form_data['phone_number'])) {
                // Handle different possible field names
                $phone = $form_data['business_phone'] ?? $form_data['business_phone_number'] ?? $form_data['phone_number'] ?? '';
                if ($phone) {
                    $placeholders['{{phone}}'] = $phone;
                }
            }
            if (!empty($form_data['business_address'])) {
                $placeholders['{{address}}'] = $form_data['business_address'];
            }
            if (!empty($form_data['business_city'])) {
                $placeholders['{{city}}'] = $form_data['business_city'];
            }
            if (!empty($form_data['business_state'])) {
                $placeholders['{{state}}'] = $form_data['business_state'];
            }
        }
        
        error_log('ESC API: Placeholder replacements: ' . print_r($placeholders, true));
        
        // Create clone manager instance
        $clone_manager = new ESC_Clone_Manager();
        
        // Clone the site with placeholders
        $result = $clone_manager->clone_site($template_site_id, $site_name, $site_url, $placeholders);
        
        if (is_wp_error($result)) {
            error_log('ESC API: Clone failed - ' . $result->get_error_message());
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $result->get_error_message(),
            ), 400);
        }
        
        // Store form data for future use (placeholder replacement, etc.)
        if (!empty($form_data) && !empty($result['site_id'])) {
            update_option('esc_site_' . $result['site_id'] . '_form_data', $form_data);
        }
        
        error_log('ESC API: Clone successful - Site ID: ' . $result['site_id']);
        
        // Return success response
        return new WP_REST_Response(array(
            'success' => true,
            'site_id' => $result['site_id'],
            'site_url' => $result['site_url'],
            'admin_url' => $result['admin_url'],
            'api_message' => 'Site cloned successfully',
        ), 200);
    }
    
    /**
     * Handle status request
     */
    public function handle_status_request($request) {
        $allowed_templates = $this->get_allowed_templates();
        
        return new WP_REST_Response(array(
            'status' => 'active',
            'plugin' => 'Elementor Site Cloner',
            'version' => ESC_VERSION,
            'api_version' => self::API_VERSION,
            'multisite' => is_multisite(),
            'site_url' => get_site_url(),
            'allowed_templates' => $allowed_templates,
        ), 200);
    }
    
    /**
     * Add CORS headers to REST API responses
     */
    public function add_cors_headers($served, $result, $request) {
        // Only add headers for our API endpoints
        $route = $request->get_route();
        if (strpos($route, '/' . self::API_NAMESPACE . '/') !== 0) {
            return $served;
        }
        
        // Get allowed origins from settings or use default
        $allowed_origins = get_option('esc_allowed_origins', array('https://yourdocketonline.com'));
        
        // Check if the request origin is allowed
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        if (in_array($origin, $allowed_origins) || in_array('*', $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, X-API-Key, Authorization');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // 24 hours
        }
        
        return $served;
    }
    
    /**
     * Handle preflight OPTIONS requests
     */
    public function handle_preflight_request() {
        // Only handle if it's an OPTIONS request to our API endpoint
        if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
            return;
        }
        
        // Check if it's our API endpoint
        $request_uri = $_SERVER['REQUEST_URI'];
        if (strpos($request_uri, '/wp-json/' . self::API_NAMESPACE . '/') === false) {
            return;
        }
        
        // Get allowed origins from settings or use default
        $allowed_origins = get_option('esc_allowed_origins', array('https://yourdocketonline.com'));
        
        // Check if the request origin is allowed
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        if (in_array($origin, $allowed_origins) || in_array('*', $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, X-API-Key, Authorization');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // 24 hours
            header('Content-Length: 0');
            header('Content-Type: text/plain');
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Get next available site number
     */
    private function get_next_site_number() {
        $sites = get_sites(array(
            'path__like' => '/docketsite%',
            'number' => 1000,
        ));
        
        $highest_number = 0;
        foreach ($sites as $site) {
            if (preg_match('/\/docketsite(\d+)\//', $site->path, $matches)) {
                $number = intval($matches[1]);
                if ($number > $highest_number) {
                    $highest_number = $number;
                }
            }
        }
        
        return $highest_number + 1;
    }
    
    /**
     * Get allowed templates for API cloning
     */
    private function get_allowed_templates() {
        // Get from option, default to standard templates
        $allowed = get_option('esc_allowed_templates', array());
        
        // If empty, return default templates
        if (empty($allowed)) {
            return array('template1', 'template2', 'template3', 'template4', 'template5');
        }
        
        return $allowed;
    }
} 