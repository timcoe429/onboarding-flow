<?php
/**
 * Client Portal Admin Interface
 * Manage client projects and status updates
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
    
    // Handle status updates
    if (isset($_POST['update_project']) && wp_verify_nonce($_POST['_wpnonce'], 'update_project')) {
        $project_id = intval($_POST['project_id']);
        $new_step = sanitize_text_field($_POST['new_step']);
        
        // Update project current step
        $wpdb->update(
            $wpdb->prefix . 'docket_client_projects',
            array('current_step' => $new_step, 'updated_at' => current_time('mysql')),
            array('id' => $project_id)
        );
        
        // Update timeline
        docket_update_project_timeline($project_id, $new_step);
        
        echo '<div class="notice notice-success"><p>Project updated successfully!</p></div>';
    }
    
    // Get all projects
    $projects = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}docket_client_projects 
        ORDER BY created_at DESC
    ");
    
    ?>
    <div class="wrap">
        <h1>Client Projects</h1>
        
        <div class="docket-admin-stats" style="display: flex; gap: 20px; margin: 20px 0;">
            <?php
            $total = count($projects);
            $building = count(array_filter($projects, function($p) { return $p->current_step === 'building'; }));
            $review = count(array_filter($projects, function($p) { return $p->current_step === 'review'; }));
            $launched = count(array_filter($projects, function($p) { return $p->current_step === 'launched'; }));
            ?>
            
            <div class="docket-stat-card" style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #185fb0; min-width: 150px;">
                <div style="font-size: 24px; font-weight: bold; color: #185fb0;"><?php echo $total; ?></div>
                <div style="color: #666; font-size: 14px;">Total Projects</div>
            </div>
            
            <div class="docket-stat-card" style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b; min-width: 150px;">
                <div style="font-size: 24px; font-weight: bold; color: #f59e0b;"><?php echo $building; ?></div>
                <div style="color: #666; font-size: 14px;">In Development</div>
            </div>
            
            <div class="docket-stat-card" style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #a855f7; min-width: 150px;">
                <div style="font-size: 24px; font-weight: bold; color: #a855f7;"><?php echo $review; ?></div>
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
                            'submitted' => '#6b7280',
                            'building' => '#185fb0', 
                            'review' => '#f59e0b',
                            'final_touches' => '#a855f7',
                            'launched' => '#7eb10f'
                        );
                        $status_labels = array(
                            'submitted' => '✅ Submitted',
                            'building' => '🔧 Building',
                            'review' => '✏️ Review', 
                            'final_touches' => '✨ Final Touches',
                            'launched' => '🚀 Launched'
                        );
                        $color = $status_colors[$project->current_step] ?? '#6b7280';
                        $label = $status_labels[$project->current_step] ?? ucfirst($project->current_step);
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
                        <form method="post" style="display: inline-block;">
                            <?php wp_nonce_field('update_project'); ?>
                            <input type="hidden" name="project_id" value="<?php echo $project->id; ?>">
                            <select name="new_step" style="font-size: 12px;">
                                <option value="submitted" <?php selected($project->current_step, 'submitted'); ?>>✅ Submitted</option>
                                <option value="building" <?php selected($project->current_step, 'building'); ?>>🔧 Building</option>
                                <option value="review" <?php selected($project->current_step, 'review'); ?>>✏️ Review</option>
                                <option value="final_touches" <?php selected($project->current_step, 'final_touches'); ?>>✨ Final Touches</option>
                                <option value="launched" <?php selected($project->current_step, 'launched'); ?>>🚀 Launched</option>
                            </select>
                            <button type="submit" name="update_project" class="button button-small">Update</button>
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
                <li><strong>Manual Updates:</strong> Use the dropdowns above to move projects through stages</li>
                <li><strong>Real-time Updates:</strong> Client portals update immediately when you change the status</li>
            </ol>
            
            <p><strong>🔗 Portal URLs:</strong> Each client gets a unique URL like <code>yoursite.com/project-status/abc123/</code></p>
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
 * Update project timeline when status changes
 */
function docket_update_project_timeline($project_id, $new_step) {
    global $wpdb;
    
    $steps = array('submitted', 'building', 'review', 'final_touches', 'launched');
    $current_step_index = array_search($new_step, $steps);
    
    // Mark all previous steps as completed
    for ($i = 0; $i <= $current_step_index; $i++) {
        $wpdb->update(
            $wpdb->prefix . 'docket_project_timeline',
            array(
                'status' => 'completed',
                'completed_date' => current_time('mysql')
            ),
            array(
                'project_id' => $project_id,
                'step_name' => $steps[$i]
            )
        );
    }
    
    // Mark future steps as pending
    for ($i = $current_step_index + 1; $i < count($steps); $i++) {
        $wpdb->update(
            $wpdb->prefix . 'docket_project_timeline',
            array(
                'status' => 'pending',
                'completed_date' => null
            ),
            array(
                'project_id' => $project_id,
                'step_name' => $steps[$i]
            )
        );
    }
}
?>
