<?php
/**
 * API Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * API class for interacting with HivePress.
 */
class VDP_API {
    /**
     * Instance of this class.
     *
     * @var VDP_API
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_API
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
        // No initialization needed for development mode
    }

    /**
     * Get vendor by user ID.
     *
     * @param int $user_id User ID.
     * @return object|null
     */
    public function get_vendor_by_user($user_id) {
        // Return dummy vendor in development mode
        return $this->get_dummy_vendor();
    }

    /**
     * Get vendor by ID.
     *
     * @param int $vendor_id Vendor ID.
     * @return object|null
     */
    public function get_vendor_by_id($vendor_id) {
        // Return dummy vendor in development mode
        return $this->get_dummy_vendor();
    }

    /**
     * Get vendor listings.
     *
     * @param int $vendor_id Vendor ID.
     * @param array $args Query arguments.
     * @return array
     */
    public function get_vendor_listings($vendor_id, $args = array()) {
        // Return empty array in development mode
        return array();
    }
    
    /**
     * Get vendor listing count.
     *
     * @param int $vendor_id Vendor ID.
     * @return int Count of vendor listings.
     */
    public function get_vendor_listing_count($vendor_id) {
        // Default to 0 for count if we can't get real data
        return 0;
    }

    /**
     * Get vendor featured listings.
     *
     * @param int $vendor_id Vendor ID.
     * @param int $limit Number of listings to get.
     * @return array
     */
    public function get_vendor_featured_listings($vendor_id, $limit = 5) {
        // Return empty array in development mode
        return array();
    }

    /**
     * Get vendor messages.
     *
     * @param int $vendor_id Vendor ID.
     * @param array $args Query arguments.
     * @return array
     */
    public function get_vendor_messages($vendor_id, $args = array()) {
        // Return empty array in development mode
        return array();
    }

    /**
     * Get vendor statistics.
     *
     * @param int $vendor_id Vendor ID.
     * @return array
     */
    public function get_vendor_statistics($vendor_id) {
        // Return demo statistics
        return vdp_get_demo_statistics();
    }
    
    /**
     * Get listing categories.
     *
     * @return array
     */
    public function get_listing_categories() {
        // Return demo categories
        return array(
            1 => 'Category 1',
            2 => 'Category 2',
            3 => 'Category 3',
        );
    }

    /**
     * Get a dummy vendor object.
     *
     * @return object
     */
    private function get_dummy_vendor() {
        // Create a simple dummy object with required methods
        $vendor = new stdClass();
        
        // Get current user data for better dummy values
        $user_id = get_current_user_id();
        $user_info = get_userdata($user_id);
        
        // Add methods to the dummy vendor
        $vendor->get_id = function() {
            return 1;
        };
        
        $vendor->get_name = function() use ($user_info) {
            // Use user display name or username if available
            if ($user_info) {
                return $user_info->display_name ?: $user_info->user_login;
            }
            return 'Vendor Store';
        };
        
        $vendor->get_image__url = function() {
            return false;
        };
        
        $vendor->is_verified = function() {
            return true;
        };
        
        $vendor->get_user__id = function() use ($user_id) {
            return $user_id;
        };
        
        // Add slug method for URLs
        $vendor->get_slug = function() use ($user_info) {
            if ($user_info) {
                return sanitize_title($user_info->display_name ?: $user_info->user_login);
            }
            return 'vendor-store';
        };
        
        // Add debug log
        vdp_debug_log('Using dummy vendor object - Name: ' . $vendor->get_name(), 'info');
        
        return $vendor;
    }
}

/**
 * Get API instance.
 *
 * @return VDP_API
 */
function vdp_api() {
    return VDP_API::instance();
}