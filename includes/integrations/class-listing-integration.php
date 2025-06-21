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
            // Usar una sola solución directa: reemplazar el botón con JavaScript
            add_action('wp_footer', array(__CLASS__, 'replace_listing_buttons'), 999);
        }
    }
    
    /**
     * Reemplaza los botones del listing directamente manipulando el DOM con JavaScript.
     * Enfoque simplificado que funciona con todos los temas.
     */
    public static function replace_listing_buttons() {
        if (!vdp_is_active()) {
            return;
        }
        
        // Solo ejecutar el script si estamos en una página de listing
        if (!is_singular('hp_listing')) {
            return;
        }
        
        // Obtener datos del listing y vendor
        $listing = hivepress()->request->get_context('listing');
        $vendor = $listing ? $listing->get_vendor() : null;
        
        if (!$listing || !$vendor) {
            return;
        }
        
        // Datos del listing para JavaScript
        $listing_data = array(
            'id' => $listing->get_id(),
            'title' => $listing->get_title(),
            'vendor_id' => $vendor->get_id(),
            'vendor_name' => $vendor->get_name()
        );
        
        // Generar HTML para el botón
        $button_html = '';
        if (is_user_logged_in()) {
            $button_html = '<button type="button" class="hp-vendor__action hp-vendor__action--message button button--large button--primary alt vdp-contact-button" data-listing-id="' . esc_attr($listing->get_id()) . '" data-vendor-id="' . esc_attr($vendor->get_id()) . '" data-vendor-name="' . esc_attr($vendor->get_name()) . '" data-listing-title="' . esc_attr($listing->get_title()) . '"><i class="fas fa-envelope"></i> ' . esc_html__('Contact Vendor', 'vendor-dashboard-pro') . '</button>';
        } else {
            $button_html = '<a href="#user_login_modal" class="hp-vendor__action hp-vendor__action--message button button--large button--primary alt"><i class="fas fa-envelope"></i> ' . esc_html__('Contact Vendor', 'vendor-dashboard-pro') . '</a>';
        }
        
        // Escapar para uso en JavaScript
        $button_html = str_replace("'", "\'", $button_html);
        ?>
        <script>
        // Ejecutar después de que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.vdpContactButtonReplaced) {
                // Buscar todos los botones de contacto existentes en la página
                var contactButtons = document.querySelectorAll('.hp-vendor__action--message');
                
                if (contactButtons.length > 0) {
                    // Reemplazar todos los botones encontrados
                    contactButtons.forEach(function(button) {
                        var newButton = document.createElement('div');
                        newButton.innerHTML = '<?php echo $button_html; ?>';
                        button.parentNode.replaceChild(newButton.firstChild, button);
                    });
                    window.vdpContactButtonReplaced = true;
                }
            }
        });
        </script>
        <?php
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