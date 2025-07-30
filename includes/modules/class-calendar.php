<?php
/**
 * Calendar Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Calendar module class.
 */
class VDP_Calendar {
    /**
     * Instance of this class.
     *
     * @var VDP_Calendar
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Calendar
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
        add_action('vdp_calendar_content', array($this, 'render_calendar'), 10);
        
        // AJAX actions
        add_action('wp_ajax_vdp_get_calendar_data', array($this, 'ajax_get_calendar_data'));
        add_action('wp_ajax_vdp_block_dates', array($this, 'ajax_block_dates'));
        add_action('wp_ajax_vdp_unblock_dates', array($this, 'ajax_unblock_dates'));
        add_action('wp_ajax_vdp_update_price_range', array($this, 'ajax_update_price_range'));
    }

    /**
     * Render calendar content.
     */
    public function render_calendar() {
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get vendor listings
        $listings = self::get_vendor_listings($vendor->get_id());
        $calendar_full_data = self::get_calendar_data($vendor->get_id());
        $calendar_data = $calendar_full_data; // Pass full structure to template
        
        include VDP_PLUGIN_DIR . 'templates/calendar-content.php';
    }

    /**
     * Get vendor listings for calendar.
     *
     * @param int $vendor_id Vendor post ID
     * @return array
     */
    public static function get_vendor_listings($vendor_id) {
        $listings = get_posts(array(
            'post_type' => 'hp_listing',
            'post_parent' => $vendor_id,
            'post_status' => array('publish', 'draft'),
            'numberposts' => -1,
        ));
        
        $listings_data = array();
        foreach ($listings as $listing) {
            $listings_data[] = array(
                'id' => $listing->ID,
                'title' => $listing->post_title,
                'status' => $listing->post_status,
                'booking_enabled' => get_post_meta($listing->ID, 'hp_booking_enabled', true),
                'time_booking' => get_post_meta($listing->ID, 'hp_booking_time_enabled', true),
                'url' => get_permalink($listing->ID),
            );
        }
        
        return $listings_data;
    }

    /**
     * Get calendar data for all vendor listings.
     *
     * @param int $vendor_id Vendor post ID
     * @param array $args Optional arguments
     * @return array
     */
    public static function get_calendar_data($vendor_id, $args = array()) {
        $args = wp_parse_args($args, array(
            'listing_id' => null,
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-t', strtotime('+2 months')),
        ));
        
        $listings = self::get_vendor_listings($vendor_id);
        $events = array();
        $price_ranges = array();
        
        // Debug: Log listings count
        error_log('VDP Calendar Debug - Listings count: ' . count($listings));
        error_log('VDP Calendar Debug - Args: ' . print_r($args, true));
        
        foreach ($listings as $listing) {
            if ($args['listing_id'] && $listing['id'] != $args['listing_id']) {
                continue;
            }
            
            // Get bookings for this listing
            $bookings = self::get_listing_bookings($listing['id'], $args);
            error_log('VDP Calendar Debug - Listing ' . $listing['id'] . ' (' . $listing['title'] . ') has ' . count($bookings) . ' bookings');
            
            foreach ($bookings as $booking) {
                $events[] = array(
                    'id' => $booking['id'],
                    'title' => $listing['title'] . ' #' . $booking['id'],
                    'start' => $booking['start_date'],
                    'end' => $booking['end_date'],
                    'allDay' => !$listing['time_booking'],
                    'color' => self::get_booking_color($booking['status']),
                    'listing_id' => $listing['id'],
                    'listing_title' => $listing['title'],
                    'status' => $booking['status'],
                    'customer' => $booking['customer_name'],
                    'amount' => $booking['amount'],
                    'extendedProps' => array(
                        'listing_id' => $listing['id'],
                        'listing_title' => $listing['title'],
                        'status' => $booking['status'],
                        'customer' => $booking['customer_name'],
                        'amount' => $booking['amount'],
                    ),
                );
            }
            
            // Get price ranges for this listing
            $ranges = self::get_listing_price_ranges($listing['id'], $args);
            foreach ($ranges as $range) {
                $price_ranges[] = array(
                    'start' => $range['start_date'],
                    'end' => $range['end_date'],
                    'price' => $range['price'],
                    'listing_id' => $listing['id'],
                    'listing_title' => $listing['title'],
                );
            }
        }
        
        // Debug: Log final events count
        error_log('VDP Calendar Debug - Total events generated: ' . count($events));
        
        return array(
            'events' => $events,
            'price_ranges' => $price_ranges,
            'listings' => $listings,
        );
    }

    /**
     * Get bookings for a specific listing.
     *
     * @param int $listing_id Listing ID
     * @param array $args Date range args
     * @return array
     */
    public static function get_listing_bookings($listing_id, $args = array()) {
        $bookings_query = array(
            'post_type' => 'hp_booking',
            'post_status' => array('publish', 'pending', 'draft', 'private'),
            'numberposts' => -1,
            'post_parent' => $listing_id,
            'date_query' => array(
                array(
                    'after' => $args['start_date'],
                    'before' => $args['end_date'],
                    'inclusive' => true,
                )
            )
        );
        
        $bookings = get_posts($bookings_query);
        $bookings_data = array();
        
        // Debug: Log booking query results
        error_log('VDP Calendar Debug - get_listing_bookings for listing ' . $listing_id . ' found ' . count($bookings) . ' raw bookings');
        error_log('VDP Calendar Debug - Query: ' . print_r($bookings_query, true));
        
        foreach ($bookings as $booking) {
            $start_date = get_post_meta($booking->ID, 'hp_start_date', true);
            $end_date = get_post_meta($booking->ID, 'hp_end_date', true);
            $start_time = get_post_meta($booking->ID, 'hp_start_time', true);
            $end_time = get_post_meta($booking->ID, 'hp_end_time', true);
            
            // Determine start and end dates with improved fallbacks
            $start_datetime = null;
            $end_datetime = null;
            
            // Priority 1: Use timestamp metadata
            if ($start_time && $end_time) {
                $start_datetime = date('c', $start_time);
                $end_datetime = date('c', $end_time);
            } 
            // Priority 2: Use date strings
            elseif ($start_date && $end_date) {
                $start_datetime = $start_date . 'T00:00:00';
                $end_datetime = $end_date . 'T23:59:59';
            }
            // Priority 3: Use single date if only one is available
            elseif ($start_date) {
                $start_datetime = $start_date . 'T00:00:00';
                $end_datetime = $start_date . 'T23:59:59';
            }
            // Priority 4: Fallback to post date
            else {
                $post_date = get_post_time('Y-m-d', false, $booking->ID);
                if ($post_date) {
                    $start_datetime = $post_date . 'T00:00:00';
                    $end_datetime = $post_date . 'T23:59:59';
                } else {
                    continue; // Skip if no valid dates at all
                }
            }
            
            $customer_id = $booking->post_author;
            $customer = get_userdata($customer_id);
            
            $bookings_data[] = array(
                'id' => $booking->ID,
                'start_date' => $start_datetime,
                'end_date' => $end_datetime,
                'status' => $booking->post_status,
                'customer_name' => $customer ? $customer->display_name : __('Guest', 'vendor-dashboard-pro'),
                'customer_email' => $customer ? $customer->user_email : '',
                'amount' => get_post_meta($booking->ID, 'hp_total', true) ?: get_post_meta($booking->ID, 'hp_price_extras', true) ?: 0,
                'quantity' => get_post_meta($booking->ID, 'hp_quantity', true) ?: 1,
            );
        }
        
        return $bookings_data;
    }

    /**
     * Get price ranges for a specific listing.
     *
     * @param int $listing_id Listing ID
     * @param array $args Date range args
     * @return array
     */
    public static function get_listing_price_ranges($listing_id, $args = array()) {
        global $wpdb;
        
        // Get price ranges from booking_range comments
        $ranges = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->comments} 
            WHERE comment_post_ID = %d 
            AND comment_type = 'booking_range'
            AND comment_date >= %s 
            AND comment_date <= %s
            ORDER BY comment_date ASC",
            $listing_id,
            $args['start_date'],
            $args['end_date']
        ));
        
        $price_ranges = array();
        foreach ($ranges as $range) {
            $range_data = maybe_unserialize($range->comment_content);
            if (is_array($range_data)) {
                $price_ranges[] = array(
                    'start_date' => $range_data['start_date'],
                    'end_date' => $range_data['end_date'],
                    'price' => $range_data['price'],
                );
            }
        }
        
        return $price_ranges;
    }

    /**
     * Get booking status color.
     *
     * @param string $status Booking status
     * @return string
     */
    public static function get_booking_color($status) {
        $colors = array(
            'publish' => '#28a745',    // Green - confirmed
            'draft' => '#ffc107',      // Yellow - pending
            'private' => '#dc3545',    // Red - blocked
            'trash' => '#6c757d',      // Gray - cancelled
        );
        
        return isset($colors[$status]) ? $colors[$status] : '#007cba';
    }

    /**
     * AJAX handler for getting calendar data.
     */
    public function ajax_get_calendar_data() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_calendar_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $args = array(
            'listing_id' => absint($_POST['listing_id'] ?? 0),
            'start_date' => sanitize_text_field($_POST['start'] ?? date('Y-m-01')),
            'end_date' => sanitize_text_field($_POST['end'] ?? date('Y-m-t')),
        );
        
        $calendar_data = self::get_calendar_data($vendor->get_id(), $args);
        
        wp_send_json_success($calendar_data);
    }

    /**
     * AJAX handler for blocking dates.
     */
    public function ajax_block_dates() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_calendar_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $listing_id = absint($_POST['listing_id']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        
        // Verify listing belongs to vendor
        if (!self::verify_listing_ownership($listing_id, $vendor->get_id())) {
            wp_die('Access denied.');
        }
        
        // Create blocked booking
        $booking_id = wp_insert_post(array(
            'post_type' => 'hp_booking',
            'post_status' => 'private',
            'post_title' => 'Blocked dates',
            'post_author' => get_current_user_id(),
        ));
        
        if ($booking_id) {
            update_post_meta($booking_id, 'hp_listing', $listing_id);
            update_post_meta($booking_id, 'hp_start_date', $start_date);
            update_post_meta($booking_id, 'hp_end_date', $end_date);
            update_post_meta($booking_id, 'hp_blocked', true);
            
            wp_send_json_success(array(
                'message' => __('Dates blocked successfully.', 'vendor-dashboard-pro'),
                'booking_id' => $booking_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Error blocking dates.', 'vendor-dashboard-pro')));
        }
    }

    /**
     * AJAX handler for unblocking dates.
     */
    public function ajax_unblock_dates() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_calendar_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $listing_id = absint($_POST['listing_id']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        
        // Verify listing belongs to vendor
        if (!self::verify_listing_ownership($listing_id, $vendor->get_id())) {
            wp_die('Access denied.');
        }
        
        // Find and remove blocked bookings in date range
        $blocked_bookings = get_posts(array(
            'post_type' => 'hp_booking',
            'post_status' => 'private',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => 'hp_listing',
                    'value' => $listing_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'hp_blocked',
                    'value' => true,
                    'compare' => '='
                ),
                array(
                    'key' => 'hp_start_date',
                    'value' => array($start_date, $end_date),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        ));
        
        $removed_count = 0;
        foreach ($blocked_bookings as $booking) {
            if (wp_delete_post($booking->ID, true)) {
                $removed_count++;
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d blocked dates removed.', 'vendor-dashboard-pro'), $removed_count)
        ));
    }

    /**
     * AJAX handler for updating price ranges.
     */
    public function ajax_update_price_range() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vdp_calendar_nonce')) {
            wp_die('Security check failed.');
        }
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_die('Access denied.');
        }
        
        $listing_id = absint($_POST['listing_id']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $price = floatval($_POST['price']);
        
        // Verify listing belongs to vendor
        if (!self::verify_listing_ownership($listing_id, $vendor->get_id())) {
            wp_die('Access denied.');
        }
        
        // Save price range as comment
        $range_data = array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'price' => $price,
        );
        
        $comment_id = wp_insert_comment(array(
            'comment_post_ID' => $listing_id,
            'comment_type' => 'booking_range',
            'comment_content' => serialize($range_data),
            'comment_approved' => 1,
            'comment_author' => get_current_user_id(),
            'comment_date' => current_time('mysql'),
        ));
        
        if ($comment_id) {
            wp_send_json_success(array(
                'message' => __('Price range updated successfully.', 'vendor-dashboard-pro')
            ));
        } else {
            wp_send_json_error(array('message' => __('Error updating price range.', 'vendor-dashboard-pro')));
        }
    }

    /**
     * Verify listing ownership.
     *
     * @param int $listing_id Listing ID
     * @param int $vendor_id Vendor ID
     * @return bool
     */
    public static function verify_listing_ownership($listing_id, $vendor_id) {
        $listing = get_post($listing_id);
        return $listing && $listing->post_parent == $vendor_id;
    }

    /**
     * Get supported languages for FullCalendar.
     *
     * @return array
     */
    public static function get_calendar_locale() {
        $locale = get_locale();
        
        // Map WordPress locales to FullCalendar locales
        $locale_map = array(
            'es_ES' => 'es',
            'es_MX' => 'es',
            'es_AR' => 'es',
            'en_US' => 'en',
            'en_GB' => 'en-gb',
            'fr_FR' => 'fr',
            'de_DE' => 'de',
            'it_IT' => 'it',
            'pt_BR' => 'pt-br',
        );
        
        return isset($locale_map[$locale]) ? $locale_map[$locale] : 'en';
    }

    /**
     * Get calendar configuration.
     *
     * @return array
     */
    public static function get_calendar_config() {
        $locale = self::get_calendar_locale();
        $is_spanish = strpos($locale, 'es') === 0;
        
        return array(
            'locale' => $locale,
            'firstDay' => 1, // Monday
            'headerToolbar' => array(
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,listWeek'
            ),
            'buttonText' => $is_spanish ? array(
                'today' => 'Hoy',
                'month' => 'Mes',
                'week' => 'Semana',
                'list' => 'Lista'
            ) : array(
                'today' => 'Today',
                'month' => 'Month',
                'week' => 'Week',
                'list' => 'List'
            ),
            'customButtons' => $is_spanish ? array(
                'block' => array(
                    'text' => 'Bloquear',
                    'icon' => 'fc-icon-lock'
                ),
                'unblock' => array(
                    'text' => 'Desbloquear',
                    'icon' => 'fc-icon-unlock'
                ),
                'pricing' => array(
                    'text' => 'Precios',
                    'icon' => 'fc-icon-dollar'
                )
            ) : array(
                'block' => array(
                    'text' => 'Block',
                    'icon' => 'fc-icon-lock'
                ),
                'unblock' => array(
                    'text' => 'Unblock',
                    'icon' => 'fc-icon-unlock'
                ),
                'pricing' => array(
                    'text' => 'Pricing',
                    'icon' => 'fc-icon-dollar'
                )
            )
        );
    }
}

VDP_Calendar::instance();