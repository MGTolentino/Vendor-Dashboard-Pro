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
            'vdpSettings',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('vdp-ajax-nonce'),
                'dashboardUrl' => vdp_get_dashboard_url(),
                'i18n' => array(
                    'confirmDelete' => __('Are you sure you want to delete this item?', 'vendor-dashboard-pro'),
                    'saveChanges' => __('Save Changes', 'vendor-dashboard-pro'),
                    'saving' => __('Saving...', 'vendor-dashboard-pro'),
                    'saved' => __('Saved!', 'vendor-dashboard-pro'),
                    'error' => __('Error', 'vendor-dashboard-pro'),
                ),
            )
        );
    }

    /**
     * Enqueue assets on frontend.
     */
    public static function enqueue_assets() {
        // Only enqueue on dashboard pages
        if (vdp_is_my_store()) {
            wp_enqueue_style('vdp-main');
            wp_enqueue_script('vdp-main');
        }
    }

    /**
     * Register admin assets.
     */
    public static function register_admin_assets() {
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
    }
}