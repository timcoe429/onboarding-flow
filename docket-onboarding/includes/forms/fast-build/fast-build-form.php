<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the fast build form
 */
function docket_render_fast_build_form($form_data = array()) {
    // Extract form data passed from onboarding
    $plan_type = isset($form_data['plan']) ? $form_data['plan'] : '';
    $management_type = isset($form_data['management']) ? $form_data['management'] : '';
    $build_type = isset($form_data['buildType']) ? $form_data['buildType'] : '';
    ?>
    
    <div class="docket-fast-form" id="docketFastBuildForm">
        <!-- Clean Progress Bar -->
        <div class="docket-form-progress">
            <div class="docket-progress-track">
                <div class="docket-progress-fill" data-progress="12.5"></div>
            </div>
            <div class="docket-progress-dots">
                <span class="active" data-step="1">1</span>
                <span data-step="2">2</span>
                <span data-step="3">3</span>
                <span data-step="4">4</span>
                <span data-step="5">5</span>
                <span data-step="6">6</span>
                <span data-step="7">7</span>
                <span data-step="8">8</span>
            </div>
        </div>

        <form id="fastBuildForm" method="post" enctype="multipart/form-data">
            <!-- Hidden fields for onboarding data -->
            <input type="hidden" name="docket_plan_type" value="<?php echo esc_attr($plan_type); ?>">
            <input type="hidden" name="docket_management_type" value="<?php echo esc_attr($management_type); ?>">
            <input type="hidden" name="docket_build_type" value="<?php echo esc_attr($build_type); ?>">
            <input type="hidden" name="select_your_docket_plan" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">

            <!-- Add WordPress nonce field -->
            <?php wp_nonce_field('docket_onboarding_nonce', 'nonce'); ?>

            <!-- Include form steps -->
            <?php 
            $steps_path = DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/fast-build/steps/';
            
            include $steps_path . 'step-1-terms.php';
            include $steps_path . 'step-2-contact.php';
            include $steps_path . 'step-3-template-info.php';
            include $steps_path . 'step-4-template-select.php';
            include $steps_path . 'step-5-service-areas.php';
            include $steps_path . 'step-6-branding.php';
            include $steps_path . 'step-7-rentals.php';
            include $steps_path . 'step-8-marketing.php';
            ?>
        </form>

        <!-- Success Screen -->
        <div class="form-success" style="display: none;">
            <div class="success-icon">✓</div>
            <h2>Fast Build Order Submitted!</h2>
            <p>Thank you! Your Fast Build website will be ready in 3 business days.</p>
            <p class="success-note">You'll receive a confirmation email shortly with next steps.</p>
        </div>
    </div>
    <?php
}

/**
 * AJAX Handler for form submission
 */
add_action('wp_ajax_docket_submit_fast_build_form', 'docket_handle_fast_build_submission');
add_action('wp_ajax_nopriv_docket_submit_fast_build_form', 'docket_handle_fast_build_submission');

function docket_handle_fast_build_submission() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Prepare email content
    $email_content = "<html><body style='font-family: Arial, sans-serif;'>";
    $email_content .= "<h2>Fast Build Form Submission</h2>";
    $email_content .= "<hr style='border: 1px solid #ccc;'><br>";

    // Order Information
    $email_content .= "<h3>Order Details</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Plan Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . ucfirst($_POST['docket_plan_type']) . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Build Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>Fast Build (3 days)</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Management Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . ucfirst($_POST['docket_management_type']) . "</td></tr>";
    $email_content .= "</table><br>";

    // WordPress Experience
    $email_content .= "<h3>WordPress Experience</h3>";
    $email_content .= "<p>" . $_POST['wordpress_exp'] . "</p><br>";

    // Contact Information
    $email_content .= "<h3>Contact Information</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Name:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['name'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['email'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Phone:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['phone_number'] . "</td></tr>";
    $email_content .= "</table><br>";

    // Business Information
    $email_content .= "<h3>Business Information</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Business Name:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['business_name'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Business Email:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['business_email'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Business Address:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['business_address'] . "</td></tr>";
    $email_content .= "</table><br>";

    // Template Selection
    $email_content .= "<h3>Website Details</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Template Selected:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['website_template_selection'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Terms Accepted:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['accept_terms'] . "</td></tr>";
    $email_content .= "</table><br>";

    // Service Areas
    $email_content .= "<h3>Service Areas</h3>";
    $email_content .= "<ol>";
    for ($i = 1; $i <= 9; $i++) {
        if (!empty($_POST['servicearea' . $i])) {
            $email_content .= "<li>" . $_POST['servicearea' . $i] . "</li>";
        }
    }
    $email_content .= "</ol><br>";

    // Blog Focus (if Pro plan)
    if (!empty($_POST['blog_focus'])) {
        $email_content .= "<h3>Blog Focus</h3>";
        $email_content .= "<p>" . $_POST['blog_focus'] . "</p><br>";
    }

    // Company Branding
    $email_content .= "<h3>Company Branding</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Logo:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['logo_question'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Primary Color:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['company_colors'] . "</td></tr>";
    if (!empty($_POST['company_colors2'])) {
        $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Secondary Color:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['company_colors2'] . "</td></tr>";
    }
    $email_content .= "</table><br>";

    // Rental Information
    $email_content .= "<h3>Rental Information</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Dumpster Color:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['dumpster_color'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Services Offered:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . implode(', ', (array)$_POST['services_offered']) . "</td></tr>";
    if (!empty($_POST['dumpster_types'])) {
        $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Dumpster Types:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . implode(', ', (array)$_POST['dumpster_types']) . "</td></tr>";
    }
    $email_content .= "</table>";

    // Dumpster Type Details
    if (!empty($_POST['roll_sizes']) || !empty($_POST['roll_pricing'])) {
        $email_content .= "<h4>Roll-Off Details</h4>";
        $email_content .= "<ul>";
        if (!empty($_POST['roll_sizes'])) $email_content .= "<li>Sizes: " . $_POST['roll_sizes'] . "</li>";
        if (!empty($_POST['roll_pricing'])) $email_content .= "<li>Pricing: " . $_POST['roll_pricing'] . "</li>";
        $email_content .= "</ul>";
    }

    if (!empty($_POST['hook_sizes']) || !empty($_POST['hook_pricing'])) {
        $email_content .= "<h4>Hook-Lift Details</h4>";
        $email_content .= "<ul>";
        if (!empty($_POST['hook_sizes'])) $email_content .= "<li>Sizes: " . $_POST['hook_sizes'] . "</li>";
        if (!empty($_POST['hook_pricing'])) $email_content .= "<li>Pricing: " . $_POST['hook_pricing'] . "</li>";
        $email_content .= "</ul>";
    }

    if (!empty($_POST['trailer_sizes']) || !empty($_POST['trailer_pricing'])) {
        $email_content .= "<h4>Dump Trailer Details</h4>";
        $email_content .= "<ul>";
        if (!empty($_POST['trailer_sizes'])) $email_content .= "<li>Sizes: " . $_POST['trailer_sizes'] . "</li>";
        if (!empty($_POST['trailer_pricing'])) $email_content .= "<li>Pricing: " . $_POST['trailer_pricing'] . "</li>";
        $email_content .= "</ul>";
    }
    $email_content .= "<br>";

    // Marketing
    $email_content .= "<h3>Marketing & SEO</h3>";
    $email_content .= "<p><strong>Working with SEO/Marketing Agency:</strong> " . $_POST['marketing_agency'] . "</p><br>";

    // File Uploads
    if (!empty($_FILES)) {
        $email_content .= "<h3>File Uploads</h3>";
        $email_content .= "<ul>";
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                for ($i = 0; $i < count($file['name']); $i++) {
                    if (!empty($file['name'][$i])) {
                        $email_content .= "<li>" . $file['name'][$i] . "</li>";
                    }
                }
            } else {
                if (!empty($file['name'])) {
                    $email_content .= "<li>" . $file['name'] . "</li>";
                }
            }
        }
        $email_content .= "</ul>";
    }

    $email_content .= "</body></html>";

// Collect uploaded files
$attachments = array();
if (!empty($_FILES)) {
    foreach ($_FILES as $file_key => $file) {
        // Handle array of files (like logo_files[])
        if (is_array($file['name'])) {
            for ($i = 0; $i < count($file['name']); $i++) {
                if ($file['error'][$i] === UPLOAD_ERR_OK && !empty($file['name'][$i])) {
                    // Move uploaded file to WordPress uploads directory
                    $upload_dir = wp_upload_dir();
                    $file_name = sanitize_file_name($file['name'][$i]);
                    $upload_path = $upload_dir['path'] . '/' . $file_name;
                    
                    if (move_uploaded_file($file['tmp_name'][$i], $upload_path)) {
                        $attachments[] = $upload_path;
                    }
                }
            }
        } else {
            // Handle single file
            if ($file['error'] === UPLOAD_ERR_OK && !empty($file['name'])) {
                // Move uploaded file to WordPress uploads directory
                $upload_dir = wp_upload_dir();
                $file_name = sanitize_file_name($file['name']);
                $upload_path = $upload_dir['path'] . '/' . $file_name;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $attachments[] = $upload_path;
                }
            }
        }
    }
}

// Send email with attachments
$to = 'tim@servicecore.com';
$subject = 'Fast Build Form Submission - ' . sanitize_text_field($_POST['business_name']);
$headers = array(
    'Content-Type: text/html; charset=UTF-8',
    'Bcc: timcoe9+skthyu1dpc3gugnyqv2j@boards.trello.com'
);

$sent = wp_mail($to, $subject, $email_content, $headers, $attachments);

if ($sent) {
    // ✅ NEW: Trigger automated site creation via API
    $site_creation_response = create_site_via_api($_POST, 'fast-build');
    
    // Trigger NS Cloner integration (if available) - FALLBACK
    do_action('docket_after_form_submission', $_POST, 'fast-build');
    
    // Create client portal entry after successful email send
    $portal_url = null;
    $new_site_url = null;
    
    if ($site_creation_response && $site_creation_response['success']) {
        $new_site_url = $site_creation_response['site_url'];
    }
    
    if (class_exists('DocketClientPortal')) {
        // Get portal instance and create project
        global $docket_client_portal;
        if ($docket_client_portal) {
            // Pass the new site URL to the portal creation
            $portal_url = $docket_client_portal->create_client_project($_POST, 'fast-build', $new_site_url);
        }
    }
    
    $response_data = array('message' => 'Form submitted successfully');
    if ($new_site_url) {
        $response_data['new_site_url'] = $new_site_url;
        $response_data['message'] = 'Form submitted and site created successfully!';
    }
    if ($portal_url) {
        $response_data['redirect_url'] = $portal_url;
    } else {
        $response_data['redirect_url'] = home_url('/thank-you/');
    }
    
    wp_send_json_success($response_data);
} else {
    wp_send_json_error(array('message' => 'Failed to send email'));
}

// Clean up - delete uploaded files after sending
if (!empty($attachments)) {
    foreach ($attachments as $file) {
        @unlink($file);
    }
}
}

/**
 * Create site via API call to dockethosting5.com
 */
if (!function_exists('create_site_via_api')) {
function create_site_via_api($form_data, $form_type) {
    // Map form template selection to our templates
    $template_mapping = array(
        'template-1' => 'template1',
        'template-2' => 'template2', 
        'template-3' => 'template3',
        'template-4' => 'template4',
        // Fallback mappings
        'Template 1' => 'template1',
        'Template 2' => 'template2',
        'Template 3' => 'template3', 
        'Template 4' => 'template4'
    );
    
    $selected_template = 'template1'; // Default
    if (!empty($form_data['website_template_selection'])) {
        $user_selection = $form_data['website_template_selection'];
        if (isset($template_mapping[$user_selection])) {
            $selected_template = $template_mapping[$user_selection];
        }
    }
    
    // Prepare service areas
    $service_areas = array();
    for ($i = 1; $i <= 9; $i++) {
        if (!empty($form_data['servicearea' . $i])) {
            $service_areas[] = $form_data['servicearea' . $i];
        }
    }
    
    // Prepare API data
    $api_data = array(
        'selected_template' => $selected_template,
        'business_name' => $form_data['business_name'] ?? '',
        'contact_name' => $form_data['name'] ?? '',
        'phone' => $form_data['phone_number'] ?? '',
        'email' => $form_data['email'] ?? '',
        'business_email' => $form_data['business_email'] ?? '',
        'business_address' => $form_data['business_address'] ?? '',
        'service_areas' => implode(', ', $service_areas),
        'services' => implode(', ', (array)($form_data['services_offered'] ?? array())),
        'form_type' => $form_type
    );
    
    $api_url = 'https://dockethosting5.com/wp-json/docket/v1/create-site';
    
    $response = wp_remote_post($api_url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-Key' => 'docket_automation_key_2025' // Same key as multisite
        ),
        'body' => json_encode($api_data),
        'timeout' => 60
    ));
    
    if (is_wp_error($response)) {
        error_log('Docket Site Creation API error: ' . $response->get_error_message());
        return false;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        error_log('Docket Site Creation API returned status: ' . $response_code);
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    if ($result && isset($result['success']) && $result['success']) {
        error_log('Docket Site Creation: Success! Site URL: ' . $result['site_url']);
        return $result;
    } else {
        $error_msg = isset($result['message']) ? $result['message'] : 'Unknown error';
        error_log('Docket Site Creation API failed: ' . $error_msg);
        return false;
    }
}
}
