<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Status - <?php echo esc_html($project->business_name); ?></title>
    
    <!-- Load WordPress styles -->
    <?php wp_head(); ?>
    
    <!-- Clean, professional portal styles -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            color: #333;
        }
        
        .portal-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .portal-header {
            position: relative;
            text-align: center;
            margin-bottom: 48px;
        }
        
        /* Refresh Button */
        .refresh-btn {
            position: absolute;
            top: 0;
            right: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
        }
        
        .refresh-btn:hover {
            background: #0052a3;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
        }
        
        .refresh-btn:active {
            transform: translateY(0);
        }
        
        .refresh-btn:disabled {
            background: #999;
            cursor: not-allowed;
            transform: none;
        }
        
        .refresh-btn.loading {
            background: #999;
            cursor: wait;
        }
        
        .refresh-icon {
            font-size: 16px;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .refresh-btn.loading .refresh-icon {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .refresh-text {
            display: inline-block;
        }
        
        .refresh-btn.loading .refresh-text {
            display: none;
        }
        
        /* Status Message for Refresh Feedback */
        .refresh-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease;
        }
        
        .refresh-message.success {
            background: #00a862;
            color: white;
        }
        
        .refresh-message.error {
            background: #dc3545;
            color: white;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .portal-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 8px 0;
        }
        
        .portal-subtitle {
            color: #666;
            font-size: 18px;
            margin: 0;
        }
        
        /* Horizontal Progress Tracker */
        .progress-container {
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            margin-bottom: 32px;
        }
        
        .progress-tracker {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 auto;
            max-width: 900px;
        }
        
        /* Progress Line */
        .progress-line {
            position: absolute;
            top: 40px;
            left: 10%;
            right: 10%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            z-index: 1;
        }
        
        .progress-line-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: #00a862;
            border-radius: 4px;
            transition: width 0.6s ease;
        }
        
        /* Progress Steps */
        .progress-step {
            position: relative;
            flex: 1;
            text-align: center;
            z-index: 2;
        }
        
        .step-number {
            width: 80px;
            height: 80px;
            margin: 0 auto 16px;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            color: #999;
            border: 4px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .progress-step.completed .step-number {
            background: #00a862;
            color: white;
            border-color: #00a862;
        }
        
        .progress-step.active .step-number {
            background: #0066cc;
            color: white;
            border-color: #0066cc;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 102, 204, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(0, 102, 204, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 102, 204, 0); }
        }
        
        .step-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0 0 4px 0;
        }
        
        .step-subtitle {
            font-size: 13px;
            color: #666;
            margin: 0;
        }
        
        /* Current Status Message */
        .status-message {
            background: #e3f2fd;
            border: 2px solid #0066cc;
            border-radius: 16px;
            padding: 24px;
            margin: 32px auto 0;
            max-width: 600px;
            text-align: center;
        }
        
        .status-message.completed {
            background: #e8f5e9;
            border-color: #00a862;
        }
        
        .status-icon {
            font-size: 48px;
            margin-bottom: 8px;
        }
        
        .status-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 8px 0;
        }
        
        .status-description {
            font-size: 16px;
            color: #666;
            margin: 0;
        }
        
        .status-timestamp {
            font-size: 14px;
            color: #999;
            margin-top: 12px;
        }
        
        /* Project Details */
        .project-details {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }
        
        .detail-item {
            text-align: center;
        }
        
        .detail-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 4px;
        }
        
        .detail-value {
            font-size: 18px;
            color: #333;
            font-weight: 600;
        }
        
        /* Contact Section */
        .contact-section {
            background: #f9f9f9;
            border-radius: 16px;
            padding: 32px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        
        .contact-section h3 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin: 0 0 12px 0;
        }
        
        .contact-section p {
            font-size: 16px;
            color: #666;
            margin: 0;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .portal-header {
                padding-top: 50px;
            }
            
            .refresh-btn {
                top: -10px;
                right: 0;
                padding: 8px 12px;
                font-size: 12px;
            }
            
            .refresh-text {
                display: none;
            }
            
            .progress-container {
                padding: 32px 20px;
            }
            
            .progress-tracker {
                flex-direction: column;
                gap: 32px;
            }
            
            .progress-line {
                top: 40px;
                left: 50%;
                right: auto;
                width: 4px;
                height: calc(100% - 80px);
                transform: translateX(-50%);
            }
            
            .step-number {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .step-title {
                font-size: 14px;
            }
            
            .step-subtitle {
                font-size: 12px;
            }
            
            .refresh-message {
                top: 10px;
                right: 10px;
                left: 10px;
                width: auto;
            }
        }
    </style>
</head>

<body>
    <div class="portal-container">
        <!-- Header -->
        <div class="portal-header">
            <button id="refresh-status-btn" class="refresh-btn" title="Refresh Project Status">
                <span class="refresh-icon">🔄</span>
                <span class="refresh-text">Refresh Status</span>
            </button>
            <h1><?php echo esc_html($project->business_name); ?></h1>
            <p class="portal-subtitle">Website Development Progress</p>
        </div>

        <?php
        // Map detailed Trello steps to simplified client-facing stages
        $stage_mapping = array(
            'docket_team' => 'in_build',
            'qa' => 'in_build',
            'ready_to_send' => 'in_build',
            'waiting_review_scheduling' => 'client_review',
            'client_reviewing' => 'client_review',
            'edits_to_complete' => 'editing',
            'review_edits_completed' => 'launch_scheduled',
            'pre_launch' => 'launch_scheduled',
            'ready_for_launch' => 'launch_scheduled',
            'web_complete_grow' => 'complete',
            'web_complete_pro' => 'complete'
        );
        
        $simplified_stages = array(
            'in_build' => array(
                'number' => 1,
                'title' => 'In Build',
                'subtitle' => 'Creating your website',
                'icon' => '🔨',
                'status_title' => 'Your website is being built!',
                'status_desc' => 'Our team is actively working on creating your website.'
            ),
            'client_review' => array(
                'number' => 2,
                'title' => 'Client Review',
                'subtitle' => 'Ready for your feedback',
                'icon' => '👀',
                'status_title' => 'Your website is ready for review!',
                'status_desc' => 'Please review your website and provide any feedback.'
            ),
            'editing' => array(
                'number' => 3,
                'title' => 'Editing',
                'subtitle' => 'Implementing changes',
                'icon' => '✏️',
                'status_title' => 'Making your requested changes',
                'status_desc' => 'We\'re implementing the edits and improvements you\'ve requested.'
            ),
            'launch_scheduled' => array(
                'number' => 4,
                'title' => 'Launch Scheduled',
                'subtitle' => 'Preparing to go live',
                'icon' => '🚀',
                'status_title' => 'Your website is ready to launch!',
                'status_desc' => 'Final preparations are complete and your site is ready to go live.'
            ),
            'complete' => array(
                'number' => 5,
                'title' => 'Complete',
                'subtitle' => 'Website is live!',
                'icon' => '🎉',
                'status_title' => 'Your website is live!',
                'status_desc' => 'Congratulations! Your website is now live and accessible to your customers.'
            )
        );
        
        // Get current simplified stage
        $current_step = $project->current_step;
        $current_stage = $stage_mapping[$current_step] ?? 'in_build';
        $current_stage_number = $simplified_stages[$current_stage]['number'];
        
        // Calculate progress percentage
        $progress_percentage = ($current_stage_number - 1) * 25;
        if ($current_stage === 'complete') {
            $progress_percentage = 100;
        }
        ?>

        <!-- Progress Tracker -->
        <div class="progress-container">
            <div class="progress-tracker">
                <!-- Progress Line -->
                <div class="progress-line">
                    <div class="progress-line-fill" style="width: <?php echo $progress_percentage; ?>%;"></div>
                </div>
                
                <!-- Progress Steps -->
                <?php foreach ($simplified_stages as $stage_key => $stage): 
                    $is_completed = $stage['number'] < $current_stage_number;
                    $is_active = $stage_key === $current_stage;
                    $step_class = $is_completed ? 'completed' : ($is_active ? 'active' : '');
                ?>
                <div class="progress-step <?php echo $step_class; ?>">
                    <div class="step-number">
                        <?php echo $is_completed ? '✓' : $stage['number']; ?>
                    </div>
                    <h3 class="step-title"><?php echo $stage['title']; ?></h3>
                    <p class="step-subtitle"><?php echo $stage['subtitle']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Current Status Message -->
            <?php $current_info = $simplified_stages[$current_stage]; ?>
            <div class="status-message <?php echo $current_stage === 'complete' ? 'completed' : ''; ?>">
                <div class="status-icon"><?php echo $current_info['icon']; ?></div>
                <h2 class="status-title"><?php echo $current_info['status_title']; ?></h2>
                <p class="status-description"><?php echo $current_info['status_desc']; ?></p>
                <?php if ($project->updated_at): ?>
                    <p class="status-timestamp">Last updated: <?php echo date('F j, Y \a\t g:i A', strtotime($project->updated_at)); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Details -->
        <div class="project-details">
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Project Type</div>
                    <div class="detail-value"><?php echo esc_html(ucwords(str_replace('_', ' ', $project->form_type))); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Started</div>
                    <div class="detail-value"><?php echo date('M j, Y', strtotime($project->created_at)); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Project ID</div>
                    <div class="detail-value">#<?php echo strtoupper(substr($project->client_uuid, 0, 8)); ?></div>
                </div>
                <?php if ($project->site_url): ?>
                <div class="detail-item">
                    <div class="detail-label">Website URL</div>
                    <div class="detail-value"><a href="<?php echo esc_url($project->site_url); ?>" target="_blank">View Site</a></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="contact-section">
            <h3>Need Help?</h3>
            <p>If you have any questions about your project, please reply to your project notification email<br>or contact our support team at <a href="mailto:support@yourdocket.com" style="color: #185fb0; text-decoration: none;">support@yourdocket.com</a> for assistance.</p>
        </div>
    </div>

    <script>
    (function() {
        const refreshBtn = document.getElementById('refresh-status-btn');
        const clientUuid = '<?php echo esc_js($project->client_uuid); ?>';
        
        if (!refreshBtn) return;
        
        refreshBtn.addEventListener('click', function() {
            // Disable button and show loading state
            refreshBtn.disabled = true;
            refreshBtn.classList.add('loading');
            refreshBtn.querySelector('.refresh-text').textContent = 'Refreshing...';
            
            // Make AJAX request
            const formData = new FormData();
            formData.append('action', 'docket_refresh_project_status');
            formData.append('client_uuid', clientUuid);
            
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showMessage('Status updated successfully!', 'success');
                    
                    // Reload page after 1 second to show updated status
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    showMessage(data.data?.message || 'Failed to refresh status', 'error');
                    
                    // Re-enable button
                    refreshBtn.disabled = false;
                    refreshBtn.classList.remove('loading');
                    refreshBtn.querySelector('.refresh-text').textContent = 'Refresh Status';
                }
            })
            .catch(error => {
                console.error('Refresh error:', error);
                showMessage('An error occurred. Please try again.', 'error');
                
                // Re-enable button
                refreshBtn.disabled = false;
                refreshBtn.classList.remove('loading');
                refreshBtn.querySelector('.refresh-text').textContent = 'Refresh Status';
            });
        });
        
        function showMessage(text, type) {
            // Remove existing message if any
            const existing = document.querySelector('.refresh-message');
            if (existing) {
                existing.remove();
            }
            
            // Create new message
            const message = document.createElement('div');
            message.className = 'refresh-message ' + type;
            message.textContent = text;
            document.body.appendChild(message);
            
            // Auto-remove after 3 seconds (unless it's a success message that will reload)
            if (type === 'error') {
                setTimeout(function() {
                    message.remove();
                }, 3000);
            }
        }
    })();
    </script>

    <?php wp_footer(); ?>
</body>
</html> 