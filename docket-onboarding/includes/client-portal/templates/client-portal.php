<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Status - <?php echo esc_html($project->business_name); ?></title>
    
    <!-- Load WordPress styles -->
    <?php wp_head(); ?>
    
    <!-- Portal-specific styles that match the form design -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .portal-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .portal-header {
            background: white;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .portal-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 8px 0;
        }
        
        .portal-subtitle {
            color: #6b7280;
            font-size: 16px;
            margin: 0 0 20px 0;
        }
        
        .project-info {
            display: inline-block;
            background: #f3f4f6;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            color: #374151;
        }
        
        .timeline-container {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .timeline-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 0 0 40px 0;
        }
        
        .timeline-line {
            position: absolute;
            top: 35px;
            left: 70px;
            right: 70px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            z-index: 1;
        }
        
        .timeline-progress {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: #185fb0;
            border-radius: 2px;
            transition: width 0.8s ease;
            z-index: 2;
        }
        
        .timeline-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 3;
        }
        
        .step-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            transition: all 0.3s ease;
            border: 4px solid transparent;
        }
        
        .step-circle.completed {
            background: #7eb10f;
            color: white;
            transform: scale(1.1);
        }
        
        .step-circle.in_progress {
            background: #185fb0;
            color: white;
            animation: pulse 2s infinite;
            transform: scale(1.1);
        }
        
        .step-circle.pending {
            background: #e5e7eb;
            color: #9ca3af;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(24, 95, 176, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(24, 95, 176, 0); }
            100% { box-shadow: 0 0 0 0 rgba(24, 95, 176, 0); }
        }
        
        .step-title {
            font-weight: 600;
            color: #111827;
            margin: 0 0 4px 0;
            font-size: 14px;
        }
        
        .step-date {
            font-size: 12px;
            color: #6b7280;
        }
        
        .current-status {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .current-status h3 {
            font-size: 20px;
            margin: 0 0 8px 0;
            color: #111827;
        }
        
        .current-status p {
            color: #6b7280;
            margin: 0;
            font-size: 15px;
        }
        
        .status-building {
            background: linear-gradient(135deg, rgba(24, 95, 176, 0.1), rgba(20, 85, 160, 0.1));
            border: 2px solid #185fb0;
        }
        
        .status-review {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
            border: 2px solid #f59e0b;
        }
        
        .status-final {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(147, 51, 234, 0.1));
            border: 2px solid #a855f7;
        }
        
        .status-launched {
            background: linear-gradient(135deg, rgba(126, 177, 15, 0.1), rgba(111, 160, 0, 0.1));
            border: 2px solid #7eb10f;
        }
        
        .next-steps {
            background: #f3f4f6;
            border-radius: 12px;
            padding: 24px;
            margin-top: 20px;
        }
        
        .next-steps h4 {
            margin: 0 0 12px 0;
            color: #111827;
            font-size: 16px;
        }
        
        .next-steps p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .contact-box {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-top: 30px;
        }
        
        .contact-box h4 {
            margin: 0 0 8px 0;
            color: #111827;
        }
        
        .contact-box p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .portal-container {
                margin: 20px auto;
                padding: 0 15px;
            }
            
            .portal-header {
                padding: 20px;
            }
            
            .portal-header h1 {
                font-size: 24px;
            }
            
            .timeline-container {
                padding: 25px 20px;
            }
            
            .timeline-steps {
                flex-direction: column;
                gap: 20px;
            }
            
            .timeline-line {
                display: none;
            }
            
            .step-circle {
                width: 60px;
                height: 60px;
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="portal-container">
        <!-- Header -->
        <div class="portal-header">
            <h1>📊 Project Dashboard</h1>
            <p class="portal-subtitle">Track your website's progress in real-time</p>
            <div class="project-info">
                <strong><?php echo esc_html($project->business_name); ?></strong> • 
                <?php echo ucfirst(str_replace('-', ' ', $project->form_type)); ?> • 
                Started <?php echo date('M j, Y', strtotime($project->created_at)); ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="timeline-container">
            <?php
            // Define step data
            $step_data = array(
                'submitted' => array(
                    'icon' => '✅',
                    'title' => 'Order Submitted',
                    'description' => 'We received your order and requirements'
                ),
                'building' => array(
                    'icon' => '🔧', 
                    'title' => 'Building Website',
                    'description' => 'Our team is designing and building your site'
                ),
                'review' => array(
                    'icon' => '✏️',
                    'title' => 'Review & Feedback', 
                    'description' => 'Time for you to review and request changes'
                ),
                'final_touches' => array(
                    'icon' => '✨',
                    'title' => 'Final Touches',
                    'description' => 'Making final adjustments and preparing launch'
                ),
                'launched' => array(
                    'icon' => '🚀',
                    'title' => 'Website Launched',
                    'description' => 'Your website is live and ready for customers!'
                )
            );
            
            // Calculate progress percentage
            $completed_count = 0;
            $current_step_name = '';
            foreach ($timeline as $step) {
                if ($step->status === 'completed') {
                    $completed_count++;
                } elseif ($step->status === 'in_progress') {
                    $current_step_name = $step->step_name;
                    break;
                } else {
                    $current_step_name = $step->step_name;
                    break;
                }
            }
            $progress_percentage = ($completed_count / count($timeline)) * 100;
            ?>
            
            <!-- Timeline Steps -->
            <div class="timeline-steps">
                <div class="timeline-line">
                    <div class="timeline-progress" style="width: <?php echo $progress_percentage; ?>%;"></div>
                </div>
                
                <?php foreach ($timeline as $step): ?>
                <div class="timeline-step">
                    <div class="step-circle <?php echo $step->status; ?>">
                        <?php echo $step_data[$step->step_name]['icon']; ?>
                    </div>
                    <div class="step-title"><?php echo $step_data[$step->step_name]['title']; ?></div>
                    <?php if ($step->completed_date): ?>
                        <div class="step-date">
                            <?php echo date('M j', strtotime($step->completed_date)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Current Status Box -->
            <?php
            $current_step_data = $step_data[$current_step_name] ?? $step_data['submitted'];
            $status_class = 'status-' . str_replace(['_'], ['-'], $current_step_name);
            ?>
            <div class="current-status <?php echo $status_class; ?>">
                <h3><?php echo $current_step_data['icon']; ?> <?php echo $current_step_data['title']; ?></h3>
                <p><?php echo $current_step_data['description']; ?></p>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps">
                <h4>What's Next?</h4>
                <?php
                switch ($current_step_name) {
                    case 'submitted':
                        echo '<p>Our team is reviewing your requirements and will start building your website within 1-2 business days.</p>';
                        break;
                    case 'building':
                        echo '<p>We\'re actively working on your website. This typically takes 3-7 business days depending on your plan.</p>';
                        break;
                    case 'review':
                        echo '<p>We\'ll send you a preview link via email so you can review your website and request any changes.</p>';
                        break;
                    case 'final_touches':
                        echo '<p>We\'re making your requested changes and preparing your website for launch. Almost there!</p>';
                        break;
                    case 'launched':
                        echo '<p>Congratulations! Your website is now live. We\'ll send you all the login details and next steps.</p>';
                        break;
                    default:
                        echo '<p>We\'ll keep you updated as your project progresses. Check back here anytime!</p>';
                }
                ?>
            </div>
        </div>
        
        <!-- Contact Box -->
        <div class="contact-box">
            <h4>❓ Questions?</h4>
            <p>Need an update or have questions? Reply to any of our emails or contact your project manager directly.</p>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html> 