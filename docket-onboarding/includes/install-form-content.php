<?php
/**
 * Simple installation script for Form Content Manager
 */

require_once('../../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied');
}

global $wpdb;
$table_name = $wpdb->prefix . 'docket_form_content';

// Drop and recreate table
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
    id int(11) NOT NULL AUTO_INCREMENT,
    form_type varchar(50) NOT NULL,
    step_number int(3) NOT NULL,
    content_key varchar(100) NOT NULL,
    content_value longtext,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_content (form_type, step_number, content_key)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Insert default content - ONE FIELD PER STEP
$default_content = array(
    // Fast Build - Step 1
    array('fast-build', 1, 'content', '<h2>Fast Build Website</h2>
<p>Let\'s start by reviewing the terms and checking your WordPress experience</p>

<div class="terms-box">
    <div class="terms-content">
        <div class="terms-section">
            <h5>What You\'re Getting</h5>
            <p>A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, delivered in just 3 business days with zero revisions before launch.</p>
        </div>
        
        <div class="terms-section">
            <h5>Timeline</h5>
            <p>Your website will be ready in 3 days. Stock content and images will be used to meet this expedited timeline.</p>
        </div>
        
        <div class="terms-section">
            <h5>What We Need From You</h5>
            <ul>
                <li>WordPress/Elementor experience level</li>
                <li>Basic business information and branding</li>
                <li>Service area details</li>
                <li>Immediate payment to begin work</li>
            </ul>
        </div>
        
        <div class="terms-section">
            <h5>Important Notes</h5>
            <ul>
                <li>Zero revisions before launch - customization is your responsibility</li>
                <li>You\'ll need WordPress/Elementor knowledge to customize</li>
                <li>Changes after launch are charged at $175/hour</li>
                <li>Rank Math SEO plugin included</li>
                <li>Additional plugin installation not permitted</li>
            </ul>
        </div>
    </div>
</div>'),
    
    // Fast Build - Step 3
    array('fast-build', 3, 'content', '<h2>Fast Build Template Information</h2>
<p>Important information about your Fast Build template</p>

<div class="info-box">
    <h4>What\'s Included in Fast Build</h4>
    
    <div class="info-section">
        <h5>Stock Content Only</h5>
        <p>Your website will be built with placeholder content and stock images. You\'ll need to customize all text and images after launch.</p>
    </div>

    <div class="info-section">
        <h5>No Revisions</h5>
        <p>Fast Build includes zero revision rounds. What you see in the template preview is what you\'ll receive.</p>
    </div>

    <div class="info-section">
        <h5>Self-Customization Required</h5>
        <p>You\'ll receive WordPress/Elementor access to customize your site. Make sure you\'re comfortable with these tools.</p>
    </div>

    <div class="info-section">
        <h5>3-Day Turnaround</h5>
        <p>Your website will be ready to launch within 3 business days of payment and domain setup.</p>
    </div>
</div>'),
    
    // Standard Build - Step 1
    array('standard-build', 1, 'content', '<h2>Terms & Conditions</h2>
<p>Please review and accept our terms</p>

<div class="terms-box">
    <div class="terms-content">
        <div class="terms-section">
            <h5>What You\'re Getting</h5>
            <p>A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, including SEO optimization and mobile responsiveness.</p>
        </div>
        
        <div class="terms-section">
            <h5>Timeline</h5>
            <p>Your website will be completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.</p>
        </div>
        
        <div class="terms-section">
            <h5>What We Need From You</h5>
            <ul>
                <li>Business information and branding materials</li>
                <li>Service area details</li>
                <li>Photos and content for your website</li>
                <li>Dumpster rental/junk removal services information</li>
            </ul>
        </div>
        
        <div class="terms-section">
            <h5>What\'s Included</h5>
            <ul>
                <li>Rank Math SEO plugin included</li>
                <li>Additional plugin installation not permitted</li>
            </ul>
        </div>
        
        <div class="terms-section">
            <h5>Post-Launch Services</h5>
            <p>After launch, website access for editing and Rank Math SEO plugin configuration is shared. If you\'d like to have the Docket Team work on your website, you\'ll need to upgrade to the WebsiteVIP plan.</p>
        </div>
    </div>
</div>'),
    
    // Standard Build - Step 3
    array('standard-build', 3, 'content', '<h2>Website Template Information</h2>
<p>You will now get to select your website template!</p>

<div class="info-box">
    <h4>Important Information About Your Template</h4>
    
    <div class="info-section">
        <h5>Customized Website Information</h5>
        <p>You will have the ability to customize your website beyond the template design once the website is launched and self-managed.</p>
    </div>

    <div class="info-section">
        <h5>Sections & Pages Included</h5>
        <p>The template preview shows exactly what\'s available — we do not add additional pages or sections beyond what is shown.</p>
    </div>

    <div class="info-section">
        <h5>Revisions to Template</h5>
        <p>We limit revisions to 1 round. You have 3 full days to review and request changes within scope. Your website is self-managed post-launch unless you upgrade to WebsiteVIP.</p>
    </div>

    <div class="info-section">
        <h5>Additional Customizations</h5>
        <p>Out-of-scope customizations are charged at $175/hour.</p>
    </div>
</div>'),
    
    // Website VIP - Step 1
    array('website-vip', 1, 'content', '<h2>Website with WebsiteVIP Terms & Conditions</h2>
<p>Please review and accept our terms for your WordPress experience with WebsiteVIP.</p>

<div class="terms-box">
    <div class="terms-content">
        <div class="terms-section">
            <h5>What You\'re Getting with WebsiteVIP</h5>
            <p>A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, with ongoing management by the Docket team for $299/month. You will not receive edit access to your website once it launches.</p>
        </div>
        
        <div class="terms-section">
            <h5>Timeline</h5>
            <p>Your website will be completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.</p>
        </div>
        
        <div class="terms-section">
            <h5>What We Need From You</h5>
            <ul>
                <li>Business information and branding materials</li>
                <li>Service area details and pricing</li>
                <li>Photos and content for your website</li>
                <li>Timely feedback during the review process</li>
            </ul>
        </div>
        
        <div class="terms-section">
            <h5>WebsiteVIP Benefits</h5>
            <ul>
                <li>Completely managed by the Docket Team</li>
                <li>Unlimited edits</li>
                <li>AI Chat Bot, On-Page SEO, Location Pages, Analytics, and more</li>
                <li>You\'ll be contacted to discuss the WebsiteVIP plan upgrade after you submit this form</li>
            </ul>
        </div>
    </div>
</div>'),
    
    // Website VIP - Step 3
    array('website-vip', 3, 'content', '<h2>Website Template Information</h2>
<p>You will now get to select your website template!</p>

<div class="info-box">
    <p>We will customize your website based on the template you choose, and the information you provide.</p>
    
    <div class="info-section">
        <h5>Customized Website Information</h5>
        <p>Please note that this is not a full custom website, it is a pre-built website theme that we add your content and images to. We do not do any additional design work or custom requests that are not already built into the website theme. This includes but isn\'t limited to logo design, product image design, adding new pages, adding new sections, and more.</p>
    </div>

    <div class="info-section">
        <h5>Sections & Pages Included per Template</h5>
        <p>Once you choose a template, you\'ll be able to preview the pages and sections included. Please note: the template preview shows exactly what\'s available — we do not add additional pages or sections beyond what is shown.</p>
    </div>

    <div class="info-section">
        <h5>Revisions to Template</h5>
        <p>Since the website is considered a small website by industry standards, (less than 10 pages) we limit our revision round to 1. You will be notified once your website is ready to be reviewed.</p>
    </div>

    <div class="info-section">
        <h5>Review Period</h5>
        <p>Once your revision round is done (You have 3 full days to fully review the site and add in any changes you see within the scope and theme that you selected) we will make the changes you request that are within the scope we can provide, and then your website will be ready to be pushed live. <strong>Once your website is live, you\'ll be on our WebsiteVIP plan where our team manages edits to your website.</strong></p>
    </div>

    <div class="info-section">
        <h5>Charges for Additional/Out of Scope Customizations</h5>
        <p>If you want our team to provide any customizations outside of scope and not included in the theme you chose, such as adding new pages, or if you would like our team to implement edits that you do not include in your review period, will be charged $175/hr for our team to execute these changes during the website build.</p>
    </div>

    <div class="info-section">
        <p>The amount paid is only refundable if we have not fulfilled our obligations to deliver the work required under the agreement. The total paid is not refundable if the development work has been started and you terminate the contract or work through no fault of ours, or if you accept ownership of the project transferred to you.</p>
        <p>Once you upgrade to WebsiteVIP, our team can make edits to your website for you as included in the plan.</p>
    </div>
</div>')
);

// Insert all default content
$inserted = 0;
foreach ($default_content as $content) {
    $result = $wpdb->insert(
        $table_name,
        array(
            'form_type' => $content[0],
            'step_number' => $content[1],
            'content_key' => $content[2],
            'content_value' => $content[3]
        ),
        array('%s', '%d', '%s', '%s')
    );
    
    if ($result) {
        $inserted++;
    }
}

echo "<h1>Form Content Table Installation</h1>";
echo "<p>Table created: {$table_name}</p>";
echo "<p>Records inserted: {$inserted}</p>";
echo "<p>Installation complete!</p>";
echo "<p><a href='" . admin_url('tools.php?page=docket-form-content') . "'>Go to Form Content Manager</a></p>";
