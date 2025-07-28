<?php
/**
 * Analytics Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analytics module class.
 */
class VDP_Analytics {
    /**
     * Instance of this class.
     *
     * @var VDP_Analytics
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Analytics
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
        add_action('vdp_analytics_content', array($this, 'render_analytics'), 10);
    }

    /**
     * Render analytics content.
     */
    public function render_analytics() {
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        $analytics_data = self::get_demo_analytics_data();
        
        include VDP_PLUGIN_DIR . 'templates/analytics-content.php';
    }

    /**
     * Get demo analytics data.
     *
     * @return array
     */
    public static function get_demo_analytics_data() {
        return array(
            'summary' => self::get_demo_summary_data(),
            'sales_chart' => self::get_demo_sales_chart_data(),
            'visits_chart' => self::get_demo_visits_chart_data(),
            'top_products' => self::get_demo_top_products(),
            'conversion_rate' => self::get_demo_conversion_rate_data(),
            'performance_metrics' => self::get_demo_performance_metrics(),
        );
    }

    /**
     * Get demo summary data.
     *
     * @return array
     */
    public static function get_demo_summary_data() {
        return array(
            'sales_count' => rand(50, 500),
            'sales_amount' => rand(5000, 50000),
            'views_count' => rand(1000, 10000),
            'conversion_rate' => rand(3, 8),
            'average_order_value' => rand(50, 200),
            'sales_increase' => rand(5, 20),
            'views_increase' => rand(10, 30),
        );
    }

    /**
     * Get demo sales chart data.
     *
     * @return array
     */
    public static function get_demo_sales_chart_data() {
        $data = array();
        $current_month = date('n');
        $current_year = date('Y');
        
        for ($i = 11; $i >= 0; $i--) {
            $month = $current_month - $i;
            $year = $current_year;
            
            if ($month <= 0) {
                $month += 12;
                $year--;
            }
            
            $month_name = date('F', mktime(0, 0, 0, $month, 1, $year));
            $sales = rand(5000, 20000);
            
            $data[] = array(
                'month' => $month_name,
                'sales' => $sales,
            );
        }
        
        return $data;
    }

    /**
     * Get vendor visits chart data.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Date range and other filters
     * @return array
     */
    public static function get_vendor_visits_chart_data($vendor_id, $args = array()) {
        // For now, return empty array as HivePress doesn't track daily views by default
        // This could be extended with Google Analytics integration or custom tracking
        return array();
    }

    /**
     * Get demo top products.
     *
     * @return array
     */
    public static function get_demo_top_products() {
        $products = array();
        
        for ($i = 1; $i <= 5; $i++) {
            $products[] = array(
                'id' => $i,
                'title' => 'Product ' . $i,
                'sales_count' => rand(10, 100),
                'sales_amount' => rand(1000, 10000),
                'views' => rand(100, 1000),
                'conversion_rate' => rand(3, 15),
            );
        }
        
        return $products;
    }

    /**
     * Get vendor conversion rate data.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Date range and other filters
     * @return array
     */
    public static function get_vendor_conversion_rate_data($vendor_id, $args = array()) {
        // Basic conversion rate calculation based on orders vs total views
        $summary = self::get_vendor_summary_data($vendor_id, $args);
        
        return array(
            'current_rate' => $summary['conversion_rate'],
            'data' => array() // Could be extended with daily breakdown
        );
    }
    
    /**
     * Get vendor performance metrics.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Date range and other filters
     * @return array
     */
    public static function get_vendor_performance_metrics($vendor_id, $args = array()) {
        // Get basic metrics from vendor and orders data
        $vendor_orders = wc_get_orders(array(
            'status' => array('wc-processing', 'wc-completed', 'wc-refunded'),
            'date_created' => $args['date_from'] . '...' . $args['date_to'],
            'meta_key' => 'hp_vendor',
            'meta_value' => $vendor_id,
            'limit' => -1,
        ));
        
        $completed_orders = array_filter($vendor_orders, function($order) {
            return $order->get_status() === 'completed';
        });
        
        $refunded_orders = array_filter($vendor_orders, function($order) {
            return $order->get_status() === 'refunded';
        });
        
        $total_orders = count($vendor_orders);
        $completion_rate = $total_orders > 0 ? round((count($completed_orders) / $total_orders) * 100, 1) : 0;
        $return_rate = $total_orders > 0 ? round((count($refunded_orders) / $total_orders) * 100, 1) : 0;
        
        return array(
            'response_time' => 24, // Could be calculated from order processing times
            'response_rate' => 95, // Could be calculated from messages/inquiries
            'order_fulfillment_time' => 2, // Could be calculated from order dates
            'customer_satisfaction' => 90, // Would need review/rating integration
            'completion_rate' => $completion_rate,
            'return_rate' => $return_rate,
        );
    }
    
    /**
     * Get vendor listings IDs.
     *
     * @param int $vendor_id Vendor post ID
     * @return array
     */
    public static function get_vendor_listings($vendor_id) {
        $listings = get_posts(array(
            'post_type' => 'hp_listing',
            'post_parent' => $vendor_id,
            'post_status' => array('publish', 'draft', 'pending'),
            'numberposts' => -1,
            'fields' => 'ids',
        ));
        
        return $listings;
    }

    /**
     * Get chart periods.
     *
     * @return array
     */
    public static function get_chart_periods() {
        return array(
            '7days' => __('Last 7 Days', 'vendor-dashboard-pro'),
            '30days' => __('Last 30 Days', 'vendor-dashboard-pro'),
            '3months' => __('Last 3 Months', 'vendor-dashboard-pro'),
            '6months' => __('Last 6 Months', 'vendor-dashboard-pro'),
            '12months' => __('Last 12 Months', 'vendor-dashboard-pro'),
        );
    }
}

VDP_Analytics::instance();