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
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Clone site endpoint
        register_rest_route(self::API_NAMESPACE . '/' . self::API_VERSION, '/clone', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_clone_request'),
            'permission_callback' => array($this, 'verify_api_key'),
            'args' => array(
                'template' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Template identifier (e.g., template1, template2)',
                ),
                'site_name' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Name for the new site',
                ),
                'site_path' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Optional custom path for the new site',
                ),
                'form_data' => array(
                    'required' => false,
                    'type' => 'object',
                    'description' => 'Additional form data for future placeholder replacement',
                ),
            ),
        ));
        
        // Status check endpoint
        register_rest_route(self::API_NAMESPACE . '/' . self::API_VERSION, '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_status_request'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));
    }
    
    /**
     * Verify API key
     */
    public function verify_api_key($request) {
        $api_key = $request->get_header('X-API-Key');
        
        if (empty($api_key)) {
            $api_key = $request->get_param('api_key');
        }
        
        // Get the stored API key
        $stored_key = get_option('esc_api_key', 'esc_docket_2025_secure_key');
        
        return $api_key === $stored_key;
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
        
        // Create clone manager instance
        $clone_manager = new ESC_Clone_Manager();
        
        // Clone the site
        $result = $clone_manager->clone_site($template_site_id, $site_name, $site_url);
        
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