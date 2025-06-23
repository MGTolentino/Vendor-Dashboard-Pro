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
    
    // Query for hp_vendor post where current user is the author
    global $wpdb;
    
    $vendor_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_type = 'hp_vendor' 
        AND post_author = %d 
        AND post_status IN ('publish', 'draft', 'pending')",
        $user_id
    ));
    
    return $vendor_count > 0;
}

/**
 * Get current user's vendor object.
 *
 * @return \HivePress\Models\Vendor|object|null
 */
function vdp_get_current_vendor() {
    // Utilizar una variable estática para cachear el resultado entre llamadas
    static $cached_vendor = null;
    static $cache_checked = false;
    
    // Si ya verificamos esta sesión, devolver el resultado cacheado
    if ($cache_checked) {
        return $cached_vendor;
    }
    
    if (!is_user_logged_in()) {
        vdp_debug_log("User not logged in in vdp_get_current_vendor()", "warning");
        $cache_checked = true;
        return null;
    }

    // Get current user ID
    $user_id = get_current_user_id();
    vdp_debug_log("Current user ID: " . $user_id);
    
    // Query for hp_vendor post where current user is the author
    global $wpdb;
    
    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} 
        WHERE post_type = 'hp_vendor' 
        AND post_author = %d 
        AND post_status IN ('publish', 'draft', 'pending') 
        LIMIT 1",
        $user_id
    );
    
    vdp_debug_log("Vendor query: " . $query);
    
    $vendor_post = $wpdb->get_row($query);
    
    if ($vendor_post) {
        vdp_debug_log("Vendor post found, ID: " . $vendor_post->ID . ", Title: " . $vendor_post->post_title);
        // Create a vendor object from post data
        $cached_vendor = vdp_create_vendor_from_post($vendor_post);
    } else {
        vdp_debug_log("No vendor post found for user ID: " . $user_id, "warning");
        // No vendor found for this user
        $cached_vendor = null;
    }
    
    $cache_checked = true;
    return $cached_vendor;
}

/**
 * Vendor object class with proper methods.
 */
class VDP_Vendor_Object {
    private $post;
    private $name;
    private $verified;
    private $user_id;
    
    public function __construct($post) {
        $this->post = $post;
        $this->name = $post->post_title;
        $this->verified = get_post_meta($post->ID, 'hp_verified', true) ?: false;
        $this->user_id = get_post_meta($post->ID, 'hp_user_id', true);
        
        // Get user data for additional info
        $user_info = get_userdata($this->user_id);
        if (empty($this->name) && $post->post_name) {
            $this->name = $post->post_name;
        } elseif ($user_info && empty($this->name)) {
            $this->name = $user_info->display_name;
        }
    }
    
    public function get_id() {
        return $this->post->ID;
    }
    
    public function get_name() {
        return !empty($this->name) ? $this->name : $this->post->post_title;
    }
    
    public function get_image__url($size = 'thumbnail') {
        $attachment_id = get_post_thumbnail_id($this->post->ID);
        if ($attachment_id) {
            $image = wp_get_attachment_image_src($attachment_id, $size);
            return $image ? $image[0] : false;
        }
        return false;
    }
    
    public function is_verified() {
        return (bool) $this->verified;
    }
    
    public function get_slug() {
        return $this->post->post_name;
    }
    
    public function get_registered_date() {
        return $this->post->post_date;
    }
    
    public function get_description() {
        return $this->post->post_content;
    }
    
    public function get_user_id() {
        return $this->user_id;
    }
    
    public function is_active_seller() {
        // Check if vendor is active based on several criteria:
        // 1. Account is verified (if verification is enabled)
        // 2. Has at least one published listing
        // 3. Profile is complete (has description)
        // 4. Has been active in the last 30 days (last login or activity)
        
        // Check if has published listings
        $listings_count = wp_count_posts('hp_listing');
        $has_listings = isset($listings_count->publish) && $listings_count->publish > 0;
        
        // Check if profile is complete
        $has_description = !empty($this->post->post_content);
        
        // Check recent activity (last login)
        $last_login = get_user_meta($this->user_id, 'vdp_last_login', true);
        $thirty_days_ago = strtotime('-30 days');
        $recently_active = empty($last_login) || strtotime($last_login) > $thirty_days_ago;
        
        // Vendor is active if they meet most criteria
        $active_criteria = array($has_listings, $has_description, $recently_active);
        $criteria_met = count(array_filter($active_criteria));
        
        return $criteria_met >= 2; // Must meet at least 2 out of 3 criteria
    }
    
    // Legacy property access for backward compatibility
    public function __get($property) {
        if ($property === 'ID') {
            return $this->get_id();
        }
        return null;
    }
}

/**
 * Create a vendor object from post data.
 *
 * @param object $post WP_Post object for vendor.
 * @return VDP_Vendor_Object Vendor object with callable methods.
 */
function vdp_create_vendor_from_post($post) {
    return new VDP_Vendor_Object($post);
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
    
    // Para dashboard, usar URL base sin parámetros
    if (empty($action) || $action === 'dashboard') {
        // Para dashboard, no añadir parámetros de acción, usar URL base
        vdp_debug_log("URL para dashboard sin parámetros: " . $url);
    } else {
        // Verificar si la acción incluye una ruta como 'messages/view'
        if (strpos($action, '/') !== false) {
            $action_parts = explode('/', $action);
            $main_action = $action_parts[0]; // 'messages'
            
            // Añadir la acción principal
            $url = add_query_arg('vdp-action', $main_action, $url);
            
            // Si hay un tercer componente, usarlo como item
            if (isset($action_parts[2]) && !empty($action_parts[2])) {
                $item = $action_parts[2];
            }
        } else {
            // Para otras secciones, añadir el parámetro vdp-action
            $url = add_query_arg('vdp-action', $action, $url);
        }
        vdp_debug_log("URL para " . $action . ": " . $url);
    }
    
    // Add item
    if (!empty($item)) {
        $url = add_query_arg('vdp-item', $item, $url);
        // Debug URL with item parameter
        vdp_debug_log("Added item parameter '$item' to URL: $url", "info");
    }
    
    // Final debug output of constructed URL
    vdp_debug_log("Final dashboard URL for action '$action', item '$item': $url", "info");
    
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
    
    // Si la variable global está definida, usarla
    if (isset($vdp_current_action) && !empty($vdp_current_action)) {
        vdp_debug_log("Usando acción de variable global: " . $vdp_current_action);
        return $vdp_current_action;
    }
    
    // Si hay un parámetro en la URL, usarlo
    if (isset($_GET['vdp-action']) && !empty($_GET['vdp-action'])) {
        $action = sanitize_key($_GET['vdp-action']);
        vdp_debug_log("Usando acción de parámetro URL: " . $action);
        
        // Establecer la variable global para uso futuro
        $vdp_current_action = $action;
        return $action;
    }
    
    // Si no hay variable global ni parámetro URL, establecer 'dashboard' como valor predeterminado
    vdp_debug_log("No se encontró acción, usando default 'dashboard'");
    $vdp_current_action = 'dashboard';
    return 'dashboard';
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
 * Función de depuración para el dashboard.
 * Solo registra mensajes cuando WP_DEBUG está activado.
 *
 * @param string $message Mensaje a registrar.
 * @param string $type Tipo de mensaje (info, warning, error).
 * @return void
 */
function vdp_debug_log($message, $type = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $prefix = 'VDP Debug';
        
        if ($type === 'warning') {
            $prefix = 'VDP Warning';
        } elseif ($type === 'error') {
            $prefix = 'VDP Error';
        }
        
        error_log("$prefix: $message");
    }
}

/**
 * Función para verificar el estado actual del dashboard.
 * Útil para depuración y diagnóstico.
 *
 * @return array Información sobre el estado actual.
 */
function vdp_get_system_status() {
    global $wpdb, $vdp_current_action, $vdp_current_item;
    
    $user_id = get_current_user_id();
    $vendor_id = null;
    
    // Obtener vendor_id
    $vendor_post = $wpdb->get_row($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
        WHERE post_type = 'hp_vendor' 
        AND post_author = %d 
        AND post_status IN ('publish', 'draft', 'pending')
        LIMIT 1",
        $user_id
    ));
    
    if ($vendor_post) {
        $vendor_id = $vendor_post->ID;
        
        // Contar listings
        $listings_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'hp_listing' 
            AND post_parent = %d 
            AND post_status IN ('publish', 'draft', 'pending')",
            $vendor_id
        ));
    }
    
    return array(
        'user_id' => $user_id,
        'vendor_id' => $vendor_id,
        'current_action' => isset($vdp_current_action) ? $vdp_current_action : 'not set',
        'current_item' => isset($vdp_current_item) ? $vdp_current_item : 'not set',
        'listings_count' => isset($listings_count) ? $listings_count : 0,
        'request_url' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
        'is_ajax' => defined('DOING_AJAX') && DOING_AJAX,
        'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
    );
}

/**
 * Verifica si el plugin Vendor Dashboard Pro está activo.
 * 
 * @return bool Verdadero si el plugin está activo.
 */
function vdp_is_active() {
    return defined('VDP_VERSION') && class_exists('Vendor_Dashboard_Pro');
}

/**
 * Obtiene el total de mensajes no leídos para un vendedor.
 * 
 * @param int $vendor_id ID del vendedor.
 * @return int Número de mensajes no leídos.
 */
function vdp_get_unread_messages_count($vendor_id) {
    global $wpdb;
    
    if (!$vendor_id) {
        return 0;
    }
    
    $table_name = $wpdb->prefix . 'vdp_messages';
    
    // Verificar si la tabla existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    
    if (!$table_exists) {
        return 0; // La tabla no existe, posiblemente el plugin se acaba de instalar
    }
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_name} 
        WHERE vendor_id = %d 
        AND is_read = 0 
        AND is_archived = 0",
        $vendor_id
    ));
    
    return (int) $count;
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

/**
 * Redirige la URL account/vendor/dashboard a my-store
 */
function vdp_redirect_vendor_dashboard() {
    // Obtener la URL actual
    $current_url = $_SERVER['REQUEST_URI'];
    
    // Verificar si la URL es account/vendor/dashboard o similares
    if (preg_match('|/account/vendor/dashboard/?|', $current_url)) {
        // Obtener la URL de My Store
        $my_store_url = home_url('/my-store/');
        
        // Redirigir
        wp_redirect($my_store_url);
        exit;
    }
}
add_action('template_redirect', 'vdp_redirect_vendor_dashboard');