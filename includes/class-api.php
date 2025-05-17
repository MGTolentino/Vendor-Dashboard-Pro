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
        // Initialize cache
        $this->init_cache();
    }

    /**
     * Initialize cache.
     */
    private function init_cache() {
        // Initialize cache (would be implemented in a production environment)
    }

    /**
     * Get vendor by user ID.
     *
     * @param int $user_id User ID.
     * @return \HivePress\Models\Vendor|null
     */
    public function get_vendor_by_user($user_id) {
        if (empty($user_id)) {
            return null;
        }
        
        $vendor = \HivePress\Models\Vendor::query()->filter(
            array(
                'user' => $user_id,
                'status' => array('publish', 'draft', 'pending'),
            )
        )->get_first();
        
        return $vendor;
    }

    /**
     * Get vendor by ID.
     *
     * @param int $vendor_id Vendor ID.
     * @return \HivePress\Models\Vendor|null
     */
    public function get_vendor_by_id($vendor_id) {
        if (empty($vendor_id)) {
            return null;
        }
        
        $vendor = \HivePress\Models\Vendor::query()->get_by_id($vendor_id);
        
        return $vendor;
    }

    /**
     * Get vendor listings.
     *
     * @param int $vendor_id Vendor ID.
     * @param array $args Query arguments.
     * @return array
     */
    public function get_vendor_listings($vendor_id, $args = array()) {
        if (empty($vendor_id)) {
            return array();
        }
        
        $default_args = array(
            'status' => 'publish',
            'order' => array('created_date' => 'desc'),
            'limit' => 10,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $default_args);
        $args['vendor'] = $vendor_id;
        
        $listings = \HivePress\Models\Listing::query()->filter($args)->get();
        
        return $listings;
    }

    /**
     * Get vendor listing count.
     *
     * @param int $vendor_id Vendor ID.
     * @param array $args Query arguments.
     * @return int
     */
    public function get_vendor_listing_count($vendor_id, $args = array()) {
        if (empty($vendor_id)) {
            return 0;
        }
        
        $default_args = array(
            'status' => 'publish',
        );
        
        $args = wp_parse_args($args, $default_args);
        $args['vendor'] = $vendor_id;
        
        $count = \HivePress\Models\Listing::query()->filter($args)->count();
        
        return $count;
    }

    /**
     * Get vendor featured listings.
     *
     * @param int $vendor_id Vendor ID.
     * @param int $limit Number of listings to get.
     * @return array
     */
    public function get_vendor_featured_listings($vendor_id, $limit = 5) {
        if (empty($vendor_id)) {
            return array();
        }
        
        $listings = \HivePress\Models\Listing::query()->filter(
            array(
                'status' => 'publish',
                'vendor' => $vendor_id,
                'featured' => true,
            )
        )->order('random')->limit($limit)->get();
        
        return $listings;
    }

    /**
     * Get listing categories.
     *
     * @return array
     */
    public function get_listing_categories() {
        $categories = array();
        
        if (taxonomy_exists('hp_listing_category')) {
            $terms = get_terms(array(
                'taxonomy' => 'hp_listing_category',
                'hide_empty' => false,
            ));
            
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    $categories[$term->term_id] = $term->name;
                }
            }
        }
        
        return $categories;
    }

    /**
     * Get vendor messages.
     *
     * @param int $vendor_id Vendor ID.
     * @param array $args Query arguments.
     * @return array
     */
    public function get_vendor_messages($vendor_id, $args = array()) {
        if (empty($vendor_id) || !class_exists('\HivePress\Models\Message')) {
            return array();
        }
        
        $default_args = array(
            'status' => 'publish',
            'order' => array('created_date' => 'desc'),
            'limit' => 10,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $default_args);
        
        // This is a simplified approach - in reality, you'd need to get all listings 
        // and then get messages related to those listings
        $vendor = $this->get_vendor_by_id($vendor_id);
        
        if (!$vendor) {
            return array();
        }
        
        $user_id = $vendor->get_user__id();
        
        if (!$user_id) {
            return array();
        }
        
        // This is a placeholder - the actual implementation would depend on HivePress's message system
        $messages = array();
        
        // For demo purposes, we'll return some sample messages
        for ($i = 1; $i <= 5; $i++) {
            $messages[] = (object) array(
                'id' => $i,
                'sender' => 'User ' . $i,
                'content' => 'This is a sample message ' . $i,
                'date' => date('Y-m-d H:i:s', strtotime('-' . $i . ' days')),
                'is_read' => rand(0, 1),
                'listing_id' => rand(1, 100),
                'listing_title' => 'Sample Listing ' . rand(1, 100),
            );
        }
        
        return $messages;
    }

    /**
     * Get vendor statistics.
     *
     * @param int $vendor_id Vendor ID.
     * @return array
     */
    public function get_vendor_statistics($vendor_id) {
        if (empty($vendor_id)) {
            return array();
        }
        
        // In a production environment, this would fetch real statistics from HivePress
        // For now, we'll use demo data
        $statistics = vdp_get_demo_statistics();
        
        return $statistics;
    }

    /**
     * Create or update a listing.
     *
     * @param array $data Listing data.
     * @param int $listing_id Optional listing ID for updating.
     * @return int|WP_Error Listing ID or error.
     */
    public function save_listing($data, $listing_id = 0) {
        // This is a placeholder - in a production environment, this would save a listing using HivePress APIs
        
        if ($listing_id) {
            // Update existing listing
            $listing = \HivePress\Models\Listing::query()->get_by_id($listing_id);
            
            if (!$listing) {
                return new WP_Error('invalid_listing', __('Listing not found.', 'vendor-dashboard-pro'));
            }
            
            // Check if current user is the owner
            $vendor = vdp_get_current_vendor();
            
            if (!$vendor || $listing->get_vendor__id() != $vendor->get_id()) {
                return new WP_Error('unauthorized', __('You are not authorized to edit this listing.', 'vendor-dashboard-pro'));
            }
            
            // Update listing
            $result = $listing->fill($data)->save();
            
            if (!$result) {
                return new WP_Error('update_failed', __('Failed to update listing.', 'vendor-dashboard-pro'));
            }
            
            return $listing->get_id();
        } else {
            // Create new listing
            $listing = new \HivePress\Models\Listing();
            
            // Set vendor
            $vendor = vdp_get_current_vendor();
            
            if (!$vendor) {
                return new WP_Error('no_vendor', __('No vendor profile found.', 'vendor-dashboard-pro'));
            }
            
            $data['vendor'] = $vendor->get_id();
            
            // Create listing
            $result = $listing->fill($data)->save();
            
            if (!$result) {
                return new WP_Error('create_failed', __('Failed to create listing.', 'vendor-dashboard-pro'));
            }
            
            return $listing->get_id();
        }
    }

    /**
     * Delete a listing.
     *
     * @param int $listing_id Listing ID.
     * @return bool
     */
    public function delete_listing($listing_id) {
        if (empty($listing_id)) {
            return false;
        }
        
        $listing = \HivePress\Models\Listing::query()->get_by_id($listing_id);
        
        if (!$listing) {
            return false;
        }
        
        // Check if current user is the owner
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor || $listing->get_vendor__id() != $vendor->get_id()) {
            return false;
        }
        
        // Delete listing
        return $listing->delete();
    }

    /**
     * Update vendor profile.
     *
     * @param array $data Vendor data.
     * @return bool
     */
    public function update_vendor_profile($data) {
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return false;
        }
        
        // Update vendor
        $result = $vendor->fill($data)->save();
        
        return (bool) $result;
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