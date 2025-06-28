<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the Website VIP form
 */
function docket_render_website_vip_form($form_data = array()) {
    // Extract form data passed from onboarding
    $plan_type = isset($form_data['plan']) ? $form_data['plan'] : '';
    $management_type = isset($form_data['management']) ? $form_data['management'] : 'vip';
    $build_type = isset($form_data['buildType']) ? $form_data['buildType'] : '';
    ?>
    
    <div class="docket-vip-form" id="docketWebsiteVipForm">
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

        <form id="websiteVipForm" method="post" enctype="multipart/form-data">
            <!-- Hidden fields for onboarding data -->
            <input type="hidden" name="docket_plan_type" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">
            <input type="hidden" name="docket_management_type" value="WebsiteVIP">
            <input type="hidden" name="docket_build_type" value="<?php echo esc_attr($build_type); ?>">
            <input type="hidden" name="select_your_docket_plan" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">

            <!-- Add WordPress nonce field -->
            <?php wp_nonce_field('docket_onboarding_nonce', 'nonce'); ?>
            
            <!-- Include form steps -->
            <?php 
            $steps_path = DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/website-vip/steps/';
            
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
            <h2>Website VIP Order Submitted!</h2>
            <p>Thank you! Your Website VIP order has been received.</p>
            <p class="success-note">Our team will contact you shortly to discuss your WebsiteVIP plan upgrade and next steps.</p>
        </div>
    </div>
    <?php
}

/**
 * AJAX Handler for form submission
 */
add_action('wp_ajax_docket_submit_website_vip_form', 'docket_handle_website_vip_submission');
add_action('wp_ajax_nopriv_docket_submit_website_vip_form', 'docket_handle_website_vip_submission');

function docket_handle_website_vip_submission() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Prepare email content
    $email_content = "<html><body style='font-family: Arial, sans-serif;'>";
    $email_content .= "<h2>Website VIP Form Submission</h2>";
    $email_content .= "<hr style='border: 1px solid #ccc;'><br>";

    // Order Information
    $email_content .= "<h3>Order Details</h3>";
    $email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Plan Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . ucfirst($_POST['docket_plan_type']) . "</td></tr>";
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Management Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>WebsiteVIP (+$299/month)</td></tr>";
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
    if (!empty($_POST['do_you_want_to_give_our_team_website_content_at_this_time']) && $_POST['do_you_want_to_give_our_team_website_content_at_this_time'] === 'Yes') {
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
    $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Dumpster Color:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['dumpster_stock_image_color_selection'] . "</td></tr>";
    
    if (!empty($_POST['what_services_do_you_offer'])) {
        $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Services Offered:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . implode(', ', (array)$_POST['what_services_do_you_offer']) . "</td></tr>";
    }
    
    if (!empty($_POST['what_types_of_dumpsters_do_you_have'])) {
        $email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Dumpster Types:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . implode(', ', (array)$_POST['what_types_of_dumpsters_do_you_have']) . "</td></tr>";
    }
    $email_content .= "</table><br>";

    // Dumpster Type Details
    if (!empty($_POST['roll_sizes']) || !empty($_POST['roll_rentalperiods'])) {
        $email_content .= "<h4>Roll-Off Details</h4>";
        $email_content .= "<ul>";
        if (!empty($_POST['roll_sizes'])) $email_content .= "<li>Sizes: " . $_POST['roll_sizes'] . "</li>";
        if (!empty($_POST['roll_rentalperiods'])) $email_content .= "<li>Rental Periods: " . $_POST['roll_rentalperiods'] . "</li>";
        if (!empty($_POST['roll_tons'])) $email_content .= "<li>Tons Allowed: " . $_POST['roll_tons'] . "</li>";
        if (!empty($_POST['roll_startingprice'])) $email_content .= "<li>Starting Prices: " . $_POST['roll_startingprice'] . "</li>";
        $email_content .= "</ul>";
    }

    if (!empty($_POST['hook_rentalsizes']) || !empty($_POST['hook_rentalperiods'])) {
        $email_content .= "<h4>Hook-Lift Details</h4>";
        $email_content .= "<ul>";
        if (!empty($_POST['hook_rentalsizes'])) $email_content .= "<li>Sizes: " . $_POST['hook_rentalsizes'] . "</li>";
        if (!empty($_POST['hook_rentalperiods'])) $email_content .= "<li>Rental Periods: " . $_POST['hook_rentalperiods'] . "</li>";
        if (!empty($_POST['hook_rentaltons'])) $email_content .= "<li>Tons Allowed: " . $_POST['hook_rentaltons'] . "</li>";
        if (!empty($_POST['hook_price'])) $email_content .= "<li>Starting Prices: " . $_POST['hook_price'] . "</li>";
        $email_content .= "</ul>";
    }

    if (!empty($_POST['dump_trailersize']) || !empty($_POST['dump_trailerrentals'])) {
        $email_content .= "<h4>Dump Trailer Details</h4>";
        $email_content .= "<ul>";
        if (!empty($_POST['dump_trailersize'])) $email_content .= "<li>Sizes: " . $_POST['dump_trailersize'] . "</li>";
        if (!empty($_POST['dump_trailerrentals'])) $email_content .= "<li>Rental Periods: " . $_POST['dump_trailerrentals'] . "</li>";
        if (!empty($_POST['dump_trailertons'])) $email_content .= "<li>Tons Allowed: " . $_POST['dump_trailertons'] . "</li>";
        if (!empty($_POST['dump_trailerprice'])) $email_content .= "<li>Starting Prices: " . $_POST['dump_trailerprice'] . "</li>";
        $email_content .= "</ul>";
    }

    // Junk Removal
    if (!empty($_POST['junk_removal'])) {
        $email_content .= "<h4>Junk Removal Services</h4>";
        $email_content .= "<ul>";
        foreach ((array)$_POST['junk_removal'] as $service) {
            $email_content .= "<li>" . $service . "</li>";
        }
        $email_content .= "</ul>";
    }
    $email_content .= "<br>";

    // Marketing
    $email_content .= "<h3>Marketing & SEO</h3>";
    $email_content .= "<p><strong>Working with SEO/Marketing Agency:</strong> " . $_POST['are_you_currently_working_with_an_seo_or_marketing_agency'] . "</p>";
    
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
    
    if (!empty($_POST['any_reviews_or_testimonials_youd_like_to_add_to_the_website'])) {
        $email_content .= "<p><strong>Reviews/Testimonials:</strong><br>" . nl2br($_POST['any_reviews_or_testimonials_youd_like_to_add_to_the_website']) . "</p>";
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
    $subject = 'Website VIP Form Submission - ' . sanitize_text_field($_POST['business_name']);
    $headers = array('Content-Type: text/html; charset=UTF-8');

    $sent = wp_mail($to, $subject, $email_content, $headers, $attachments);

    if ($sent) {
        wp_send_json_success(array('message' => 'Form submitted successfully'));
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
