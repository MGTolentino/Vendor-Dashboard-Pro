<?php
/**
 * Settings Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings module class.
 */
class VDP_Settings {
    /**
     * Instance of this class.
     *
     * @var VDP_Settings
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Settings
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
        add_action('vdp_settings_content', array($this, 'render_settings'), 10);
    }

    /**
     * Render settings content.
     */
    public function render_settings() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get vendor settings
        $settings = self::get_vendor_settings($vendor);
        
        // Include settings template
        include VDP_PLUGIN_DIR . 'templates/settings-content.php';
    }

    /**
     * Get vendor settings.
     *
     * @param \HivePress\Models\Vendor $vendor Vendor object.
     * @return array
     */
    public static function get_vendor_settings($vendor) {
        return array(
            'profile' => array(
                'name' => $vendor->get_name(),
                'description' => $vendor->get_description(),
                'image_id' => $vendor->get_image__id(),
                'image_url' => $vendor->get_image__url('thumbnail'),
                'email' => $vendor->get_user__email(),
                'phone' => get_user_meta($vendor->get_user__id(), 'phone', true),
            ),
            'store' => array(
                'store_name' => $vendor->get_name(),
                'store_logo' => $vendor->get_image__id(),
                'store_banner' => get_post_meta($vendor->get_id(), 'banner_image', true),
                'store_tagline' => get_post_meta($vendor->get_id(), 'tagline', true),
            ),
            'payments' => array(
                'payment_method' => get_post_meta($vendor->get_id(), 'payment_method', true),
                'paypal_email' => get_post_meta($vendor->get_id(), 'paypal_email', true),
                'bank_account' => get_post_meta($vendor->get_id(), 'bank_account', true),
            ),
            'shipping' => array(
                'shipping_policy' => get_post_meta($vendor->get_id(), 'shipping_policy', true),
                'return_policy' => get_post_meta($vendor->get_id(), 'return_policy', true),
            ),
            'notifications' => array(
                'email_notifications' => get_user_meta($vendor->get_user__id(), 'email_notifications', true) !== 'no',
                'order_notifications' => get_user_meta($vendor->get_user__id(), 'order_notifications', true) !== 'no',
                'message_notifications' => get_user_meta($vendor->get_user__id(), 'message_notifications', true) !== 'no',
                'review_notifications' => get_user_meta($vendor->get_user__id(), 'review_notifications', true) !== 'no',
            ),
        );
    }

    /**
     * Get payment methods.
     *
     * @return array
     */
    public static function get_payment_methods() {
        return array(
            'paypal' => __('PayPal', 'vendor-dashboard-pro'),
            'bank_transfer' => __('Bank Transfer', 'vendor-dashboard-pro'),
            'cash' => __('Cash on Delivery', 'vendor-dashboard-pro'),
        );
    }
}

// Initialize Settings module
VDP_Settings::instance();