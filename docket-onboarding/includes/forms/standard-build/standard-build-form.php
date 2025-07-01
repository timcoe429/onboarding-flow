<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the standard build form
 */
function docket_render_standard_build_form($form_data = array()) {
    // Extract form data passed from onboarding
    $plan_type = isset($form_data['plan']) ? $form_data['plan'] : '';
    $management_type = isset($form_data['management']) ? $form_data['management'] : '';
    $build_type = isset($form_data['buildType']) ? $form_data['buildType'] : '';
    ?>
    
    <div class="docket-standard-form" id="docketStandardBuildForm">
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

        <form id="standardBuildForm" method="post" enctype="multipart/form-data">
            <!-- Hidden fields for onboarding data -->
            <input type="hidden" name="docket_plan_type" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">
            <input type="hidden" name="docket_management_type" value="<?php echo esc_attr($management_type); ?>">
            <input type="hidden" name="docket_build_type" value="<?php echo esc_attr($build_type); ?>">
            <input type="hidden" name="select_your_docket_plan" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">

            <!-- Add WordPress nonce field -->
<?php wp_nonce_field('docket_onboarding_nonce', 'nonce'); ?>
            <!-- Include form steps -->
            <?php 
            $steps_path = DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/standard-build/steps/';
            
            include $steps_path . 'step-1-terms.php';
            include $steps_path . 'step-2-contact.php';
            include $steps_path . 'step-3-template-info.php';
            include $steps_path . 'step-4-template-select.php';
            include $steps_path . 'step-5-content.php';
            include $steps_path . 'step-6-branding.php';
            include $steps_path . 'step-7-rentals.php';
            include $steps_path . 'step-8-marketing.php';
            ?>
        </form>

        <!-- Success Screen -->
        <div class="form-success" style="display: none;">
            <div class="success-icon">✓</div>
            <h2>Order Submitted!</h2>
            <p>Thank you! We'll start building your website right away.</p>
            <p class="success-note">You'll receive a confirmation email shortly with next steps.</p>
        </div>
    </div>
    <?php
}

/**
 * AJAX Handler for form submission
 */
add_action('wp_ajax_docket_submit_standard_build_form', 'docket_handle_standard_build_submission');
add_action('wp_ajax_nopriv_docket_submit_standard_build_form', 'docket_handle_standard_build_submission');

function docket_handle_standard_build_submission() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Prepare email content
    $email_content = "<html><body style='font-family: Arial, sans-serif;'>";
    $email_content .= "<h2>Standard Build Form Submission</h2>";
    $email_content .= "<hr style='border: 1px solid #ccc;'><br>";

    // Order Information
    $email_content .= "<h3>Order Details</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Plan Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . ucfirst($_POST['docket_plan_type']) . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Build Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>Standard Build (21-30 days)</td></tr>";
    $email_content .= "</table><br>";

    // Contact Information
    $email_content .= "<h3>Contact Information</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Name:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['contact_name'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['contact_email_address'] . "</td></tr>";
    $email_content .= "</table><br>";

    // Business Information
    $email_content .= "<h3>Business Information</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Business Name:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['business_name'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Phone:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['business_phone_number'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['business_email'] . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Address:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['business_address'] . "</td></tr>";
    $email_content .= "</table><br>";

    // Template Selection
    $email_content .= "<h3>Template Selection</h3>";
    $email_content .= "<p>" . $_POST['website_template_selection'] . "</p><br>";

    // Website Content
    if (!empty($_POST['provide_content_now']) && $_POST['provide_content_now'] === 'Yes') {
        $email_content .= "<h3>Website Content</h3>";
        $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
        
        if (!empty($_POST['company_tagline'])) {
            $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Tagline:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['company_tagline'] . "</td></tr>";
        }
        
        if (!empty($_POST['company_faqs'])) {
            $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>FAQs:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . nl2br($_POST['company_faqs']) . "</td></tr>";
        }
        
        if (!empty($_POST['benefits_what_we_do'])) {
            $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Benefits/What We Do:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . nl2br($_POST['benefits_what_we_do']) . "</td></tr>";
        }
        
        if (!empty($_POST['website_footer'])) {
            $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Footer Text:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['website_footer'] . "</td></tr>";
        }
        
        $email_content .= "</table><br>";
    }

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
    if (!empty($_POST['font_name'])) {
        $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Font:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['font_name'] . "</td></tr>";
    }
    $email_content .= "</table><br>";

    // Rental Information
    $email_content .= "<h3>Rental Information</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Dumpster Color:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['dumpster_color'] . "</td></tr>";
    
    if (!empty($_POST['services_offered'])) {
        $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Services Offered:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . implode(', ', (array)$_POST['services_offered']) . "</td></tr>";
    }
    
    if (!empty($_POST['dumpster_types'])) {
        $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Dumpster Types:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . implode(', ', (array)$_POST['dumpster_types']) . "</td></tr>";
    }
    $email_content .= "</table><br>";

    // Marketing
    $email_content .= "<h3>Marketing & SEO</h3>";
    $email_content .= "<p><strong>Working with SEO/Marketing Agency:</strong> " . $_POST['marketing_agency'] . "</p>";
    
    // Social Media
    if (!empty($_POST['facebook']) || !empty($_POST['instagram']) || !empty($_POST['twitter']) || !empty($_POST['YouTube'])) {
        $email_content .= "<p><strong>Social Media:</strong></p>";
        $email_content .= "<ul>";
        if (!empty($_POST['facebook'])) $email_content .= "<li>Facebook: " . $_POST['facebook'] . "</li>";
        if (!empty($_POST['instagram'])) $email_content .= "<li>Instagram: " . $_POST['instagram'] . "</li>";
        if (!empty($_POST['twitter'])) $email_content .= "<li>X (Twitter): " . $_POST['twitter'] . "</li>";
        if (!empty($_POST['YouTube'])) $email_content .= "<li>YouTube: " . $_POST['YouTube'] . "</li>";
        $email_content .= "</ul>";
    }
    
    if (!empty($_POST['reviews_testimonials'])) {
        $email_content .= "<p><strong>Reviews/Testimonials:</strong><br>" . nl2br($_POST['reviews_testimonials']) . "</p>";
    }
    
    $email_content .= "<br>";

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
$subject = 'Standard Build Form Submission - ' . sanitize_text_field($_POST['business_name']);
$headers = array('Content-Type: text/html; charset=UTF-8');

$sent = wp_mail($to, $subject, $email_content, $headers, $attachments);

if ($sent) {
    // Create client portal entry after successful email send
    $portal_url = null;
    if (class_exists('DocketClientPortal')) {
        // Get portal instance and create project
        global $docket_client_portal;
        if ($docket_client_portal) {
            $portal_url = $docket_client_portal->create_client_project($_POST, 'standard-build');
        }
    }
    
    $response_data = array('message' => 'Form submitted successfully');
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
