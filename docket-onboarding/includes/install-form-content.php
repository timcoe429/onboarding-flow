<?php
/**
 * Quick installation script for Form Content Manager
 * Run this once to create the table and populate default content
 */

// Load WordPress
require_once('../../../../wp-load.php');

// Check if user has admin privileges
if (!current_user_can('manage_options')) {
    die('Access denied');
}

global $wpdb;
$table_name = $wpdb->prefix . 'docket_form_content';

// Drop existing table to start fresh
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// Create the table
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

// Insert default content
$default_content = array(
    // Fast Build - Step 1
    array('fast-build', 1, 'form_title', 'Fast Build Website'),
    array('fast-build', 1, 'form_subtitle', 'Let\'s start by reviewing the terms and checking your WordPress experience'),
    array('fast-build', 1, 'what_youre_getting', 'A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, delivered in just 3 business days with zero revisions before launch.'),
    array('fast-build', 1, 'timeline', 'Your website will be ready in 3 days. Stock content and images will be used to meet this expedited timeline.'),
    array('fast-build', 1, 'what_we_need', '<li>WordPress/Elementor experience level</li><li>Basic business information and branding</li><li>Service area details</li><li>Immediate payment to begin work</li>'),
    array('fast-build', 1, 'important_notes', '<li>Zero revisions before launch - customization is your responsibility</li><li>You\'ll need WordPress/Elementor knowledge to customize</li><li>Changes after launch are charged at $175/hour</li><li>Rank Math SEO plugin included</li><li>Additional plugin installation not permitted</li>'),
    
    // Fast Build - Step 3
    array('fast-build', 3, 'form_title', 'Fast Build Template Information'),
    array('fast-build', 3, 'form_subtitle', 'Important information about your Fast Build template'),
    array('fast-build', 3, 'info_title', 'What\'s Included in Fast Build'),
    array('fast-build', 3, 'stock_content', 'Your website will be built with placeholder content and stock images. You\'ll need to customize all text and images after launch.'),
    array('fast-build', 3, 'no_revisions', 'Fast Build includes zero revision rounds. What you see in the template preview is what you\'ll receive.'),
    array('fast-build', 3, 'self_customization', 'You\'ll receive WordPress/Elementor access to customize your site. Make sure you\'re comfortable with these tools.'),
    array('fast-build', 3, 'turnaround', 'Your website will be ready to launch within 3 business days of payment and domain setup.'),
    array('fast-build', 3, 'acceptance_text', 'I understand the Fast Build limitations and am ready to proceed'),
    
    // Standard Build - Step 1
    array('standard-build', 1, 'form_title', 'Terms & Conditions'),
    array('standard-build', 1, 'form_subtitle', 'Please review and accept our terms'),
    array('standard-build', 1, 'what_youre_getting', 'A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, including SEO optimization and mobile responsiveness.'),
    array('standard-build', 1, 'timeline', 'Your website will be completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.'),
    array('standard-build', 1, 'what_we_need', '<li>Business information and branding materials</li><li>Service area details</li><li>Photos and content for your website</li><li>Dumpster rental/junk removal services information</li>'),
    array('standard-build', 1, 'whats_included', '<li>Rank Math SEO plugin included</li><li>Additional plugin installation not permitted</li>'),
    array('standard-build', 1, 'post_launch_services', 'After launch, website access for editing and Rank Math SEO plugin configuration is shared. If you\'d like to have the Docket Team work on your website, you\'ll need to upgrade to the WebsiteVIP plan.'),
    
    // Standard Build - Step 3
    array('standard-build', 3, 'form_title', 'Website Template Information'),
    array('standard-build', 3, 'form_subtitle', 'You will now get to select your website template!'),
    array('standard-build', 3, 'info_title', 'Important Information About Your Template'),
    array('standard-build', 3, 'customized_website', 'You will have the ability to customize your website beyond the template design once the website is launched and self-managed.'),
    array('standard-build', 3, 'sections_pages', 'The template preview shows exactly what\'s available — we do not add additional pages or sections beyond what is shown.'),
    array('standard-build', 3, 'revisions', 'We limit revisions to 1 round. You have 3 full days to review and request changes within scope. Your website is self-managed post-launch unless you upgrade to WebsiteVIP.'),
    array('standard-build', 3, 'additional_customizations', 'Out-of-scope customizations are charged at $175/hour.'),
    array('standard-build', 3, 'acceptance_text', 'I understand the above clarifications on the process'),
    
    // Website VIP - Step 1
    array('website-vip', 1, 'form_title', 'Website with WebsiteVIP Terms & Conditions'),
    array('website-vip', 1, 'form_subtitle', 'Please review and accept our terms for your WordPress experience with WebsiteVIP.'),
    array('website-vip', 1, 'what_youre_getting', 'A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, with ongoing management by the Docket team for $299/month. You will not receive edit access to your website once it launches.'),
    array('website-vip', 1, 'timeline', 'Your website will be completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.'),
    array('website-vip', 1, 'what_we_need', '<li>Business information and branding materials</li><li>Service area details and pricing</li><li>Photos and content for your website</li><li>Timely feedback during the review process</li>'),
    array('website-vip', 1, 'vip_benefits', '<li>Completely managed by the Docket Team</li><li>Unlimited edits</li><li>AI Chat Bot, On-Page SEO, Location Pages, Analytics, and more</li><li>You\'ll be contacted to discuss the WebsiteVIP plan upgrade after you submit this form</li>'),
    
    // Website VIP - Step 3
    array('website-vip', 3, 'form_title', 'Website Template Information'),
    array('website-vip', 3, 'form_subtitle', 'You will now get to select your website template!'),
    array('website-vip', 3, 'intro_text', 'We will customize your website based on the template you choose, and the information you provide.'),
    array('website-vip', 3, 'customized_website', 'Please note that this is not a full custom website, it is a pre-built website theme that we add your content and images to. We do not do any additional design work or custom requests that are not already built into the website theme. This includes but isn\'t limited to logo design, product image design, adding new pages, adding new sections, and more.'),
    array('website-vip', 3, 'sections_pages', 'Once you choose a template, you\'ll be able to preview the pages and sections included. Please note: the template preview shows exactly what\'s available — we do not add additional pages or sections beyond what is shown.'),
    array('website-vip', 3, 'revisions', 'Since the website is considered a small website by industry standards, (less than 10 pages) we limit our revision round to 1. You will be notified once your website is ready to be reviewed.'),
    array('website-vip', 3, 'review_period', 'Once your revision round is done (You have 3 full days to fully review the site and add in any changes you see within the scope and theme that you selected) we will make the changes you request that are within the scope we can provide, and then your website will be ready to be pushed live. <strong>Once your website is live, you\'ll be on our WebsiteVIP plan where our team manages edits to your website.</strong>'),
    array('website-vip', 3, 'charges', 'If you want our team to provide any customizations outside of scope and not included in the theme you chose, such as adding new pages, or if you would like our team to implement edits that you do not include in your review period, will be charged $175/hr for our team to execute these changes during the website build.'),
    array('website-vip', 3, 'refund_policy', 'The amount paid is only refundable if we have not fulfilled our obligations to deliver the work required under the agreement. The total paid is not refundable if the development work has been started and you terminate the contract or work through no fault of ours, or if you accept ownership of the project transferred to you. Once you upgrade to WebsiteVIP, our team can make edits to your website for you as included in the plan.'),
    array('website-vip', 3, 'acceptance_text', 'I understand the above clarifications on the process')
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
