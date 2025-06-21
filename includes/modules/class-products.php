<?php
/**
 * Products Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Products module class.
 */
class VDP_Products {
    /**
     * Instance of this class.
     *
     * @var VDP_Products
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Products
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
        add_action('vdp_products_content', array($this, 'render_products_list'), 10);
        add_action('vdp_product_edit_content', array($this, 'render_product_edit_form'), 10);
    }

    /**
     * Render products list.
     */
    public function render_products_list() {
        global $wpdb;
        
        // Get current user ID
        $user_id = get_current_user_id();
        if (!$user_id) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Please login to view your listings.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Obtener directamente el post del vendor para el usuario actual
        $vendor_post = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} 
            WHERE post_type = 'hp_vendor' 
            AND post_author = %d 
            AND post_status IN ('publish', 'draft', 'pending')
            LIMIT 1",
            $user_id
        ));
        
        if (!$vendor_post) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('No vendor profile found for your account. Please register as a vendor.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Usar directamente el ID del post de vendor
        $vendor_id = $vendor_post->ID;
        
        // Crear el vendor object para mantener compatibilidad con el resto del código
        $vendor = vdp_create_vendor_from_post($vendor_post);
        
        // Get current page
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        
        // Get listings per page
        $per_page = 10;
        
        // Get vendor listings directamente por post_parent
        $listings_query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} 
            WHERE post_type = 'hp_listing' 
            AND post_parent = %d 
            AND post_status IN ('publish', 'draft', 'pending')
            ORDER BY post_date DESC
            LIMIT %d OFFSET %d",
            $vendor_id, $per_page, ($paged - 1) * $per_page
        );
        
        $listings_posts = $wpdb->get_results($listings_query);
        
        // Formatear los listings
        $listings = array();
        foreach ($listings_posts as $listing) {
            $price = get_post_meta($listing->ID, 'hp_price', true);
            $thumbnail_id = get_post_thumbnail_id($listing->ID);
            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
            
            $listings[] = array(
                'id' => $listing->ID,
                'title' => $listing->post_title,
                'status' => $listing->post_status,
                'date' => $listing->post_date,
                'price' => $price ? $price : 0,
                'thumbnail' => $thumbnail_url,
                'edit_url' => vdp_get_dashboard_url('products', $listing->ID),
            );
        }
        
        // Get total listings count
        $count_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'hp_listing' 
            AND post_parent = %d 
            AND post_status IN ('publish', 'draft', 'pending')",
            $vendor_id
        );
        
        $total_listings = (int) $wpdb->get_var($count_query);
        
        // Calculate total pages
        $total_pages = ceil($total_listings / $per_page);
        
        // Get listing categories
        $categories = self::get_listing_categories();
        
        // Ejecutar el hook antes de incluir el template
        // Esto permite que otros plugins o temas puedan modificar los datos
        do_action('vdp_before_products_content', $vendor_id, $listings, $total_listings);
        
        // *** ASEGURARNOS QUE LAS VARIABLES ESTÁN DISPONIBLES EN EL TEMPLATE ***
        // Incluir explícitamente el template con las variables en el scope
        include VDP_PLUGIN_DIR . 'templates/products-content.php';
        
        // Importante: Retornar aquí para evitar que se incluya el template dos veces
        return;
    }

    /**
     * Render product edit form.
     */
    public function render_product_edit_form() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Check if we're editing an existing listing
        $listing_id = get_query_var('vdp_item', 0);
        $listing = null;
        
        if ($listing_id) {
            // Get listing
            $listing = \HivePress\Models\Listing::query()->get_by_id($listing_id);
            
            // Check if listing exists and belongs to the current vendor
            if (!$listing || $listing->get_vendor__id() != $vendor->get_id()) {
                echo '<div class="vdp-notice vdp-notice-error">';
                echo '<p>' . esc_html__('Listing not found or you do not have permission to edit it.', 'vendor-dashboard-pro') . '</p>';
                echo '</div>';
                return;
            }
        }
        
        // Get listing categories
        $categories = vdp_api()->get_listing_categories();
        
        // Get listing images
        $images = array();
        
        if ($listing && $listing->get_image__id()) {
            $images[] = array(
                'id' => $listing->get_image__id(),
                'url' => $listing->get_image__url('thumbnail'),
            );
        }
        
        // Include product edit template
        include VDP_PLUGIN_DIR . 'templates/product-edit-content.php';
    }

    /**
     * Get product status label.
     *
     * @param string $status Product status.
     * @return string
     */
    public static function get_status_label($status) {
        $statuses = array(
            'publish' => __('Published', 'vendor-dashboard-pro'),
            'draft' => __('Draft', 'vendor-dashboard-pro'),
            'pending' => __('Pending Review', 'vendor-dashboard-pro'),
        );
        
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }

    /**
     * Get product status class.
     *
     * @param string $status Product status.
     * @return string
     */
    public static function get_status_class($status) {
        $classes = array(
            'publish' => 'vdp-status-published',
            'draft' => 'vdp-status-draft',
            'pending' => 'vdp-status-pending',
        );
        
        return isset($classes[$status]) ? $classes[$status] : '';
    }
    
    /**
     * Get vendor listings from database.
     *
     * @param int $vendor_id Vendor ID.
     * @param int $limit Number of listings to get.
     * @param int $offset Offset for pagination.
     * @return array Array of listing objects.
     */
    public static function get_vendor_listings($vendor_id, $limit = 10, $offset = 0) {
        // Implementar un sistema de caché para evitar consultas repetidas
        static $listings_cache = array();
        $cache_key = $vendor_id . '_' . $limit . '_' . $offset;
        
        // Si ya tenemos estos listings en caché, devolverlos directamente
        if (isset($listings_cache[$cache_key])) {
            vdp_debug_log("Usando listings en caché para vendor_id: {$vendor_id}, limit: {$limit}, offset: {$offset}");
            return $listings_cache[$cache_key];
        }
        
        global $wpdb;
        
        // Si no tenemos vendor_id, no podemos continuar
        if (!$vendor_id) {
            return array(); // Devolver array vacío
        }
        
        // Mejorar la eficiencia de la consulta SQL - obtener los datos que necesitamos en una sola consulta
        // Joins para obtener meta_datos y thumbnails en la misma consulta
        $query = $wpdb->prepare(
            "SELECT p.*, 
                    pm_price.meta_value as price,
                    pm_thumbnail.meta_value as thumbnail_id  
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm_price ON (p.ID = pm_price.post_id AND pm_price.meta_key = 'hp_price')
             LEFT JOIN {$wpdb->postmeta} pm_thumbnail ON (p.ID = pm_thumbnail.post_id AND pm_thumbnail.meta_key = '_thumbnail_id')
             WHERE p.post_type = 'hp_listing' 
             AND p.post_parent = %d 
             AND p.post_status IN ('publish', 'draft', 'pending')
             GROUP BY p.ID
             ORDER BY p.post_date DESC
             LIMIT %d OFFSET %d",
            $vendor_id, $limit, $offset
        );
        
        vdp_debug_log("Consulta optimizada de listings: " . $query);
        
        // Ejecutar la consulta
        $listings = $wpdb->get_results($query);
        
        $formatted_listings = array();
        
        foreach ($listings as $listing) {
            $price = !empty($listing->price) ? $listing->price : 0;
            $thumbnail_id = !empty($listing->thumbnail_id) ? $listing->thumbnail_id : 0;
            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
            
            $formatted_listings[] = array(
                'id' => $listing->ID,
                'title' => $listing->post_title,
                'status' => $listing->post_status,
                'date' => $listing->post_date,
                'price' => $price ? $price : 0,
                'thumbnail' => $thumbnail_url,
                'edit_url' => vdp_get_dashboard_url('products', $listing->ID),
            );
        }
        
        // Guardar en caché para futuras peticiones
        $listings_cache[$cache_key] = $formatted_listings;
        
        return $formatted_listings;
    }
    
    /**
     * Get total count of vendor listings.
     *
     * @param int $vendor_id Vendor ID.
     * @return int Total count.
     */
    public static function get_vendor_listing_count($vendor_id) {
        // Implementar caché para evitar consultas repetidas
        static $count_cache = array();
        
        // Si ya tenemos este conteo en caché, devolverlo directamente
        if (isset($count_cache[$vendor_id])) {
            vdp_debug_log("Usando conteo de listings en caché para vendor_id: {$vendor_id}");
            return $count_cache[$vendor_id];
        }
        
        global $wpdb;
        
        // Si no tenemos vendor_id, no podemos continuar
        if (!$vendor_id) {
            return 0; // Devolver cero
        }
        
        // Contar listings donde post_parent = vendor_id (relación estricta)
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'hp_listing' 
            AND post_parent = %d 
            AND post_status IN ('publish', 'draft', 'pending')",
            $vendor_id
        );
        
        vdp_debug_log("Listing count query: " . $query);
        
        $count = (int) $wpdb->get_var($query);
        
        vdp_debug_log("Listing count result: " . $count);
        
        // Guardar en caché para futuras peticiones
        $count_cache[$vendor_id] = $count;
        
        return $count;
    }
    
    /**
     * Get listing categories.
     *
     * @return array Array of categories.
     */
    public static function get_listing_categories() {
        $categories = get_terms(array(
            'taxonomy' => 'hp_listing_category',
            'hide_empty' => false,
        ));
        
        $formatted_categories = array();
        
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $formatted_categories[$category->term_id] = $category->name;
            }
        }
        
        return $formatted_categories;
    }
}

// Initialize Products module
VDP_Products::instance();