<?php
/**
 * Form Content Helper Functions
 * 
 * Helper functions for retrieving dynamic content in form templates
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get form content from the database
 * 
 * @param string $form_type The form type (fast-build, standard-build, website-vip)
 * @param int $step_number The step number
 * @param string $content_key The content key to retrieve
 * @param string $default Default value if content not found
 * @return string The content value
 */
function docket_get_form_content($form_type, $step_number, $content_key, $default = '') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'docket_form_content';
    
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT content_value FROM {$table_name} 
         WHERE form_type = %s AND step_number = %d AND content_key = %s",
        $form_type, $step_number, $content_key
    ));
    
    // Remove extra slashes
    if ($result !== null) {
        return stripslashes($result);
    }
    
    return $default;
}

/**
 * Get all content for a specific form step
 * 
 * @param string $form_type The form type
 * @param int $step_number The step number
 * @return array Associative array of content_key => content_value
 */
function docket_get_step_content($form_type, $step_number) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'docket_form_content';
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT content_key, content_value FROM {$table_name} 
         WHERE form_type = %s AND step_number = %d",
        $form_type, $step_number
    ), ARRAY_A);
    
    $content = array();
    foreach ($results as $result) {
        $content[$result['content_key']] = $result['content_value'];
    }
    
    return $content;
}

/**
 * Echo form content (convenience function)
 * 
 * @param string $form_type The form type
 * @param int $step_number The step number
 * @param string $content_key The content key
 * @param string $default Default value if content not found
 */
function docket_form_content($form_type, $step_number, $content_key, $default = '') {
    echo docket_get_form_content($form_type, $step_number, $content_key, $default);
}

/**
 * Echo form content with HTML allowed (for rich content)
 * 
 * @param string $form_type The form type
 * @param int $step_number The step number
 * @param string $content_key The content key
 * @param string $default Default value if content not found
 */
function docket_form_content_html($form_type, $step_number, $content_key, $default = '') {
    echo wp_kses_post(docket_get_form_content($form_type, $step_number, $content_key, $default));
}

/**
 * Get form content as list items (for bullet points)
 * 
 * @param string $form_type The form type
 * @param int $step_number The step number
 * @param string $content_key The content key
 * @param string $default Default value if content not found
 * @return string HTML list items
 */
function docket_form_content_list($form_type, $step_number, $content_key, $default = '') {
    $content = docket_get_form_content($form_type, $step_number, $content_key, $default);
    
    // If content doesn't start with <li>, wrap each line as a list item
    if (!empty($content) && strpos($content, '<li>') === false) {
        $lines = explode("\n", $content);
        $list_items = array();
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $list_items[] = '<li>' . esc_html($line) . '</li>';
            }
        }
        return implode('', $list_items);
    }
    
    return $content;
}

/**
 * Echo form content as list items
 * 
 * @param string $form_type The form type
 * @param int $step_number The step number
 * @param string $content_key The content key
 * @param string $default Default value if content not found
 */
function docket_form_content_list_echo($form_type, $step_number, $content_key, $default = '') {
    echo docket_form_content_list($form_type, $step_number, $content_key, $default);
}
