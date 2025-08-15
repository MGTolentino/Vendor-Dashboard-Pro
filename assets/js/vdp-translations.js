/**
 * VDP Translations for JavaScript
 * Handles dynamic translations for frontend JavaScript code
 */

(function() {
    'use strict';
    
    // Detect current language from WordPress locale
    const currentLocale = document.documentElement.lang || 'en';
    const currentLang = currentLocale.startsWith('es') ? 'es' : 'en';
    
    // Translation dictionary
    const translations = {
        // General
        'Loading...': { es: 'Cargando...', en: 'Loading...' },
        'Error': { es: 'Error', en: 'Error' },
        'Success': { es: 'Éxito', en: 'Success' },
        'Warning': { es: 'Advertencia', en: 'Warning' },
        'Confirm': { es: 'Confirmar', en: 'Confirm' },
        'Cancel': { es: 'Cancelar', en: 'Cancel' },
        'Save': { es: 'Guardar', en: 'Save' },
        'Delete': { es: 'Eliminar', en: 'Delete' },
        'Edit': { es: 'Editar', en: 'Edit' },
        'View': { es: 'Ver', en: 'View' },
        'Close': { es: 'Cerrar', en: 'Close' },
        'OK': { es: 'OK', en: 'OK' },
        'Yes': { es: 'Sí', en: 'Yes' },
        'No': { es: 'No', en: 'No' },
        
        // Messages
        'Are you sure?': { es: '¿Estás seguro?', en: 'Are you sure?' },
        'Are you sure you want to delete this item?': { es: '¿Estás seguro de que quieres eliminar este elemento?', en: 'Are you sure you want to delete this item?' },
        'This action cannot be undone.': { es: 'Esta acción no se puede deshacer.', en: 'This action cannot be undone.' },
        'Item deleted successfully': { es: 'Elemento eliminado exitosamente', en: 'Item deleted successfully' },
        'Changes saved successfully': { es: 'Cambios guardados exitosamente', en: 'Changes saved successfully' },
        'An error occurred. Please try again.': { es: 'Ocurrió un error. Por favor, inténtalo de nuevo.', en: 'An error occurred. Please try again.' },
        'Please fill in all required fields.': { es: 'Por favor, completa todos los campos requeridos.', en: 'Please fill in all required fields.' },
        'Invalid email address.': { es: 'Dirección de email inválida.', en: 'Invalid email address.' },
        'Password must be at least 8 characters long.': { es: 'La contraseña debe tener al menos 8 caracteres.', en: 'Password must be at least 8 characters long.' },
        'Passwords do not match.': { es: 'Las contraseñas no coinciden.', en: 'Passwords do not match.' },
        
        // Upload and file handling
        'Choose file': { es: 'Elegir archivo', en: 'Choose file' },
        'Upload': { es: 'Subir', en: 'Upload' },
        'Uploading...': { es: 'Subiendo...', en: 'Uploading...' },
        'Upload complete': { es: 'Subida completa', en: 'Upload complete' },
        'Upload failed': { es: 'Error en la subida', en: 'Upload failed' },
        'File too large': { es: 'Archivo muy grande', en: 'File too large' },
        'Invalid file type': { es: 'Tipo de archivo inválido', en: 'Invalid file type' },
        'Maximum file size is 2MB': { es: 'El tamaño máximo de archivo es 2MB', en: 'Maximum file size is 2MB' },
        'Drag and drop files here': { es: 'Arrastra y suelta archivos aquí', en: 'Drag and drop files here' },
        'Or click to browse': { es: 'O haz clic para navegar', en: 'Or click to browse' },
        
        // Search and filter
        'Search...': { es: 'Buscar...', en: 'Search...' },
        'Filter': { es: 'Filtrar', en: 'Filter' },
        'Clear filters': { es: 'Limpiar filtros', en: 'Clear filters' },
        'No results found': { es: 'No se encontraron resultados', en: 'No results found' },
        'Showing %s of %s results': { es: 'Mostrando %s de %s resultados', en: 'Showing %s of %s results' },
        'Load more': { es: 'Cargar más', en: 'Load more' },
        
        // Time and dates
        'Today': { es: 'Hoy', en: 'Today' },
        'Yesterday': { es: 'Ayer', en: 'Yesterday' },
        'Tomorrow': { es: 'Mañana', en: 'Tomorrow' },
        'Just now': { es: 'Ahora mismo', en: 'Just now' },
        'minute ago': { es: 'hace un minuto', en: 'minute ago' },
        'minutes ago': { es: 'hace %s minutos', en: '%s minutes ago' },
        'hour ago': { es: 'hace una hora', en: 'hour ago' },
        'hours ago': { es: 'hace %s horas', en: '%s hours ago' },
        'day ago': { es: 'hace un día', en: 'day ago' },
        'days ago': { es: 'hace %s días', en: '%s days ago' },
        'week ago': { es: 'hace una semana', en: 'week ago' },
        'weeks ago': { es: 'hace %s semanas', en: '%s weeks ago' },
        'month ago': { es: 'hace un mes', en: 'month ago' },
        'months ago': { es: 'hace %s meses', en: '%s months ago' },
        'year ago': { es: 'hace un año', en: 'year ago' },
        'years ago': { es: 'hace %s años', en: '%s years ago' },
        
        // Status updates
        'Status updated successfully': { es: 'Estado actualizado exitosamente', en: 'Status updated successfully' },
        'Failed to update status': { es: 'Error al actualizar el estado', en: 'Failed to update status' },
        'Processing...': { es: 'Procesando...', en: 'Processing...' },
        'Please wait...': { es: 'Por favor espera...', en: 'Please wait...' },
        'Updating...': { es: 'Actualizando...', en: 'Updating...' },
        
        // Lead management
        'Lead status changed': { es: 'Estado del lead cambiado', en: 'Lead status changed' },
        'Note added successfully': { es: 'Nota agregada exitosamente', en: 'Note added successfully' },
        'Failed to add note': { es: 'Error al agregar nota', en: 'Failed to add note' },
        'Lead updated': { es: 'Lead actualizado', en: 'Lead updated' },
        'Select new status': { es: 'Seleccionar nuevo estado', en: 'Select new status' },
        'Add a note (optional)': { es: 'Agregar una nota (opcional)', en: 'Add a note (optional)' },
        
        // Order management
        'Order updated': { es: 'Pedido actualizado', en: 'Order updated' },
        'Order status changed': { es: 'Estado del pedido cambiado', en: 'Order status changed' },
        'Note sent to customer': { es: 'Nota enviada al cliente', en: 'Note sent to customer' },
        'Failed to send note': { es: 'Error al enviar nota', en: 'Failed to send note' },
        
        // Messages
        'Message sent': { es: 'Mensaje enviado', en: 'Message sent' },
        'Failed to send message': { es: 'Error al enviar mensaje', en: 'Failed to send message' },
        'Message marked as read': { es: 'Mensaje marcado como leído', en: 'Message marked as read' },
        'Message archived': { es: 'Mensaje archivado', en: 'Message archived' },
        'Reply sent': { es: 'Respuesta enviada', en: 'Reply sent' },
        'Type your message...': { es: 'Escribe tu mensaje...', en: 'Type your message...' },
        'Type your reply...': { es: 'Escribe tu respuesta...', en: 'Type your reply...' },
        
        // Validation messages
        'This field is required': { es: 'Este campo es requerido', en: 'This field is required' },
        'Please enter a valid email': { es: 'Por favor, ingresa un email válido', en: 'Please enter a valid email' },
        'Please enter a valid phone number': { es: 'Por favor, ingresa un número de teléfono válido', en: 'Please enter a valid phone number' },
        'Please select a valid date': { es: 'Por favor, selecciona una fecha válida', en: 'Please select a valid date' },
        'Please enter a valid URL': { es: 'Por favor, ingresa una URL válida', en: 'Please enter a valid URL' },
        
        // Notifications
        'New message received': { es: 'Nuevo mensaje recibido', en: 'New message received' },
        'New order received': { es: 'Nuevo pedido recibido', en: 'New order received' },
        'Order status updated': { es: 'Estado del pedido actualizado', en: 'Order status updated' },
        'New lead generated': { es: 'Nuevo lead generado', en: 'New lead generated' },
        'Payment received': { es: 'Pago recibido', en: 'Payment received' },
        
        // Connection status
        'Connected': { es: 'Conectado', en: 'Connected' },
        'Disconnected': { es: 'Desconectado', en: 'Disconnected' },
        'Connecting...': { es: 'Conectando...', en: 'Connecting...' },
        'Connection failed': { es: 'Error de conexión', en: 'Connection failed' },
        'Reconnecting...': { es: 'Reconectando...', en: 'Reconnecting...' },
        
        // Data operations
        'Data saved': { es: 'Datos guardados', en: 'Data saved' },
        'Failed to save data': { es: 'Error al guardar datos', en: 'Failed to save data' },
        'Data loaded': { es: 'Datos cargados', en: 'Data loaded' },
        'Failed to load data': { es: 'Error al cargar datos', en: 'Failed to load data' },
        'Refreshing...': { es: 'Actualizando...', en: 'Refreshing...' },
        'Data refreshed': { es: 'Datos actualizados', en: 'Data refreshed' },
        
        // Settings
        'Settings saved': { es: 'Configuración guardada', en: 'Settings saved' },
        'Failed to save settings': { es: 'Error al guardar configuración', en: 'Failed to save settings' },
        'Settings reset': { es: 'Configuración reiniciada', en: 'Settings reset' },
        'Are you sure you want to reset all settings?': { es: '¿Estás seguro de que quieres reiniciar toda la configuración?', en: 'Are you sure you want to reset all settings?' },
        
        // Pipeline and drag & drop
        'Item moved': { es: 'Elemento movido', en: 'Item moved' },
        'Failed to move item': { es: 'Error al mover elemento', en: 'Failed to move item' },
        'Drop here': { es: 'Soltar aquí', en: 'Drop here' },
        'Drag to reorder': { es: 'Arrastrar para reordenar', en: 'Drag to reorder' },
        
        // Calendar
        'Event created': { es: 'Evento creado', en: 'Event created' },
        'Event updated': { es: 'Evento actualizado', en: 'Event updated' },
        'Event deleted': { es: 'Evento eliminado', en: 'Event deleted' },
        'Failed to create event': { es: 'Error al crear evento', en: 'Failed to create event' },
        'Select date': { es: 'Seleccionar fecha', en: 'Select date' },
        'Select time': { es: 'Seleccionar hora', en: 'Select time' },
        
        // Export/Import
        'Export completed': { es: 'Exportación completada', en: 'Export completed' },
        'Export failed': { es: 'Error en la exportación', en: 'Export failed' },
        'Import completed': { es: 'Importación completada', en: 'Import completed' },
        'Import failed': { es: 'Error en la importación', en: 'Import failed' },
        'Preparing export...': { es: 'Preparando exportación...', en: 'Preparing export...' },
        'Processing import...': { es: 'Procesando importación...', en: 'Processing import...' },
        
        // Analytics
        'Chart updated': { es: 'Gráfico actualizado', en: 'Chart updated' },
        'Failed to load chart data': { es: 'Error al cargar datos del gráfico', en: 'Failed to load chart data' },
        'Generating report...': { es: 'Generando reporte...', en: 'Generating report...' },
        'Report generated': { es: 'Reporte generado', en: 'Report generated' },
        
        // Contact and communication
        'Contact information updated': { es: 'Información de contacto actualizada', en: 'Contact information updated' },
        'Call initiated': { es: 'Llamada iniciada', en: 'Call initiated' },
        'Email sent': { es: 'Email enviado', en: 'Email sent' },
        'Failed to send email': { es: 'Error al enviar email', en: 'Failed to send email' },
        
        // Pagination
        'Previous': { es: 'Anterior', en: 'Previous' },
        'Next': { es: 'Siguiente', en: 'Next' },
        'First': { es: 'Primero', en: 'First' },
        'Last': { es: 'Último', en: 'Last' },
        'Page': { es: 'Página', en: 'Page' },
        'of': { es: 'de', en: 'of' },
        
        // Bookings
        'Booking confirmed': { es: 'Reserva confirmada', en: 'Booking confirmed' },
        'Booking cancelled': { es: 'Reserva cancelada', en: 'Booking cancelled' },
        'Failed to confirm booking': { es: 'Error al confirmar reserva', en: 'Failed to confirm booking' },
        'Booking details updated': { es: 'Detalles de reserva actualizados', en: 'Booking details updated' },
        
        // Quick actions
        'Quick action completed': { es: 'Acción rápida completada', en: 'Quick action completed' },
        'Quick action failed': { es: 'Error en acción rápida', en: 'Quick action failed' },
        'Select an action': { es: 'Seleccionar una acción', en: 'Select an action' },
        
        // Bulk operations
        'Bulk operation completed': { es: 'Operación masiva completada', en: 'Bulk operation completed' },
        'Bulk operation failed': { es: 'Error en operación masiva', en: 'Bulk operation failed' },
        'Select items first': { es: 'Seleccionar elementos primero', en: 'Select items first' },
        'items selected': { es: 'elementos seleccionados', en: 'items selected' },
        'Select all': { es: 'Seleccionar todo', en: 'Select all' },
        'Deselect all': { es: 'Deseleccionar todo', en: 'Deselect all' },
        
        // Password strength
        'Weak': { es: 'Débil', en: 'Weak' },
        'Fair': { es: 'Regular', en: 'Fair' },
        'Good': { es: 'Buena', en: 'Good' },
        'Strong': { es: 'Fuerte', en: 'Strong' },
        'Very Strong': { es: 'Muy Fuerte', en: 'Very Strong' },
        'Password strength': { es: 'Fortaleza de contraseña', en: 'Password strength' },
        
        // Theme and appearance
        'Theme changed': { es: 'Tema cambiado', en: 'Theme changed' },
        'Dark mode enabled': { es: 'Modo oscuro activado', en: 'Dark mode enabled' },
        'Light mode enabled': { es: 'Modo claro activado', en: 'Light mode enabled' },
        
        // Offline/Online status
        'You are offline': { es: 'Estás desconectado', en: 'You are offline' },
        'You are back online': { es: 'Estás de vuelta en línea', en: 'You are back online' },
        'Connection restored': { es: 'Conexión restaurada', en: 'Connection restored' },
        
        // Auto-save
        'Auto-saved': { es: 'Guardado automáticamente', en: 'Auto-saved' },
        'Auto-save failed': { es: 'Error en guardado automático', en: 'Auto-save failed' },
        'Saving draft...': { es: 'Guardando borrador...', en: 'Saving draft...' },
        'Draft saved': { es: 'Borrador guardado', en: 'Draft saved' },
        
        // Copy to clipboard
        'Copied to clipboard': { es: 'Copiado al portapapeles', en: 'Copied to clipboard' },
        'Failed to copy': { es: 'Error al copiar', en: 'Failed to copy' },
        'Copy': { es: 'Copiar', en: 'Copy' },
        
        // Keyboard shortcuts
        'Press Ctrl+S to save': { es: 'Presiona Ctrl+S para guardar', en: 'Press Ctrl+S to save' },
        'Press Escape to close': { es: 'Presiona Escape para cerrar', en: 'Press Escape to close' },
        'Keyboard shortcuts': { es: 'Atajos de teclado', en: 'Keyboard shortcuts' },
        
        // Accessibility
        'Skip to content': { es: 'Saltar al contenido', en: 'Skip to content' },
        'Close modal': { es: 'Cerrar modal', en: 'Close modal' },
        'Open menu': { es: 'Abrir menú', en: 'Open menu' },
        'Close menu': { es: 'Cerrar menú', en: 'Close menu' }
    };
    
    /**
     * Translate a text string
     * @param {string} text - The text to translate
     * @param {object} replacements - Optional replacements for placeholders
     * @return {string} Translated text
     */
    function __(text, replacements = {}) {
        let translated = text;
        
        if (translations[text] && translations[text][currentLang]) {
            translated = translations[text][currentLang];
        }
        
        // Replace placeholders
        Object.keys(replacements).forEach(key => {
            translated = translated.replace(new RegExp('%s', 'g'), replacements[key]);
        });
        
        return translated;
    }
    
    /**
     * Get current language
     * @return {string} Current language code
     */
    function getCurrentLanguage() {
        return currentLang;
    }
    
    /**
     * Check if current language is Spanish
     * @return {boolean}
     */
    function isSpanish() {
        return currentLang === 'es';
    }
    
    /**
     * Check if current language is English
     * @return {boolean}
     */
    function isEnglish() {
        return currentLang === 'en';
    }
    
    /**
     * Add custom translation
     * @param {string} text - Original text
     * @param {string} esTranslation - Spanish translation
     * @param {string} enTranslation - English translation (optional)
     */
    function addTranslation(text, esTranslation, enTranslation = null) {
        translations[text] = {
            es: esTranslation,
            en: enTranslation || text
        };
    }
    
    // Make functions globally available
    window.VDP = window.VDP || {};
    window.VDP.__ = __;
    window.VDP.getCurrentLanguage = getCurrentLanguage;
    window.VDP.isSpanish = isSpanish;
    window.VDP.isEnglish = isEnglish;
    window.VDP.addTranslation = addTranslation;
    
    // Also make __ available globally for convenience
    window.__ = __;
    
})();