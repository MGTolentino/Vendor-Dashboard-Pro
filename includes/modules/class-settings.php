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
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
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
        
        // Initialize variables for template
        $user = get_userdata($vendor->get_user_id());
        $payment_methods = self::get_payment_methods();
        $payment_settings = $settings['payments'];
        $shipping = $settings['shipping'];
        $notifications = array(
            'email' => array(
                'new_order' => get_user_meta($vendor->get_user_id(), 'notify_new_order', true) !== 'no',
                'order_status' => get_user_meta($vendor->get_user_id(), 'notify_order_status', true) !== 'no',
                'new_message' => get_user_meta($vendor->get_user_id(), 'notify_new_message', true) !== 'no',
                'new_review' => get_user_meta($vendor->get_user_id(), 'notify_new_review', true) !== 'no',
                'low_stock' => get_user_meta($vendor->get_user_id(), 'notify_low_stock', true) !== 'no',
                'payout' => get_user_meta($vendor->get_user_id(), 'notify_payout', true) !== 'no',
            ),
            'dashboard' => array(
                'new_order' => get_user_meta($vendor->get_user_id(), 'notify_dashboard_new_order', true) !== 'no',
                'new_message' => get_user_meta($vendor->get_user_id(), 'notify_dashboard_new_message', true) !== 'no',
                'new_review' => get_user_meta($vendor->get_user_id(), 'notify_dashboard_new_review', true) !== 'no',
                'payout' => get_user_meta($vendor->get_user_id(), 'notify_dashboard_payout', true) !== 'no',
            ),
            'reports' => array(
                'sales_frequency' => get_user_meta($vendor->get_user_id(), 'sales_report_frequency', true) ?: 'weekly',
                'inventory_frequency' => get_user_meta($vendor->get_user_id(), 'inventory_report_frequency', true) ?: 'monthly',
            ),
        );
        $countries = array(
            'US' => __('United States', 'vendor-dashboard-pro'),
            'CA' => __('Canada', 'vendor-dashboard-pro'),
            'MX' => __('Mexico', 'vendor-dashboard-pro'),
            'GB' => __('United Kingdom', 'vendor-dashboard-pro'),
            'AU' => __('Australia', 'vendor-dashboard-pro'),
            'DE' => __('Germany', 'vendor-dashboard-pro'),
            'FR' => __('France', 'vendor-dashboard-pro'),
            'ES' => __('Spain', 'vendor-dashboard-pro'),
            'IT' => __('Italy', 'vendor-dashboard-pro'),
            'BR' => __('Brazil', 'vendor-dashboard-pro'),
            'AR' => __('Argentina', 'vendor-dashboard-pro'),
            'CL' => __('Chile', 'vendor-dashboard-pro'),
            'CO' => __('Colombia', 'vendor-dashboard-pro'),
            'PE' => __('Peru', 'vendor-dashboard-pro'),
        );
        
        // Include settings template
        include VDP_PLUGIN_DIR . 'templates/settings-content.php';
    }

    /**
     * Enqueue settings assets.
     */
    public function enqueue_assets() {
        // Only enqueue on dashboard pages
        if (!vdp_is_dashboard_page()) {
            return;
        }

        wp_enqueue_script(
            'vdp-settings-tabs',
            VDP_PLUGIN_URL . 'assets/js/settings-tabs.js',
            array('jquery'),
            VDP_VERSION,
            true
        );
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
                'image_id' => get_post_thumbnail_id($vendor->get_id()),
                'image_url' => $vendor->get_image__url('thumbnail'),
                'email' => ($user_data = get_userdata($vendor->get_user_id())) ? $user_data->user_email : '',
                'phone' => get_user_meta($vendor->get_user_id(), 'phone', true),
            ),
            'store' => array(
                'store_name' => $vendor->get_name(),
                'store_logo' => get_post_thumbnail_id($vendor->get_id()),
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
                'email_notifications' => get_user_meta($vendor->get_user_id(), 'email_notifications', true) !== 'no',
                'order_notifications' => get_user_meta($vendor->get_user_id(), 'order_notifications', true) !== 'no',
                'message_notifications' => get_user_meta($vendor->get_user_id(), 'message_notifications', true) !== 'no',
                'review_notifications' => get_user_meta($vendor->get_user_id(), 'review_notifications', true) !== 'no',
            ),
        );
    }

    /**
     * Get payment methods.
     *
     * @return array
     */
    public static function get_payment_methods() {
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            return array();
        }
        
        $vendor_id = $vendor->get_user_id();
        
        return array(
            'paypal' => array(
                'name' => __('PayPal', 'vendor-dashboard-pro'),
                'connected' => !empty(get_user_meta($vendor_id, 'paypal_email', true)),
            ),
            'bank_transfer' => array(
                'name' => __('Bank Transfer', 'vendor-dashboard-pro'),
                'connected' => !empty(get_user_meta($vendor_id, 'bank_account_number', true)),
            ),
            'cash' => array(
                'name' => __('Cash on Delivery', 'vendor-dashboard-pro'),
                'connected' => get_user_meta($vendor_id, 'accept_cash', true) === 'yes',
            ),
        );
    }
}

// Initialize Settings module
VDP_Settings::instance();