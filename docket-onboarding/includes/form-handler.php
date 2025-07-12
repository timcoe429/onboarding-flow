<?php
/**
 * Form Handler
 * Handles all form-related functionality including AJAX loading and submission
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include form rendering functions
require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/fast-build/fast-build-form.php';
require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/standard-build/standard-build-form.php';
require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/website-vip/website-vip-form.php';

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
    $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/fast-build/fast-build-form.css?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
    
    // Add JavaScript link
    $js_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/fast-build/fast-build-form.js?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<script src="' . esc_url($js_url) . '"></script>';
    
    // Localize script with AJAX URL
    echo '<script>window.ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
    
    // Add script to initialize the form after loading
    echo '<script>
        // Wait for script to load then initialize
        function waitForFastBuildScript() {
            if (typeof jQuery !== "undefined" && jQuery("#fastBuildForm").length > 0) {
                // Initialize the form directly since it\'s loaded via AJAX
                jQuery(function($) {
                    // Initialize modal functionality
                    window.openTermsModal = function() {
                        var modal = document.getElementById("termsModal");
                        if (modal) {
                            modal.style.display = "block";
                        }
                    }
                    
                    // Close modal when clicking X or outside
                    $(document).on("click", ".docket-modal-close, .docket-modal", function(e) {
                        if (e.target === this) {
                            $("#termsModal").hide();
                        }
                    });
                    
                    // Prevent modal content clicks from closing
                    $(document).on("click", ".docket-modal-content", function(e) {
                        e.stopPropagation();
                    });
                    
                    // The rest of the initialization is handled by the loaded fast-build-form.js file
                });
            } else {
                setTimeout(waitForFastBuildScript, 100);
            }
        }
        
        waitForFastBuildScript();
    </script>';
    
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
    
    // Add JavaScript link
    $js_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/standard-build/standard-build-form.js?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<script src="' . esc_url($js_url) . '"></script>';
    
    // Localize script with AJAX URL
    echo '<script>window.ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
    
    // Add script to initialize the form after loading
    echo '<script>
        // Wait for script to load then initialize
        function waitForScript() {
            if (typeof jQuery !== "undefined" && jQuery("#standardBuildForm").length > 0) {
                // Initialize the form directly since it\'s loaded via AJAX
                jQuery(function($) {
                    // Copy the initialization code from standard-build-form.js
                    var currentStep = 1;
                    var totalSteps = 8;
                    
                    // Initialize modal functionality
                    window.openTermsModal = function() {
                        var modal = document.getElementById("termsModal");
                        if (modal) {
                            modal.style.display = "block";
                        }
                    }
                    
                    // Close modal when clicking X or outside
                    $(document).on("click", ".docket-modal-close, .docket-modal", function(e) {
                        if (e.target === this) {
                            $("#termsModal").hide();
                        }
                    });
                    
                    // Prevent modal content clicks from closing
                    $(document).on("click", ".docket-modal-content", function(e) {
                        e.stopPropagation();
                    });
                    
                    // Rest of form initialization
                    initializeFormHandlers();
                });
            } else {
                setTimeout(waitForScript, 100);
            }
        }
        
        waitForScript();
        
        function initializeFormHandlers() {
            // Form navigation and validation code
            var $ = jQuery;
            var form = $("#standardBuildForm");
            var currentStep = 1;
            var totalSteps = 8;
            
            // Navigation handlers
            form.find(".btn-next").off("click").on("click", function() {
                if (validateStep(currentStep)) {
                    currentStep++;
                    showStep(currentStep);
                }
            });
            
            form.find(".btn-prev").off("click").on("click", function() {
                currentStep--;
                showStep(currentStep);
            });
            
            function showStep(step) {
                $(".form-step").removeClass("active");
                $(".form-step[data-step=\'" + step + "\']").addClass("active");
                updateProgressBar(step);
            }
            
            function updateProgressBar(step) {
                var progress = (step / totalSteps) * 100;
                $(".docket-progress-fill").css("width", progress + "%");
                $(".docket-progress-dots span").removeClass("active completed");
                for (var i = 1; i <= step; i++) {
                    if (i < step) {
                        $(".docket-progress-dots span[data-step=\'" + i + "\']").addClass("completed");
                    } else {
                        $(".docket-progress-dots span[data-step=\'" + i + "\']").addClass("active");
                    }
                }
            }
            
            function validateStep(step) {
                var isValid = true;
                var currentStepElement = $(".form-step[data-step=\'" + step + "\']");
                
                currentStepElement.find("input[required], select[required], textarea[required]").each(function() {
                    if (!this.checkValidity()) {
                        this.reportValidity();
                        isValid = false;
                        return false;
                    }
                });
                
                return isValid;
            }
        }
    </script>';
    
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
 * Handle AJAX request to load Website VIP form
 */
add_action('wp_ajax_docket_load_website_vip_form', 'docket_ajax_load_website_vip_form');
add_action('wp_ajax_nopriv_docket_load_website_vip_form', 'docket_ajax_load_website_vip_form');

function docket_ajax_load_website_vip_form() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // Get form data
    $form_data = array(
        'plan' => sanitize_text_field($_POST['plan']),
        'management' => 'vip',
        'buildType' => sanitize_text_field($_POST['buildType'])
    );
    
    // Start output buffering
    ob_start();
    
    // Add CSS link
    $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/website-vip/website-vip-form.css?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
    
    // Add JavaScript link
    $js_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/website-vip/website-vip-form.js?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<script src="' . esc_url($js_url) . '"></script>';
    
    // Localize script with AJAX URL
    echo '<script>window.ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
    
    // Add script to initialize the form after loading
    echo '<script>
        // Wait for script to load then initialize
        function waitForWebsiteVipScript() {
            if (typeof jQuery !== "undefined" && jQuery("#websiteVipForm").length > 0) {
                // Initialize the form directly since it\'s loaded via AJAX
                jQuery(function($) {
                    // Initialize modal functionality
                    window.openTermsModal = function() {
                        var modal = document.getElementById("termsModal");
                        if (modal) {
                            modal.style.display = "block";
                        }
                    }
                    
                    // Close modal when clicking X or outside
                    $(document).on("click", ".docket-modal-close, .docket-modal", function(e) {
                        if (e.target === this) {
                            $("#termsModal").hide();
                        }
                    });
                    
                    // Prevent modal content clicks from closing
                    $(document).on("click", ".docket-modal-content", function(e) {
                        e.stopPropagation();
                    });
                    
                    // The rest of the initialization is handled by the loaded website-vip-form.js file
                });
            } else {
                setTimeout(waitForWebsiteVipScript, 100);
            }
        }
        
        waitForWebsiteVipScript();
    </script>';
    
    // Render the Website VIP form
    if (function_exists('docket_render_website_vip_form')) {
        docket_render_website_vip_form($form_data);
    } else {
        echo '<p>Error: Website VIP form not found.</p>';
    }
    
    // Get the output
    $form_html = ob_get_clean();
    
    // Apply filter for additional modifications
    $form_html = apply_filters('docket_website_vip_form_response', $form_html);
    
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
    }
    
    // Process the form data
    $form_data = array();
    $form_data['form_type'] = $form_type;
    
    // Sanitize all form fields
    foreach ($_POST as $key => $value) {
        if (!in_array($key, array('nonce', '_wpnonce', 'docket_nonce', 'action'))) {
            if (is_array($value)) {
                $form_data[$key] = array_map('sanitize_text_field', $value);
            } else {
                $form_data[$key] = sanitize_text_field($value);
            }
        }
    }
    
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
    }
    
    // Get configuration for remote API
    $api_url = get_option('docket_cloner_api_url', 'https://dockethosting5.com');
    $api_key = get_option('docket_cloner_api_key', 'esc_docket_2025_secure_key');
    
    // Make API call to Elementor Site Cloner on dockethosting5.com
    error_log('Docket Onboarding: Making API call to ' . $api_url . ' for ' . $form_type . ' form submission');
    
    // Get the template selection (default to template1 if not specified)
    $selected_template = isset($form_data['website_template_selection']) ? $form_data['website_template_selection'] : 'template1';
    
    // Get the business name from the form
    $site_name = !empty($form_data['business_name']) ? $form_data['business_name'] : 'Docket Site ' . time();
    
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
        error_log('Docket Onboarding: API request failed - ' . $response->get_error_message());
        wp_send_json_error(array(
            'message' => 'Failed to connect to site creation service: ' . $response->get_error_message()
        ));
        wp_die();
    }
    
    // Parse the response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data) || !isset($data['success'])) {
        error_log('Docket Onboarding: Invalid API response - ' . $body);
        error_log('Docket Onboarding: Response code - ' . wp_remote_retrieve_response_code($response));
        error_log('Docket Onboarding: Response headers - ' . print_r(wp_remote_retrieve_headers($response), true));
        
        // Check if it's an HTML error page
        if (strpos($body, '<html') !== false || strpos($body, 'Access denied') !== false) {
            error_log('Docket Onboarding: Received HTML response instead of JSON - possible security plugin blocking');
            wp_send_json_error(array(
                'message' => 'Site creation blocked by security settings. Please check server configuration.'
            ));
            wp_die();
        }
        
        wp_send_json_error(array(
            'message' => 'Invalid response from site creation service'
        ));
        wp_die();
    }
    
    if (!$data['success']) {
        error_log('Docket Onboarding: Site creation failed - ' . ($data['data']['message'] ?? 'Unknown error'));
        wp_send_json_error(array(
            'message' => 'Site creation failed: ' . ($data['data']['message'] ?? 'Unknown error')
        ));
        wp_die();
    }
    
    // Success! Store the new site information with the form submission
    $form_data['new_site_id'] = $data['data']['site_id'];
    $form_data['new_site_url'] = $data['data']['site_url'];
    update_option('docket_submission_' . $submission_id, $form_data);
    
    error_log('Docket Onboarding: Site created successfully - ID: ' . $data['data']['site_id'] . ', URL: ' . $data['data']['site_url']);
    
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
        
        $trello_sync = new DocketTrelloSync();
        $trello_card = $trello_sync->create_trello_card($form_data);
        if ($trello_card) {
            error_log('Docket Onboarding: Trello card created for ' . $form_data['business_name']);
        } else {
            error_log('Docket Onboarding: Failed to create Trello card for ' . $form_data['business_name']);
        }
    }
    
    // Send success response
    wp_send_json_success(array(
        'message' => 'Form submitted and site created successfully',
        'submission_id' => $submission_id,
        'site_id' => $data['data']['site_id'],
        'site_url' => $data['data']['site_url'],
        'admin_url' => $data['data']['admin_url'],
        'portal_url' => $portal_url,
        'redirect_url' => $portal_url // Redirect to client portal, not the admin
    ));
    
    wp_die();
}
