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
        // Initialize hooks
        add_action('vdp_analytics_content', array($this, 'render_analytics'), 10);
    }

    /**
     * Render analytics content.
     */
    public function render_analytics() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get analytics data
        $analytics_data = self::get_demo_analytics_data();
        
        // Include analytics template
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
        
        // Last 12 months data
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
     * Get demo visits chart data.
     *
     * @return array
     */
    public static function get_demo_visits_chart_data() {
        $data = array();
        $days = 30;
        $date = new DateTime();
        $date->modify('-' . ($days - 1) . ' days');
        
        for ($i = 0; $i < $days; $i++) {
            $current_date = $date->format('Y-m-d');
            $views = rand(20, 200);
            
            $data[] = array(
                'date' => $current_date,
                'views' => $views,
            );
            
            $date->modify('+1 day');
        }
        
        return $data;
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
     * Get demo conversion rate data.
     *
     * @return array
     */
    public static function get_demo_conversion_rate_data() {
        $data = array();
        $days = 30;
        $date = new DateTime();
        $date->modify('-' . ($days - 1) . ' days');
        
        for ($i = 0; $i < $days; $i++) {
            $current_date = $date->format('Y-m-d');
            $rate = rand(3, 8) + (rand(0, 100) / 100);
            
            $data[] = array(
                'date' => $current_date,
                'rate' => $rate,
            );
            
            $date->modify('+1 day');
        }
        
        return $data;
    }

    /**
     * Get demo performance metrics.
     *
     * @return array
     */
    public static function get_demo_performance_metrics() {
        return array(
            'response_time' => rand(1, 24),
            'response_rate' => rand(80, 100),
            'order_fulfillment_time' => rand(1, 5),
            'customer_satisfaction' => rand(80, 100),
            'repeat_customer_rate' => rand(20, 50),
            'return_rate' => rand(1, 10),
        );
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

// Initialize Analytics module
VDP_Analytics::instance();