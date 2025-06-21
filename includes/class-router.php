<?php
/**
 * Router Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Router class for handling shortcode and dashboard display.
 */
class VDP_Router {
    /**
     * Initialize router.
     */
    public static function init() {
        // Handle AJAX requests
        add_action('wp_ajax_vdp_load_content', array(__CLASS__, 'ajax_load_content'));
    }

    /**
     * Shortcode callback
     *
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public static function shortcode_callback($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'default_action' => 'dashboard',
        ), $atts, 'vendor_dashboard_pro');
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return self::get_login_form();
        }
        
        // Check if user is a vendor
        if (!vdp_is_user_vendor()) {
            return self::get_not_vendor_message();
        }
        
        // Get current action and item from URL parameters
        // Importante: Siempre usar 'dashboard' como acción por defecto
        $current_action = isset($_GET['vdp-action']) ? sanitize_key($_GET['vdp-action']) : 'dashboard';
        $current_item = isset($_GET['vdp-item']) ? sanitize_key($_GET['vdp-item']) : '';
        
        // Store current action and item in globals for template access
        global $vdp_current_action, $vdp_current_item;
        $vdp_current_action = $current_action;
        $vdp_current_item = $current_item;
        
        // Registrar para depuración
        error_log("VDP Debug: Acción actual establecida en router: " . $vdp_current_action);
        
        // Check if this is an AJAX request
        $is_ajax = isset($_GET['vdp_ajax']) && $_GET['vdp_ajax'] == 1;
        
        // Start output buffering
        ob_start();
        
        // Include dashboard template
        if ($is_ajax) {
            // For AJAX requests, only include the content part
            self::render_content($current_action, $current_item);
        } else {
            // For regular page loads, include the full dashboard template
            include(VDP_PLUGIN_DIR . 'templates/dashboard.php');
        }
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for loading dashboard content
     */
    public static function ajax_load_content() {
        // Check for nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vdp-ajax-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to access this content.', 'vendor-dashboard-pro')));
        }
        
        // Check if user is a vendor
        if (!vdp_is_user_vendor()) {
            wp_send_json_error(array('message' => __('You must be a vendor to access this content.', 'vendor-dashboard-pro')));
        }
        
        // Get action and item from request
        $action = isset($_POST['action']) ? sanitize_key($_POST['action']) : 'dashboard';
        if ($action === 'vdp_load_content') {
            $action = 'dashboard'; // Default action if only the AJAX action name is provided
        }
        
        $item = isset($_POST['item']) ? sanitize_key($_POST['item']) : '';
        
        // Set globals for template access
        global $vdp_current_action, $vdp_current_item;
        $vdp_current_action = $action;
        $vdp_current_item = $item;
        
        // Start output buffering
        ob_start();
        
        // Render content
        self::render_content($action, $item);
        
        // Get buffered content
        $content = ob_get_clean();
        
        // Send response
        wp_send_json_success(array(
            'content' => $content,
            'title' => self::get_page_title($action, $item),
        ));
    }
    
    /**
     * Render dashboard content based on action and item
     *
     * @param string $action Current action.
     * @param string $item Current item ID.
     */
    public static function render_content($action, $item) {
        // Variable para controlar si ya se ha renderizado contenido
        $content_rendered = false;
        
        // Registrar para depuración
        error_log("VDP Debug: Renderizando contenido para acción: " . $action);
        
        // Crear un nombre de acción basado en el módulo
        $action_hook = 'vdp_' . $action . '_content';
        
        // Si es una vista de detalle, agregar sufijo
        if ($item && $action != 'dashboard') {
            // Ejecutar hook específico para vista de detalle
            $detail_hook = 'vdp_' . $action . '_view_content';
            
            // Primero verificar si alguien está escuchando este hook
            if (has_action($detail_hook)) {
                do_action($detail_hook, $item);
                $content_rendered = true;
                return; // Salir después de renderizar
            }
        }
        
        // Verificar si hay manejadores para este hook
        if (has_action($action_hook) && !$content_rendered) {
            // Ejecutar la acción que renderizará el contenido
            do_action($action_hook, $item);
            $content_rendered = true;
            return; // Salir después de renderizar
        }
        
        // Fallback al sistema de include de templates si no hay hooks y aún no se ha renderizado contenido
        if (!$content_rendered) {
            switch ($action) {
                case 'products':
                    if ($item === 'add' || $item === 'edit') {
                        include(VDP_PLUGIN_DIR . 'templates/products-edit-content.php');
                    } else {
                        // Crear una instancia de Products y llamar directamente al método
                        if (class_exists('VDP_Products')) {
                            $products = VDP_Products::instance();
                            $products->render_products_list();
                        } else {
                            include(VDP_PLUGIN_DIR . 'templates/products-content.php');
                        }
                    }
                    break;
                    
                case 'leads':
                    include(VDP_PLUGIN_DIR . 'templates/leads-content.php');
                    break;
                    
                case 'orders':
                    if ($item) {
                        include(VDP_PLUGIN_DIR . 'templates/order-view-content.php');
                    } else {
                        include(VDP_PLUGIN_DIR . 'templates/orders-content.php');
                    }
                    break;
                    
                case 'messages':
                    if ($item) {
                        include(VDP_PLUGIN_DIR . 'templates/message-view-content.php');
                    } else {
                        include(VDP_PLUGIN_DIR . 'templates/messages-content.php');
                    }
                    break;
                    
                case 'analytics':
                    include(VDP_PLUGIN_DIR . 'templates/analytics-content.php');
                    break;
                    
                case 'settings':
                    include(VDP_PLUGIN_DIR . 'templates/settings-content.php');
                    break;
                    
                case 'dashboard':
                default:
                    include(VDP_PLUGIN_DIR . 'templates/dashboard-content.php');
                    break;
            }
        }
    }
    
    /**
     * Get login form HTML
     *
     * @return string Login form HTML.
     */
    public static function get_login_form() {
        ob_start();
        ?>
        <div class="vdp-login-required">
            <h3><?php esc_html_e('Login Required', 'vendor-dashboard-pro'); ?></h3>
            <p><?php esc_html_e('You must be logged in to access the vendor dashboard.', 'vendor-dashboard-pro'); ?></p>
            <?php wp_login_form(array('redirect' => get_permalink())); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get not vendor message HTML
     *
     * @return string Not vendor message HTML.
     */
    public static function get_not_vendor_message() {
        ob_start();
        ?>
        <div class="vdp-not-vendor">
            <h3><?php esc_html_e('Vendor Access Required', 'vendor-dashboard-pro'); ?></h3>
            <p><?php esc_html_e('You must be registered as a vendor to access this dashboard.', 'vendor-dashboard-pro'); ?></p>
            <?php if (function_exists('hivepress') && method_exists(hivepress()->router, 'get_url')): ?>
                <?php $register_url = hivepress()->router->get_url('vendor_register_page'); ?>
                <?php if ($register_url): ?>
                    <a href="<?php echo esc_url($register_url); ?>" class="vdp-btn vdp-btn-primary">
                        <?php esc_html_e('Register as Vendor', 'vendor-dashboard-pro'); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get page title based on action and item
     *
     * @param string $action Current action.
     * @param string $item Current item ID.
     * @return string Page title.
     */
    public static function get_page_title($action, $item = '') {
        $titles = array(
            'dashboard' => __('Dashboard', 'vendor-dashboard-pro'),
            'products' => __('Listings', 'vendor-dashboard-pro'),
            'orders' => __('Orders', 'vendor-dashboard-pro'),
            'leads' => __('Leads', 'vendor-dashboard-pro'),
            'messages' => __('Messages', 'vendor-dashboard-pro'),
            'analytics' => __('Analytics', 'vendor-dashboard-pro'),
            'settings' => __('Settings', 'vendor-dashboard-pro'),
        );
        
        // Special cases for add/edit/view actions
        if ($action === 'products' && $item === 'add') {
            return __('Add New Listing', 'vendor-dashboard-pro');
        } elseif ($action === 'products' && $item === 'edit') {
            return __('Edit Listing', 'vendor-dashboard-pro');
        } elseif ($action === 'orders' && $item) {
            return __('Order Details', 'vendor-dashboard-pro');
        } elseif ($action === 'messages' && $item) {
            return __('Message Details', 'vendor-dashboard-pro');
        }
        
        // Default to action title or dashboard
        return isset($titles[$action]) ? $titles[$action] : $titles['dashboard'];
    }
}