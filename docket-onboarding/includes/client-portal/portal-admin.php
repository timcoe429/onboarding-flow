<?php
/**
 * Client Portal Admin Interface
 * View-only interface for managing client projects
 * Status updates are handled automatically via Trello sync
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin menu and interface
 */
add_action('admin_menu', 'docket_portal_admin_menu');

function docket_portal_admin_menu() {
    add_menu_page(
        'Client Projects',
        'Client Projects', 
        'manage_options',
        'docket-client-projects',
        'docket_portal_admin_page',
        'dashicons-clipboard',
        30
    );
}

function docket_portal_admin_page() {
    global $wpdb;
    
    // Handle project deletion
    if (isset($_POST['delete_project']) && wp_verify_nonce($_POST['_wpnonce'], 'delete_project')) {
        $project_id = intval($_POST['project_id']);
        
        // Delete project timeline entries
        $wpdb->delete(
            $wpdb->prefix . 'docket_project_timeline',
            array('project_id' => $project_id),
            array('%d')
        );
        
        // Delete project
        $deleted = $wpdb->delete(
            $wpdb->prefix . 'docket_client_projects',
            array('id' => $project_id),
            array('%d')
        );
        
        if ($deleted) {
            echo '<div class="notice notice-success"><p>Project deleted successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Failed to delete project.</p></div>';
        }
    }
    
    // Handle viewing submission data
    $view_submission_id = isset($_GET['view_submission']) ? sanitize_text_field($_GET['view_submission']) : null;
    $submission_data = null;
    if ($view_submission_id) {
        $submission_data = get_option('docket_submission_' . $view_submission_id);
    }
    
    // Get all projects
    $projects = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}docket_client_projects 
        ORDER BY created_at DESC
    ");
    
    // If viewing submission data, show modal/expanded view
    if ($submission_data && $view_submission_id):
        ?>
        <div class="wrap">
            <h1>View Submission Data</h1>
            <p><a href="<?php echo admin_url('admin.php?page=docket-client-projects'); ?>" class="button">← Back to Projects</a></p>
            
            <div class="docket-submission-view" style="background: white; padding: 20px; border-radius: 8px; margin-top: 20px;">
                <h2>Submission ID: <?php echo esc_html($view_submission_id); ?></h2>
                
                <table class="widefat" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($submission_data as $key => $value): 
                            if (is_array($value)) {
                                $display_value = implode(', ', array_map('esc_html', $value));
                            } else {
                                $display_value = esc_html($value);
                            }
                            
                            // Skip very long text fields (show truncated)
                            if (strlen($display_value) > 500) {
                                $display_value = substr($display_value, 0, 500) . '... (truncated)';
                            }
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></strong></td>
                            <td><?php echo $display_value; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return;
    endif;
    
    ?>
    <div class="wrap">
        <h1>Client Projects</h1>
        
        <div class="docket-admin-stats" style="display: flex; gap: 20px; margin: 20px 0;">
            <?php
            $total = count($projects);
            // Count projects in development (docket_team, qa, ready_to_send, waiting_review_scheduling)
            $in_development = count(array_filter($projects, function($p) { 
                return in_array($p->current_step, array('docket_team', 'qa', 'ready_to_send', 'waiting_review_scheduling')); 
            }));
            // Count projects in review (client_reviewing, edits_to_complete, review_edits_completed)
            $in_review = count(array_filter($projects, function($p) { 
                return in_array($p->current_step, array('client_reviewing', 'edits_to_complete', 'review_edits_completed')); 
            }));
            // Count launched projects (web_complete_grow, web_complete_pro, ready_for_launch, pre_launch)
            $launched = count(array_filter($projects, function($p) { 
                return in_array($p->current_step, array('web_complete_grow', 'web_complete_pro', 'ready_for_launch', 'pre_launch')); 
            }));
            ?>
            
            <div class="docket-stat-card" style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #185fb0; min-width: 150px;">
                <div style="font-size: 24px; font-weight: bold; color: #185fb0;"><?php echo $total; ?></div>
                <div style="color: #666; font-size: 14px;">Total Projects</div>
            </div>
            
            <div class="docket-stat-card" style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b; min-width: 150px;">
                <div style="font-size: 24px; font-weight: bold; color: #f59e0b;"><?php echo $in_development; ?></div>
                <div style="color: #666; font-size: 14px;">In Development</div>
            </div>
            
            <div class="docket-stat-card" style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #a855f7; min-width: 150px;">
                <div style="font-size: 24px; font-weight: bold; color: #a855f7;"><?php echo $in_review; ?></div>
                <div style="color: #666; font-size: 14px;">In Review</div>
            </div>
            
            <div class="docket-stat-card" style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #7eb10f; min-width: 150px;">
                <div style="font-size: 24px; font-weight: bold; color: #7eb10f;"><?php echo $launched; ?></div>
                <div style="color: #666; font-size: 14px;">Launched</div>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 200px;">Business Name</th>
                    <th style="width: 100px;">Form Type</th>
                    <th style="width: 120px;">Current Status</th>
                    <th style="width: 100px;">Started</th>
                    <th style="width: 100px;">Portal Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($project->business_name); ?></strong><br>
                        <small style="color: #666;"><?php echo esc_html($project->business_email); ?></small>
                    </td>
                    <td>
                        <span class="form-type-badge" style="background: #e5e7eb; padding: 4px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; font-weight: bold;">
                            <?php echo esc_html(str_replace('-', ' ', $project->form_type)); ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $status_colors = array(
                            'docket_team' => '#185fb0',
                            'qa' => '#3b82f6',
                            'ready_to_send' => '#60a5fa',
                            'waiting_review_scheduling' => '#93c5fd',
                            'client_reviewing' => '#f59e0b',
                            'edits_to_complete' => '#f97316',
                            'review_edits_completed' => '#fb923c',
                            'pre_launch' => '#a855f7',
                            'ready_for_launch' => '#c084fc',
                            'web_complete_grow' => '#7eb10f',
                            'web_complete_pro' => '#7eb10f'
                        );
                        $status_labels = array(
                            'docket_team' => '🔧 Docket Team',
                            'qa' => '🔍 QA',
                            'ready_to_send' => '📤 Ready to Send',
                            'waiting_review_scheduling' => '⏳ Waiting Review',
                            'client_reviewing' => '👀 Client Reviewing',
                            'edits_to_complete' => '✏️ Edits to Complete',
                            'review_edits_completed' => '✅ Edits Completed',
                            'pre_launch' => '🚀 Pre Launch',
                            'ready_for_launch' => '🚀 Ready for Launch',
                            'web_complete_grow' => '✅ Web Complete',
                            'web_complete_pro' => '✅ Web Complete'
                        );
                        $color = $status_colors[$project->current_step] ?? '#6b7280';
                        $label = $status_labels[$project->current_step] ?? ucwords(str_replace('_', ' ', $project->current_step));
                        ?>
                        <span style="color: <?php echo $color; ?>; font-weight: bold;">
                            <?php echo $label; ?>
                        </span>
                    </td>
                    <td>
                        <?php echo date('M j, Y', strtotime($project->created_at)); ?>
                    </td>
                    <td>
                        <a href="<?php echo home_url("/project-status/{$project->client_uuid}/"); ?>" 
                           target="_blank" 
                           class="button button-small"
                           style="font-size: 11px;">
                            View Portal
                        </a>
                    </td>
                    <td>
                        <?php
                        // Find submission data by business name/email
                        $submission_id = docket_find_submission_id($project->business_name, $project->business_email);
                        if ($submission_id):
                        ?>
                        <a href="<?php echo admin_url('admin.php?page=docket-client-projects&view_submission=' . urlencode($submission_id)); ?>" 
                           class="button button-small" 
                           style="font-size: 11px; margin-right: 5px;">
                            View Submission
                        </a>
                        <?php else: ?>
                        <span style="color: #999; font-size: 11px;">No submission data found</span>
                        <?php endif; ?>
                        
                        <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this project? This action cannot be undone.');">
                            <?php wp_nonce_field('delete_project'); ?>
                            <input type="hidden" name="project_id" value="<?php echo $project->id; ?>">
                            <button type="submit" name="delete_project" class="button button-small" style="background: #dc2626; color: white; border-color: #dc2626;">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                        No projects yet. They'll appear here after clients submit the onboarding forms.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
            <h3 style="margin-top: 0;">💡 How This Works</h3>
            <ol>
                <li><strong>Automatic Creation:</strong> Projects are created automatically when clients submit onboarding forms</li>
                <li><strong>Email Notification:</strong> Clients get an email with their unique portal link</li>
                <li><strong>Automatic Status Updates:</strong> Project statuses are automatically synced from Trello board positions</li>
                <li><strong>View Submission Data:</strong> Click "View Submission" to see all form data submitted by the client</li>
                <li><strong>Real-time Updates:</strong> Client portals update automatically when Trello cards move</li>
            </ol>
            
            <p><strong>🔗 Portal URLs:</strong> Each client gets a unique URL like <code>yoursite.com/project-status/abc123/</code></p>
            <p><strong>📊 Status Updates:</strong> All status changes are managed automatically via Trello sync - no manual updates needed!</p>
        </div>
    </div>
    
    <style>
        .docket-admin-stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .docket-stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            min-width: 150px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .form-type-badge {
            background: #e5e7eb;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
            color: #374151;
        }
        
        .wp-list-table td {
            vertical-align: top;
            padding: 12px 8px;
        }
        
        .wp-list-table select {
            font-size: 12px;
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .docket-admin-stats {
                flex-direction: column;
            }
            
            .docket-stat-card {
                min-width: auto;
            }
        }
    </style>
    <?php
}

/**
 * Find submission ID by business name or email
 */
function docket_find_submission_id($business_name, $business_email) {
    global $wpdb;
    
    // Get all options that start with 'docket_submission_'
    $options = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'docket_submission_%' ORDER BY option_id DESC LIMIT 100"
    );
    
    foreach ($options as $option) {
        $submission_data = maybe_unserialize($option->option_value);
        
        if (is_array($submission_data)) {
            // Check if business name or email matches
            $submission_business_name = isset($submission_data['business_name']) ? strtolower(trim($submission_data['business_name'])) : '';
            $submission_business_email = isset($submission_data['business_email']) ? strtolower(trim($submission_data['business_email'])) : '';
            
            $search_business_name = strtolower(trim($business_name));
            $search_business_email = strtolower(trim($business_email));
            
            if ($submission_business_name === $search_business_name || 
                $submission_business_email === $search_business_email) {
                // Extract submission ID from option name
                return str_replace('docket_submission_', '', $option->option_name);
            }
        }
    }
    
    return null;
}

?>
