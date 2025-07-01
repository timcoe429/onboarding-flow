<?php
/**
 * Trello API Integration for Project Status Sync
 * Syncs project status based on card positions in Trello board
 */

class DocketTrelloSync {
    
    private $api_key = 'bb036ac8c5b6301f2547de0bae0e551d';
    private $token = 'ATTAdc6d631ead317c421f6a29fda75409bd50dbbfddbd7a789327860f8eb31fce23A6577396';
    private $board_id = 'XzUXs3SH';
    private $api_base = 'https://api.trello.com/1';
    
    // Map Trello list names to project status
    private $status_mapping = array(
        'Docket Team' => 'docket_team',
        'QA' => 'qa', 
        'Waiting on Review Scheduling' => 'waiting_review_scheduling',
        'Client Reviewing' => 'client_reviewing',
        'Edits to Complete' => 'edits_to_complete',
        'Ready for Launch' => 'ready_for_launch'
    );
    
    public function __construct() {
        // Hook into WordPress admin for manual sync
        add_action('wp_ajax_sync_trello', array($this, 'manual_sync'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Schedule automatic sync (every 30 minutes)
        add_action('wp', array($this, 'schedule_sync'));
        add_action('docket_trello_sync_hook', array($this, 'sync_all_projects'));
    }
    
    /**
     * Schedule automatic sync
     */
    public function schedule_sync() {
        if (!wp_next_scheduled('docket_trello_sync_hook')) {
            wp_schedule_event(time(), 'every_30_minutes', 'docket_trello_sync_hook');
        }
    }
    
    /**
     * Add custom cron interval
     */
    public function add_cron_intervals($schedules) {
        $schedules['every_30_minutes'] = array(
            'interval' => 1800, // 30 minutes
            'display' => __('Every 30 Minutes')
        );
        return $schedules;
    }
    
    /**
     * Add admin menu for manual sync
     */
    public function add_admin_menu() {
        add_management_page(
            'Trello Sync',
            'Trello Sync', 
            'manage_options',
            'trello-sync',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page for manual sync
     */
    public function admin_page() {
        if (isset($_POST['sync_now'])) {
            $results = $this->sync_all_projects();
            echo '<div class="notice notice-success"><p>Sync completed! Updated ' . count($results) . ' projects.</p></div>';
        }
        
        ?>
        <div class="wrap">
            <h1>Trello Project Sync</h1>
            <p>Sync project statuses with Trello board positions.</p>
            
            <form method="post">
                <?php wp_nonce_field('trello_sync'); ?>
                <button type="submit" name="sync_now" class="button button-primary">Sync Now</button>
            </form>
            
            <h2>Board Info</h2>
            <p><strong>Board ID:</strong> <?php echo $this->board_id; ?></p>
            <p><strong>Board URL:</strong> <a href="https://trello.com/b/<?php echo $this->board_id; ?>" target="_blank">View Board</a></p>
            
            <h2>Status Mapping</h2>
            <table class="widefat">
                <thead>
                    <tr><th>Trello Column</th><th>Project Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($this->status_mapping as $trello => $status): ?>
                    <tr>
                        <td><?php echo esc_html($trello); ?></td>
                        <td><?php echo esc_html($status); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Manual sync via AJAX
     */
    public function manual_sync() {
        check_ajax_referer('trello_sync');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $results = $this->sync_all_projects();
        wp_send_json_success($results);
    }
    
    /**
     * Get all lists from the Trello board
     */
    private function get_board_lists() {
        $url = "{$this->api_base}/boards/{$this->board_id}/lists";
        $url .= "?key={$this->api_key}&token={$this->token}";
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            error_log('Trello API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $lists = json_decode($body, true);
        
        if (!$lists) {
            error_log('Trello API: Failed to decode lists response');
            return false;
        }
        
        return $lists;
    }
    
    /**
     * Get all cards from a specific list
     */
    private function get_list_cards($list_id) {
        $url = "{$this->api_base}/lists/{$list_id}/cards";
        $url .= "?key={$this->api_key}&token={$this->token}";
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            error_log('Trello API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $cards = json_decode($body, true);
        
        return $cards ?: array();
    }
    
    /**
     * Find project by business name in card title
     */
    private function find_project_by_business_name($business_name) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'docket_client_projects';
        
        $project = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE business_name = %s",
            $business_name
        ));
        
        return $project;
    }
    
    /**
     * Update project status and timeline
     */
    private function update_project_status($project_id, $new_status) {
        global $wpdb;
        
        // Update current step in projects table
        $projects_table = $wpdb->prefix . 'docket_client_projects';
        $timeline_table = $wpdb->prefix . 'docket_project_timeline';
        
        // Update project current step
        $updated = $wpdb->update(
            $projects_table,
            array('current_step' => $new_status),
            array('id' => $project_id),
            array('%s'),
            array('%d')
        );
        
        if ($updated === false) {
            error_log("Failed to update project {$project_id} status to {$new_status}");
            return false;
        }
        
        // Get all steps in order
        $all_steps = array_values($this->status_mapping);
        $current_step_index = array_search($new_status, $all_steps);
        
        if ($current_step_index === false) {
            return false;
        }
        
        // Mark all previous steps as completed
        for ($i = 0; $i <= $current_step_index; $i++) {
            $step_name = $all_steps[$i];
            $status = ($i == $current_step_index) ? 'in_progress' : 'completed';
            
            // Check if step exists
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$timeline_table} WHERE project_id = %d AND step_name = %s",
                $project_id, $step_name
            ));
            
            if ($existing) {
                // Update existing step
                $wpdb->update(
                    $timeline_table,
                    array(
                        'status' => $status,
                        'completed_date' => ($status === 'completed') ? current_time('mysql') : null
                    ),
                    array('id' => $existing->id),
                    array('%s', '%s'),
                    array('%d')
                );
            } else {
                // Create new step
                $wpdb->insert(
                    $timeline_table,
                    array(
                        'project_id' => $project_id,
                        'step_name' => $step_name,
                        'status' => $status,
                        'completed_date' => ($status === 'completed') ? current_time('mysql') : null,
                        'created_at' => current_time('mysql')
                    ),
                    array('%d', '%s', '%s', '%s', '%s')
                );
            }
        }
        
        // Mark remaining steps as pending
        for ($i = $current_step_index + 1; $i < count($all_steps); $i++) {
            $step_name = $all_steps[$i];
            
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$timeline_table} WHERE project_id = %d AND step_name = %s",
                $project_id, $step_name
            ));
            
            if ($existing) {
                $wpdb->update(
                    $timeline_table,
                    array(
                        'status' => 'pending',
                        'completed_date' => null
                    ),
                    array('id' => $existing->id),
                    array('%s', '%s'),
                    array('%d')
                );
            }
        }
        
        return true;
    }
    
    /**
     * Sync all projects with Trello
     */
    public function sync_all_projects() {
        $lists = $this->get_board_lists();
        
        if (!$lists) {
            error_log('Trello Sync: Failed to get board lists');
            return array();
        }
        
        $updated_projects = array();
        
        foreach ($lists as $list) {
            $list_name = $list['name'];
            
            // Skip if list name doesn't match our mapping
            if (!isset($this->status_mapping[$list_name])) {
                continue;
            }
            
            $project_status = $this->status_mapping[$list_name];
            $cards = $this->get_list_cards($list['id']);
            
            if (!$cards) {
                continue;
            }
            
            foreach ($cards as $card) {
                $card_name = $card['name'];
                
                // Try to find project by business name
                // Cards might be formatted as "Business Name - Project Type" or just "Business Name"
                $business_name = $this->extract_business_name($card_name);
                $project = $this->find_project_by_business_name($business_name);
                
                if ($project) {
                    // Only update if status has changed
                    if ($project->current_step !== $project_status) {
                        $success = $this->update_project_status($project->id, $project_status);
                        
                        if ($success) {
                            $updated_projects[] = array(
                                'project_id' => $project->id,
                                'business_name' => $project->business_name,
                                'old_status' => $project->current_step,
                                'new_status' => $project_status,
                                'trello_list' => $list_name
                            );
                            
                            error_log("Updated project {$project->business_name} from {$project->current_step} to {$project_status}");
                        }
                    }
                } else {
                    error_log("No project found for Trello card: {$card_name}");
                }
            }
        }
        
        return $updated_projects;
    }
    
    /**
     * Extract business name from card title
     * Handles formats like "Business Name - Project Type" or "Business Name"
     */
    private function extract_business_name($card_name) {
        // If there's a dash, take everything before it
        $parts = explode(' - ', $card_name);
        return trim($parts[0]);
    }
    
    /**
     * Create Trello card when project is submitted
     */
    public function create_trello_card($project_data) {
        // This is already handled by the BCC email integration
        // But we could add direct API card creation here if needed
        
        $lists = $this->get_board_lists();
        if (!$lists) {
            return false;
        }
        
        // Find the first list (Docket Team)
        $first_list = null;
        foreach ($lists as $list) {
            if ($list['name'] === 'Docket Team') {
                $first_list = $list;
                break;
            }
        }
        
        if (!$first_list) {
            return false;
        }
        
        $card_name = $project_data['business_name'] . ' - ' . ucwords(str_replace('-', ' ', $project_data['form_type']));
        $card_desc = "Business: {$project_data['business_name']}\nProject Type: {$project_data['form_type']}\nSubmitted: " . date('M j, Y g:i A');
        
        $url = "{$this->api_base}/cards";
        $data = array(
            'key' => $this->api_key,
            'token' => $this->token,
            'idList' => $first_list['id'],
            'name' => $card_name,
            'desc' => $card_desc
        );
        
        $response = wp_remote_post($url, array(
            'body' => $data
        ));
        
        if (is_wp_error($response)) {
            error_log('Trello Card Creation Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $card = json_decode($body, true);
        
        return $card;
    }
}

// Initialize Trello sync
new DocketTrelloSync();

// Add the cron interval filter
add_filter('cron_schedules', function($schedules) {
    $schedules['every_30_minutes'] = array(
        'interval' => 1800, // 30 minutes
        'display' => __('Every 30 Minutes')
    );
    return $schedules;
}); 