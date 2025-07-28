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
        
        // Get real analytics data for this vendor
        $analytics_data = self::get_vendor_analytics_data($vendor->get_id());
        
        include VDP_PLUGIN_DIR . 'templates/analytics-content.php';
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
     * Get vendor analytics data.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Date range and other filters
     * @return array
     */
    public static function get_vendor_analytics_data($vendor_id, $args = array()) {
        $args = wp_parse_args($args, array(
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to' => date('Y-m-d'),
        ));
        
        return array(
            'summary' => self::get_vendor_summary_data($vendor_id, $args),
            'sales_chart' => self::get_vendor_sales_chart_data($vendor_id, $args),
            'top_products' => self::get_vendor_top_products($vendor_id, $args),
            'performance_metrics' => self::get_vendor_performance_metrics($vendor_id, $args),
        );
    }

    /**
     * Get vendor summary data.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Date range and other filters
     * @return array
     */
    public static function get_vendor_summary_data($vendor_id, $args = array()) {
        // Get current period orders
        $current_orders = wc_get_orders(array(
            'status' => array('wc-processing', 'wc-completed'),
            'date_created' => $args['date_from'] . '...' . $args['date_to'],
            'meta_key' => 'hp_vendor',
            'meta_value' => $vendor_id,
            'limit' => -1,
        ));
        
        // Get previous period for comparison
        $days_diff = (strtotime($args['date_to']) - strtotime($args['date_from'])) / 86400;
        $previous_from = date('Y-m-d', strtotime($args['date_from'] . ' -' . $days_diff . ' days'));
        $previous_to = date('Y-m-d', strtotime($args['date_from'] . ' -1 day'));
        
        $previous_orders = wc_get_orders(array(
            'status' => array('wc-processing', 'wc-completed'),
            'date_created' => $previous_from . '...' . $previous_to,
            'meta_key' => 'hp_vendor',
            'meta_value' => $vendor_id,
            'limit' => -1,
        ));
        
        // Calculate current period metrics
        $current_sales_count = count($current_orders);
        $current_sales_amount = array_sum(array_map(function($order) {
            return $order->get_total();
        }, $current_orders));
        
        // Calculate previous period metrics
        $previous_sales_count = count($previous_orders);
        $previous_sales_amount = array_sum(array_map(function($order) {
            return $order->get_total();
        }, $previous_orders));
        
        // Calculate percentage changes
        $sales_increase = $previous_sales_count > 0 
            ? round((($current_sales_count - $previous_sales_count) / $previous_sales_count) * 100, 1)
            : 0;
            
        $amount_increase = $previous_sales_amount > 0 
            ? round((($current_sales_amount - $previous_sales_amount) / $previous_sales_amount) * 100, 1)
            : 0;
        
        // Get views count from listings
        $listings = self::get_vendor_listings($vendor_id);
        $views_count = 0;
        foreach ($listings as $listing_id) {
            $views_count += (int) get_post_meta($listing_id, 'hp_view_count', true);
        }
        
        // Calculate conversion rate
        $conversion_rate = $views_count > 0 ? round(($current_sales_count / $views_count) * 100, 2) : 0;
        
        // Calculate average order value
        $average_order_value = $current_sales_count > 0 ? $current_sales_amount / $current_sales_count : 0;
        
        return array(
            'sales_count' => $current_sales_count,
            'sales_amount' => $current_sales_amount,
            'views_count' => $views_count,
            'conversion_rate' => $conversion_rate,
            'average_order_value' => $average_order_value,
            'sales_increase' => $sales_increase,
            'amount_increase' => $amount_increase,
        );
    }

    /**
     * Get vendor sales chart data.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Date range and other filters
     * @return array
     */
    public static function get_vendor_sales_chart_data($vendor_id, $args = array()) {
        $chart_data = array();
        $start_date = new DateTime($args['date_from']);
        $end_date = new DateTime($args['date_to']);
        
        while ($start_date <= $end_date) {
            $current_date = $start_date->format('Y-m-d');
            
            // Get orders for this specific day
            $daily_orders = wc_get_orders(array(
                'status' => array('wc-processing', 'wc-completed'),
                'date_created' => $current_date,
                'meta_key' => 'hp_vendor',
                'meta_value' => $vendor_id,
                'limit' => -1,
            ));
            
            $daily_sales = array_sum(array_map(function($order) {
                return $order->get_total();
            }, $daily_orders));
            
            $chart_data[] = array(
                'date' => $current_date,
                'sales' => $daily_sales,
                'orders' => count($daily_orders),
            );
            
            $start_date->modify('+1 day');
        }
        
        return $chart_data;
    }

    /**
     * Get vendor top products.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Date range and other filters
     * @return array
     */
    public static function get_vendor_top_products($vendor_id, $args = array()) {
        // Get all orders in the period
        $orders = wc_get_orders(array(
            'status' => array('wc-processing', 'wc-completed'),
            'date_created' => $args['date_from'] . '...' . $args['date_to'],
            'meta_key' => 'hp_vendor',
            'meta_value' => $vendor_id,
            'limit' => -1,
        ));
        
        $product_stats = array();
        
        foreach ($orders as $order) {
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $product = $item->get_product();
                
                if (!$product) continue;
                
                if (!isset($product_stats[$product_id])) {
                    $product_stats[$product_id] = array(
                        'id' => $product_id,
                        'title' => $product->get_name(),
                        'sales_count' => 0,
                        'quantity_sold' => 0,
                        'sales_amount' => 0,
                        'views' => (int) get_post_meta($product_id, 'hp_view_count', true),
                    );
                }
                
                $product_stats[$product_id]['sales_count']++;
                $product_stats[$product_id]['quantity_sold'] += $item->get_quantity();
                $product_stats[$product_id]['sales_amount'] += $item->get_total();
            }
        }
        
        // Calculate conversion rates and sort by sales amount
        foreach ($product_stats as &$stats) {
            $stats['conversion_rate'] = $stats['views'] > 0 
                ? round(($stats['sales_count'] / $stats['views']) * 100, 2) 
                : 0;
        }
        
        // Sort by sales amount descending
        uasort($product_stats, function($a, $b) {
            return $b['sales_amount'] <=> $a['sales_amount'];
        });
        
        // Return top 5 products
        return array_slice($product_stats, 0, 5);
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