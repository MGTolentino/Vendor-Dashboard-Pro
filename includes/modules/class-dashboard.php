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
        
        $vendor_id = null;
        
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
        
        // Obtener listings recientes
        $recent_listings = array();
        if ($vendor_id) {
            // Asegurar que siempre usemos la misma función para obtener listings
            $recent_listings = self::get_recent_vendor_listings($vendor_id, 3);
            // Registrar para depuración
            vdp_debug_log("Cargados " . count($recent_listings) . " listings recientes para el dashboard");
        }
        
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
    
    /**
     * Obtiene los listings recientes de un vendor.
     * Esta función centralizada se usa tanto en el dashboard como en la sección de productos.
     *
     * @param int $vendor_id ID del vendor.
     * @param int $limit Número máximo de listings a obtener.
     * @return array Array de listings formateados.
     */
    public static function get_recent_vendor_listings($vendor_id, $limit = 3) {
        if (!$vendor_id) {
            return array();
        }
        
        // Primero intentar usar la clase de Products si existe
        if (class_exists('VDP_Products')) {
            return VDP_Products::get_vendor_listings($vendor_id, $limit, 0);
        }
        
        // Fallback a consulta directa si la clase Products no está disponible
        global $wpdb;
        $listings_posts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} 
            WHERE post_type = 'hp_listing' 
            AND post_parent = %d 
            AND post_status IN ('publish', 'draft', 'pending')
            ORDER BY post_date DESC
            LIMIT %d",
            $vendor_id, $limit
        ));
        
        $recent_listings = array();
        foreach ($listings_posts as $listing) {
            $price = get_post_meta($listing->ID, 'hp_price', true);
            $thumbnail_id = get_post_thumbnail_id($listing->ID);
            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
            
            $recent_listings[] = array(
                'id' => $listing->ID,
                'title' => $listing->post_title,
                'status' => $listing->post_status,
                'date' => $listing->post_date,
                'price' => $price ? $price : 0,
                'thumbnail' => $thumbnail_url,
                'edit_url' => vdp_get_dashboard_url('products', $listing->ID),
            );
        }
        
        return $recent_listings;
    }
}

// Initialize Dashboard module
VDP_Dashboard::instance();