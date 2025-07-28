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
        add_action('vdp_orders_content', array($this, 'render_orders_list'), 10);
        add_action('vdp_order_view_content', array($this, 'render_order_view'), 10);
        
        // AJAX actions
        add_action('wp_ajax_vdp_update_order_status', array($this, 'ajax_update_order_status'));
        add_action('wp_ajax_vdp_add_order_note', array($this, 'ajax_add_order_note'));
        add_action('wp_ajax_vdp_filter_orders', array($this, 'ajax_filter_orders'));
    }

    /**
     * Render orders list.
     */
    public function render_orders_list() {
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get filter parameters
        $args = array(
            'status' => sanitize_text_field($_GET['status'] ?? 'any'),
            'search' => sanitize_text_field($_GET['search'] ?? ''),
            'date_from' => sanitize_text_field($_GET['date_from'] ?? ''),
            'date_to' => sanitize_text_field($_GET['date_to'] ?? ''),
            'per_page' => absint($_GET['per_page'] ?? 20),
            'page' => absint($_GET['paged'] ?? 1),
        );
        
        $orders_data = self::get_vendor_orders_data($vendor->get_id(), $args);
        
        include VDP_PLUGIN_DIR . 'templates/orders-content.php';
    }

    /**
     * Render order view.
     */
    public function render_order_view() {
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        $order_id = absint(get_query_var('vdp_item', 0));
        
        if (!$order_id) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Orden no encontrada.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        $order = self::get_vendor_order($order_id, $vendor->get_id());
        
        if (!$order) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Orden no encontrada o no tienes permisos para verla.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        include VDP_PLUGIN_DIR . 'templates/order-view-content.php';
    }

    /**
     * Get vendor orders data with summary and statistics.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_vendor_orders_data($vendor_id, $args = array()) {
        $args = wp_parse_args($args, array(
            'status' => 'any',
            'search' => '',
            'date_from' => '',
            'date_to' => '',
            'per_page' => 20,
            'page' => 1
        ));
        
        return array(
            'orders' => self::get_vendor_orders($vendor_id, $args),
            'summary' => self::get_vendor_orders_summary($vendor_id, $args),
            'pagination' => self::get_orders_pagination($vendor_id, $args),
            'statistics' => self::get_vendor_orders_statistics($vendor_id, $args),
        );
    }

    /**
     * Get vendor orders from WooCommerce.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_vendor_orders($vendor_id, $args = array()) {
        // Build WooCommerce orders query
        $query_args = array(
            'meta_key' => 'hp_vendor',
            'meta_value' => $vendor_id,
            'limit' => $args['per_page'],
            'offset' => ($args['page'] - 1) * $args['per_page'],
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Status filter
        if ($args['status'] !== 'any' && !empty($args['status'])) {
            $query_args['status'] = 'wc-' . $args['status'];
        }
        
        // Date filters
        if (!empty($args['date_from']) || !empty($args['date_to'])) {
            $date_query = '';
            if (!empty($args['date_from'])) {
                $date_query .= $args['date_from'];
            }
            if (!empty($args['date_to'])) {
                $date_query .= '...' . $args['date_to'];
            }
            if ($date_query) {
                $query_args['date_created'] = $date_query;
            }
        }
        
        // Get orders from WooCommerce
        $wc_orders = wc_get_orders($query_args);
        $orders = array();
        
        foreach ($wc_orders as $wc_order) {
            // Additional search filter (applied after WC query for performance)
            if (!empty($args['search'])) {
                $search_term = strtolower($args['search']);
                $order_number = strtolower($wc_order->get_order_number());
                $customer_name = strtolower($wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name());
                $customer_email = strtolower($wc_order->get_billing_email());
                
                if (strpos($order_number, $search_term) === false && 
                    strpos($customer_name, $search_term) === false && 
                    strpos($customer_email, $search_term) === false) {
                    continue;
                }
            }
            
            $order_data = array(
                'id' => $wc_order->get_id(),
                'order_number' => $wc_order->get_order_number(),
                'customer_name' => $wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name(),
                'customer_email' => $wc_order->get_billing_email(),
                'customer_id' => $wc_order->get_customer_id(),
                'date' => $wc_order->get_date_created()->format('Y-m-d H:i:s'),
                'status' => $wc_order->get_status(),
                'total' => $wc_order->get_total(),
                'currency' => $wc_order->get_currency(),
                'items_count' => $wc_order->get_item_count(),
                'payment_method' => $wc_order->get_payment_method_title(),
                'shipping_method' => self::get_order_shipping_method($wc_order),
                'needs_processing' => $wc_order->get_status() === 'processing',
            );
            
            $orders[] = $order_data;
        }
        
        return $orders;
    }

    /**
     * Get vendor orders summary.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_vendor_orders_summary($vendor_id, $args = array()) {
        // Get all orders for summary (without pagination)
        $summary_args = array(
            'meta_key' => 'hp_vendor',
            'meta_value' => $vendor_id,
            'limit' => -1,
        );
        
        // Apply date filters if present
        if (!empty($args['date_from']) || !empty($args['date_to'])) {
            $date_query = '';
            if (!empty($args['date_from'])) {
                $date_query .= $args['date_from'];
            }
            if (!empty($args['date_to'])) {
                $date_query .= '...' . $args['date_to'];
            }
            if ($date_query) {
                $summary_args['date_created'] = $date_query;
            }
        }
        
        $all_orders = wc_get_orders($summary_args);
        
        $summary = array(
            'total' => 0,
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'on_hold' => 0,
            'cancelled' => 0,
            'refunded' => 0,
            'total_revenue' => 0,
        );
        
        foreach ($all_orders as $order) {
            $status = $order->get_status();
            $summary['total']++;
            
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
            
            // Add to revenue if order is completed or processing
            if (in_array($status, ['completed', 'processing'])) {
                $summary['total_revenue'] += $order->get_total();
            }
        }
        
        return $summary;
    }

    /**
     * Get orders pagination info.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_orders_pagination($vendor_id, $args = array()) {
        // Count total orders matching criteria
        $count_args = array(
            'meta_key' => 'hp_vendor',
            'meta_value' => $vendor_id,
            'limit' => -1,
            'return' => 'ids',
        );
        
        if ($args['status'] !== 'any' && !empty($args['status'])) {
            $count_args['status'] = 'wc-' . $args['status'];
        }
        
        $total_orders = count(wc_get_orders($count_args));
        $total_pages = ceil($total_orders / $args['per_page']);
        
        return array(
            'total_orders' => $total_orders,
            'total_pages' => $total_pages,
            'current_page' => $args['page'],
            'per_page' => $args['per_page'],
        );
    }

    /**
     * Get vendor orders statistics.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_vendor_orders_statistics($vendor_id, $args = array()) {
        $summary = self::get_vendor_orders_summary($vendor_id, $args);
        
        // Calculate additional statistics
        $statistics = array(
            'average_order_value' => 0,
            'completion_rate' => 0,
            'pending_orders_value' => 0,
            'recent_orders_trend' => 0,
        );
        
        if ($summary['total'] > 0) {
            $statistics['average_order_value'] = $summary['total_revenue'] / ($summary['completed'] + $summary['processing']);
            $statistics['completion_rate'] = round(($summary['completed'] / $summary['total']) * 100, 1);
        }
        
        // Get pending orders value
        $pending_orders = wc_get_orders(array(
            'meta_key' => 'hp_vendor',
            'meta_value' => $vendor_id,
            'status' => array('wc-pending', 'wc-processing'),
            'limit' => -1,
        ));
        
        foreach ($pending_orders as $order) {
            $statistics['pending_orders_value'] += $order->get_total();
        }
        
        return $statistics;
    }

    /**
     * Get single vendor order.
     *
     * @param int $order_id Order ID
     * @param int $vendor_id Vendor post ID
     * @return array|null
     */
    public static function get_vendor_order($order_id, $vendor_id) {
        $wc_order = wc_get_order($order_id);
        
        if (!$wc_order) {
            return null;
        }
        
        // Verify this order belongs to the vendor
        $order_vendor_id = get_post_meta($order_id, 'hp_vendor', true);
        if ($order_vendor_id != $vendor_id) {
            return null;
        }
        
        $order_data = array(
            'id' => $wc_order->get_id(),
            'order_number' => $wc_order->get_order_number(),
            'date_created' => $wc_order->get_date_created(),
            'date_modified' => $wc_order->get_date_modified(),
            'status' => $wc_order->get_status(),
            'currency' => $wc_order->get_currency(),
            'total' => $wc_order->get_total(),
            'subtotal' => $wc_order->get_subtotal(),
            'total_tax' => $wc_order->get_total_tax(),
            'shipping_total' => $wc_order->get_shipping_total(),
            'discount_total' => $wc_order->get_discount_total(),
            'payment_method' => $wc_order->get_payment_method_title(),
            'transaction_id' => $wc_order->get_transaction_id(),
            
            // Customer info
            'customer_id' => $wc_order->get_customer_id(),
            'customer_note' => $wc_order->get_customer_note(),
            
            // Billing address
            'billing_address' => array(
                'first_name' => $wc_order->get_billing_first_name(),
                'last_name' => $wc_order->get_billing_last_name(),
                'company' => $wc_order->get_billing_company(),
                'address_1' => $wc_order->get_billing_address_1(),
                'address_2' => $wc_order->get_billing_address_2(),
                'city' => $wc_order->get_billing_city(),
                'state' => $wc_order->get_billing_state(),
                'postcode' => $wc_order->get_billing_postcode(),
                'country' => $wc_order->get_billing_country(),
                'email' => $wc_order->get_billing_email(),
                'phone' => $wc_order->get_billing_phone(),
            ),
            
            // Shipping address
            'shipping_address' => array(
                'first_name' => $wc_order->get_shipping_first_name(),
                'last_name' => $wc_order->get_shipping_last_name(),
                'company' => $wc_order->get_shipping_company(),
                'address_1' => $wc_order->get_shipping_address_1(),
                'address_2' => $wc_order->get_shipping_address_2(),
                'city' => $wc_order->get_shipping_city(),
                'state' => $wc_order->get_shipping_state(),
                'postcode' => $wc_order->get_shipping_postcode(),
                'country' => $wc_order->get_shipping_country(),
            ),
            
            // Order items
            'items' => self::get_order_items($wc_order),
            
            // Order notes
            'notes' => self::get_order_notes($order_id),
            
            // Additional data
            'needs_payment' => $wc_order->needs_payment(),
            'needs_processing' => $wc_order->needs_processing(),
        );
        
        return $order_data;
    }

    /**
     * Get order items.
     *
     * @param WC_Order $order WooCommerce order object
     * @return array
     */
    public static function get_order_items($order) {
        $items = array();
        
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            
            $item_data = array(
                'id' => $item_id,
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total(),
                'subtotal' => $item->get_subtotal(),
                'tax_total' => $item->get_total_tax(),
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
            );
            
            if ($product) {
                $item_data['sku'] = $product->get_sku();
                $item_data['price'] = $product->get_price();
                $item_data['image_url'] = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
            }
            
            $items[] = $item_data;
        }
        
        return $items;
    }

    /**
     * Get order notes.
     *
     * @param int $order_id Order ID
     * @return array
     */
    public static function get_order_notes($order_id) {
        $notes = wc_get_order_notes(array(
            'order_id' => $order_id,
            'order_by' => 'date_created',
            'order' => 'DESC',
        ));
        
        $notes_data = array();
        foreach ($notes as $note) {
            $notes_data[] = array(
                'id' => $note->id,
                'author' => $note->added_by,
                'content' => $note->content,
                'date' => $note->date_created->format('Y-m-d H:i:s'),
                'customer_note' => (bool) $note->customer_note,
            );
        }
        
        return $notes_data;
    }

    /**
     * Get order shipping method.
     *
     * @param WC_Order $order WooCommerce order object
     * @return string
     */
    public static function get_order_shipping_method($order) {
        $shipping_methods = array();
        foreach ($order->get_shipping_methods() as $shipping_method) {
            $shipping_methods[] = $shipping_method->get_method_title();
        }
        
        return !empty($shipping_methods) ? implode(', ', $shipping_methods) : __('N/A', 'vendor-dashboard-pro');
    }

    /**
     * AJAX handler for updating order status.
     */
    public function ajax_update_order_status() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_orders_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $order_id = absint($_POST['order_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        // Verify this order belongs to the vendor
        $order_vendor_id = get_post_meta($order_id, 'hp_vendor', true);
        if ($order_vendor_id != $vendor->get_id()) {
            wp_die('Access denied.');
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => 'Orden no encontrada.'));
        }
        
        // Update order status
        $order->update_status($new_status, __('Estado actualizado por el vendor.', 'vendor-dashboard-pro'));
        
        wp_send_json_success(array('message' => 'Estado de la orden actualizado correctamente.'));
    }

    /**
     * AJAX handler for adding order notes.
     */
    public function ajax_add_order_note() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_orders_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $order_id = absint($_POST['order_id']);
        $note_content = sanitize_textarea_field($_POST['note']);
        $customer_note = isset($_POST['customer_note']) ? 1 : 0;
        
        // Verify this order belongs to the vendor
        $order_vendor_id = get_post_meta($order_id, 'hp_vendor', true);
        if ($order_vendor_id != $vendor->get_id()) {
            wp_die('Access denied.');
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => 'Orden no encontrada.'));
        }
        
        // Add order note
        $note_id = $order->add_order_note($note_content, $customer_note);
        
        if ($note_id) {
            wp_send_json_success(array('message' => 'Nota agregada correctamente.'));
        } else {
            wp_send_json_error(array('message' => 'Error al agregar la nota.'));
        }
    }

    /**
     * AJAX handler for filtering orders.
     */
    public function ajax_filter_orders() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_orders_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $args = array(
            'status' => sanitize_text_field($_POST['status'] ?? 'any'),
            'search' => sanitize_text_field($_POST['search'] ?? ''),
            'date_from' => sanitize_text_field($_POST['date_from'] ?? ''),
            'date_to' => sanitize_text_field($_POST['date_to'] ?? ''),
            'per_page' => absint($_POST['per_page'] ?? 20),
            'page' => absint($_POST['page'] ?? 1),
        );
        
        $orders_data = self::get_vendor_orders_data($vendor->get_id(), $args);
        
        wp_send_json_success($orders_data);
    }

    /**
     * Get order status label.
     *
     * @param string $status Order status.
     * @return string
     */
    public static function get_status_label($status) {
        $statuses = array(
            'pending' => __('Pendiente', 'vendor-dashboard-pro'),
            'processing' => __('Procesando', 'vendor-dashboard-pro'),
            'on-hold' => __('En espera', 'vendor-dashboard-pro'),
            'completed' => __('Completada', 'vendor-dashboard-pro'),
            'cancelled' => __('Cancelada', 'vendor-dashboard-pro'),
            'refunded' => __('Reembolsada', 'vendor-dashboard-pro'),
            'failed' => __('Fallida', 'vendor-dashboard-pro'),
        );
        
        return isset($statuses[$status]) ? $statuses[$status] : ucfirst(str_replace('-', ' ', $status));
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
            'on-hold' => 'vdp-status-on-hold',
            'completed' => 'vdp-status-completed',
            'cancelled' => 'vdp-status-cancelled',
            'refunded' => 'vdp-status-refunded',
            'failed' => 'vdp-status-failed',
        );
        
        return isset($classes[$status]) ? $classes[$status] : 'vdp-status-default';
    }
}

VDP_Orders::instance();