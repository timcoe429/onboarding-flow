<?php
/**
 * Shortcode Handler for Docket Onboarding
 * 
 * This file handles the [docket_onboarding] shortcode output
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the onboarding form
 */
function docket_onboarding_render_shortcode($atts) {
    // Check if this is a success page
    if (isset($_GET['success']) && $_GET['success'] == '1') {
        // Load success page template
        require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/success-page.php';
        
        // Start output buffering
        ob_start();
        docket_render_success_page();
        return ob_get_clean();
    }
    
    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'style' => 'default',
        'start_step' => '1'
    ), $atts, 'docket_onboarding');
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="docket-onboarding" data-style="<?php echo esc_attr($atts['style']); ?>">
        
        <!-- STEP 1: PLAN SELECTION -->
        <div class="docket-step docket-step1 active" id="docket-step-1" data-step="1">
            <div class="docket-nav-container">
                <!-- Progress bar removed from nav, will be placed after header -->
            </div>
            
            <div class="docket-header">
                <h1>Which Docket Plan Do You Have?</h1>
                <p><em>Hint: You can check your billing if you are unsure. GROW plans are $200/per technician, and PRO plans are $250/per technician.</em></p>
            </div>

            <div class="docket-progress-container">
                <div class="docket-progress-bar">
                    <div class="docket-progress-fill"></div>
                </div>
            </div>

            <div class="docket-plans">
                <div class="docket-plan-card grow">
                    <div class="docket-plan-icon">G</div>
                    <h2 class="docket-plan-title">Grow</h2>
                    <ul class="docket-plan-features">
                        <li>Professional Website Design</li>
                        <li>Responsive Layout</li>
                        <li>Rank Math SEO Plugin</li>
                        <li>No changes after launch unless WebsiteVIP upgrade is purchased</li>
                    </ul>
                    <button class="docket-plan-btn">Get Started</button>
                </div>

                <div class="docket-plan-card pro">
                    <div class="docket-plan-badge">+ Local SEO</div>
                    <div class="docket-plan-icon">P</div>
                    <h2 class="docket-plan-title">Pro</h2>
                    <ul class="docket-plan-features">
                        <li>Professional Website Design</li>
                        <li>Responsive Layout</li>
                        <li>Rank Math SEO Plugin</li>
                        <li>Local SEO Blogs Included</li>
                        <li>No changes after launch unless WebsiteVIP upgraded is purchased</li>
                    </ul>
                    <button class="docket-plan-btn">Get Started</button>
                </div>
            </div>
        </div>

        <!-- STEP 2: CHECKLIST -->
        <div class="docket-step docket-step2" id="docket-step-2" data-step="2">
            <div class="docket-nav-container">
                <button class="docket-back-btn" data-target="1">← Back to Plans</button>
                <div class="docket-progress-container">
                    <div class="docket-progress-bar">
                        <div class="docket-progress-fill"></div>
                    </div>
                </div>
            </div>
            
            <div class="docket-content">
                <div class="docket-header">
                    <h2>Start Your Website Build</h2>
                    <p>Before kicking off your website build, make sure you've got everything on the checklist ready to go.</p>
                </div>

                <div class="docket-checklist">
                    <h3>Pre-Build Checklist</h3>
                    
                    <div class="docket-checklist-item" data-checkbox="1">
                        <div class="docket-checklist-checkbox"></div>
                        <div class="docket-checklist-content">
                            <h4>Website Domain</h4>
                            <p>Docket does not provide website domains (the URL your website will live at). If you don't have one, we recommend <strong>GoDaddy</strong>.</p>
                        </div>
                    </div>

                    <div class="docket-checklist-item" data-checkbox="2">
                        <div class="docket-checklist-checkbox"></div>
                        <div class="docket-checklist-content">
                            <h4>Company Logo and/or Company Branding</h4>
                            <p>You should have a logo file and/or company colors for us to use on your website.</p>
                        </div>
                    </div>

                    <div class="docket-checklist-item" data-checkbox="3">
                        <div class="docket-checklist-checkbox"></div>
                        <div class="docket-checklist-content">
                            <h4>Photos of Your Dumpsters, Recent Completed Jobs, Staff, etc.</h4>
                            <p>To make your customized template personalized, we recommend having photos of your dumpsters, recent jobs, team members, or anything else that showcases your work. These visuals make a big impact on your final design.</p>
                        </div>
                    </div>

                    <div class="docket-checklist-item" data-checkbox="4">
                        <div class="docket-checklist-checkbox"></div>
                        <div class="docket-checklist-content">
                            <h4>Company Email Address</h4>
                            <p>Planning to text your customers with a Docket Local Phone Number? <strong>You MUST have one of the following email addresses:</strong></p>
                            <ul>
                                <li>Gmail (example is yourdumpstercompany@gmail.com)</li>
                                <li>A Branded Email Address (example is support@yourdumpstercompany.com)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <p class="not-ready" style="text-align: center;">Not quite there yet? Come back when you're ready.</p>
                <button class="docket-ready-btn">I'M READY</button>

                <div class="docket-benefits">
                    <h3>Benefits of Your Docket Website</h3>
                    <div class="docket-benefits-grid">
                        <div class="docket-benefit-item">
                            <h4>Easy to Edit</h4>
                            <p>Drag and drop editing made easy! Our websites are created with WordPress and powered by the Elementor editor.</p>
                        </div>
                        <div class="docket-benefit-item">
                            <h4>10DLC Compliant</h4>
                            <p>Our websites meet Plivo's 10 DLC standards so you can get verified to text your customers through Docket with ease.</p>
                        </div>
                        <div class="docket-benefit-item">
                            <h4>DocketShop Ready</h4>
                            <p>Once you set up Online Booking in Docket, we'll handle adding DocketShop to your website for you.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: MANAGEMENT SELECTION -->
        <div class="docket-step docket-step3" id="docket-step-3" data-step="3">
            <div class="docket-nav-container">
                <button class="docket-back-btn" data-target="2">← Back to Checklist</button>
                <div class="docket-progress-container">
                    <div class="docket-progress-bar">
                        <div class="docket-progress-fill"></div>
                    </div>
                </div>
            </div>
            
            <div class="docket-content">
                <div class="docket-header">
                    <h2>Choose Your Website Management Plan</h2>
                </div>

                <div class="docket-plans">
                    <div class="docket-plan-card self-managed">
                        <div class="docket-plan-icon">📝</div>
                        <h2 class="docket-plan-title">Self-Managed Website</h2>
                        <p class="docket-plan-subtitle"><strong>Included with GROW and PRO plan</strong></p>
                        <ul class="docket-plan-features">
                            <li>Managed by you once launched</li>
                            <li>Access to help page with tutorials for edits</li>
                        </ul>
                        <button class="docket-plan-btn">I WILL MANAGE MY WEBSITE</button>
                    </div>

                    <div class="docket-plan-card website-vip">
                        <div class="docket-plan-badge">Recommended</div>
                        <div class="docket-plan-icon">⭐</div>
                        <h2 class="docket-plan-title">WebsiteVIP</h2>
                        <p class="docket-plan-subtitle"><strong>+$299/month</strong></p>
                        <ul class="docket-plan-features">
                            <li>Completely managed by the Docket Team</li>
                            <li>Unlimited edits</li>
                            <li>AI Chat Bot, On-Page SEO, Location Pages, Analytics, and more</li>
                            <li><strong>You'll be contacted to discuss the WebsiteVIP plan upgrade after you submit the form on the next page.</strong></li>
                        </ul>
                        <button class="docket-plan-btn">I WANT WEBSITEVIP</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 4: BUILD TYPE SELECTION -->
        <div class="docket-step docket-step4" id="docket-step-4" data-step="4">
            <div class="docket-nav-container">
                <button class="docket-back-btn" data-target="3">← Back to Management</button>
                <div class="docket-progress-container">
                    <div class="docket-progress-bar">
                        <div class="docket-progress-fill"></div>
                    </div>
                </div>
            </div>
            
            <div class="docket-content">
                <div class="docket-header">
                    <h2>Select Your Website Build Type</h2>
                    <p class="not-ready">If you don't have a logo, product images, or brand colors, we recommend the Fast Build option OR waiting to start your website build.</p>
                </div>

                <div class="docket-plans">
                    <div class="docket-plan-card fast-build">
                        <div class="docket-plan-icon">⚡</div>
                        <h2 class="docket-plan-title">Fast Build</h2>
                        <ul class="docket-plan-features">
                            <li>Ready for Launch in 3 Days</li>
                            <li>Zero Revisions Before Launch</li>
                            <li>Stock Content + Your Logo + Your Dumpster Content</li>
                        </ul>
                        <button class="docket-plan-btn">Select Fast Build</button>
                    </div>

                    <div class="docket-plan-card standard-build">
                        <div class="docket-plan-badge">Recommended</div>
                        <div class="docket-plan-icon">🏗️</div>
                        <h2 class="docket-plan-title">Standard Build</h2>
                        <ul class="docket-plan-features">
                            <li>Ready for Launch in 21-30 Days</li>
                            <li>Website Review Included</li>
                            <li>Built with Stock Content & Your Content + Your Logo + Your Dumpster Content</li>
                        </ul>
                        <button class="docket-plan-btn">Select Standard Build</button>
                    </div>
                </div>

                <div class="docket-warning">
                    <div class="docket-warning-box">
                        <p><strong>Important:</strong> If you select Fast Build, you will be in charge of customizing your selected template once the website is launched, and will not get a revision round before the website is launched.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <?php
    // Return the buffered content
    return ob_get_clean();
}