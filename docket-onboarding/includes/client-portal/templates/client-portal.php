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
            background: #fafbfc;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            color: #172b4d;
        }
        
        .portal-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .portal-header {
            background: white;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid #dfe1e6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        
        .portal-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #172b4d;
            margin: 0 0 8px 0;
        }
        
        .portal-subtitle {
            color: #5e6c84;
            font-size: 16px;
            margin: 0 0 24px 0;
        }
        
        .project-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }
        
        .meta-item {
            background: #f4f5f7;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #dfe1e6;
        }
        
        .meta-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #5e6c84;
            margin-bottom: 4px;
        }
        
        .meta-value {
            font-size: 14px;
            color: #172b4d;
            font-weight: 500;
        }
        
        .timeline-container {
            background: white;
            border-radius: 12px;
            padding: 32px;
            border: 1px solid #dfe1e6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        
        .timeline-header {
            margin-bottom: 32px;
        }
        
        .timeline-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: #172b4d;
            margin: 0 0 8px 0;
        }
        
        .timeline-subtitle {
            color: #5e6c84;
            font-size: 14px;
            margin: 0;
        }
        
        .timeline-steps {
            display: grid;
            gap: 16px;
        }
        
        .timeline-step {
            display: flex;
            align-items: center;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dfe1e6;
            transition: all 0.2s ease;
        }
        
        .timeline-step.completed {
            background: #e3fcef;
            border-color: #36b37e;
        }
        
        .timeline-step.in_progress {
            background: #deebff;
            border-color: #0052cc;
        }
        
        .timeline-step.pending {
            background: #f4f5f7;
            border-color: #dfe1e6;
        }
        
        .step-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 14px;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .step-icon.completed {
            background: #36b37e;
            color: white;
        }
        
        .step-icon.in_progress {
            background: #0052cc;
            color: white;
        }
        
        .step-icon.pending {
            background: #b3bac5;
            color: white;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-size: 16px;
            font-weight: 600;
            color: #172b4d;
            margin: 0 0 4px 0;
        }
        
        .step-description {
            font-size: 14px;
            color: #5e6c84;
            margin: 0;
        }
        
        .step-date {
            font-size: 12px;
            color: #5e6c84;
            margin-top: 4px;
        }
        
        .current-status {
            background: #e3fcef;
            border: 1px solid #36b37e;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: center;
        }
        
        .status-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .status-title {
            font-size: 18px;
            font-weight: 600;
            color: #172b4d;
            margin: 0 0 4px 0;
        }
        
        .status-description {
            font-size: 14px;
            color: #5e6c84;
            margin: 0;
        }
        
        .contact-info {
            background: #f4f5f7;
            border-radius: 8px;
            padding: 20px;
            margin-top: 24px;
            border: 1px solid #dfe1e6;
        }
        
        .contact-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: #172b4d;
            margin: 0 0 12px 0;
        }
        
        .contact-info p {
            font-size: 14px;
            color: #5e6c84;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="portal-container">
        <!-- Header -->
        <div class="portal-header">
            <h1>Project Dashboard</h1>
            <p class="portal-subtitle">Track your website development progress in real-time</p>
            
            <div class="project-meta">
                <div class="meta-item">
                    <div class="meta-label">Business Name</div>
                    <div class="meta-value"><?php echo esc_html($project->business_name); ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Project Type</div>
                    <div class="meta-value"><?php echo esc_html(ucwords(str_replace('-', ' ', $project->form_type))); ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Started</div>
                    <div class="meta-value"><?php echo date('M j, Y', strtotime($project->created_at)); ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Project ID</div>
                    <div class="meta-value"><?php echo substr($project->client_uuid, 0, 8); ?></div>
                </div>
            </div>
        </div>

        <!-- Current Status -->
        <?php
        $current_step = $project->current_step;
        $status_info = array(
            'docket_team' => array('icon' => '📋', 'title' => 'Project Received', 'desc' => 'Your project has been received and is in our queue'),
            'qa' => array('icon' => '🔍', 'title' => 'Quality Assurance', 'desc' => 'Our team is reviewing and setting up your project'),
            'ready_to_send' => array('icon' => '📤', 'title' => 'Ready to Send', 'desc' => 'Your website build is ready for initial review'),
            'waiting_review_scheduling' => array('icon' => '📅', 'title' => 'Scheduling Review', 'desc' => 'We\'re scheduling your review session'),
            'client_reviewing' => array('icon' => '👀', 'title' => 'Client Review', 'desc' => 'Your website is ready for your review and feedback'),
            'edits_to_complete' => array('icon' => '✏️', 'title' => 'Making Edits', 'desc' => 'We\'re implementing your requested changes'),
            'review_edits_completed' => array('icon' => '✅', 'title' => 'Edits Complete', 'desc' => 'Your requested changes have been completed'),
            'pre_launch' => array('icon' => '🚦', 'title' => 'Pre-Launch', 'desc' => 'Final preparations and testing before launch'),
            'ready_for_launch' => array('icon' => '🚀', 'title' => 'Ready for Launch', 'desc' => 'Your website is ready to go live!'),
            'web_complete_grow' => array('icon' => '🌱', 'title' => 'Complete - Grow/Legacy', 'desc' => 'Project completed on Grow or Legacy plan'),
            'web_complete_pro' => array('icon' => '💼', 'title' => 'Complete - Pro/ServiceCore', 'desc' => 'Project completed on Pro or ServiceCore plan')
        );
        $current_status = $status_info[$current_step] ?? $status_info['docket_team'];
        ?>
        
        <div class="current-status">
            <div class="status-icon"><?php echo $current_status['icon']; ?></div>
            <h3 class="status-title"><?php echo $current_status['title']; ?></h3>
            <p class="status-description"><?php echo $current_status['desc']; ?></p>
        </div>

        <!-- Timeline -->
        <div class="timeline-container">
            <div class="timeline-header">
                <h2>Project Timeline</h2>
                <p class="timeline-subtitle">Follow your website's journey from start to launch</p>
            </div>
            
            <div class="timeline-steps">
                <?php
                $step_details = array(
                    'docket_team' => array('title' => 'Docket Team', 'desc' => 'Project received and queued for development'),
                    'qa' => array('title' => 'Quality Assurance', 'desc' => 'Initial review and project setup'),
                    'ready_to_send' => array('title' => 'Ready to Send', 'desc' => 'Website build completed and ready for review'),
                    'waiting_review_scheduling' => array('title' => 'Review Scheduling', 'desc' => 'Coordinating review session with client'),
                    'client_reviewing' => array('title' => 'Client Review', 'desc' => 'Client feedback and approval process'),
                    'edits_to_complete' => array('title' => 'Implementing Edits', 'desc' => 'Making requested changes and refinements'),
                    'review_edits_completed' => array('title' => 'Edits Complete', 'desc' => 'All requested changes have been implemented'),
                    'pre_launch' => array('title' => 'Pre-Launch', 'desc' => 'Final testing and launch preparations'),
                    'ready_for_launch' => array('title' => 'Ready for Launch', 'desc' => 'Final preparations and website launch'),
                    'web_complete_grow' => array('title' => 'Complete - Grow/Legacy', 'desc' => 'Project completed on Grow or Legacy plan'),
                    'web_complete_pro' => array('title' => 'Complete - Pro/ServiceCore', 'desc' => 'Project completed on Pro or ServiceCore plan')
                );
                
                $step_number = 1;
                foreach ($step_details as $step_key => $step_info):
                    $step_data = null;
                    foreach ($timeline as $t) {
                        if ($t->step_name === $step_key) {
                            $step_data = $t;
                            break;
                        }
                    }
                    $status = $step_data ? $step_data->status : 'pending';
                ?>
                <div class="timeline-step <?php echo $status; ?>">
                    <div class="step-icon <?php echo $status; ?>">
                        <?php if ($status === 'completed'): ?>
                            ✓
                        <?php elseif ($status === 'in_progress'): ?>
                            <?php echo $step_number; ?>
                        <?php else: ?>
                            <?php echo $step_number; ?>
                        <?php endif; ?>
                    </div>
                    <div class="step-content">
                        <h4 class="step-title"><?php echo $step_info['title']; ?></h4>
                        <p class="step-description"><?php echo $step_info['desc']; ?></p>
                        <?php if ($step_data && $step_data->completed_date): ?>
                            <div class="step-date">Completed: <?php echo date('M j, Y g:i A', strtotime($step_data->completed_date)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                $step_number++;
                endforeach; 
                ?>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="contact-info">
            <h3>Questions?</h3>
            <p>If you have any questions about your project, feel free to reply to your project notification email or contact our support team.</p>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html> 