<?php
/**
 * Assets Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets class for managing styles and scripts.
 */
class VDP_Assets {
    /**
     * Initialize assets.
     */
    public static function init() {
        // Register and enqueue assets
        add_action('wp_enqueue_scripts', array(__CLASS__, 'register_assets'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        
        // Admin assets
        add_action('admin_enqueue_scripts', array(__CLASS__, 'register_admin_assets'));
        
        // Add shortcode
        add_shortcode('vendor_dashboard_pro', array('VDP_Router', 'shortcode_callback'));
    }

    /**
     * Register assets.
     */
    public static function register_assets() {
        // Register styles
        wp_register_style(
            'vdp-fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            array(),
            '5.15.4'
        );
        
        wp_register_style(
            'vdp-main',
            VDP_PLUGIN_URL . 'assets/css/main.css',
            array('vdp-fontawesome'),
            VDP_VERSION
        );
        
        wp_register_style(
            'vdp-dashboard',
            VDP_PLUGIN_URL . 'assets/css/dashboard.css',
            array('vdp-main'),
            VDP_VERSION
        );
        
        wp_register_style(
            'vdp-orders',
            VDP_PLUGIN_URL . 'assets/css/orders.css',
            array('vdp-main'),
            VDP_VERSION
        );
        
        // Register common messages styles that will always be loaded
        wp_register_style(
            'vdp-messages-common',
            VDP_PLUGIN_URL . 'assets/css/messages-common.css',
            array('vdp-main'),
            VDP_VERSION
        );
        
        // Register full messages styles that depend on the common styles
        wp_register_style(
            'vdp-messages',
            VDP_PLUGIN_URL . 'assets/css/messages.css',
            array('vdp-main', 'vdp-messages-common'),
            VDP_VERSION
        );
        
        // Register scripts
        wp_register_script(
            'vdp-chart',
            'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js',
            array(),
            '3.7.0',
            true
        );
        
        wp_register_script(
            'vdp-main',
            VDP_PLUGIN_URL . 'assets/js/main.js',
            array('jquery', 'vdp-chart'),
            VDP_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script(
            'vdp-main',
            'vdp_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('vdp-ajax-nonce'),
                'dashboard_url' => vdp_get_dashboard_url(),
                'texts' => array(
                    'confirm_delete' => __('Are you sure you want to delete this item?', 'vendor-dashboard-pro'),
                    'save_changes' => __('Save Changes', 'vendor-dashboard-pro'),
                    'loading' => __('Loading...', 'vendor-dashboard-pro'),
                    'saving' => __('Saving...', 'vendor-dashboard-pro'),
                    'saved' => __('Saved!', 'vendor-dashboard-pro'),
                    'error' => __('An error occurred. Please try again.', 'vendor-dashboard-pro'),
                    'send_reply' => __('Send Reply', 'vendor-dashboard-pro'),
                    'sending' => __('Sending...', 'vendor-dashboard-pro'),
                ),
            )
        );
    }

    /**
     * Enqueue assets on frontend.
     */
    public static function enqueue_assets() {
        // If we're on a page with the vendor dashboard shortcode or AJAX request
        if (vdp_is_dashboard_page() || (isset($_GET['vdp_ajax']) && $_GET['vdp_ajax'])) {
            wp_enqueue_style('vdp-main');
            wp_enqueue_style('vdp-dashboard');
            
            // Always load common messages styles
            wp_enqueue_style('vdp-messages-common');
            
            // Siempre cargar los estilos completos de mensajes para evitar problemas de CSS
            wp_enqueue_style('vdp-messages');
            
            // Enqueue orders stylesheet if on orders page
            if (isset($_GET['vdp-action']) && ($_GET['vdp-action'] === 'orders' || strpos($_GET['vdp-action'], 'orders/') === 0)) {
                wp_enqueue_style('vdp-orders');
            }
            
            // Log para depuración CSS
            if (function_exists('vdp_debug_log')) {
                vdp_debug_log("Cargando estilos CSS de mensajes en todas las páginas", "info");
                
                // Log adicional para mensajes específicos
                if (isset($_GET['vdp-action'])) {
                    vdp_debug_log("Acción actual: " . $_GET['vdp-action'], "info");
                    
                    if (isset($_GET['vdp-item'])) {
                        vdp_debug_log("Viendo mensaje con ID: " . $_GET['vdp-item'], "info");
                    }
                }
            }
            
            wp_enqueue_script('vdp-main');
        }
        
        // También cargamos el CSS de mensajes en las páginas de listing individual para el modal de contacto
        if (is_singular('hp_listing') && vdp_is_active()) {
            wp_enqueue_style('vdp-messages');
        }
    }

    /**
     * Check if shortcode exists on the current page.
     *
     * @return bool Whether the shortcode exists.
     */
    public static function has_shortcode() {
        global $post;
        
        // Return false if no post object or post content
        if (!is_a($post, 'WP_Post') || !$post->post_content) {
            return false;
        }
        
        // Check for shortcode in content
        return has_shortcode($post->post_content, 'vendor_dashboard_pro');
    }

    /**
     * Register admin assets.
     */
    public static function register_admin_assets($hook) {
        wp_register_style(
            'vdp-admin',
            VDP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            VDP_VERSION
        );
        
        wp_register_script(
            'vdp-admin',
            VDP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            VDP_VERSION,
            true
        );
        
        // Only load on the plugin's settings page
        if ('toplevel_page_vendor-dashboard-pro' === $hook) {
            wp_enqueue_style('vdp-admin');
            wp_enqueue_script('vdp-admin');
            
            // Localize admin script
            wp_localize_script(
                'vdp-admin',
                'vdpAdmin',
                array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('vdp-admin-nonce'),
                )
            );
        }
    }
}