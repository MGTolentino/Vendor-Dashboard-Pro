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
    
    // Check if user has a vendor profile
    $vendor = \HivePress\Models\Vendor::query()->filter(
        array(
            'user' => $user_id,
            'status' => 'publish',
        )
    )->get_first();
    
    return !empty($vendor);
}

/**
 * Get current user's vendor object.
 *
 * @return \HivePress\Models\Vendor|null
 */
function vdp_get_current_vendor() {
    if (!is_user_logged_in()) {
        return null;
    }

    // Get current user ID
    $user_id = get_current_user_id();
    
    // Get vendor
    $vendor = \HivePress\Models\Vendor::query()->filter(
        array(
            'user' => $user_id,
            'status' => array('publish', 'draft', 'pending'),
        )
    )->get_first();
    
    return $vendor;
}

/**
 * Check if we're on the my-store page.
 *
 * @return bool
 */
function vdp_is_my_store() {
    return get_query_var('vdp_dashboard', false) !== false;
}

/**
 * Get the my-store URL.
 *
 * @param string $section Optional dashboard section.
 * @return string
 */
function vdp_get_dashboard_url($section = '') {
    $url = home_url('my-store/');
    
    if (!empty($section)) {
        $url = trailingslashit($url) . $section . '/';
    }
    
    return $url;
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
    if (function_exists('hivepress')) {
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
 * Get current dashboard section.
 *
 * @return string
 */
function vdp_get_current_section() {
    $section = get_query_var('vdp_section', 'dashboard');
    
    return sanitize_key($section);
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