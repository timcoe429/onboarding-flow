<?php
/**
 * Restore the original content
 */

require_once('../../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied');
}

global $wpdb;
$table_name = $wpdb->prefix . 'docket_form_content';

// Restore Fast Build Step 1
$original_content = '<h2>Fast Build Website</h2>
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
</div>';

$result = $wpdb->replace(
    $table_name,
    array(
        'form_type' => 'fast-build',
        'step_number' => 1,
        'content_key' => 'content',
        'content_value' => $original_content
    ),
    array('%s', '%d', '%s', '%s')
);

echo "<h1>Content Restored!</h1>";
echo "<p>Fast Build Step 1 content has been restored to original.</p>";
echo "<p><a href='" . admin_url('tools.php?page=docket-form-content') . "'>Go back to Form Content Manager</a></p>";
