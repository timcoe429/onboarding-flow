<?php
/**
 * Form Handler for Docket Onboarding
 * Handles AJAX requests and form integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the fast build form file
if (file_exists(DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/fast-build-form.php')) {
    require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/fast-build-form.php';
}

// Include the standard build form file
if (file_exists(DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/standard-build/standard-build-form.php')) {
    require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/standard-build/standard-build-form.php';
}
}

/**
 * Handle AJAX request to load fast build form
 */
add_action('wp_ajax_docket_load_fast_build_form', 'docket_ajax_load_fast_build_form');
add_action('wp_ajax_nopriv_docket_load_fast_build_form', 'docket_ajax_load_fast_build_form');

function docket_ajax_load_fast_build_form() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // Get form data
    $form_data = array(
        'plan' => sanitize_text_field($_POST['plan']),
        'management' => sanitize_text_field($_POST['management']),
        'buildType' => sanitize_text_field($_POST['buildType'])
    );
    
    // Start output buffering
    ob_start();
    
    // Add CSS link
    $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'assets/fast-build-form.css?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
    
    // Render the fast build form
    if (function_exists('docket_render_fast_build_form')) {
        docket_render_fast_build_form($form_data);
    } else {
        echo '<p>Error: Fast build form not found.</p>';
    }
    
    // Get the output
    $form_html = ob_get_clean();
    
    // Apply filter for additional modifications
    $form_html = apply_filters('docket_fast_build_form_response', $form_html);
    
    wp_send_json_success(array(
        'form_html' => $form_html,
        'message' => 'Form loaded successfully'
    ));
    
    wp_die();
}

/**
 * Handle AJAX request to load standard build form
 */
add_action('wp_ajax_docket_load_standard_build_form', 'docket_ajax_load_standard_build_form');
add_action('wp_ajax_nopriv_docket_load_standard_build_form', 'docket_ajax_load_standard_build_form');

function docket_ajax_load_standard_build_form() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // Get form data
    $form_data = array(
        'plan' => sanitize_text_field($_POST['plan']),
        'management' => sanitize_text_field($_POST['management']),
        'buildType' => sanitize_text_field($_POST['buildType'])
    );
    
    // Start output buffering
    ob_start();
    
    // Add CSS link
    $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/standard-build/standard-build-form.css?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
    
    // Render the standard build form
    if (function_exists('docket_render_standard_build_form')) {
        docket_render_standard_build_form($form_data);
    } else {
        echo '<p>Error: Standard build form not found.</p>';
    }
    
    // Get the output
    $form_html = ob_get_clean();
    
    // Apply filter for additional modifications
    $form_html = apply_filters('docket_standard_build_form_response', $form_html);
    
    wp_send_json_success(array(
        'form_html' => $form_html,
        'message' => 'Form loaded successfully'
    ));
    
    wp_die();
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
    }
    
    // Get form ID
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : array();
    
    if (!$form_id) {
        wp_send_json_error(array('message' => 'Invalid form ID'));
        wp_die();
    }
    
    // Try to get the Avada form
    $form_html = '';
    
    // Method 1: Using Avada Forms shortcode
    if (shortcode_exists('fusion_form')) {
        // Store form data in a transient for the form to access
        $transient_key = 'docket_form_data_' . wp_generate_uuid4();
        set_transient($transient_key, $form_data, 60 * 60); // 1 hour expiry
        
        // Add hidden field with transient key to the form
        add_filter('fusion_form_render_field', function($field_html, $field) use ($transient_key, $form_data) {
            // Add our data as hidden fields at the beginning of the form
            static $added_fields = false;
            if (!$added_fields) {
                $hidden_fields = '<input type="hidden" name="docket_data_key" value="' . esc_attr($transient_key) . '" />';
                $hidden_fields .= '<input type="hidden" name="docket_plan" value="' . esc_attr($form_data['plan']) . '" />';
                $hidden_fields .= '<input type="hidden" name="docket_management" value="' . esc_attr($form_data['management']) . '" />';
                $hidden_fields .= '<input type="hidden" name="docket_build_type" value="' . esc_attr($form_data['buildType']) . '" />';
                $hidden_fields .= '<input type="hidden" name="docket_build_speed" value="' . esc_attr($form_data['buildSpeed']) . '" />';
                
                $field_html = $hidden_fields . $field_html;
                $added_fields = true;
            }
            return $field_html;
        }, 10, 2);
        
        // Generate the form
        $form_html = do_shortcode('[fusion_form form_post_id="' . $form_id . '" /]');
    }
    
    // Method 2: Direct post content (if form is stored as post content)
    if (empty($form_html)) {
        $form_post = get_post($form_id);
        if ($form_post && $form_post->post_type === 'avada_form') {
            $form_html = apply_filters('the_content', $form_post->post_content);
        }
    }
    
    if (!empty($form_html)) {
        wp_send_json_success(array(
            'form_html' => $form_html,
            'message' => 'Form loaded successfully'
        ));
    } else {
        wp_send_json_error(array('message' => 'Unable to load form'));
    }
    
    wp_die();
}

/**
 * Handle form data storage for redirect method
 */
add_action('wp_ajax_docket_store_form_data', 'docket_store_form_data');
add_action('wp_ajax_nopriv_docket_store_form_data', 'docket_store_form_data');

function docket_store_form_data() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : array();
    
    // Store in session or transient
    $session_key = 'docket_form_session_' . wp_get_session_token();
    set_transient($session_key, $form_data, 60 * 30); // 30 minutes
    
    // Also set a cookie as backup
    setcookie('docket_form_key', $session_key, time() + 1800, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
    
    wp_send_json_success(array('message' => 'Data stored successfully'));
    wp_die();
}

/**
 * Handle VIP or special submissions
 */
add_action('wp_ajax_docket_onboarding_submit', 'docket_onboarding_submit');
add_action('wp_ajax_nopriv_docket_onboarding_submit', 'docket_onboarding_submit');

function docket_onboarding_submit() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // Get submission data
    $plan = sanitize_text_field($_POST['plan']);
    $management = sanitize_text_field($_POST['management']);
    $build_type = sanitize_text_field($_POST['buildType']);
    $submission_type = sanitize_text_field($_POST['submissionType']);
    
    // Handle VIP submissions
    if ($submission_type === 'vip') {
        // You can customize this to send an email, create a lead, etc.
        $admin_email = get_option('admin_email');
        $subject = 'New WebsiteVIP Inquiry';
        $message = "New WebsiteVIP inquiry received:\n\n";
        $message .= "Plan: " . ucfirst($plan) . "\n";
        $message .= "Management: WebsiteVIP\n";
        $message .= "Timestamp: " . current_time('mysql') . "\n";
        
        wp_mail($admin_email, $subject, $message);
        
        wp_send_json_success(array(
            'message' => 'Thank you! We\'ll contact you soon about the WebsiteVIP plan.',
            'redirect' => home_url('/thank-you-vip')
        ));
    }
    
    wp_die();
}

/**
 * Shortcode to retrieve form data on redirect pages
 */
add_shortcode('docket_form_data', 'docket_retrieve_form_data');

function docket_retrieve_form_data($atts) {
    $atts = shortcode_atts(array(
        'field' => '',
        'format' => 'text'
    ), $atts);
    
    // Try to get data from cookie first
    $session_key = isset($_COOKIE['docket_form_key']) ? $_COOKIE['docket_form_key'] : '';
    
    if (empty($session_key)) {
        // Try session token
        $session_key = 'docket_form_session_' . wp_get_session_token();
    }
    
    $form_data = get_transient($session_key);
    
    if (!$form_data) {
        return '';
    }
    
    // Return specific field or all data
    if ($atts['field']) {
        return isset($form_data[$atts['field']]) ? esc_html($form_data[$atts['field']]) : '';
    }
    
    // Return formatted data
    if ($atts['format'] === 'json') {
        return json_encode($form_data);
    }
    
    // Return as readable text
    $output = '<div class="docket-form-data">';
    $output .= '<p><strong>Selected Plan:</strong> ' . ucfirst($form_data['selectedPlan']) . '</p>';
    $output .= '<p><strong>Management:</strong> ' . ($form_data['selectedManagement'] === 'vip' ? 'WebsiteVIP' : 'Self-Managed') . '</p>';
    $output .= '<p><strong>Build Type:</strong> ' . ($form_data['selectedBuildType'] === 'fast' ? 'Fast Build (3 days)' : 'Standard Build (21-30 days)') . '</p>';
    $output .= '</div>';
    
    return $output;
}

/**
 * Add custom fields to Avada form submissions
 */
add_filter('fusion_form_submission_data', 'docket_add_form_submission_data', 10, 3);

function docket_add_form_submission_data($data, $form_id, $form_submission_data) {
    // Check if this is one of our forms
    $our_forms = array(311, 65); // Fast build and Standard build form IDs
    
    if (in_array($form_id, $our_forms)) {
        // Add our custom data if available
        if (isset($_POST['docket_plan'])) {
            $data['docket_plan'] = sanitize_text_field($_POST['docket_plan']);
        }
        if (isset($_POST['docket_management'])) {
            $data['docket_management'] = sanitize_text_field($_POST['docket_management']);
        }
        if (isset($_POST['docket_build_type'])) {
            $data['docket_build_type'] = sanitize_text_field($_POST['docket_build_type']);
        }
        if (isset($_POST['docket_build_speed'])) {
            $data['docket_build_speed'] = sanitize_text_field($_POST['docket_build_speed']);
        }
        
        // Try to get data from transient if key is provided
        if (isset($_POST['docket_data_key'])) {
            $transient_data = get_transient($_POST['docket_data_key']);
            if ($transient_data) {
                $data = array_merge($data, $transient_data);
                // Delete transient after use
                delete_transient($_POST['docket_data_key']);
            }
        }
    }
    
    return $data;
}

/**
 * Helper function to create form pages if using redirect method
 */
function docket_create_form_pages() {
    // Check if pages exist
    $fast_page = get_page_by_path('fast-build-order');
    $standard_page = get_page_by_path('standard-build-order');
    
    // Create Fast Build page
    if (!$fast_page) {
        $fast_page_id = wp_insert_post(array(
            'post_title' => 'Fast Build Order',
            'post_name' => 'fast-build-order',
            'post_content' => '[fusion_form form_post_id="311" /]' . "\n\n" . '[docket_form_data]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1
        ));
    }
    
    // Create Standard Build page
    if (!$standard_page) {
        $standard_page_id = wp_insert_post(array(
            'post_title' => 'Standard Build Order',
            'post_name' => 'standard-build-order',
            'post_content' => '[fusion_form form_post_id="65" /]' . "\n\n" . '[docket_form_data]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1
        ));
    }
}

// Uncomment this line to create the pages on plugin activation
// add_action('init', 'docket_create_form_pages');
