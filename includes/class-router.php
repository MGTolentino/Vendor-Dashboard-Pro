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
        
        // Debug request parameters before processing
        vdp_debug_log("Shortcode callback triggered with URL: " . $_SERVER['REQUEST_URI'], "info");
        vdp_debug_log("GET parameters: " . json_encode($_GET), "info");
        
        // Get current action and item from URL parameters
        // Importante: Siempre usar 'dashboard' como acción por defecto
        $current_action = isset($_GET['vdp-action']) ? sanitize_key($_GET['vdp-action']) : 'dashboard';
        $current_item = isset($_GET['vdp-item']) ? sanitize_key($_GET['vdp-item']) : '';
        
        // Corregir acción si viene como 'message' (singular) en lugar de 'messages' (plural)
        if ($current_action === 'message') {
            vdp_debug_log("Corrigiendo acción 'message' a 'messages' en shortcode", "info");
            $current_action = 'messages';
        }
        
        // Debugging importante - verificar que los parámetros de la URL se capturan correctamente
        vdp_debug_log("Shortcode callback capturando parámetros: action=$current_action, item=$current_item", "info");
        vdp_debug_log("URL Parameters: " . json_encode($_GET), "info");
        
        // Debug parsed parameters
        vdp_debug_log("Parsed action: '$current_action', item: '$current_item'", "info");
        
        // Store current action and item in globals for template access
        global $vdp_current_action, $vdp_current_item;
        $vdp_current_action = $current_action;
        $vdp_current_item = $current_item;
        
        // Si la URL actual es la URL base (sin vdp-action=dashboard), actualizar para evitar duplicidad
        if ($current_action === 'dashboard') {
            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $base_url = strtok($current_url, '?'); // Obtener URL sin parámetros
            
            // Si hay parámetros en la URL actual que incluyen vdp-action=dashboard
            if (strpos($current_url, 'vdp-action=dashboard') !== false) {
                vdp_debug_log("Redirigiendo de URL con parámetros a URL base");
                
                // Eliminar solo el parámetro vdp-action=dashboard, manteniendo otros parámetros si existen
                $url_parts = parse_url($current_url);
                if (isset($url_parts['query'])) {
                    parse_str($url_parts['query'], $query_params);
                    unset($query_params['vdp-action']); // Eliminar parámetro vdp-action
                    
                    // Reconstruir URL solo con los parámetros restantes
                    if (count($query_params) > 0) {
                        $base_url .= '?' . http_build_query($query_params);
                    }
                }
                
                // Si no estamos en una solicitud AJAX, redirigir a la URL base
                if (!wp_doing_ajax()) {
                    wp_redirect($base_url);
                    exit;
                }
            }
        }
        
        // Registrar para depuración
        vdp_debug_log("Acción actual establecida en router: " . $vdp_current_action);
        
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
        
        // Debug AJAX request
        vdp_debug_log("AJAX load_content called with parameters:", "info");
        vdp_debug_log("POST data: " . json_encode($_POST), "info");
        
        // Get action and item from request
        $action = isset($_POST['action']) ? sanitize_key($_POST['action']) : 'dashboard';
        if ($action === 'vdp_load_content') {
            $action = isset($_POST['section']) ? sanitize_key($_POST['section']) : 'dashboard';
        }
        
        $item = isset($_POST['item']) ? sanitize_key($_POST['item']) : '';
        
        // Additional debug for AJAX content loading
        vdp_debug_log("AJAX loading content for action: " . $action . ", item: " . $item, "info");
        
        // Special handling for message view
        if ($action === 'messages' && !empty($item)) {
            vdp_debug_log("AJAX loading message view for message ID: " . $item, "info");
        }
        
        // Corregir acción si viene como 'message' (singular) en lugar de 'messages' (plural)
        if ($action === 'message') {
            vdp_debug_log("Corrigiendo acción 'message' a 'messages'", "info");
            $action = 'messages';
        }
        
        // Set globals for template access
        global $vdp_current_action, $vdp_current_item;
        $vdp_current_action = $action;
        $vdp_current_item = $item;
        
        // Start output buffering
        ob_start();
        
        // Render SOLO el contenido específico, no la estructura completa
        self::render_content($action, $item);
        
        // Get buffered content
        $content = ob_get_clean();
        
        // Verificar si el contenido es demasiado grande (posible inclusión de estructura completa)
        if (strlen($content) > 50000) { // Umbral arbitrario para detectar contenido excesivo
            vdp_debug_log("Respuesta AJAX muy grande (" . strlen($content) . " bytes) para acción: " . $action, "warning");
        }
        
        // Send response con información adicional para depuración
        wp_send_json_success(array(
            'content' => $content,
            'title' => self::get_page_title($action, $item),
            'action' => $action,
            'debug_info' => array(
                'content_length' => strlen($content),
                'action_loaded' => $action,
                'item_loaded' => $item
            )
        ));
    }
    
    /**
     * Render dashboard content based on action and item
     *
     * @param string $action Current action.
     * @param string $item Current item ID.
     */
    public static function render_content($action, $item) {
        // VERIFICACIÓN CRÍTICA: Si estamos en una vista de mensaje con item, saltarnos toda la lógica
        // compleja y cargar directamente la plantilla de vista de mensaje
        if ($action === 'messages' && !empty($item)) {
            vdp_debug_log("SOLUCIÓN DIRECTA: Detectado acción 'messages' con item ID: " . $item, "info");
            
            // Obtener datos del vendedor y mensaje
            $vendor = vdp_get_current_vendor();
            $vendor_id = null;
            
            if ($vendor) {
                if (is_object($vendor) && method_exists($vendor, 'get_id')) {
                    $vendor_id = $vendor->get_id();
                } elseif (is_object($vendor) && isset($vendor->get_id) && is_callable($vendor->get_id)) {
                    $vendor_id = ($vendor->get_id)();
                }
            }
            
            // Obtener el mensaje - primero intentar real, luego demo
            if (class_exists('VDP_Messages')) {
                vdp_debug_log("Obteniendo datos del mensaje ID: " . $item, "info");
                
                if (method_exists('VDP_Messages', 'are_tables_created') && VDP_Messages::are_tables_created()) {
                    $message = VDP_Messages::get_message($item, $vendor_id);
                    
                    if (!$message) {
                        vdp_debug_log("Mensaje real no encontrado, usando mensaje demo", "info");
                        $message = VDP_Messages::get_demo_message($item);
                    }
                } else {
                    $message = VDP_Messages::get_demo_message($item);
                }
            } else {
                // Mensaje ficticio
                vdp_debug_log("Clase VDP_Messages no encontrada, creando mensaje ficticio", "warning");
                $message = array(
                    'id' => $item,
                    'subject' => 'Mensaje de ejemplo',
                    'content' => 'Este es un mensaje de ejemplo porque no se pudo encontrar el mensaje real.',
                    'date' => date('Y-m-d H:i:s'),
                    'is_read' => false,
                    'sender_name' => 'Usuario de prueba',
                    'sender_id' => 1,
                    'sender_avatar' => '',
                    'product_title' => 'Producto de prueba',
                    'product_url' => '#',
                    'replies' => array(),
                    'interactions' => array(),
                );
            }
            
            // FORZAR INCLUIR LA PLANTILLA DE VISTA DE MENSAJE
            vdp_debug_log("Forzando carga de plantilla message-view-content.php", "info");
            include(VDP_PLUGIN_DIR . 'templates/message-view-content.php');
            return;
        }
        
        // Variable para controlar si ya se ha renderizado contenido
        $content_rendered = false;
        
        // Registrar para depuración
        vdp_debug_log("Renderizando contenido para acción: " . $action . ", item: " . $item);
        
        // Log detailed request information
        vdp_debug_log("URL Parameters: " . json_encode($_GET), "info");
        vdp_debug_log("Request URI: " . $_SERVER['REQUEST_URI'], "info");
        
        // Crear un nombre de acción basado en el módulo
        $action_hook = 'vdp_' . $action . '_content';
        
        // Si es una vista de detalle, agregar sufijo
        if ($item && $action != 'dashboard' && $action != 'messages') { // Excluir messages de esta lógica
            // Ejecutar hook específico para vista de detalle
            $detail_hook = 'vdp_' . $action . '_view_content';
            
            vdp_debug_log("Attempting to use detail hook: " . $detail_hook, "info");
            
            // Primero verificar si alguien está escuchando este hook
            if (has_action($detail_hook)) {
                vdp_debug_log("Detail hook found, executing: " . $detail_hook, "info");
                do_action($detail_hook, $item);
                $content_rendered = true;
                return; // Salir después de renderizar
            } else {
                vdp_debug_log("No listeners found for detail hook: " . $detail_hook, "warning");
            }
        }
        
        // Verificar si hay manejadores para este hook
        if (has_action($action_hook) && !$content_rendered) {
            vdp_debug_log("Action hook found, executing: " . $action_hook, "info");
            // Ejecutar la acción que renderizará el contenido
            do_action($action_hook, $item);
            $content_rendered = true;
            return; // Salir después de renderizar
        } else if (!$content_rendered) {
            vdp_debug_log("No listeners found for action hook: " . $action_hook, "warning");
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
                    // Este caso ahora es sólo para la lista de mensajes,
                    // la vista individual se maneja al inicio de la función render_content
                    vdp_debug_log("Including messages-content.php (list view) - Caso del switch", "info");
                    include(VDP_PLUGIN_DIR . 'templates/messages-content.php');
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