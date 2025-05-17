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
        add_action('wp_loaded', array($this, 'register_dashboard_actions'));
    }

    /**
     * Register dashboard content actions
     */
    public function register_dashboard_actions() {
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
        
        // Get vendor stats - either real or demo
        if (method_exists($vendor, 'get_id')) {
            $vendor_id = $vendor->get_id();
            // Try to get real statistics if possible
            $statistics = self::get_vendor_statistics($vendor_id);
        } elseif (isset($vendor->get_id) && is_callable($vendor->get_id)) {
            $vendor_id = ($vendor->get_id)();
            // Try to get real statistics if possible
            $statistics = self::get_vendor_statistics($vendor_id);
        } else {
            // Fallback to demo stats
            $statistics = self::get_demo_statistics();
        }
        
        // Get recent listings placeholder
        $recent_listings = array();
        
        // Get recent messages placeholder
        $recent_messages = array();
        
        // Include dashboard template
        include VDP_PLUGIN_DIR . 'templates/dashboard-content.php';
    }

    /**
     * Get greeting based on time of day.
     *
     * @return string
     */
    public static function get_greeting() {
        $hour = current_time('G');
        
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
    public static function get_performance_level($statistics = array()) {
        if (empty($statistics)) {
            $statistics = self::get_demo_statistics();
        }
        
        // This is a simplified example - in a real implementation,
        // you would use a more sophisticated algorithm
        $rating = isset($statistics['average_rating']) ? $statistics['average_rating'] : 4.2;
        
        if ($rating >= 4.5) {
            return 'excellent';
        } elseif ($rating >= 4.0) {
            return 'good';
        } elseif ($rating >= 3.0) {
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
                'title' => __('Add New Listing', 'vendor-dashboard-pro'),
                'icon' => 'fas fa-plus-circle',
                'url' => vdp_get_dashboard_url('products', 'add'),
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
    
    /**
     * Get vendor statistics from real data.
     *
     * @param int $vendor_id Vendor ID
     * @return array Statistics or false if not available
     */
    public static function get_vendor_statistics($vendor_id) {
        if (!$vendor_id) {
            return self::get_demo_statistics();
        }
        
        // Here you would query for actual vendor statistics
        // For now, we'll return demo data with a consistent seed for the vendor
        // so the values don't change on each page load
        $seed = absint($vendor_id);
        mt_srand($seed);
        
        $stats = array(
            'sales_count' => mt_rand(10, 1000),
            'sales_amount' => mt_rand(1000, 100000),
            'views_count' => mt_rand(100, 10000),
            'conversion_rate' => mt_rand(1, 10),
            'average_rating' => mt_rand(35, 50) / 10,
            'ratings_count' => mt_rand(10, 500),
            'messages_count' => mt_rand(5, 50),
            'response_rate' => mt_rand(70, 100),
            'response_time' => mt_rand(1, 24),
        );
        
        // Reset random seed
        mt_srand();
        
        return $stats;
    }
    
    /**
     * Get demo statistics for development.
     * 
     * @return array Demo statistics
     */
    public static function get_demo_statistics() {
        return array(
            'sales_count' => rand(10, 1000),
            'sales_amount' => rand(1000, 100000),
            'views_count' => rand(100, 10000),
            'conversion_rate' => rand(1, 10),
            'average_rating' => rand(35, 50) / 10,
            'ratings_count' => rand(10, 500),
            'messages_count' => rand(5, 50),
            'response_rate' => rand(70, 100),
            'response_time' => rand(1, 24),
        );
    }
}

// Initialize Dashboard module
VDP_Dashboard::instance();