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
        
        return $vendor;
    }
    
    /**
     * Delete listing.
     *
     * @param int $listing_id Listing ID to delete.
     * @return bool True on success, false on failure.
     */
    public function delete_listing($listing_id) {
        if (empty($listing_id)) {
            return false;
        }
        
        // Get current vendor to verify ownership
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            return false;
        }
        
        // Get listing post
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'hp_listing') {
            return false;
        }
        
        // Verify vendor owns this listing
        if ($listing->post_parent !== $vendor->get_id()) {
            return false;
        }
        
        // Delete the listing
        $result = wp_delete_post($listing_id, true);
        
        return !empty($result);
    }
    
    /**
     * Save listing.
     *
     * @param array $sanitized_data Sanitized listing data.
     * @param int   $listing_id     Listing ID (for updates) or 0 (for new).
     * @return int|WP_Error Listing ID on success, WP_Error on failure.
     */
    public function save_listing($sanitized_data, $listing_id = 0) {
        // Get current vendor
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            return new WP_Error('no_vendor', __('Vendor not found.', 'vendor-dashboard-pro'));
        }
        
        // Prepare post data
        $post_data = array(
            'post_type' => 'hp_listing',
            'post_status' => 'publish',
            'post_parent' => $vendor->get_id(),
            'post_title' => isset($sanitized_data['title']) ? $sanitized_data['title'] : '',
            'post_content' => isset($sanitized_data['description']) ? $sanitized_data['description'] : '',
        );
        
        if ($listing_id > 0) {
            // Update existing listing
            $post_data['ID'] = $listing_id;
            
            // Verify vendor owns this listing
            $existing = get_post($listing_id);
            if (!$existing || $existing->post_parent !== $vendor->get_id()) {
                return new WP_Error('invalid_listing', __('Invalid listing.', 'vendor-dashboard-pro'));
            }
            
            $result = wp_update_post($post_data);
        } else {
            // Create new listing
            $result = wp_insert_post($post_data);
        }
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Save meta fields
        if (isset($sanitized_data['price'])) {
            update_post_meta($result, 'hp_price', floatval($sanitized_data['price']));
        }
        
        // Save other meta fields as needed
        $meta_fields = array('category', 'location', 'featured');
        foreach ($meta_fields as $field) {
            if (isset($sanitized_data[$field])) {
                update_post_meta($result, 'hp_' . $field, $sanitized_data[$field]);
            }
        }
        
        return $result;
    }
    
    /**
     * Update vendor profile.
     *
     * @param array $sanitized_data Sanitized vendor data.
     * @return bool True on success, false on failure.
     */
    public function update_vendor_profile($sanitized_data) {
        // Get current vendor
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            return false;
        }
        
        // Prepare post data
        $post_data = array(
            'ID' => $vendor->get_id(),
        );
        
        // Update title if provided
        if (isset($sanitized_data['name'])) {
            $post_data['post_title'] = $sanitized_data['name'];
        }
        
        // Update description if provided
        if (isset($sanitized_data['description'])) {
            $post_data['post_content'] = $sanitized_data['description'];
        }
        
        // Update post
        $result = wp_update_post($post_data);
        
        if (is_wp_error($result)) {
            return false;
        }
        
        // Save meta fields
        $meta_fields = array('email', 'phone', 'website', 'address');
        foreach ($meta_fields as $field) {
            if (isset($sanitized_data[$field])) {
                update_post_meta($vendor->get_id(), 'hp_' . $field, $sanitized_data[$field]);
            }
        }
        
        return true;
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