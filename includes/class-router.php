<?php
/**
 * Router Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Router class for handling custom routes.
 */
class VDP_Router {
    /**
     * Initialize router.
     */
    public static function init() {
        // Add query vars
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'));
        
        // Register rewrite rules
        add_action('init', array(__CLASS__, 'add_rewrite_rules'));
        
        // Handle template include
        add_filter('template_include', array(__CLASS__, 'template_include'), 100);
    }

    /**
     * Add custom query vars.
     *
     * @param array $vars Existing query vars.
     * @return array
     */
    public static function add_query_vars($vars) {
        $vars[] = 'vdp_dashboard';
        $vars[] = 'vdp_section';
        $vars[] = 'vdp_action';
        $vars[] = 'vdp_item';
        return $vars;
    }

    /**
     * Add rewrite rules.
     */
    public static function add_rewrite_rules() {
        // Main dashboard route
        add_rewrite_rule(
            'my-store/?$',
            'index.php?vdp_dashboard=1',
            'top'
        );
        
        // Dashboard sections
        add_rewrite_rule(
            'my-store/([^/]+)/?$',
            'index.php?vdp_dashboard=1&vdp_section=$matches[1]',
            'top'
        );
        
        // Dashboard actions
        add_rewrite_rule(
            'my-store/([^/]+)/([^/]+)/?$',
            'index.php?vdp_dashboard=1&vdp_section=$matches[1]&vdp_action=$matches[2]',
            'top'
        );
        
        // Dashboard items
        add_rewrite_rule(
            'my-store/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?vdp_dashboard=1&vdp_section=$matches[1]&vdp_action=$matches[2]&vdp_item=$matches[3]',
            'top'
        );
    }

    /**
     * Handle template include for dashboard pages.
     *
     * @param string $template Template path.
     * @return string
     */
    public static function template_include($template) {
        // Check if we're on a dashboard page
        if (get_query_var('vdp_dashboard', false) !== false) {
            // Check if user is logged in
            if (!is_user_logged_in()) {
                // Redirect to login page
                auth_redirect();
                exit;
            }
            
            // Check if user is a vendor
            if (!vdp_is_user_vendor()) {
                // Redirect to vendor registration if available
                if (function_exists('hivepress') && method_exists(hivepress()->router, 'get_url')) {
                    $redirect_url = hivepress()->router->get_url('vendor_register_page');
                    
                    if ($redirect_url) {
                        wp_redirect($redirect_url);
                        exit;
                    }
                }
                
                // Otherwise, redirect to home
                wp_redirect(home_url());
                exit;
            }
            
            // Get current section
            $section = get_query_var('vdp_section', 'dashboard');
            $action = get_query_var('vdp_action', '');
            $item = get_query_var('vdp_item', '');
            
            // Load appropriate template based on section
            switch ($section) {
                case 'products':
                    if ($action === 'edit' && !empty($item)) {
                        $template_file = 'product-edit.php';
                    } elseif ($action === 'add') {
                        $template_file = 'product-edit.php';
                    } else {
                        $template_file = 'products.php';
                    }
                    break;
                    
                case 'orders':
                    if ($action === 'view' && !empty($item)) {
                        $template_file = 'order-view.php';
                    } else {
                        $template_file = 'orders.php';
                    }
                    break;
                    
                case 'messages':
                    if ($action === 'view' && !empty($item)) {
                        $template_file = 'message-view.php';
                    } else {
                        $template_file = 'messages.php';
                    }
                    break;
                    
                case 'analytics':
                    $template_file = 'analytics.php';
                    break;
                    
                case 'settings':
                    $template_file = 'settings.php';
                    break;
                    
                case 'dashboard':
                default:
                    $template_file = 'dashboard.php';
                    break;
            }
            
            // Locate template
            $located = self::locate_template($template_file);
            
            if ($located) {
                return $located;
            }
        }
        
        return $template;
    }

    /**
     * Locate a template file.
     *
     * @param string $template_name Template file name.
     * @return string|false
     */
    public static function locate_template($template_name) {
        // Check theme directory first
        $template = locate_template(array(
            'vendor-dashboard-pro/' . $template_name,
        ));
        
        // If not found in theme, check plugin directory
        if (!$template) {
            $template = VDP_PLUGIN_DIR . 'templates/' . $template_name;
            
            if (!file_exists($template)) {
                return false;
            }
        }
        
        return $template;
    }
}