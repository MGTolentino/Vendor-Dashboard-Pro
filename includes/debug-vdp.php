<?php
/**
 * Debugging file for Vendor Dashboard PRO
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced debug logging function for VDP
 * 
 * @param string $message Message to log
 * @param string $type Log type (info, warning, error)
 * @param array $context Additional context data
 */
function vdp_enhanced_debug($message, $type = 'info', $context = []) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $prefix = 'VDP Debug';
        
        if ($type === 'warning') {
            $prefix = 'VDP Warning';
        } elseif ($type === 'error') {
            $prefix = 'VDP Error';
        }
        
        // Add context data if available
        if (!empty($context)) {
            $context_str = json_encode($context);
            $message .= " | Context: $context_str";
        }
        
        error_log("$prefix: $message");
    }
}

/**
 * Add debugging hooks and filters
 */
function vdp_setup_debugging() {
    // Debug router rendering
    add_action('vdp_before_render_content', 'vdp_debug_render_content', 10, 2);
    
    // Debug AJAX request handling
    add_action('wp_ajax_vdp_load_content', 'vdp_debug_ajax_request', 1);
    
    // Debug URL generation
    add_filter('vdp_dashboard_url', 'vdp_debug_url_generation', 10, 3);
    
    // Hook into template inclusion
    add_action('all', 'vdp_debug_template_includes');
    
    // Debug request parameters
    add_action('init', 'vdp_debug_request_params');
}

/**
 * Debug render_content function
 * 
 * @param string $action Current action
 * @param string $item Current item
 */
function vdp_debug_render_content($action, $item) {
    vdp_enhanced_debug("Rendering content with action '$action' and item '$item'", 'info', [
        'GET_params' => $_GET,
        'current_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'is_ajax' => wp_doing_ajax(),
        'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
    ]);
}

/**
 * Debug AJAX request handling
 */
function vdp_debug_ajax_request() {
    vdp_enhanced_debug("AJAX request received", 'info', [
        'POST_params' => $_POST,
        'GET_params' => $_GET
    ]);
}

/**
 * Debug URL generation
 * 
 * @param string $url Generated URL
 * @param string $action Action
 * @param string $item Item ID
 * @return string Original URL
 */
function vdp_debug_url_generation($url, $action, $item) {
    vdp_enhanced_debug("Dashboard URL generated: $url", 'info', [
        'action' => $action,
        'item' => $item,
        'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
    ]);
    
    return $url;
}

/**
 * Debug template includes
 */
function vdp_debug_template_includes() {
    $current_filter = current_filter();
    
    // Only track template_include actions
    if ($current_filter === 'template_include') {
        $template = func_get_arg(0);
        vdp_enhanced_debug("Template included: $template", 'info');
    }
    
    // Track VDP specific template inclusions
    if (strpos($current_filter, 'vdp_') === 0) {
        vdp_enhanced_debug("VDP Action fired: $current_filter", 'info', [
            'args' => func_get_args()
        ]);
    }
}

/**
 * Debug request parameters
 */
function vdp_debug_request_params() {
    // Only run on front-end requests
    if (is_admin() && !wp_doing_ajax()) {
        return;
    }
    
    global $vdp_current_action, $vdp_current_item;
    
    vdp_enhanced_debug("Request parameters", 'info', [
        'GET' => $_GET,
        'global_action' => isset($vdp_current_action) ? $vdp_current_action : 'not set',
        'global_item' => isset($vdp_current_item) ? $vdp_current_item : 'not set',
        'is_dashboard_page' => function_exists('vdp_is_dashboard_page') ? vdp_is_dashboard_page() : 'function not available'
    ]);
}

// Hook to add debugging action right before content is rendered
add_action('init', 'vdp_add_render_content_hook');

/**
 * Add a hook point right before content is rendered
 */
function vdp_add_render_content_hook() {
    // Create a wrapper for VDP_Router::render_content
    if (class_exists('VDP_Router')) {
        // Only apply if not already applied
        if (!has_action('vdp_before_render_content')) {
            add_filter('vdp_router_render_content', 'vdp_router_render_content_wrapper', 10, 2);
        }
    }
}

/**
 * Wrapper for render_content to add a hook point
 * 
 * @param string $action Current action
 * @param string $item Current item
 */
function vdp_router_render_content_wrapper($action, $item) {
    // Trigger hook before rendering content
    do_action('vdp_before_render_content', $action, $item);
    
    // Call original method
    VDP_Router::render_content($action, $item);
}

// Initialize debugging
vdp_setup_debugging();