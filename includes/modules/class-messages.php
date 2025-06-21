<?php
/**
 * Messages Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Messages module class.
 */
class VDP_Messages {
    /**
     * Instance of this class.
     *
     * @var VDP_Messages
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Messages
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
        add_action('vdp_messages_content', array($this, 'render_messages_list'), 10);
        add_action('vdp_message_view_content', array($this, 'render_message_view'), 10);
    }

    /**
     * Render messages list.
     */
    public function render_messages_list() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Verificar que el vendedor tiene el método get_id
        $vendor_id = null;
        
        if (is_object($vendor) && method_exists($vendor, 'get_id')) {
            $vendor_id = $vendor->get_id();
        } elseif (is_object($vendor) && isset($vendor->get_id) && is_callable($vendor->get_id)) {
            $vendor_id = ($vendor->get_id)();
        } else {
            return; // No podemos continuar sin un vendor_id
        }
        
        // Get current page
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        
        // Get messages per page
        $per_page = 10;
        
        // Check if we should use real or demo data
        if (self::are_tables_created()) {
            $messages = self::get_vendor_messages($vendor_id, $paged, $per_page);
            $total_messages = self::get_total_messages_count($vendor_id);
        } else {
            // For demo purposes if tables aren't created yet
            $messages = self::get_demo_messages();
            $total_messages = count($messages);
        }
        
        // Calculate total pages
        $total_pages = ceil($total_messages / $per_page);
        
        // Include messages list template
        include VDP_PLUGIN_DIR . 'templates/messages-content.php';
    }

    /**
     * Render message view.
     */
    public function render_message_view() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Verificar que el vendedor tiene el método get_id
        $vendor_id = null;
        
        if (is_object($vendor) && method_exists($vendor, 'get_id')) {
            $vendor_id = $vendor->get_id();
        } elseif (is_object($vendor) && isset($vendor->get_id) && is_callable($vendor->get_id)) {
            $vendor_id = ($vendor->get_id)();
        } else {
            return; // No podemos continuar sin un vendor_id
        }
        
        // Get message ID
        $message_id = isset($_GET['vdp-item']) ? absint($_GET['vdp-item']) : 0;
        
        // Debugging message ID detection
        vdp_debug_log("Message view render function called with vendor_id: $vendor_id", "info");
        vdp_debug_log("GET parameters: " . json_encode($_GET), "info");
        vdp_debug_log("Detected message_id: $message_id", "info");
        
        // Show error if no message ID
        if (!$message_id) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Message not found. No message ID provided.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Check if we should use real or demo data
        if (self::are_tables_created()) {
            $message = self::get_message($message_id, $vendor_id);
            
            // Mark message as read
            if ($message && !$message['is_read']) {
                self::mark_message_as_read($message_id);
            }
        } else {
            // For demo purposes if tables aren't created yet
            $message = self::get_demo_message($message_id);
        }
        
        if (!$message) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Message not found or you do not have permission to view it.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Include message view template
        include VDP_PLUGIN_DIR . 'templates/message-view-content.php';
    }
    
    /**
     * Check if the database tables have been created.
     *
     * @return bool
     */
    private static function are_tables_created() {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'vdp_messages';
        $table_replies = $wpdb->prefix . 'vdp_message_replies';
        
        $messages_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_messages}'") === $table_messages;
        $replies_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_replies}'") === $table_replies;
        
        return $messages_exists && $replies_exists;
    }
    
    /**
     * Get vendor messages from the database.
     *
     * @param int $vendor_id Vendor ID.
     * @param int $paged Current page.
     * @param int $per_page Items per page.
     * @return array
     */
    public static function get_vendor_messages($vendor_id, $paged = 1, $per_page = 10) {
        global $wpdb;
        
        $offset = ($paged - 1) * $per_page;
        $table_messages = $wpdb->prefix . 'vdp_messages';
        $table_replies = $wpdb->prefix . 'vdp_message_replies';
        
        // Obtener mensajes de la base de datos
        $query = $wpdb->prepare(
            "SELECT m.*, 
                   COUNT(r.id) > 0 AS has_response,
                   u.display_name AS sender_name,
                   p.post_title AS listing_title
            FROM {$table_messages} m
            LEFT JOIN {$table_replies} r ON m.id = r.message_id
            LEFT JOIN {$wpdb->users} u ON m.sender_id = u.ID
            LEFT JOIN {$wpdb->posts} p ON m.listing_id = p.ID
            WHERE m.vendor_id = %d
            AND m.is_archived = 0
            GROUP BY m.id
            ORDER BY m.date_created DESC
            LIMIT %d OFFSET %d",
            $vendor_id, $per_page, $offset
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // Formatear los resultados
        $messages = array();
        
        if (!empty($results)) {
            foreach ($results as $result) {
                // Obtener avatar del remitente
                $sender_avatar = get_avatar_url($result['sender_id'], array('size' => 96));
                
                // Información del usuario
                $user = get_userdata($result['sender_id']);
                $customer_since = ($user && isset($user->user_registered)) ? $user->user_registered : '';
                
                // Contar pedidos (esto depende de la estructura de WooCommerce)
                $orders_count = 0;
                if (function_exists('wc_get_orders')) {
                    $args = array(
                        'customer_id' => $result['sender_id'],
                        'return' => 'ids',
                        'limit' => -1,
                    );
                    $orders = wc_get_orders($args);
                    $orders_count = count($orders);
                }
                
                // Información del producto/listing
                $product_url = get_permalink($result['listing_id']);
                
                // Formatear mensaje
                $messages[] = array(
                    'id' => $result['id'],
                    'sender_id' => $result['sender_id'],
                    'sender_name' => $result['sender_name'],
                    'sender_avatar' => $sender_avatar,
                    'subject' => $result['subject'],
                    'content' => $result['content'],
                    'date' => $result['date_created'],
                    'is_read' => (bool) $result['is_read'],
                    'listing_id' => $result['listing_id'],
                    'listing_title' => $result['listing_title'],
                    'has_response' => (bool) $result['has_response'],
                    'customer_since' => $customer_since,
                    'orders_count' => $orders_count,
                    'product_url' => $product_url,
                    'product_title' => $result['listing_title'],
                );
            }
        }
        
        return $messages;
    }
    
    /**
     * Get total messages count.
     *
     * @param int $vendor_id Vendor ID.
     * @return int
     */
    public static function get_total_messages_count($vendor_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'vdp_messages';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_messages} WHERE vendor_id = %d AND is_archived = 0",
            $vendor_id
        ));
        
        return (int) $count;
    }
    
    /**
     * Get a specific message with its details.
     *
     * @param int $message_id Message ID.
     * @param int $vendor_id Vendor ID.
     * @return array|null
     */
    public static function get_message($message_id, $vendor_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'vdp_messages';
        
        // Obtener el mensaje principal
        $query = $wpdb->prepare(
            "SELECT m.*, 
                   u.display_name AS sender_name,
                   p.post_title AS listing_title
            FROM {$table_messages} m
            LEFT JOIN {$wpdb->users} u ON m.sender_id = u.ID
            LEFT JOIN {$wpdb->posts} p ON m.listing_id = p.ID
            WHERE m.id = %d AND m.vendor_id = %d",
            $message_id, $vendor_id
        );
        
        $message = $wpdb->get_row($query, ARRAY_A);
        
        if (!$message) {
            return null;
        }
        
        // Obtener avatar del remitente
        $sender_avatar = get_avatar_url($message['sender_id'], array('size' => 96));
        
        // Información del usuario
        $user = get_userdata($message['sender_id']);
        $customer_since = ($user && isset($user->user_registered)) ? $user->user_registered : '';
        
        // Contar pedidos (esto depende de la estructura de WooCommerce)
        $orders_count = 0;
        if (function_exists('wc_get_orders')) {
            $args = array(
                'customer_id' => $message['sender_id'],
                'return' => 'ids',
                'limit' => -1,
            );
            $orders = wc_get_orders($args);
            $orders_count = count($orders);
        }
        
        // Información del producto/listing
        $product_url = get_permalink($message['listing_id']);
        
        // Obtener respuestas
        $table_replies = $wpdb->prefix . 'vdp_message_replies';
        
        $replies_query = $wpdb->prepare(
            "SELECT r.*, u.display_name AS name
            FROM {$table_replies} r
            LEFT JOIN {$wpdb->users} u ON r.sender_id = u.ID
            WHERE r.message_id = %d
            ORDER BY r.date_created ASC",
            $message_id
        );
        
        $replies_results = $wpdb->get_results($replies_query, ARRAY_A);
        
        // Formatear respuestas
        $replies = array();
        
        if (!empty($replies_results)) {
            foreach ($replies_results as $reply) {
                // Obtener avatar
                $avatar = get_avatar_url($reply['sender_id'], array('size' => 96));
                
                $replies[] = array(
                    'id' => $reply['id'],
                    'content' => $reply['content'],
                    'date' => $reply['date_created'],
                    'is_vendor' => (bool) $reply['is_vendor'],
                    'name' => $reply['name'],
                    'avatar' => $avatar,
                );
            }
        }
        
        // Formatear información de interacciones recientes
        $interactions = array();
        
        // Ejemplo: últimos pedidos
        if (function_exists('wc_get_orders') && $orders_count > 0) {
            $recent_orders = wc_get_orders(array(
                'customer_id' => $message['sender_id'],
                'limit' => 3,
                'orderby' => 'date',
                'order' => 'DESC',
            ));
            
            foreach ($recent_orders as $order) {
                $interactions[] = array(
                    'title' => sprintf(__('Ordered %s for %s', 'vendor-dashboard-pro'), $order->get_item_count() . ' ' . _n('item', 'items', $order->get_item_count(), 'vendor-dashboard-pro'), wc_price($order->get_total())),
                    'icon' => 'fas fa-shopping-cart',
                    'date' => $order->get_date_created()->date('Y-m-d H:i:s'),
                );
            }
        }
        
        // Formatear mensaje completo
        $formatted_message = array(
            'id' => $message['id'],
            'sender_id' => $message['sender_id'],
            'sender_name' => $message['sender_name'],
            'sender_avatar' => $sender_avatar,
            'subject' => $message['subject'],
            'content' => $message['content'],
            'date' => $message['date_created'],
            'is_read' => (bool) $message['is_read'],
            'listing_id' => $message['listing_id'],
            'listing_title' => $message['listing_title'],
            'has_response' => !empty($replies),
            'customer_since' => $customer_since,
            'orders_count' => $orders_count,
            'product_url' => $product_url,
            'product_title' => $message['listing_title'],
            'replies' => $replies,
            'interactions' => $interactions,
        );
        
        return $formatted_message;
    }
    
    /**
     * Mark a message as read.
     *
     * @param int $message_id Message ID.
     * @return bool
     */
    public static function mark_message_as_read($message_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'vdp_messages';
        
        $result = $wpdb->update(
            $table_messages,
            array('is_read' => 1),
            array('id' => $message_id),
            array('%d'),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Get demo messages for testing.
     *
     * @return array
     */
    public static function get_demo_messages() {
        $messages = array();
        
        for ($i = 1; $i <= 20; $i++) {
            $is_read = rand(0, 1) == 1;
            $date = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
            
            $messages[] = array(
                'id' => $i,
                'sender_name' => 'Customer ' . $i,
                'sender_email' => 'customer' . $i . '@example.com',
                'subject' => 'Question about Product ' . rand(1, 10),
                'content' => 'This is a sample message ' . $i . '. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla facilisi. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'date' => $date,
                'is_read' => $is_read,
                'listing_id' => rand(1, 100),
                'listing_title' => 'Sample Product ' . rand(1, 100),
                'has_response' => rand(0, 1) == 1,
            );
        }
        
        return $messages;
    }

    /**
     * Get a demo message by ID.
     *
     * @param int $message_id Message ID.
     * @return array|null
     */
    public static function get_demo_message($message_id) {
        $messages = self::get_demo_messages();
        
        foreach ($messages as $message) {
            if ($message['id'] == $message_id) {
                // Add more details for the single message view
                $message['product_url'] = '#';
                $message['product_title'] = $message['listing_title'];
                $message['customer_since'] = date('Y-m-d H:i:s', strtotime('-1 year'));
                $message['orders_count'] = rand(1, 10);
                $message['sender_avatar'] = '';
                $message['interactions'] = array(
                    array(
                        'title' => 'Ordered 2 items for $150',
                        'icon' => 'fas fa-shopping-cart',
                        'date' => date('Y-m-d H:i:s', strtotime('-10 days')),
                    ),
                    array(
                        'title' => 'Sent a message',
                        'icon' => 'fas fa-envelope',
                        'date' => date('Y-m-d H:i:s', strtotime('-20 days')),
                    ),
                );
                $message['replies'] = self::get_demo_replies($message_id);
                
                return $message;
            }
        }
        
        return null;
    }
    
    /**
     * Get demo replies for a message.
     *
     * @param int $message_id Message ID.
     * @return array
     */
    public static function get_demo_replies($message_id) {
        $replies = array();
        $num_replies = rand(0, 3);
        
        for ($i = 1; $i <= $num_replies; $i++) {
            $is_vendor = ($i % 2 == 0);
            $date = date('Y-m-d H:i:s', strtotime('-' . (5 - $i) . ' days'));
            
            $replies[] = array(
                'id' => $i,
                'content' => 'This is a reply message ' . $i . '. ' . ($is_vendor ? 'Thank you for your interest in our products.' : 'Thanks for your quick response.'),
                'date' => $date,
                'is_vendor' => $is_vendor,
                'name' => $is_vendor ? 'Vendor' : 'Customer',
                'avatar' => '',
            );
        }
        
        return $replies;
    }

    /**
     * Get predefined responses.
     *
     * @return array
     */
    public static function get_predefined_responses() {
        return array(
            array(
                'title' => __('Thank you for your inquiry', 'vendor-dashboard-pro'),
                'content' => __('Thank you for your inquiry. I appreciate your interest in my products. I\'ll get back to you with more information shortly.', 'vendor-dashboard-pro'),
            ),
            array(
                'title' => __('Product availability', 'vendor-dashboard-pro'),
                'content' => __('Thank you for your interest. This product is currently in stock and ready to ship within 1-2 business days.', 'vendor-dashboard-pro'),
            ),
            array(
                'title' => __('Shipping information', 'vendor-dashboard-pro'),
                'content' => __('Thank you for your order. Your item will be shipped within 1-2 business days. You\'ll receive a tracking number once it\'s on the way.', 'vendor-dashboard-pro'),
            ),
            array(
                'title' => __('Out of stock', 'vendor-dashboard-pro'),
                'content' => __('Thank you for your interest. Unfortunately, this product is currently out of stock. It should be available again within 2 weeks.', 'vendor-dashboard-pro'),
            ),
        );
    }
}

// Initialize Messages module
VDP_Messages::instance();