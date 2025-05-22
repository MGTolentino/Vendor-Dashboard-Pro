<?php
/**
 * Ajax Handler Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax Handler class for processing Ajax requests.
 */
class VDP_Ajax_Handler {
    /**
     * Initialize Ajax handler.
     */
    public static function init() {
        // Register Ajax actions
        add_action('wp_ajax_vdp_save_listing', array(__CLASS__, 'save_listing'));
        add_action('wp_ajax_vdp_delete_listing', array(__CLASS__, 'delete_listing'));
        add_action('wp_ajax_vdp_get_dashboard_data', array(__CLASS__, 'get_dashboard_data'));
        add_action('wp_ajax_vdp_save_vendor_settings', array(__CLASS__, 'save_vendor_settings'));
        add_action('wp_ajax_vdp_get_chart_data', array(__CLASS__, 'get_chart_data'));
        add_action('wp_ajax_vdp_trigger_listing_form', array(__CLASS__, 'trigger_listing_form'));
    }

    /**
     * Verify Ajax request.
     *
     * @param string $nonce_action Nonce action.
     * @return bool
     */
    private static function verify_ajax_request($nonce_action = 'vdp-ajax-nonce') {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to perform this action.', 'vendor-dashboard-pro')));
            return false;
        }
        
        // Check nonce
        if (!check_ajax_referer($nonce_action, 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
            return false;
        }
        
        // Check if user is a vendor
        if (!vdp_is_user_vendor()) {
            wp_send_json_error(array('message' => __('You must be a vendor to perform this action.', 'vendor-dashboard-pro')));
            return false;
        }
        
        return true;
    }

    /**
     * Save listing Ajax handler.
     */
    public static function save_listing() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get listing data
        $listing_data = isset($_POST['listing_data']) ? $_POST['listing_data'] : array();
        $listing_id = isset($_POST['listing_id']) ? absint($_POST['listing_id']) : 0;
        
        // Validate required fields
        if (empty($listing_data['title'])) {
            wp_send_json_error(array('message' => __('Title is required.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Sanitize data
        $sanitized_data = array();
        
        // Basic fields
        $sanitized_data['title'] = sanitize_text_field($listing_data['title']);
        $sanitized_data['description'] = wp_kses_post($listing_data['description']);
        $sanitized_data['price'] = floatval($listing_data['price']);
        $sanitized_data['featured'] = !empty($listing_data['featured']);
        
        // Categories
        if (!empty($listing_data['categories'])) {
            $sanitized_data['categories'] = array_map('absint', (array) $listing_data['categories']);
        }
        
        // Save listing
        $result = vdp_api()->save_listing($sanitized_data, $listing_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success(array(
            'listing_id' => $result,
            'message' => __('Listing saved successfully.', 'vendor-dashboard-pro'),
            'redirect' => vdp_get_dashboard_url('products'),
        ));
    }

    /**
     * Delete listing Ajax handler.
     */
    public static function delete_listing() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get listing ID
        $listing_id = isset($_POST['listing_id']) ? absint($_POST['listing_id']) : 0;
        
        if (empty($listing_id)) {
            wp_send_json_error(array('message' => __('Invalid listing ID.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Delete listing
        $result = vdp_api()->delete_listing($listing_id);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to delete listing.', 'vendor-dashboard-pro')));
            return;
        }
        
        wp_send_json_success(array(
            'message' => __('Listing deleted successfully.', 'vendor-dashboard-pro'),
        ));
    }

    /**
     * Get dashboard data Ajax handler.
     */
    public static function get_dashboard_data() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get vendor statistics
        $statistics = vdp_api()->get_vendor_statistics($vendor->get_id());
        
        // Get recent listings
        $recent_listings = vdp_api()->get_vendor_listings($vendor->get_id(), array(
            'limit' => 5,
        ));
        
        // Format listings for response
        $formatted_listings = array();
        
        foreach ($recent_listings as $listing) {
            $formatted_listings[] = array(
                'id' => $listing->get_id(),
                'title' => $listing->get_title(),
                'price' => $listing->get_price(),
                'featured' => $listing->is_featured(),
                'image' => $listing->get_image__url('thumbnail'),
                'url' => get_permalink($listing->get_id()),
                'edit_url' => vdp_get_dashboard_url('products/edit/' . $listing->get_id()),
            );
        }
        
        // Get recent messages
        $recent_messages = vdp_api()->get_vendor_messages($vendor->get_id(), array(
            'limit' => 5,
        ));
        
        // Format messages for response
        $formatted_messages = array();
        
        foreach ($recent_messages as $message) {
            $formatted_messages[] = array(
                'id' => $message->id,
                'sender' => $message->sender,
                'content' => $message->content,
                'date' => vdp_time_ago($message->date),
                'is_read' => $message->is_read,
                'listing_title' => $message->listing_title,
                'view_url' => vdp_get_dashboard_url('messages/view/' . $message->id),
            );
        }
        
        // Send response
        wp_send_json_success(array(
            'statistics' => $statistics,
            'recent_listings' => $formatted_listings,
            'recent_messages' => $formatted_messages,
        ));
    }

    /**
     * Save vendor settings Ajax handler.
     */
    public static function save_vendor_settings() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get vendor data
        $vendor_data = isset($_POST['vendor_data']) ? $_POST['vendor_data'] : array();
        
        // Validate required fields
        if (empty($vendor_data['name'])) {
            wp_send_json_error(array('message' => __('Name is required.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Sanitize data
        $sanitized_data = array();
        
        // Basic fields
        $sanitized_data['name'] = sanitize_text_field($vendor_data['name']);
        $sanitized_data['description'] = wp_kses_post($vendor_data['description']);
        
        // Save vendor profile
        $result = vdp_api()->update_vendor_profile($sanitized_data);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to update vendor profile.', 'vendor-dashboard-pro')));
            return;
        }
        
        wp_send_json_success(array(
            'message' => __('Vendor profile updated successfully.', 'vendor-dashboard-pro'),
        ));
    }

    /**
     * Get chart data Ajax handler.
     */
    public static function get_chart_data() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get parameters
        $metric = isset($_POST['metric']) ? sanitize_key($_POST['metric']) : 'sales';
        $period = isset($_POST['period']) ? sanitize_key($_POST['period']) : '30days';
        
        // Determine number of days based on period
        switch ($period) {
            case '7days':
                $days = 7;
                break;
                
            case '90days':
                $days = 90;
                break;
                
            case '30days':
            default:
                $days = 30;
                break;
        }
        
        // Get chart data
        $chart_data = vdp_get_demo_chart_data($metric, $days);
        
        wp_send_json_success(array(
            'chart_data' => $chart_data,
        ));
    }
    
    /**
     * Trigger HivePress listing form Ajax handler.
     */
    public static function trigger_listing_form() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Try to get HivePress listing submit URL
        $form_url = '';
        
        if (function_exists('hivepress')) {
            try {
                // Try different possible page names for listing submission
                $possible_pages = array('listing_submit_page', 'listing_add_page', 'submit_listing_page');
                
                foreach ($possible_pages as $page_name) {
                    try {
                        $form_url = hivepress()->router->get_url($page_name);
                        if (!empty($form_url)) {
                            break;
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }
            } catch (Exception $e) {
                $form_url = '';
            }
        }
        
        // If still no URL, try to find listing submit page in WordPress
        if (empty($form_url)) {
            $submit_page = get_option('hp_listing_submit_page');
            if ($submit_page) {
                $form_url = get_permalink($submit_page);
            }
        }
        
        if (!empty($form_url)) {
            wp_send_json_success(array(
                'form_url' => $form_url,
                'message' => __('Redirecting to listing form...', 'vendor-dashboard-pro'),
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Listing form not available.', 'vendor-dashboard-pro'),
            ));
        }
    }
}