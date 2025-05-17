<?php
/**
 * Dashboard Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard module class.
 */
class VDP_Dashboard {
    /**
     * Instance of this class.
     *
     * @var VDP_Dashboard
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Dashboard
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        // Initialize hooks
        add_action('vdp_dashboard_content', array($this, 'render_dashboard'), 10);
    }

    /**
     * Render dashboard content.
     */
    public function render_dashboard() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get vendor statistics
        $statistics = vdp_api()->get_vendor_statistics($vendor->get_id());
        
        // Get recent listings
        $recent_listings = vdp_api()->get_vendor_listings($vendor->get_id(), array(
            'limit' => 5,
        ));
        
        // Get featured listings
        $featured_listings = vdp_api()->get_vendor_featured_listings($vendor->get_id(), 3);
        
        // Get recent messages
        $recent_messages = vdp_api()->get_vendor_messages($vendor->get_id(), array(
            'limit' => 5,
        ));
        
        // Include dashboard template
        include VDP_PLUGIN_DIR . 'templates/dashboard-content.php';
    }

    /**
     * Get greeting based on time of day.
     *
     * @return string
     */
    public static function get_greeting() {
        $hour = date('G');
        
        if ($hour >= 5 && $hour < 12) {
            return __('Good morning', 'vendor-dashboard-pro');
        } elseif ($hour >= 12 && $hour < 18) {
            return __('Good afternoon', 'vendor-dashboard-pro');
        } else {
            return __('Good evening', 'vendor-dashboard-pro');
        }
    }

    /**
     * Get performance level based on statistics.
     *
     * @param array $statistics Vendor statistics.
     * @return string
     */
    public static function get_performance_level($statistics) {
        // This is a simplified example - in a real implementation,
        // you would use a more sophisticated algorithm
        
        if (!empty($statistics['average_rating']) && $statistics['average_rating'] >= 4.5) {
            return 'excellent';
        } elseif (!empty($statistics['average_rating']) && $statistics['average_rating'] >= 4.0) {
            return 'good';
        } elseif (!empty($statistics['average_rating']) && $statistics['average_rating'] >= 3.0) {
            return 'average';
        } else {
            return 'needs-improvement';
        }
    }

    /**
     * Get sales trend.
     *
     * @return string
     */
    public static function get_sales_trend() {
        // This would be based on real data in a production environment
        $trends = array('up', 'down', 'steady');
        return $trends[array_rand($trends)];
    }

    /**
     * Get quick actions for dashboard.
     *
     * @return array
     */
    public static function get_quick_actions() {
        return array(
            array(
                'title' => __('Add New Product', 'vendor-dashboard-pro'),
                'icon' => 'fas fa-plus-circle',
                'url' => vdp_get_dashboard_url('products/add'),
                'color' => '#3483fa',
            ),
            array(
                'title' => __('View Messages', 'vendor-dashboard-pro'),
                'icon' => 'fas fa-envelope',
                'url' => vdp_get_dashboard_url('messages'),
                'color' => '#39b54a',
            ),
            array(
                'title' => __('Edit Profile', 'vendor-dashboard-pro'),
                'icon' => 'fas fa-user-edit',
                'url' => vdp_get_dashboard_url('settings'),
                'color' => '#f5a623',
            ),
            array(
                'title' => __('View Analytics', 'vendor-dashboard-pro'),
                'icon' => 'fas fa-chart-line',
                'url' => vdp_get_dashboard_url('analytics'),
                'color' => '#9b59b6',
            ),
        );
    }
}

// Initialize Dashboard module
VDP_Dashboard::instance();