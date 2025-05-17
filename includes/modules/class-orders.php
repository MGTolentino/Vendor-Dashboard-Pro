<?php
/**
 * Orders Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Orders module class.
 */
class VDP_Orders {
    /**
     * Instance of this class.
     *
     * @var VDP_Orders
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Orders
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
        add_action('vdp_orders_content', array($this, 'render_orders_list'), 10);
        add_action('vdp_order_view_content', array($this, 'render_order_view'), 10);
    }

    /**
     * Render orders list.
     */
    public function render_orders_list() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get current page
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        
        // Get orders per page
        $per_page = 10;
        
        // For demo purposes, we'll create sample orders
        // In a real implementation, you would get actual orders from HivePress
        $orders = self::get_demo_orders();
        
        // Calculate total pages (for demo)
        $total_orders = count($orders);
        $total_pages = ceil($total_orders / $per_page);
        
        // Include orders list template
        include VDP_PLUGIN_DIR . 'templates/orders-content.php';
    }

    /**
     * Render order view.
     */
    public function render_order_view() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get order ID
        $order_id = get_query_var('vdp_item', 0);
        
        if (!$order_id) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Order not found.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // For demo purposes, we'll get a sample order
        // In a real implementation, you would get the actual order from HivePress
        $order = self::get_demo_order($order_id);
        
        if (!$order) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Order not found.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Include order view template
        include VDP_PLUGIN_DIR . 'templates/order-view-content.php';
    }

    /**
     * Get demo orders for testing.
     *
     * @return array
     */
    public static function get_demo_orders() {
        $orders = array();
        
        for ($i = 1; $i <= 20; $i++) {
            $status = self::get_random_status();
            $date = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
            
            $orders[] = array(
                'id' => $i,
                'order_number' => 'ORD-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'customer_name' => 'Customer ' . $i,
                'customer_email' => 'customer' . $i . '@example.com',
                'date' => $date,
                'status' => $status,
                'total' => rand(10, 1000),
                'items' => rand(1, 5),
                'payment_method' => self::get_random_payment_method(),
                'shipping_method' => self::get_random_shipping_method(),
            );
        }
        
        return $orders;
    }

    /**
     * Get a demo order by ID.
     *
     * @param int $order_id Order ID.
     * @return array|null
     */
    public static function get_demo_order($order_id) {
        $orders = self::get_demo_orders();
        
        foreach ($orders as $order) {
            if ($order['id'] == $order_id) {
                // Add more details for the single order view
                $order['items_details'] = self::get_demo_order_items($order_id);
                $order['shipping_address'] = self::get_demo_shipping_address();
                $order['billing_address'] = self::get_demo_billing_address();
                $order['notes'] = self::get_demo_order_notes();
                
                return $order;
            }
        }
        
        return null;
    }

    /**
     * Get demo order items.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public static function get_demo_order_items($order_id) {
        $items = array();
        $num_items = rand(1, 5);
        
        for ($i = 1; $i <= $num_items; $i++) {
            $price = rand(10, 200);
            $quantity = rand(1, 3);
            
            $items[] = array(
                'id' => $i,
                'name' => 'Product ' . $i . ' (Order ' . $order_id . ')',
                'sku' => 'SKU-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'price' => $price,
                'quantity' => $quantity,
                'total' => $price * $quantity,
            );
        }
        
        return $items;
    }

    /**
     * Get demo shipping address.
     *
     * @return array
     */
    public static function get_demo_shipping_address() {
        return array(
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_1' => '123 Main St',
            'address_2' => 'Apt 4B',
            'city' => 'New York',
            'state' => 'NY',
            'postcode' => '10001',
            'country' => 'US',
            'phone' => '123-456-7890',
            'email' => 'john.doe@example.com',
        );
    }

    /**
     * Get demo billing address.
     *
     * @return array
     */
    public static function get_demo_billing_address() {
        return array(
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_1' => '123 Main St',
            'address_2' => 'Apt 4B',
            'city' => 'New York',
            'state' => 'NY',
            'postcode' => '10001',
            'country' => 'US',
            'phone' => '123-456-7890',
            'email' => 'john.doe@example.com',
        );
    }

    /**
     * Get demo order notes.
     *
     * @return array
     */
    public static function get_demo_order_notes() {
        $notes = array();
        $num_notes = rand(1, 3);
        
        for ($i = 1; $i <= $num_notes; $i++) {
            $notes[] = array(
                'id' => $i,
                'author' => ($i % 2 == 0) ? 'Customer' : 'Vendor',
                'content' => 'This is a note ' . $i . ' about the order.',
                'date' => date('Y-m-d H:i:s', strtotime('-' . (5 - $i) . ' days')),
            );
        }
        
        return $notes;
    }

    /**
     * Get a random order status.
     *
     * @return string
     */
    public static function get_random_status() {
        $statuses = array(
            'pending', 'processing', 'completed', 'on-hold', 'cancelled'
        );
        
        return $statuses[array_rand($statuses)];
    }

    /**
     * Get a random payment method.
     *
     * @return string
     */
    public static function get_random_payment_method() {
        $methods = array(
            'Credit Card', 'PayPal', 'Bank Transfer', 'Cash on Delivery'
        );
        
        return $methods[array_rand($methods)];
    }

    /**
     * Get a random shipping method.
     *
     * @return string
     */
    public static function get_random_shipping_method() {
        $methods = array(
            'Standard Shipping', 'Express Shipping', 'Free Shipping', 'Local Pickup'
        );
        
        return $methods[array_rand($methods)];
    }

    /**
     * Get order status label.
     *
     * @param string $status Order status.
     * @return string
     */
    public static function get_status_label($status) {
        $statuses = array(
            'pending' => __('Pending', 'vendor-dashboard-pro'),
            'processing' => __('Processing', 'vendor-dashboard-pro'),
            'completed' => __('Completed', 'vendor-dashboard-pro'),
            'on-hold' => __('On Hold', 'vendor-dashboard-pro'),
            'cancelled' => __('Cancelled', 'vendor-dashboard-pro'),
        );
        
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }

    /**
     * Get order status class.
     *
     * @param string $status Order status.
     * @return string
     */
    public static function get_status_class($status) {
        $classes = array(
            'pending' => 'vdp-status-pending',
            'processing' => 'vdp-status-processing',
            'completed' => 'vdp-status-completed',
            'on-hold' => 'vdp-status-on-hold',
            'cancelled' => 'vdp-status-cancelled',
        );
        
        return isset($classes[$status]) ? $classes[$status] : '';
    }
}

// Initialize Orders module
VDP_Orders::instance();