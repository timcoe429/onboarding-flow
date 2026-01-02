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
    
    // Handle bulk deletion
    if (isset($_POST['bulk_delete']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk_delete')) {
        $project_ids = isset($_POST['project_ids']) ? array_map('intval', $_POST['project_ids']) : array();
        
        if (!empty($project_ids)) {
            // Sanitize IDs - only keep positive integers
            $project_ids = array_filter($project_ids, function($id) { return $id > 0; });
            
            if (!empty($project_ids)) {
                $ids_string = implode(',', $project_ids);
                
                // Delete timeline entries
                $wpdb->query(
                    "DELETE FROM {$wpdb->prefix}docket_project_timeline WHERE project_id IN ($ids_string)"
                );
                
                // Delete projects
                $deleted = $wpdb->query(
                    "DELETE FROM {$wpdb->prefix}docket_client_projects WHERE id IN ($ids_string)"
                );
                
                if ($deleted !== false) {
                    echo '<div class="notice notice-success"><p>' . count($project_ids) . ' project(s) deleted successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Failed to delete projects.</p></div>';
                }
            }
        }
    }
    
    // Handle single project deletion
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
        
        <div class="docket-admin-stats" style="display: flex; gap: 24px; margin: 24px 0;">
            <?php
            $total = count($projects);
            ?>
            
            <div class="docket-stat-card" style="background: white; padding: 24px; border-radius: 8px; border-left: 4px solid #185fb0; min-width: 160px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                <div style="font-size: 28px; font-weight: bold; color: #185fb0; margin-bottom: 4px;"><?php echo $total; ?></div>
                <div style="color: #666; font-size: 14px; font-weight: 500;">Total Projects</div>
            </div>
        </div>
        
        <!-- Bulk Actions Bar -->
        <div class="docket-bulk-actions" style="margin: 20px 0; padding: 12px 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; display: none; align-items: center; gap: 12px;">
            <form method="post" id="bulk-delete-form" style="display: flex; align-items: center; gap: 12px; margin: 0;">
                <?php wp_nonce_field('bulk_delete'); ?>
                <button type="submit" name="bulk_delete" class="button" style="background: #dc2626; color: white; border-color: #dc2626;" onclick="return docketBulkDeleteConfirm();">
                    Delete Selected
                </button>
                <span id="selected-count" style="color: #666; font-size: 14px;">
                    <strong id="count-number">0</strong> project(s) selected
                </span>
            </form>
        </div>
        
        <table class="wp-list-table widefat fixed striped docket-projects-table">
            <thead>
                <tr>
                    <th style="width: 40px; padding: 12px 8px;">
                        <input type="checkbox" id="select-all-projects" style="margin: 0;">
                    </th>
                    <th style="width: 220px; padding: 12px 16px;">Business Name</th>
                    <th style="width: 140px; padding: 12px 16px;">Form Type</th>
                    <th style="width: 140px; padding: 12px 16px;">Started</th>
                    <th style="width: 140px; padding: 12px 16px;">Portal Link</th>
                    <th style="padding: 12px 16px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td style="padding: 16px 8px; text-align: center;">
                        <input type="checkbox" name="project_ids[]" value="<?php echo $project->id; ?>" class="project-checkbox" style="margin: 0;">
                    </td>
                    <td style="padding: 16px;">
                        <strong><?php echo esc_html($project->business_name); ?></strong><br>
                        <small style="color: #666;"><?php echo esc_html($project->business_email); ?></small>
                    </td>
                    <td style="padding: 16px;">
                        <span class="form-type-badge" style="background: #e5e7eb; padding: 4px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; font-weight: bold;">
                            <?php echo esc_html(str_replace('-', ' ', $project->form_type)); ?>
                        </span>
                    </td>
                    <td style="padding: 16px;">
                        <?php echo date('M j, Y', strtotime($project->created_at)); ?>
                    </td>
                    <td style="padding: 16px;">
                        <a href="<?php echo home_url("/project-status/{$project->client_uuid}/"); ?>" 
                           target="_blank" 
                           class="button button-small"
                           style="font-size: 12px; padding: 6px 12px;">
                            View Portal
                        </a>
                    </td>
                    <td style="padding: 16px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <?php
                            // Find submission data by business name/email
                            $submission_id = docket_find_submission_id($project->business_name, $project->business_email);
                            if ($submission_id):
                            ?>
                            <a href="<?php echo admin_url('admin.php?page=docket-client-projects&view_submission=' . urlencode($submission_id)); ?>" 
                               class="button button-small" 
                               style="font-size: 12px; padding: 6px 12px;">
                                View Submission
                            </a>
                            <?php else: ?>
                            <span style="color: #999; font-size: 12px;">No submission data</span>
                            <?php endif; ?>
                            
                            <form method="post" style="display: inline-block; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this project? This action cannot be undone.');">
                                <?php wp_nonce_field('delete_project'); ?>
                                <input type="hidden" name="project_id" value="<?php echo $project->id; ?>">
                                <button type="submit" name="delete_project" class="button button-small" style="background: #dc2626; color: white; border-color: #dc2626; font-size: 12px; padding: 6px 12px;">
                                    Delete
                                </button>
                            </form>
                        </div>
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
            padding: 24px;
            border-radius: 8px;
            min-width: 160px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .docket-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        
        .docket-projects-table td {
            vertical-align: middle;
        }
        
        .docket-projects-table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .docket-projects-table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .docket-bulk-actions {
            display: none;
        }
        
        .docket-bulk-actions.has-selection {
            display: flex;
        }
        
        @media (max-width: 768px) {
            .docket-admin-stats {
                flex-direction: column;
            }
            
            .docket-stat-card {
                min-width: auto;
            }
            
            .docket-projects-table {
                font-size: 13px;
            }
            
            .docket-projects-table th,
            .docket-projects-table td {
                padding: 12px 8px !important;
            }
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Select All functionality
        $('#select-all-projects').on('change', function() {
            $('.project-checkbox').prop('checked', this.checked);
            updateSelectedCount();
        });
        
        // Individual checkbox change
        $(document).on('change', '.project-checkbox', function() {
            updateSelectedCount();
            updateSelectAllState();
        });
        
        // Update selected count
        function updateSelectedCount() {
            var count = $('.project-checkbox:checked').length;
            $('#count-number').text(count);
            if (count > 0) {
                $('.docket-bulk-actions').show();
            } else {
                $('.docket-bulk-actions').hide();
            }
        }
        
        // Update select all checkbox state
        function updateSelectAllState() {
            var total = $('.project-checkbox').length;
            var checked = $('.project-checkbox:checked').length;
            $('#select-all-projects').prop('checked', total > 0 && checked === total);
        }
        
        // Update form submission to include all checked boxes
        $('#bulk-delete-form').on('submit', function(e) {
            var checkedIds = [];
            $('.project-checkbox:checked').each(function() {
                checkedIds.push($(this).val());
            });
            
            if (checkedIds.length === 0) {
                e.preventDefault();
                alert('Please select at least one project to delete.');
                return false;
            }
            
            // Clear existing hidden inputs
            $(this).find('input[name="project_ids[]"]').remove();
            
            // Add checked IDs as hidden inputs
            checkedIds.forEach(function(id) {
                $(this).append($('<input>').attr({
                    type: 'hidden',
                    name: 'project_ids[]',
                    value: id
                }));
            }.bind(this));
        });
    });
    
    // Bulk delete confirmation
    function docketBulkDeleteConfirm() {
        var count = jQuery('.project-checkbox:checked').length;
        if (count === 0) {
            alert('Please select at least one project to delete.');
            return false;
        }
        return confirm('Are you sure you want to delete ' + count + ' selected project(s)? This action cannot be undone.');
    }
    </script>
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
