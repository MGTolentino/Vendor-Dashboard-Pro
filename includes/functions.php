<?php
/**
 * Helper functions
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if the current user is a vendor.
 *
 * @return bool
 */
function vdp_is_user_vendor() {
    if (!is_user_logged_in()) {
        return false;
    }

    // Get current user ID
    $user_id = get_current_user_id();
    
    // Direct database query approach - more reliable across different setups
    global $wpdb;
    
    // Query for hp_vendor post type associated with the current user
    $vendor_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p 
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'hp_vendor' 
        AND p.post_status = 'publish' 
        AND pm.meta_key = 'hp_user_id' 
        AND pm.meta_value = %d",
        $user_id
    ));
    
    // If we found a vendor post for this user
    if ($vendor_count > 0) {
        return true;
    }
    
    // Try HivePress API as fallback if available
    if (class_exists('\HivePress\Models\Vendor')) {
        try {
            // Check if user has a vendor profile using HivePress API
            $vendor = \HivePress\Models\Vendor::query()->filter(
                array(
                    'user' => $user_id,
                    'status' => 'publish',
                )
            )->get_first();
            
            return !empty($vendor);
        } catch (Exception $e) {
            // If HivePress API fails, we already tried direct DB query
            return false;
        }
    }
    
    // No vendor found for this user
    return false;
}

/**
 * Get current user's vendor object.
 *
 * @return \HivePress\Models\Vendor|object|null
 */
function vdp_get_current_vendor() {
    if (!is_user_logged_in()) {
        return null;
    }

    // Get current user ID
    $user_id = get_current_user_id();
    
    // Try direct database query first - more reliable across different setups
    global $wpdb;
    
    // Query for hp_vendor post associated with current user
    $vendor_post = $wpdb->get_row($wpdb->prepare(
        "SELECT p.* FROM {$wpdb->posts} p 
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'hp_vendor' 
        AND p.post_status IN ('publish', 'draft', 'pending') 
        AND pm.meta_key = 'hp_user_id' 
        AND pm.meta_value = %d 
        LIMIT 1",
        $user_id
    ));
    
    if ($vendor_post) {
        // Create a vendor object from post data
        return vdp_create_vendor_from_post($vendor_post);
    }
    
    // Try HivePress API as fallback if available
    if (class_exists('\HivePress\Models\Vendor')) {
        try {
            // Get vendor using HivePress API
            $vendor = \HivePress\Models\Vendor::query()->filter(
                array(
                    'user' => $user_id,
                    'status' => array('publish', 'draft', 'pending'),
                )
            )->get_first();
            
            if (!empty($vendor)) {
                return $vendor;
            }
        } catch (Exception $e) {
            // If HivePress API fails, we already tried direct DB query
        }
    }
    
    // No vendor found for this user
    return null;
}

/**
 * Create a vendor object from post data.
 *
 * @param object $post WP_Post object for vendor.
 * @return object Vendor object with callable methods.
 */
function vdp_create_vendor_from_post($post) {
    // Create an object with callable methods
    $vendor = new stdClass();
    
    // Get vendor metadata
    // Use post_title first, then try post_name, then fallback to user display name
    $name = $post->post_title;
    $verified = get_post_meta($post->ID, 'hp_verified', true) ?: false;
    $user_id = get_post_meta($post->ID, 'hp_user_id', true);
    
    // Get user data for additional info
    $user_info = get_userdata($user_id);
    if (empty($name) && $post->post_name) {
        $name = $post->post_name;
    } elseif ($user_info && empty($name)) {
        $name = $user_info->display_name;
    }
    
    // Debug log
    error_log('Vendor ID: ' . $post->ID);
    error_log('Vendor post_name: ' . $post->post_name);
    error_log('Vendor post_title: ' . $post->post_title);
    error_log('Final vendor name: ' . $name);
    
    // Add methods to the vendor object
    $vendor->get_id = function() use ($post) {
        return $post->ID;
    };
    
    $vendor->get_name = function() use ($name, $post) {
        return !empty($name) ? $name : $post->post_title;
    };
    
    $vendor->get_image__url = function($size = 'thumbnail') use ($post) {
        $attachment_id = get_post_thumbnail_id($post->ID);
        if ($attachment_id) {
            $image = wp_get_attachment_image_src($attachment_id, $size);
            return $image ? $image[0] : false;
        }
        return false;
    };
    
    $vendor->is_verified = function() use ($verified) {
        return (bool) $verified;
    };
    
    $vendor->get_slug = function() use ($post) {
        return $post->post_name;
    };
    
    $vendor->get_registered_date = function() use ($post) {
        return $post->post_date;
    };
    
    $vendor->get_description = function() use ($post) {
        return $post->post_content;
    };
    
    $vendor->get_user_id = function() use ($user_id) {
        return $user_id;
    };
    
    $vendor->is_active_seller = function() use ($post) {
        // Check if vendor is active based on several criteria:
        // 1. Account is verified (if verification is enabled)
        // 2. Has at least one published listing
        // 3. Profile is complete (has description)
        // 4. Has been active in the last 30 days (last login or activity)
        
        $is_active = true;
        
        // Check if has published listings
        $listings_count = wp_count_posts('hp_listing');
        $has_listings = isset($listings_count->publish) && $listings_count->publish > 0;
        
        // Check if profile is complete
        $has_description = !empty($post->post_content);
        
        // Check recent activity (last login)
        $user_id = get_post_meta($post->ID, 'hp_user_id', true);
        $last_login = get_user_meta($user_id, 'vdp_last_login', true);
        $thirty_days_ago = strtotime('-30 days');
        $recently_active = empty($last_login) || strtotime($last_login) > $thirty_days_ago;
        
        // Vendor is active if they meet most criteria
        $active_criteria = array($has_listings, $has_description, $recently_active);
        $criteria_met = count(array_filter($active_criteria));
        
        return $criteria_met >= 2; // Must meet at least 2 out of 3 criteria
    };
    
    return $vendor;
}

/**
 * Check if we're on a vendor dashboard page.
 *
 * @return bool
 */
function vdp_is_dashboard_page() {
    global $post;
    
    // Check if it's an AJAX request
    if (isset($_GET['vdp_ajax']) && $_GET['vdp_ajax']) {
        return true;
    }
    
    // Check if we're on a page with our shortcode
    if (is_a($post, 'WP_Post')) {
        return has_shortcode($post->post_content, 'vendor_dashboard_pro');
    }
    
    return false;
}

/**
 * Get the URL for a dashboard section.
 *
 * @param string $action Optional dashboard action/section.
 * @param string $item Optional item ID.
 * @return string
 */
function vdp_get_dashboard_url($action = '', $item = '') {
    // Get the URL of the page with the shortcode
    $dashboard_page_id = vdp_get_dashboard_page_id();
    
    if (!$dashboard_page_id) {
        // Fallback to current page if we can't find a dashboard page
        $url = get_permalink();
    } else {
        $url = get_permalink($dashboard_page_id);
    }
    
    // Add action
    if (!empty($action) && $action !== 'dashboard') {
        $url = add_query_arg('vdp-action', $action, $url);
    }
    
    // Add item
    if (!empty($item)) {
        $url = add_query_arg('vdp-item', $item, $url);
    }
    
    return $url;
}

/**
 * Get the ID of the page containing the vendor dashboard shortcode.
 *
 * @return int|null Page ID or null if not found.
 */
function vdp_get_dashboard_page_id() {
    static $dashboard_page_id = null;
    
    if ($dashboard_page_id !== null) {
        return $dashboard_page_id;
    }
    
    // Find the page with our shortcode
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        's' => '[vendor_dashboard_pro'
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        $dashboard_page_id = $query->posts[0]->ID;
    } else {
        $dashboard_page_id = 0; // No page found
    }
    
    return $dashboard_page_id;
}

/**
 * Get current dashboard action.
 *
 * @return string
 */
function vdp_get_current_action() {
    global $vdp_current_action;
    
    if (isset($vdp_current_action)) {
        return $vdp_current_action;
    }
    
    // Fallback to URL parameter
    return isset($_GET['vdp-action']) ? sanitize_key($_GET['vdp-action']) : 'dashboard';
}

/**
 * Get current dashboard item.
 *
 * @return string
 */
function vdp_get_current_item() {
    global $vdp_current_item;
    
    if (isset($vdp_current_item)) {
        return $vdp_current_item;
    }
    
    // Fallback to URL parameter
    return isset($_GET['vdp-item']) ? sanitize_key($_GET['vdp-item']) : '';
}

/**
 * Format a number for display.
 *
 * @param int $number Number to format.
 * @param int $decimals Number of decimal points.
 * @return string
 */
function vdp_format_number($number, $decimals = 0) {
    if ($number >= 1000000) {
        return number_format($number / 1000000, $decimals) . 'M';
    } elseif ($number >= 1000) {
        return number_format($number / 1000, $decimals) . 'K';
    }
    
    return number_format($number, $decimals);
}

/**
 * Format price for display.
 *
 * @param float $price Price value.
 * @return string
 */
function vdp_format_price($price) {
    if (function_exists('hivepress') && method_exists(hivepress()->translator, 'get_string')) {
        return hivepress()->translator->get_string('price_format', [ $price ]);
    }
    
    return '$' . number_format($price, 2);
}

/**
 * Format a date.
 *
 * @param string $date Date in MySQL format.
 * @param string $format Optional format string.
 * @return string
 */
function vdp_format_date($date, $format = '') {
    if (empty($format)) {
        $format = get_option('date_format');
    }
    
    return date_i18n($format, strtotime($date));
}

/**
 * Format a time ago string.
 *
 * @param string $date Date in MySQL format.
 * @return string
 */
function vdp_time_ago($date) {
    $time = strtotime($date);
    $current = current_time('timestamp');
    $diff = $current - $time;
    
    if ($diff < 60) {
        return __('just now', 'vendor-dashboard-pro');
    }
    
    $intervals = array(
        31536000 => array(__('year', 'vendor-dashboard-pro'), __('years', 'vendor-dashboard-pro')),
        2592000  => array(__('month', 'vendor-dashboard-pro'), __('months', 'vendor-dashboard-pro')),
        604800   => array(__('week', 'vendor-dashboard-pro'), __('weeks', 'vendor-dashboard-pro')),
        86400    => array(__('day', 'vendor-dashboard-pro'), __('days', 'vendor-dashboard-pro')),
        3600     => array(__('hour', 'vendor-dashboard-pro'), __('hours', 'vendor-dashboard-pro')),
        60       => array(__('minute', 'vendor-dashboard-pro'), __('minutes', 'vendor-dashboard-pro')),
    );
    
    foreach ($intervals as $seconds => $strings) {
        $count = floor($diff / $seconds);
        
        if ($count > 0) {
            if ($count == 1) {
                $text = $strings[0];
            } else {
                $text = $strings[1];
            }
            
            return sprintf(__('%d %s ago', 'vendor-dashboard-pro'), $count, $text);
        }
    }
    
    return __('just now', 'vendor-dashboard-pro');
}

/**
 * Get random statistics for demo purposes.
 * In a production environment, this would be replaced with real data.
 *
 * @return array
 */
function vdp_get_demo_statistics() {
    return array(
        'sales_count' => rand(10, 1000),
        'sales_amount' => rand(1000, 100000),
        'views_count' => rand(100, 10000),
        'conversion_rate' => rand(1, 10),
        'average_rating' => rand(35, 50) / 10,
        'ratings_count' => rand(10, 500),
        'messages_count' => rand(5, 50),
        'response_rate' => rand(70, 100),
        'response_time' => rand(1, 24),
    );
}

/**
 * Get demo chart data for various metrics.
 * 
 * @param string $metric Metric name.
 * @param int $days Number of days.
 * @return array
 */
function vdp_get_demo_chart_data($metric = 'sales', $days = 30) {
    $data = array();
    $date = new DateTime();
    $date->modify('-' . ($days - 1) . ' days');
    
    for ($i = 0; $i < $days; $i++) {
        $current_date = $date->format('Y-m-d');
        
        switch ($metric) {
            case 'sales':
                $value = rand(0, 100);
                break;
                
            case 'views':
                $value = rand(10, 1000);
                break;
                
            case 'conversion':
                $value = rand(1, 10) / 10;
                break;
                
            default:
                $value = rand(1, 100);
        }
        
        $data[] = array(
            'date' => $current_date,
            'value' => $value,
        );
        
        $date->modify('+1 day');
    }
    
    return $data;
}