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
        
        // Get current page
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        
        // Get messages per page
        $per_page = 10;
        
        // For demo purposes, we'll create sample messages
        // In a real implementation, you would get actual messages from HivePress
        $messages = self::get_demo_messages();
        
        // Calculate total pages (for demo)
        $total_messages = count($messages);
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
        
        // Get message ID
        $message_id = get_query_var('vdp_item', 0);
        
        if (!$message_id) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Message not found.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // For demo purposes, we'll get a sample message
        // In a real implementation, you would get the actual message from HivePress
        $message = self::get_demo_message($message_id);
        
        if (!$message) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Message not found.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Include message view template
        include VDP_PLUGIN_DIR . 'templates/message-view-content.php';
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
                $message['conversation'] = self::get_demo_conversation($message_id);
                
                return $message;
            }
        }
        
        return null;
    }

    /**
     * Get demo conversation for a message.
     *
     * @param int $message_id Message ID.
     * @return array
     */
    public static function get_demo_conversation($message_id) {
        $conversation = array();
        $num_messages = rand(2, 5);
        
        for ($i = 1; $i <= $num_messages; $i++) {
            $is_customer = ($i % 2 == 1);
            $date = date('Y-m-d H:i:s', strtotime('-' . ($num_messages - $i) . ' days'));
            
            $conversation[] = array(
                'id' => $i,
                'is_customer' => $is_customer,
                'sender' => $is_customer ? 'Customer' : 'You',
                'content' => 'Message ' . $i . ' in the conversation. ' . ($is_customer ? 'Question from customer.' : 'Response from you.'),
                'date' => $date,
            );
        }
        
        return $conversation;
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