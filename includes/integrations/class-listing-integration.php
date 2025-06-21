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
            // Usamos prioridad alta para ejecutar nuestro código antes que HivePress
            add_action('wp_head', array(__CLASS__, 'replace_listing_buttons'), 5);
            
            // Eliminar botón de mensaje original si existe la acción
            if (has_action('hivepress/v1/templates/listing_view_page/vendor_actions', ['HivePress\Controllers\Message', 'render_send_message_modal'])) {
                remove_action('hivepress/v1/templates/listing_view_page/vendor_actions', ['HivePress\Controllers\Message', 'render_send_message_modal'], 10);
            }
            
            // Añadir nuestro filtro para el contenido completo de la página
            add_filter('the_content', array(__CLASS__, 'modify_listing_content'), 999);
            
            // Añadir nuestro botón en el hook de acciones del vendedor
            add_action('hivepress/v1/templates/listing_view_page/vendor_actions', array(__CLASS__, 'replace_contact_button'), 10);
            
            // Añadir nuestro botón en la plantilla del tema hijo de Kava
            add_filter('hivepress/v1/templates/listing_view_page/listing-details-section', array(__CLASS__, 'modify_listing_details'));
        }
    }
    
    /**
     * Reemplaza los botones del listing directamente manipulando el DOM con JavaScript.
     * Este es un enfoque alternativo que funciona en muchos temas, incluido Kava.
     */
    public static function replace_listing_buttons() {
        if (!vdp_is_active()) {
            return;
        }
        
        // Solo ejecutar el script si estamos en una página de listing
        if (!is_singular('hp_listing')) {
            return;
        }
        ?>
        <script>
        // Ejecutar después de que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Buscar todos los botones de contacto existentes en la página
            var contactButtons = document.querySelectorAll('.hp-vendor__action--message');
            
            // Si encontramos botones, los reemplazamos
            if (contactButtons.length > 0) {
                // Obtener los datos del listing y vendedor
                var listing = <?php 
                    $listing = hivepress()->request->get_context('listing');
                    $vendor = $listing ? $listing->get_vendor() : null;
                    
                    if ($listing && $vendor) {
                        echo json_encode(array(
                            'id' => $listing->get_id(),
                            'title' => $listing->get_title(),
                            'vendor_id' => $vendor->get_id(),
                            'vendor_name' => $vendor->get_name()
                        ));
                    } else {
                        echo 'null';
                    }
                ?>;
                
                if (listing) {
                    contactButtons.forEach(function(button) {
                        // Si el usuario está logueado, crear botón interactivo
                        if (<?php echo is_user_logged_in() ? 'true' : 'false'; ?>) {
                            // Crear el nuevo botón
                            button.classList.add('vdp-contact-button');
                            button.setAttribute('data-listing-id', listing.id);
                            button.setAttribute('data-vendor-id', listing.vendor_id);
                            button.setAttribute('data-vendor-name', listing.vendor_name);
                            button.setAttribute('data-listing-title', listing.title);
                        }
                        
                        // Asegurarse de que tiene el icono de sobre
                        if (button.querySelector('i') === null) {
                            var icon = document.createElement('i');
                            icon.className = 'fas fa-envelope';
                            button.insertBefore(icon, button.firstChild);
                        }
                        
                        // Asegurarse de que tiene el texto "Contact Vendor"
                        var text = button.textContent.trim();
                        if (!text) {
                            button.appendChild(document.createTextNode(' <?php esc_html_e('Contact Vendor', 'vendor-dashboard-pro'); ?>'));
                        }
                    });
                }
            }
        });
        </script>
        <?php
    }
    
    /**
     * Método para reemplazar el botón de contacto en vendor_actions
     */
    public static function replace_contact_button() {
        // Comprobar si VDP está activo
        if (!vdp_is_active()) {
            return;
        }
        
        // Obtener el listing y vendor
        $listing = hivepress()->request->get_context('listing');
        $vendor = null;
        
        if ($listing && method_exists($listing, 'get_vendor')) {
            $vendor = $listing->get_vendor();
        }
        
        if (!$vendor) {
            return; 
        }
        
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