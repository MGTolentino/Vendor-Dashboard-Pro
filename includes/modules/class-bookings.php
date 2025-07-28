<?php
/**
 * Bookings Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Bookings module class.
 */
class VDP_Bookings {
    /**
     * Instance of this class.
     *
     * @var VDP_Bookings
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Bookings
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
        add_action('vdp_bookings_content', array($this, 'render_bookings'), 10);
        add_action('wp_ajax_vdp_filter_bookings', array($this, 'ajax_filter_bookings'));
        add_action('wp_ajax_vdp_update_booking_status', array($this, 'ajax_update_booking_status'));
    }

    /**
     * Render bookings content.
     */
    public function render_bookings() {
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get bookings data for this vendor
        $bookings_data = self::get_vendor_bookings_data($vendor->get_id());
        
        include VDP_PLUGIN_DIR . 'templates/bookings-content.php';
    }

    /**
     * Get vendor bookings data.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Optional arguments for filtering
     * @return array
     */
    public static function get_vendor_bookings_data($vendor_id, $args = array()) {
        $args = wp_parse_args($args, array(
            'status' => 'all',
            'date_from' => '',
            'date_to' => '',
            'listing_id' => '',
            'per_page' => 20,
            'page' => 1
        ));
        
        return array(
            'summary' => self::get_vendor_bookings_summary($vendor_id, $args),
            'bookings' => self::get_vendor_bookings($vendor_id, $args),
            'calendar_data' => self::get_vendor_calendar_data($vendor_id, $args),
            'statistics' => self::get_vendor_booking_statistics($vendor_id, $args),
        );
    }

    /**
     * Get vendor bookings summary.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_vendor_bookings_summary($vendor_id, $args = array()) {
        // Get vendor's listings first
        $listing_ids = self::get_vendor_listings($vendor_id);
        
        if (empty($listing_ids)) {
            return array(
                'total_bookings' => 0,
                'confirmed_bookings' => 0,
                'pending_bookings' => 0,
                'cancelled_bookings' => 0,
                'total_revenue' => 0,
            );
        }
        
        // Build date filter
        $date_query = array();
        if (!empty($args['date_from'])) {
            $date_query[] = array(
                'after' => $args['date_from'],
                'inclusive' => true,
            );
        }
        if (!empty($args['date_to'])) {
            $date_query[] = array(
                'before' => $args['date_to'],
                'inclusive' => true,
            );
        }
        
        // Get all bookings for vendor's listings
        $bookings_query = array(
            'post_type' => 'hp_booking',
            'post_status' => array('publish', 'pending', 'draft', 'trash'),
            'meta_query' => array(
                array(
                    'key' => 'hp_listing',
                    'value' => $listing_ids,
                    'compare' => 'IN',
                ),
            ),
            'posts_per_page' => -1,
        );
        
        if (!empty($date_query)) {
            $bookings_query['date_query'] = $date_query;
        }
        
        $bookings = get_posts($bookings_query);
        
        $summary = array(
            'total_bookings' => count($bookings),
            'confirmed_bookings' => 0,
            'pending_bookings' => 0,
            'cancelled_bookings' => 0,
            'total_revenue' => 0,
        );
        
        foreach ($bookings as $booking) {
            switch ($booking->post_status) {
                case 'publish':
                    $summary['confirmed_bookings']++;
                    break;
                case 'pending':
                    $summary['pending_bookings']++;
                    break;
                case 'trash':
                    $summary['cancelled_bookings']++;
                    break;
            }
            
            // Calculate revenue from related WooCommerce orders
            $booking_price = get_post_meta($booking->ID, 'hp_price', true);
            if ($booking_price && $booking->post_status !== 'trash') {
                $summary['total_revenue'] += (float) $booking_price;
            }
        }
        
        return $summary;
    }

    /**
     * Get vendor bookings list.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_vendor_bookings($vendor_id, $args = array()) {
        // Get vendor's listings first
        $listing_ids = self::get_vendor_listings($vendor_id);
        
        if (empty($listing_ids)) {
            return array();
        }
        
        // Build query arguments
        $query_args = array(
            'post_type' => 'hp_booking',
            'posts_per_page' => $args['per_page'],
            'paged' => $args['page'],
            'meta_query' => array(
                array(
                    'key' => 'hp_listing',
                    'value' => $listing_ids,
                    'compare' => 'IN',
                ),
            ),
            'meta_key' => 'hp_start_time',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
        );
        
        // Status filter
        if ($args['status'] !== 'all') {
            switch ($args['status']) {
                case 'confirmed':
                    $query_args['post_status'] = 'publish';
                    break;
                case 'pending':
                    $query_args['post_status'] = 'pending';
                    break;
                case 'cancelled':
                    $query_args['post_status'] = 'trash';
                    break;
                default:
                    $query_args['post_status'] = array('publish', 'pending', 'draft', 'trash');
            }
        } else {
            $query_args['post_status'] = array('publish', 'pending', 'draft', 'trash');
        }
        
        // Date filters
        if (!empty($args['date_from']) || !empty($args['date_to'])) {
            $meta_query = array();
            
            if (!empty($args['date_from'])) {
                $meta_query[] = array(
                    'key' => 'hp_start_time',
                    'value' => strtotime($args['date_from']),
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                );
            }
            
            if (!empty($args['date_to'])) {
                $meta_query[] = array(
                    'key' => 'hp_start_time',
                    'value' => strtotime($args['date_to'] . ' 23:59:59'),
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                );
            }
            
            if (isset($query_args['meta_query'])) {
                $query_args['meta_query'] = array_merge($query_args['meta_query'], $meta_query);
            } else {
                $query_args['meta_query'] = $meta_query;
            }
        }
        
        // Listing filter
        if (!empty($args['listing_id'])) {
            $query_args['meta_query'][] = array(
                'key' => 'hp_listing',
                'value' => $args['listing_id'],
                'compare' => '=',
            );
        }
        
        $bookings_query = new WP_Query($query_args);
        $bookings = array();
        
        if ($bookings_query->have_posts()) {
            while ($bookings_query->have_posts()) {
                $bookings_query->the_post();
                $booking_id = get_the_ID();
                
                $booking_data = array(
                    'id' => $booking_id,
                    'start_time' => get_post_meta($booking_id, 'hp_start_time', true),
                    'end_time' => get_post_meta($booking_id, 'hp_end_time', true),
                    'status' => get_post_status($booking_id),
                    'notes' => get_the_content(),
                    'customer_id' => get_post_field('post_author', $booking_id),
                    'listing_id' => get_post_meta($booking_id, 'hp_listing', true),
                    'price' => get_post_meta($booking_id, 'hp_price', true),
                    'created_date' => get_the_date('c', $booking_id),
                );
                
                // Get customer info
                if ($booking_data['customer_id']) {
                    $customer = get_userdata($booking_data['customer_id']);
                    $booking_data['customer_name'] = $customer ? $customer->display_name : '';
                    $booking_data['customer_email'] = $customer ? $customer->user_email : '';
                }
                
                // Get listing info
                if ($booking_data['listing_id']) {
                    $listing = get_post($booking_data['listing_id']);
                    $booking_data['listing_title'] = $listing ? $listing->post_title : '';
                }
                
                // Get related WooCommerce order if exists
                $order_id = get_post_meta($booking_id, 'hp_order', true);
                if ($order_id) {
                    $order = wc_get_order($order_id);
                    if ($order) {
                        $booking_data['order_id'] = $order_id;
                        $booking_data['order_status'] = $order->get_status();
                        $booking_data['order_total'] = $order->get_total();
                    }
                }
                
                $bookings[] = $booking_data;
            }
            wp_reset_postdata();
        }
        
        return $bookings;
    }

    /**
     * Get vendor calendar data for calendar view.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_vendor_calendar_data($vendor_id, $args = array()) {
        // Get vendor's listings
        $listing_ids = self::get_vendor_listings($vendor_id);
        
        if (empty($listing_ids)) {
            return array();
        }
        
        // Get bookings for calendar display
        $calendar_query = array(
            'post_type' => 'hp_booking',
            'post_status' => array('publish', 'pending', 'draft'),
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'hp_listing',
                    'value' => $listing_ids,
                    'compare' => 'IN',
                ),
            ),
        );
        
        $bookings = get_posts($calendar_query);
        $calendar_events = array();
        
        foreach ($bookings as $booking) {
            $start_time = get_post_meta($booking->ID, 'hp_start_time', true);
            $end_time = get_post_meta($booking->ID, 'hp_end_time', true);
            $listing_id = get_post_meta($booking->ID, 'hp_listing', true);
            
            if ($start_time && $end_time) {
                $listing = get_post($listing_id);
                $customer = get_userdata($booking->post_author);
                
                $calendar_events[] = array(
                    'id' => $booking->ID,
                    'title' => $listing ? $listing->post_title : 'Booking',
                    'start' => date('c', $start_time),
                    'end' => date('c', $end_time),
                    'status' => $booking->post_status,
                    'customer' => $customer ? $customer->display_name : '',
                    'listing_title' => $listing ? $listing->post_title : '',
                );
            }
        }
        
        return $calendar_events;
    }

    /**
     * Get vendor booking statistics.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Filter arguments
     * @return array
     */
    public static function get_vendor_booking_statistics($vendor_id, $args = array()) {
        $summary = self::get_vendor_bookings_summary($vendor_id, $args);
        
        // Calculate additional statistics
        $total_bookings = $summary['total_bookings'];
        
        $statistics = array(
            'occupancy_rate' => 0,
            'average_booking_value' => 0,
            'cancellation_rate' => 0,
            'upcoming_bookings' => 0,
        );
        
        if ($total_bookings > 0) {
            $statistics['average_booking_value'] = $summary['total_revenue'] / ($total_bookings - $summary['cancelled_bookings']);
            $statistics['cancellation_rate'] = round(($summary['cancelled_bookings'] / $total_bookings) * 100, 1);
        }
        
        // Get upcoming bookings (next 30 days)
        $upcoming_args = array_merge($args, array(
            'date_from' => date('Y-m-d'),
            'date_to' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'confirmed'
        ));
        
        $upcoming_summary = self::get_vendor_bookings_summary($vendor_id, $upcoming_args);
        $statistics['upcoming_bookings'] = $upcoming_summary['confirmed_bookings'];
        
        return $statistics;
    }

    /**
     * Get vendor listings IDs.
     *
     * @param int $vendor_id Vendor post ID
     * @return array
     */
    public static function get_vendor_listings($vendor_id) {
        $listings = get_posts(array(
            'post_type' => 'hp_listing',
            'post_parent' => $vendor_id,
            'post_status' => array('publish', 'draft', 'pending'),
            'numberposts' => -1,
            'fields' => 'ids',
        ));
        
        return $listings;
    }

    /**
     * AJAX handler for filtering bookings.
     */
    public function ajax_filter_bookings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_bookings_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $args = array(
            'status' => sanitize_text_field($_POST['status'] ?? 'all'),
            'date_from' => sanitize_text_field($_POST['date_from'] ?? ''),
            'date_to' => sanitize_text_field($_POST['date_to'] ?? ''),
            'listing_id' => absint($_POST['listing_id'] ?? 0),
            'per_page' => absint($_POST['per_page'] ?? 20),
            'page' => absint($_POST['page'] ?? 1),
        );
        
        $bookings_data = self::get_vendor_bookings_data($vendor->get_id(), $args);
        
        wp_send_json_success($bookings_data);
    }

    /**
     * AJAX handler for updating booking status.
     */
    public function ajax_update_booking_status() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_bookings_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $booking_id = absint($_POST['booking_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        // Verify this booking belongs to the vendor
        $listing_id = get_post_meta($booking_id, 'hp_listing', true);
        $listing = get_post($listing_id);
        
        if (!$listing || $listing->post_parent != $vendor->get_id()) {
            wp_die('Access denied.');
        }
        
        // Update booking status
        $status_map = array(
            'confirmed' => 'publish',
            'pending' => 'pending',
            'cancelled' => 'trash',
        );
        
        if (isset($status_map[$new_status])) {
            wp_update_post(array(
                'ID' => $booking_id,
                'post_status' => $status_map[$new_status],
            ));
            
            wp_send_json_success(array('message' => 'Booking status updated successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Invalid status.'));
        }
    }
}

VDP_Bookings::instance();