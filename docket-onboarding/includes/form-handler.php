<?php
/**
 * Form Handler
 * Handles all form-related functionality including AJAX loading and submission
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include form configuration
require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/form-config.php';

// Include unified form renderer
require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/unified-form-renderer.php';

/**
 * Unified AJAX handler to load any form type
 * 
 * @param string $form_type Form type (fast-build, standard-build, website-vip)
 */
function docket_ajax_load_form($form_type = null) {
    // Clear any previous output to prevent headers already sent errors
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
        return; // Explicit return to prevent further execution in tests
    }
    
    // Determine form type from POST or parameter
    if (!$form_type) {
        // Try to determine from action name
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        if (strpos($action, 'fast_build') !== false) {
            $form_type = 'fast-build';
        } elseif (strpos($action, 'standard_build') !== false) {
            $form_type = 'standard-build';
        } elseif (strpos($action, 'website_vip') !== false || strpos($action, 'vip') !== false) {
            $form_type = 'website-vip';
        } else {
            // Try to get from POST data
            $form_type = isset($_POST['form_type']) ? sanitize_text_field($_POST['form_type']) : 'fast-build';
        }
    }
    
    // Validate form type
    $config = docket_get_form_config_by_type($form_type);
    if (!$config) {
        error_log('Docket Onboarding: Form type "' . $form_type . '" not found in config. Available types: ' . implode(', ', array_keys(docket_get_form_config())));
        wp_send_json_error(array('message' => 'Invalid form type: ' . $form_type));
        wp_die();
        return; // Explicit return to prevent further execution in tests
    }
    
    try {
        // Get form data
        $form_data = array(
            'plan' => isset($_POST['plan']) ? sanitize_text_field($_POST['plan']) : '',
            'management' => isset($_POST['management']) ? sanitize_text_field($_POST['management']) : '',
            'buildType' => isset($_POST['buildType']) ? sanitize_text_field($_POST['buildType']) : ''
        );
        
        // Special handling for website-vip
        if ($form_type === 'website-vip' && !isset($_POST['management'])) {
            $form_data['management'] = 'vip';
        }
        
        // Start output buffering
        ob_start();
        
        // Add unified CSS link with unique cache-busting version
        $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'assets/docket-forms-unified.css?ver=' . time();
        echo '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
        
        // Add unified JavaScript link with unique cache-busting version
        $js_url = DOCKET_ONBOARDING_PLUGIN_URL . 'assets/docket-form-unified.js?ver=' . time();
        echo '<script src="' . esc_url($js_url) . '"></script>';
        
        // Localize script with AJAX URL - Create docket_ajax object for form submission
        echo '<script>
            window.docket_ajax = {
                ajax_url: "' . admin_url('admin-ajax.php') . '",
                nonce: "' . wp_create_nonce('docket_onboarding_nonce') . '"
            };
        </script>';
        
        // Render the form using unified renderer
        if (function_exists('docket_render_form')) {
            docket_render_form($form_type, $form_data);
        } else {
            throw new Exception('Form rendering function not found');
        }
        
        // Get the output
        $form_html = ob_get_clean();
        
        if (empty($form_html)) {
            throw new Exception('Form HTML is empty');
        }
        
        // Apply filter for additional modifications
        $filter_name = 'docket_' . str_replace('-', '_', $form_type) . '_form_response';
        $form_html = apply_filters($filter_name, $form_html);
        
        // For website-vip, return separate CSS/JS URLs (legacy behavior)
        if ($form_type === 'website-vip') {
            wp_send_json_success(array(
                'form_html' => $form_html,
                'css_url' => $css_url,
                'js_url' => $js_url,
                'ajax_url' => admin_url('admin-ajax.php'),
                'message' => 'Form loaded successfully'
            ));
        } else {
            wp_send_json_success(array(
                'form_html' => $form_html,
                'message' => 'Form loaded successfully'
            ));
        }
        
    } catch (Exception $e) {
        // Clean any output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Log the error
        error_log('Docket Onboarding: ' . ucfirst($form_type) . ' form load error - ' . $e->getMessage());
        
        wp_send_json_error(array(
            'message' => 'Failed to load form: ' . $e->getMessage()
        ));
    }
    
    wp_die();
}

/**
 * Handle AJAX request to load fast build form
 * @deprecated Use docket_ajax_load_form('fast-build') instead
 */
add_action('wp_ajax_docket_load_fast_build_form', 'docket_ajax_load_fast_build_form');
add_action('wp_ajax_nopriv_docket_load_fast_build_form', 'docket_ajax_load_fast_build_form');

function docket_ajax_load_fast_build_form() {
    // Wrapper for backward compatibility
    return docket_ajax_load_form('fast-build');
}

/**
 * Handle AJAX request to load standard build form
 * @deprecated Use docket_ajax_load_form('standard-build') instead
 */
add_action('wp_ajax_docket_load_standard_build_form', 'docket_ajax_load_standard_build_form');
add_action('wp_ajax_nopriv_docket_load_standard_build_form', 'docket_ajax_load_standard_build_form');

function docket_ajax_load_standard_build_form() {
    // Wrapper for backward compatibility
    return docket_ajax_load_form('standard-build');
}

/**
 * Handle AJAX request to load Website VIP form
 * @deprecated Use docket_ajax_load_form('website-vip') instead
 */
add_action('wp_ajax_docket_load_website_vip_form', 'docket_ajax_load_website_vip_form');
add_action('wp_ajax_nopriv_docket_load_website_vip_form', 'docket_ajax_load_website_vip_form');

function docket_ajax_load_website_vip_form() {
    // Wrapper for backward compatibility
    error_log('Docket Onboarding: docket_ajax_load_website_vip_form called');
    return docket_ajax_load_form('website-vip');
}

/**
 * Handle AJAX request to load Avada form (keeping for backward compatibility)
 */
add_action('wp_ajax_docket_load_avada_form', 'docket_load_avada_form');
add_action('wp_ajax_nopriv_docket_load_avada_form', 'docket_load_avada_form');

function docket_load_avada_form() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
        return; // Explicit return to prevent further execution in tests
    }
    
    // Get form ID
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    
    if (!$form_id) {
        wp_send_json_error(array('message' => 'Invalid form ID'));
        wp_die();
    }
    
    // Fetch the Avada form shortcode
    $form_html = do_shortcode('[fusion_form form_post_id="' . $form_id . '" /]');
    
    if (empty($form_html)) {
        wp_send_json_error(array('message' => 'Form not found'));
        wp_die();
    }
    
    wp_send_json_success(array(
        'form_html' => $form_html,
        'message' => 'Form loaded successfully'
    ));
    
    wp_die();
}

/**
 * Handle generic form submission (backward compatibility)
 */
add_action('wp_ajax_docket_submit_onboarding', 'docket_handle_form_submission');
add_action('wp_ajax_nopriv_docket_submit_onboarding', 'docket_handle_form_submission');

function docket_handle_form_submission() {
    return docket_handle_any_form_submission('generic');
}

/**
 * Handle Fast Build form submission
 */
add_action('wp_ajax_docket_submit_fast_build_form', 'docket_handle_fast_build_submission');
add_action('wp_ajax_nopriv_docket_submit_fast_build_form', 'docket_handle_fast_build_submission');

function docket_handle_fast_build_submission() {
    return docket_handle_any_form_submission('fast_build');
}

/**
 * Handle Standard Build form submission
 */
add_action('wp_ajax_docket_submit_standard_build_form', 'docket_handle_standard_build_submission');
add_action('wp_ajax_nopriv_docket_submit_standard_build_form', 'docket_handle_standard_build_submission');

function docket_handle_standard_build_submission() {
    return docket_handle_any_form_submission('standard_build');
}

/**
 * Handle Website VIP form submission
 */
add_action('wp_ajax_docket_submit_website_vip_form', 'docket_handle_website_vip_submission');
add_action('wp_ajax_nopriv_docket_submit_website_vip_form', 'docket_handle_website_vip_submission');

function docket_handle_website_vip_submission() {
    return docket_handle_any_form_submission('website_vip');
}

/**
 * Unified form submission handler that creates sites using Elementor Site Cloner
 */
function docket_handle_any_form_submission($form_type = 'generic') {
    // Verify nonce - check for different nonce field names
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : 
             (isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : 
             (isset($_POST['docket_nonce']) ? $_POST['docket_nonce'] : ''));
    
    if (!$nonce || !wp_verify_nonce($nonce, 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
        return; // Explicit return to prevent further execution in tests
    }
    
    // Process the form data
    $form_data = array();
    $form_data['form_type'] = $form_type;
    
    // Sanitize all form fields
    foreach ($_POST as $key => $value) {
        if (!in_array($key, array('nonce', '_wpnonce', 'docket_nonce', 'action'))) {
            if (is_array($value)) {
                $form_data[$key] = array_map(function($v) { return wp_unslash(sanitize_text_field($v)); }, $value);
            } else {
                $form_data[$key] = wp_unslash(sanitize_text_field($value));
            }
        }
    }
    
    // Process file uploads
    $uploaded_files = array();
    if (!empty($_FILES)) {
        docket_log_info("Processing file uploads", [
            'files_count' => count($_FILES),
            'files' => array_keys($_FILES)
        ]);
        
        foreach ($_FILES as $field_name => $file_data) {
            if (is_array($file_data['name'])) {
                // Multiple files
                $file_urls = array();
                for ($i = 0; $i < count($file_data['name']); $i++) {
                    if ($file_data['error'][$i] === UPLOAD_ERR_OK) {
                        $upload_result = docket_handle_file_upload($file_data, $i);
                        if ($upload_result) {
                            $file_urls[] = $upload_result;
                        }
                    }
                }
                if (!empty($file_urls)) {
                    $uploaded_files[$field_name] = $file_urls;
                    $form_data[$field_name . '_urls'] = $file_urls;
                }
            } else {
                // Single file
                if ($file_data['error'] === UPLOAD_ERR_OK) {
                    $upload_result = docket_handle_file_upload($file_data);
                    if ($upload_result) {
                        $uploaded_files[$field_name] = $upload_result;
                        $form_data[$field_name . '_url'] = $upload_result;
                    }
                }
            }
        }
        
        if (!empty($uploaded_files)) {
            docket_log_info("Files uploaded successfully", [
                'uploaded_files' => $uploaded_files
            ]);
        }
    }
    
    // Log form submission
    docket_log_info("Form submission started", [
        'form_type' => $form_type,
        'business_name' => $form_data['business_name'] ?? 'Unknown',
        'template' => $form_data['website_template_selection'] ?? 'Unknown',
        'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ]);
    
    // Save form submission for reference
    $submission_id = time() . '_' . rand(1000, 9999);
    update_option('docket_submission_' . $submission_id, $form_data);
    
    // Check if API calls are disabled (for debugging)
    if (get_option('docket_disable_api_calls', false)) {
        error_log('Docket Onboarding: API calls disabled, returning success without site creation');
        
        // Create client portal entry if available
        if (class_exists('DocketClientPortal')) {
            global $docket_client_portal;
            if ($docket_client_portal) {
                $portal_url = $docket_client_portal->create_client_project($form_data, $form_type);
                error_log('Docket Onboarding: Client portal created at ' . $portal_url);
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Form submitted successfully (API calls disabled)',
            'submission_id' => $submission_id,
            'debug_mode' => true
        ));
        wp_die();
        return; // Explicit return to prevent further execution in tests
    }
    
    // Get configuration for remote API
    $api_url = get_option('docket_cloner_api_url', 'https://dockethosting5.com');
    $api_key = get_option('docket_cloner_api_key', 'esc_docket_2025_secure_key');
    
    // Get the template selection (default to template1 if not specified)
    $selected_template = isset($form_data['website_template_selection']) ? $form_data['website_template_selection'] : 'template1';
    
    // Get the business name from the form
    $site_name = !empty($form_data['business_name']) ? $form_data['business_name'] : 'Docket Site ' . time();
    
    // Make API call to Elementor Site Cloner on dockethosting5.com
    docket_log_info("Making API call to remote cloner", [
        'api_url' => $api_url,
        'form_type' => $form_type,
        'template' => $selected_template,
        'site_name' => $site_name
    ]);
    
    // Prepare API request data
    $api_data = array(
        'template' => $selected_template,
        'site_name' => $site_name,
        'form_data' => $form_data,
        'api_key' => $api_key  // Add API key to body
    );
    
    // Add error logging for debugging
    error_log('Docket Onboarding: API Request Data: ' . json_encode($api_data));
    
    // Make the API request with better error handling
    try {
        // Use WordPress AJAX endpoint which is always accessible
        $response = wp_remote_post($api_url . '/wp-admin/admin-ajax.php', array(
        'timeout' => 60,
            'body' => array_merge($api_data, array(
                'action' => 'esc_clone_site',
                'api_key' => $api_key
            )),
            'sslverify' => false // Temporarily disable SSL verification for debugging
        ));
    } catch (Exception $e) {
        error_log('Docket Onboarding: Exception during API call - ' . $e->getMessage());
        wp_send_json_error(array(
            'message' => 'Error connecting to site creation service: ' . $e->getMessage()
    ));
        wp_die();
    }
    
    // Check for errors
    if (is_wp_error($response)) {
        docket_log_error("API request failed", [
            'error' => $response->get_error_message(),
            'template' => $selected_template,
            'api_url' => $api_url
        ]);
        wp_send_json_error(array(
            'message' => 'Failed to connect to site creation service: ' . $response->get_error_message()
        ));
        wp_die();
        return; // Explicit return to prevent further execution in tests
    }
    
    // Parse the response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data) || !isset($data['success'])) {
        $response_code = wp_remote_retrieve_response_code($response);
        
        docket_log_error("Invalid API response received", [
            'response_body' => $body,
            'response_code' => $response_code,
            'template' => $selected_template,
            'api_url' => $api_url,
            'is_html_response' => strpos($body, '<html') !== false,
            'contains_critical_error' => strpos($body, 'critical error') !== false
        ]);
        
        // Check if it's an HTML error page
        if (strpos($body, '<html') !== false || strpos($body, 'Access denied') !== false) {
            if (strpos($body, 'critical error') !== false) {
                docket_log_error("Template 4 causing critical error on remote server", [
                    'template' => $selected_template,
                    'business_name' => $form_data['business_name']
                ]);
                wp_send_json_error(array(
                    'message' => 'Template 4 is currently experiencing issues. Please try Template 1, 2, or 3 instead.'
                ));
            } else {
                wp_send_json_error(array(
                    'message' => 'Site creation blocked by security settings. Please check server configuration.'
                ));
            }
            wp_die();
        }
        
        wp_send_json_error(array(
            'message' => 'Invalid response from site creation service'
        ));
        wp_die();
    }
    
    if (!$data || !isset($data['success']) || !$data['success']) {
        docket_log_error("Site creation failed", [
            'error' => isset($data['data']['message']) ? $data['data']['message'] : 'Unknown error',
            'template' => $selected_template,
            'api_response' => $data
        ]);
        wp_send_json_error(array(
            'message' => 'Site creation failed: ' . (isset($data['data']['message']) ? $data['data']['message'] : 'Unknown error')
        ));
        wp_die();
    }
    
    // Check if background processing is being used
    if (isset($data['data']['background']) && $data['data']['background']) {
        // Background processing - poll for completion
        $job_id = $data['data']['job_id'];
        $max_attempts = 60; // 5 minutes max (5 second intervals)
        $attempt = 0;
        
        docket_log_info("Background clone started", [
            'job_id' => $job_id,
            'template' => $selected_template,
            'business_name' => $form_data['business_name']
        ]);
        
        while ($attempt < $max_attempts) {
            sleep(5); // Wait 5 seconds between checks
            $attempt++;
            
            // Check clone status
            $status_response = wp_remote_post($api_url . '/wp-admin/admin-ajax.php', array(
                'timeout' => 10,
                'body' => array(
                    'action' => 'esc_check_clone_status',
                    'job_id' => $job_id,
                    'api_key' => $api_key
                ),
                'sslverify' => false
            ));
            
            if (is_wp_error($status_response)) {
                continue; // Try again
            }
            
            $status_body = wp_remote_retrieve_body($status_response);
            $status_data = json_decode($status_body, true);
            
            if ($status_data && $status_data['success']) {
                $job_status = $status_data['data']['status'];
                
                if ($job_status === 'completed') {
                    // Success! Use the completed job data
                    $data['data']['site_id'] = $status_data['data']['site_id'];
                    $data['data']['site_url'] = $status_data['data']['site_url'];
                    $data['data']['admin_url'] = $status_data['data']['admin_url'];
                    
                    docket_log_info("Background clone completed", [
                        'job_id' => $job_id,
                        'site_id' => $data['data']['site_id'],
                        'attempts' => $attempt
                    ]);
                    break;
                    
                } elseif ($job_status === 'failed') {
                    $error_message = $status_data['data']['error_message'] ?? 'Background processing failed';
                    
                    docket_log_error("Background clone failed", [
                        'job_id' => $job_id,
                        'error' => $error_message,
                        'attempts' => $attempt
                    ]);
                    
                    wp_send_json_error(array(
                        'message' => 'Site creation failed: ' . $error_message
                    ));
                    wp_die();
                }
                // If status is 'pending' or 'processing', continue polling
            }
        }
        
        // If we've exceeded max attempts, return timeout error
        if ($attempt >= $max_attempts) {
            docket_log_error("Background clone timeout", [
                'job_id' => $job_id,
                'template' => $selected_template,
                'max_attempts' => $max_attempts
            ]);
            
            wp_send_json_error(array(
                'message' => 'Site creation is taking longer than expected. Please contact support with job ID: ' . $job_id
            ));
            wp_die();
        }
    }
    
    // Success! Store the new site information with the form submission
    $form_data['new_site_id'] = $data['data']['site_id'];
    $form_data['new_site_url'] = $data['data']['site_url'];
    
    // Extract client credentials if available
    if (!empty($data['data']['client_credentials'])) {
        $form_data['client_credentials'] = $data['data']['client_credentials'];
    }
    
    update_option('docket_submission_' . $submission_id, $form_data);
    
    docket_log_info("Site created successfully", [
        'site_id' => $data['data']['site_id'],
        'site_url' => $data['data']['site_url'],
        'template' => $selected_template,
        'business_name' => $form_data['business_name']
    ]);
    
    // Create client portal entry after successful site creation
    $portal_url = '';
    if (class_exists('DocketClientPortal')) {
        global $docket_client_portal;
        if ($docket_client_portal) {
            $portal_url = $docket_client_portal->create_client_project($form_data, $form_type, $data['data']['site_url']);
            error_log('Docket Onboarding: Client portal created at ' . $portal_url);
        }
    }
    
    // Create Trello card for the project
    if (class_exists('DocketTrelloSync')) {
        // Add important URLs to form data for Trello card
        $form_data['new_site_url'] = $data['data']['site_url'];
        $form_data['portal_url'] = $portal_url;
        
        // Ensure client credentials are included if available
        if (!empty($data['data']['client_credentials'])) {
            $form_data['client_credentials'] = $data['data']['client_credentials'];
        }
        
        $trello_sync = new DocketTrelloSync();
        $trello_card = $trello_sync->create_trello_card($form_data);
        
        if (!$trello_card) {
            $trello_debug_log = WP_CONTENT_DIR . '/trello-debug.log';
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: Failed to create Trello card for " . $form_data['business_name'] . "\n", FILE_APPEND);
        }
    }
    
    // Redirect to thank you page
    $success_url = 'https://yourdocketonline.com/thankyou/';
    
    // Send success response
    wp_send_json_success(array(
        'message' => 'Form submitted and site created successfully',
        'submission_id' => $submission_id,
        'site_id' => $data['data']['site_id'],
        'site_url' => $data['data']['site_url'],
        'admin_url' => $data['data']['admin_url'],
        'portal_url' => $portal_url,
        'redirect_url' => $success_url // Redirect to thank you page
    ));
    
    wp_die();
}

/**
 * Handle individual file upload
 */
function docket_handle_file_upload($file_data, $index = null) {
    // Handle array or single file
    if ($index !== null) {
        $file = array(
            'name' => $file_data['name'][$index],
            'type' => $file_data['type'][$index],
            'tmp_name' => $file_data['tmp_name'][$index],
            'error' => $file_data['error'][$index],
            'size' => $file_data['size'][$index]
        );
    } else {
        $file = $file_data;
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        docket_log_error("File too large", [
            'filename' => $file['name'],
            'size' => $file['size']
        ]);
        return false;
    }
    
    // Check file type
    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
    if (!in_array($file['type'], $allowed_types)) {
        docket_log_error("Invalid file type", [
            'filename' => $file['name'],
            'type' => $file['type']
        ]);
        return false;
    }
    
    // Use WordPress upload functions
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($file, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        docket_log_info("File uploaded successfully", [
            'filename' => $file['name'],
            'url' => $movefile['url']
        ]);
        return $movefile['url'];
    } else {
        docket_log_error("File upload failed", [
            'filename' => $file['name'],
            'error' => $movefile['error'] ?? 'Unknown error'
        ]);
        return false;
    }
}
