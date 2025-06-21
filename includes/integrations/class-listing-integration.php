<?php
/**
 * Listing Integration Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para integrar VDP con listados de HivePress.
 */
class VDP_Listing_Integration {
    /**
     * Inicializar integración.
     */
    public static function init() {
        // Verificar si estamos en WordPress admin
        if (is_admin()) {
            return;
        }
        
        // Agregar acción para la página de listing
        add_action('wp', array(__CLASS__, 'setup_listing_integration'));
        
        // Registrar scripts para el modal de contacto
        add_action('wp_enqueue_scripts', array(__CLASS__, 'register_scripts'));
    }
    
    /**
     * Configurar integración en páginas de listings.
     */
    public static function setup_listing_integration() {
        // Si estamos en una página de listing individual
        if (is_singular('hp_listing')) {
            // Eliminar botón de mensaje original (se ejecuta antes)
            // Primero eliminamos la acción del formulario modal
            if (has_action('hivepress/v1/templates/listing_view_page/vendor_actions', ['HivePress\Controllers\Message', 'render_send_message_modal'])) {
                remove_action('hivepress/v1/templates/listing_view_page/vendor_actions', ['HivePress\Controllers\Message', 'render_send_message_modal'], 10);
            }
            
            // Luego buscamos en la prioridad específica del Kava Child theme (si existe)
            if (function_exists('remove_all_actions')) {
                // Eliminar las acciones en el punto de 'vendor_actions'
                remove_all_actions('hivepress/v1/templates/listing_view_page/vendor_actions');
            }
            
            // Añadir nuestro botón en la plantilla del tema hijo de Kava
            add_filter('hivepress/v1/templates/listing_view_page/listing-details-section', array(__CLASS__, 'modify_listing_details'));
        }
    }
    
    /**
     * Modificar la sección de detalles del listing para añadir nuestro botón.
     *
     * @param string $content Contenido del template.
     * @return string Contenido modificado.
     */
    public static function modify_listing_details($content) {
        // Comprobar si VDP está activo
        if (!vdp_is_active()) {
            return $content; // Devolver contenido original si VDP no está activo
        }
        
        // Patrón para buscar el botón original y reemplazarlo por nuestro botón
        $pattern_logged_in = '/<button type="button"[^>]*class="hp-vendor__action hp-vendor__action--message[^>]*>(.*?)<\/button>/s';
        $pattern_not_logged_in = '/<a href="#user_login_modal"[^>]*class="hp-vendor__action hp-vendor__action--message[^>]*>(.*?)<\/a>/s';
        
        // Obtener el listing y vendor
        $listing = hivepress()->request->get_context('listing');
        $vendor = null;
        
        if ($listing && method_exists($listing, 'get_vendor')) {
            $vendor = $listing->get_vendor();
        }
        
        if (!$vendor) {
            return $content; // No hay vendor, devolver contenido original
        }
        
        ob_start();
        
        if (is_user_logged_in()) {
            // Botón para usuarios logueados
            ?>
            <button type="button" 
                    class="hp-vendor__action hp-vendor__action--message button button--large button--primary alt vdp-contact-button"
                    data-listing-id="<?php echo esc_attr($listing->get_id()); ?>"
                    data-vendor-id="<?php echo esc_attr($vendor->get_id()); ?>"
                    data-vendor-name="<?php echo esc_attr($vendor->get_name()); ?>"
                    data-listing-title="<?php echo esc_attr($listing->get_title()); ?>">
                <i class="fas fa-envelope"></i> <?php esc_html_e('Contact Vendor', 'vendor-dashboard-pro'); ?>
            </button>
            <?php
        } else {
            // Botón para usuarios no logueados
            ?>
            <a href="#user_login_modal" 
               class="hp-vendor__action hp-vendor__action--message button button--large button--primary alt">
                <i class="fas fa-envelope"></i> <?php esc_html_e('Contact Vendor', 'vendor-dashboard-pro'); ?>
            </a>
            <?php
        }
        
        $button = ob_get_clean();
        
        // Reemplazar el botón en el contenido
        $new_content = preg_replace($pattern_logged_in, $button, $content);
        
        // Si el patrón anterior no coincidió, intentar con el patrón para usuarios no logueados
        if ($new_content === $content) {
            $new_content = preg_replace($pattern_not_logged_in, $button, $content);
        }
        
        // Si no se encuentra ninguno de los patrones, devolvemos el contenido original
        return $new_content !== null ? $new_content : $content;
    }
    
    /**
     * Registrar scripts para el modal de contacto.
     */
    public static function register_scripts() {
        if (!is_singular('hp_listing') || !vdp_is_active()) {
            return;
        }
        
        // Registrar y encolar script para el modal
        wp_register_script(
            'vdp-contact-modal',
            VDP_PLUGIN_URL . 'assets/js/contact-modal.js',
            array('jquery'),
            VDP_VERSION,
            true
        );
        
        // Pasar datos al script
        wp_localize_script(
            'vdp-contact-modal',
            'vdp_contact_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('vdp-contact-nonce'),
                'texts' => array(
                    'send_message' => __('Send Message', 'vendor-dashboard-pro'),
                    'message_sent' => __('Your message has been sent!', 'vendor-dashboard-pro'),
                    'error' => __('An error occurred. Please try again.', 'vendor-dashboard-pro'),
                ),
            )
        );
        
        wp_enqueue_script('vdp-contact-modal');
        
        // Añadir el modal al footer
        add_action('wp_footer', array(__CLASS__, 'render_contact_modal'));
    }
    
    /**
     * Renderizar modal de contacto en el footer.
     */
    public static function render_contact_modal() {
        // Incluir el template del modal
        include VDP_PLUGIN_DIR . 'templates/contact-modal.php';
    }
}

// Inicializar integración
VDP_Listing_Integration::init();