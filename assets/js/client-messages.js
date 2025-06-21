/**
 * Client Messages JavaScript
 *
 * @package Vendor Dashboard Pro
 */

(function($) {
    'use strict';
    
    // Variables
    var messagesContainer = $('.vdp-client-messages-container');
    var messageView = $('.vdp-client-message-view');
    
    // Initialize
    function init() {
        // Si estamos en la vista de mensajes
        if (messagesContainer.length > 0) {
            // No hay que hacer nada especial, todo se carga server-side
        }
        
        // Si estamos en la vista de un mensaje
        if (messageView.length > 0) {
            // Ya manejado en el template con JavaScript embebido
        }
    }
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(init);
    
})(jQuery);