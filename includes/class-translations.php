<?php
/**
 * Dynamic Translations Handler
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VDP_Translations class.
 */
class VDP_Translations {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Translations cache
     */
    private $translations = array();
    
    /**
     * Current language
     */
    private $current_lang = 'en';
    
    /**
     * Get instance
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize translations
     */
    private function init() {
        // Detect language
        $locale = get_locale();
        $this->current_lang = strpos($locale, 'es') === 0 ? 'es' : 'en';
        
        // Hook into WordPress translation system
        add_filter('gettext', array($this, 'translate_text'), 20, 3);
        add_filter('ngettext', array($this, 'translate_text_plural'), 20, 5);
        
        // Load translations
        $this->load_translations();
    }
    
    /**
     * Load translations from array or JSON
     */
    private function load_translations() {
        // Comprehensive translations for all plugin texts
        $this->translations = array(
            // General Navigation
            'Dashboard' => array('es' => 'Panel de Control', 'en' => 'Dashboard'),
            'Orders' => array('es' => 'Pedidos', 'en' => 'Orders'),
            'Products' => array('es' => 'Productos', 'en' => 'Products'),
            'Messages' => array('es' => 'Mensajes', 'en' => 'Messages'),
            'Leads' => array('es' => 'Leads', 'en' => 'Leads'),
            'Calendar' => array('es' => 'Calendario', 'en' => 'Calendar'),
            'Analytics' => array('es' => 'Análisis', 'en' => 'Analytics'),
            'Settings' => array('es' => 'Configuración', 'en' => 'Settings'),
            'Bookings' => array('es' => 'Reservas', 'en' => 'Bookings'),
            'Listings' => array('es' => 'Anuncios', 'en' => 'Listings'),
            
            // Dashboard Stats
            'Total Revenue' => array('es' => 'Ingresos Totales', 'en' => 'Total Revenue'),
            'Active Orders' => array('es' => 'Pedidos Activos', 'en' => 'Active Orders'),
            'Total Products' => array('es' => 'Total de Productos', 'en' => 'Total Products'),
            'Customer Messages' => array('es' => 'Mensajes de Clientes', 'en' => 'Customer Messages'),
            'Total Orders' => array('es' => 'Total de Órdenes', 'en' => 'Total Orders'),
            'Total Leads' => array('es' => 'Total de Leads', 'en' => 'Total Leads'),
            'Total Messages' => array('es' => 'Total de Mensajes', 'en' => 'Total Messages'),
            'Sales' => array('es' => 'Ventas', 'en' => 'Sales'),
            'Views' => array('es' => 'Vistas', 'en' => 'Views'),
            'Conversion Rate' => array('es' => 'Tasa de Conversión', 'en' => 'Conversion Rate'),
            'Average Performance' => array('es' => 'Rendimiento Promedio', 'en' => 'Average Performance'),
            
            // Order Status
            'All Orders' => array('es' => 'Todos los Pedidos', 'en' => 'All Orders'),
            'Pending' => array('es' => 'Pendiente', 'en' => 'Pending'),
            'Processing' => array('es' => 'Procesando', 'en' => 'Processing'),
            'Completed' => array('es' => 'Completado', 'en' => 'Completed'),
            'Cancelled' => array('es' => 'Cancelado', 'en' => 'Cancelled'),
            'Refunded' => array('es' => 'Reembolsado', 'en' => 'Refunded'),
            'On Hold' => array('es' => 'En Espera', 'en' => 'On Hold'),
            'Draft' => array('es' => 'Borrador', 'en' => 'Draft'),
            'Published' => array('es' => 'Publicado', 'en' => 'Published'),
            
            // Lead Status
            'Pendientes' => array('es' => 'Pendientes', 'en' => 'Pending'),
            'Contactados' => array('es' => 'Contactados', 'en' => 'Contacted'),
            'Negociación' => array('es' => 'Negociación', 'en' => 'Negotiation'),
            'Propuesta Enviada' => array('es' => 'Propuesta Enviada', 'en' => 'Proposal Sent'),
            'Cerrado Ganado' => array('es' => 'Cerrado Ganado', 'en' => 'Closed Won'),
            'Cerrado Perdido' => array('es' => 'Cerrado Perdido', 'en' => 'Closed Lost'),
            'Cita Agendada' => array('es' => 'Cita Agendada', 'en' => 'Appointment Scheduled'),
            'Confirmada' => array('es' => 'Confirmada', 'en' => 'Confirmed'),
            'Cancelada' => array('es' => 'Cancelada', 'en' => 'Cancelled'),
            
            // Product Management
            'Add New Product' => array('es' => 'Agregar Nuevo Producto', 'en' => 'Add New Product'),
            'Edit Product' => array('es' => 'Editar Producto', 'en' => 'Edit Product'),
            'Product Name' => array('es' => 'Nombre del Producto', 'en' => 'Product Name'),
            'Price' => array('es' => 'Precio', 'en' => 'Price'),
            'Stock' => array('es' => 'Inventario', 'en' => 'Stock'),
            'Status' => array('es' => 'Estado', 'en' => 'Status'),
            'SKU:' => array('es' => 'SKU:', 'en' => 'SKU:'),
            'Quantity' => array('es' => 'Cantidad', 'en' => 'Quantity'),
            'Description' => array('es' => 'Descripción', 'en' => 'Description'),
            'Images' => array('es' => 'Imágenes', 'en' => 'Images'),
            'Main Image' => array('es' => 'Imagen Principal', 'en' => 'Main Image'),
            'Product' => array('es' => 'Producto', 'en' => 'Product'),
            'Producto' => array('es' => 'Producto', 'en' => 'Product'),
            'Out of Stock' => array('es' => 'Sin Stock', 'en' => 'Out of Stock'),
            'Item Available' => array('es' => 'Artículo Disponible', 'en' => 'Item Available'),
            'Free' => array('es' => 'Gratis', 'en' => 'Free'),
            
            // Messages
            'Inbox' => array('es' => 'Bandeja de Entrada', 'en' => 'Inbox'),
            'Sent' => array('es' => 'Enviados', 'en' => 'Sent'),
            'New Message' => array('es' => 'Nuevo Mensaje', 'en' => 'New Message'),
            'Reply' => array('es' => 'Responder', 'en' => 'Reply'),
            'Message' => array('es' => 'Mensaje', 'en' => 'Message'),
            'Message Details' => array('es' => 'Detalles del Mensaje', 'en' => 'Message Details'),
            'Send Message' => array('es' => 'Enviar Mensaje', 'en' => 'Send Message'),
            'Send Reply' => array('es' => 'Enviar Respuesta', 'en' => 'Send Reply'),
            'Mark as Read' => array('es' => 'Marcar como Leído', 'en' => 'Mark as Read'),
            'Mark Completed' => array('es' => 'Marcar Completado', 'en' => 'Mark Completed'),
            'Marked as Read' => array('es' => 'Marcado como Leído', 'en' => 'Marked as Read'),
            'All Messages' => array('es' => 'Todos los Mensajes', 'en' => 'All Messages'),
            'My Messages' => array('es' => 'Mis Mensajes', 'en' => 'My Messages'),
            'Unread' => array('es' => 'No Leído', 'en' => 'Unread'),
            'Read' => array('es' => 'Leído', 'en' => 'Read'),
            'Archive' => array('es' => 'Archivar', 'en' => 'Archive'),
            'Back to Messages' => array('es' => 'Volver a Mensajes', 'en' => 'Back to Messages'),
            'Search messages...' => array('es' => 'Buscar mensajes...', 'en' => 'Search messages...'),
            'New Messages' => array('es' => 'Nuevos Mensajes', 'en' => 'New Messages'),
            'Unread message' => array('es' => 'Mensaje no leído', 'en' => 'Unread message'),
            'Type your reply here...' => array('es' => 'Escribe tu respuesta aquí...', 'en' => 'Type your reply here...'),
            'Reply to this message' => array('es' => 'Responder a este mensaje', 'en' => 'Reply to this message'),
            'Reply to this conversation' => array('es' => 'Responder a esta conversación', 'en' => 'Reply to this conversation'),
            
            // Settings
            'Store Information' => array('es' => 'Información de la Tienda', 'en' => 'Store Information'),
            'Store Name' => array('es' => 'Nombre de la Tienda', 'en' => 'Store Name'),
            'Store Email' => array('es' => 'Email de la Tienda', 'en' => 'Store Email'),
            'Store Phone' => array('es' => 'Teléfono de la Tienda', 'en' => 'Store Phone'),
            'Store Description' => array('es' => 'Descripción de la Tienda', 'en' => 'Store Description'),
            'Store URL' => array('es' => 'URL de la Tienda', 'en' => 'Store URL'),
            'Store Banner' => array('es' => 'Banner de la Tienda', 'en' => 'Store Banner'),
            'Store Logo' => array('es' => 'Logo de la Tienda', 'en' => 'Store Logo'),
            'Store Logo & Banner' => array('es' => 'Logo y Banner de la Tienda', 'en' => 'Store Logo & Banner'),
            'Account' => array('es' => 'Cuenta', 'en' => 'Account'),
            'Account Information' => array('es' => 'Información de la Cuenta', 'en' => 'Account Information'),
            'First Name' => array('es' => 'Nombre', 'en' => 'First Name'),
            'Last Name' => array('es' => 'Apellido', 'en' => 'Last Name'),
            'Email Address' => array('es' => 'Dirección de Email', 'en' => 'Email Address'),
            'Change Password' => array('es' => 'Cambiar Contraseña', 'en' => 'Change Password'),
            'Current Password' => array('es' => 'Contraseña Actual', 'en' => 'Current Password'),
            'New Password' => array('es' => 'Nueva Contraseña', 'en' => 'New Password'),
            'Confirm New Password' => array('es' => 'Confirmar Nueva Contraseña', 'en' => 'Confirm New Password'),
            'Basic Information' => array('es' => 'Información Básica', 'en' => 'Basic Information'),
            'Basic information about your store that will be visible to customers' => array('es' => 'Información básica sobre tu tienda que será visible para los clientes', 'en' => 'Basic information about your store that will be visible to customers'),
            
            // Notifications
            'Notifications' => array('es' => 'Notificaciones', 'en' => 'Notifications'),
            'Email Notifications' => array('es' => 'Notificaciones por Email', 'en' => 'Email Notifications'),
            'Dashboard Notifications' => array('es' => 'Notificaciones del Panel', 'en' => 'Dashboard Notifications'),
            'New Order' => array('es' => 'Nuevo Pedido', 'en' => 'New Order'),
            'Order Status Changes' => array('es' => 'Cambios de Estado del Pedido', 'en' => 'Order Status Changes'),
            'New Customer Message' => array('es' => 'Nuevo Mensaje de Cliente', 'en' => 'New Customer Message'),
            'New Review' => array('es' => 'Nueva Reseña', 'en' => 'New Review'),
            'New Reviews' => array('es' => 'Nuevas Reseñas', 'en' => 'New Reviews'),
            'Low Stock Alert' => array('es' => 'Alerta de Inventario Bajo', 'en' => 'Low Stock Alert'),
            'Low Stock Alerts' => array('es' => 'Alertas de Inventario Bajo', 'en' => 'Low Stock Alerts'),
            'Payout Notifications' => array('es' => 'Notificaciones de Pago', 'en' => 'Payout Notifications'),
            'New Order Notifications' => array('es' => 'Notificaciones de Nuevos Pedidos', 'en' => 'New Order Notifications'),
            
            // Payment Methods
            'Payments' => array('es' => 'Pagos', 'en' => 'Payments'),
            'Payment Methods' => array('es' => 'Métodos de Pago', 'en' => 'Payment Methods'),
            'Payment Information' => array('es' => 'Información de Pago', 'en' => 'Payment Information'),
            'Payment Accounts' => array('es' => 'Cuentas de Pago', 'en' => 'Payment Accounts'),
            'PayPal' => array('es' => 'PayPal', 'en' => 'PayPal'),
            'PayPal Email Address' => array('es' => 'Dirección de Email de PayPal', 'en' => 'PayPal Email Address'),
            'Bank Transfer' => array('es' => 'Transferencia Bancaria', 'en' => 'Bank Transfer'),
            'Bank Account' => array('es' => 'Cuenta Bancaria', 'en' => 'Bank Account'),
            'Bank Name' => array('es' => 'Nombre del Banco', 'en' => 'Bank Name'),
            'Account Number' => array('es' => 'Número de Cuenta', 'en' => 'Account Number'),
            'Account Holder Name' => array('es' => 'Nombre del Titular de la Cuenta', 'en' => 'Account Holder Name'),
            'Routing Number' => array('es' => 'Número de Ruta', 'en' => 'Routing Number'),
            'Cash on Delivery' => array('es' => 'Pago en Efectivo', 'en' => 'Cash on Delivery'),
            'Stripe' => array('es' => 'Stripe', 'en' => 'Stripe'),
            'Connect PayPal' => array('es' => 'Conectar PayPal', 'en' => 'Connect PayPal'),
            'Connect Bank Account' => array('es' => 'Conectar Cuenta Bancaria', 'en' => 'Connect Bank Account'),
            'Connected' => array('es' => 'Conectado', 'en' => 'Connected'),
            'Not Connected' => array('es' => 'No Conectado', 'en' => 'Not Connected'),
            'Disconnect' => array('es' => 'Desconectar', 'en' => 'Disconnect'),
            'Connect your PayPal account to receive payments' => array('es' => 'Conecta tu cuenta de PayPal para recibir pagos', 'en' => 'Connect your PayPal account to receive payments'),
            'Connect your Stripe account for direct payments' => array('es' => 'Conecta tu cuenta de Stripe para pagos directos', 'en' => 'Connect your Stripe account for direct payments'),
            'Set up direct bank transfers' => array('es' => 'Configurar transferencias bancarias directas', 'en' => 'Set up direct bank transfers'),
            
            // Actions & Buttons
            'Edit' => array('es' => 'Editar', 'en' => 'Edit'),
            'Delete' => array('es' => 'Eliminar', 'en' => 'Delete'),
            'Remove' => array('es' => 'Remover', 'en' => 'Remove'),
            'View' => array('es' => 'Ver', 'en' => 'View'),
            'View Details' => array('es' => 'Ver Detalles', 'en' => 'View Details'),
            'View All' => array('es' => 'Ver Todo', 'en' => 'View All'),
            'View All Notifications' => array('es' => 'Ver Todas las Notificaciones', 'en' => 'View All Notifications'),
            'Search' => array('es' => 'Buscar', 'en' => 'Search'),
            'Filter' => array('es' => 'Filtrar', 'en' => 'Filter'),
            'Export' => array('es' => 'Exportar', 'en' => 'Export'),
            'Import' => array('es' => 'Importar', 'en' => 'Import'),
            'Save' => array('es' => 'Guardar', 'en' => 'Save'),
            'Save Changes' => array('es' => 'Guardar Cambios', 'en' => 'Save Changes'),
            'Save Account Settings' => array('es' => 'Guardar Configuración de Cuenta', 'en' => 'Save Account Settings'),
            'Save Store Settings' => array('es' => 'Guardar Configuración de Tienda', 'en' => 'Save Store Settings'),
            'Save Payment Settings' => array('es' => 'Guardar Configuración de Pagos', 'en' => 'Save Payment Settings'),
            'Save Notification Settings' => array('es' => 'Guardar Configuración de Notificaciones', 'en' => 'Save Notification Settings'),
            'Save Listing' => array('es' => 'Guardar Anuncio', 'en' => 'Save Listing'),
            'Update' => array('es' => 'Actualizar', 'en' => 'Update'),
            'Update Status' => array('es' => 'Actualizar Estado', 'en' => 'Update Status'),
            'Cancel' => array('es' => 'Cancelar', 'en' => 'Cancel'),
            'Confirm' => array('es' => 'Confirmar', 'en' => 'Confirm'),
            'Actions' => array('es' => 'Acciones', 'en' => 'Actions'),
            'Quick Actions' => array('es' => 'Acciones Rápidas', 'en' => 'Quick Actions'),
            'Upload Image' => array('es' => 'Subir Imagen', 'en' => 'Upload Image'),
            'Upload Logo' => array('es' => 'Subir Logo', 'en' => 'Upload Logo'),
            'Upload Banner' => array('es' => 'Subir Banner', 'en' => 'Upload Banner'),
            'Choose Image' => array('es' => 'Elegir Imagen', 'en' => 'Choose Image'),
            'Drag & drop your image here or click to browse' => array('es' => 'Arrastra y suelta tu imagen aquí o haz clic para navegar', 'en' => 'Drag & drop your image here or click to browse'),
            
            // Calendar & Time
            'Today' => array('es' => 'Hoy', 'en' => 'Today'),
            'Yesterday' => array('es' => 'Ayer', 'en' => 'Yesterday'),
            'Week' => array('es' => 'Semana', 'en' => 'Week'),
            'Month' => array('es' => 'Mes', 'en' => 'Month'),
            'Weekly' => array('es' => 'Semanal', 'en' => 'Weekly'),
            'Monthly' => array('es' => 'Mensual', 'en' => 'Monthly'),
            'Daily' => array('es' => 'Diario', 'en' => 'Daily'),
            'Bi-Weekly' => array('es' => 'Quincenal', 'en' => 'Bi-Weekly'),
            'Monday' => array('es' => 'Lunes', 'en' => 'Monday'),
            'Tuesday' => array('es' => 'Martes', 'en' => 'Tuesday'),
            'Wednesday' => array('es' => 'Miércoles', 'en' => 'Wednesday'),
            'Thursday' => array('es' => 'Jueves', 'en' => 'Thursday'),
            'Friday' => array('es' => 'Viernes', 'en' => 'Friday'),
            'Saturday' => array('es' => 'Sábado', 'en' => 'Saturday'),
            'Sunday' => array('es' => 'Domingo', 'en' => 'Sunday'),
            'Booking' => array('es' => 'Reserva', 'en' => 'Booking'),
            'Appointment' => array('es' => 'Cita', 'en' => 'Appointment'),
            'Just now' => array('es' => 'Ahora mismo', 'en' => 'Just now'),
            '2 hours ago' => array('es' => 'Hace 2 horas', 'en' => '2 hours ago'),
            '3 hours ago' => array('es' => 'Hace 3 horas', 'en' => '3 hours ago'),
            'Date:' => array('es' => 'Fecha:', 'en' => 'Date:'),
            'Invalid date' => array('es' => 'Fecha inválida', 'en' => 'Invalid date'),
            'No date' => array('es' => 'Sin fecha', 'en' => 'No date'),
            'Last day of month' => array('es' => 'Último día del mes', 'en' => 'Last day of month'),
            
            // Lead Management
            'Add Lead' => array('es' => 'Agregar Lead', 'en' => 'Add Lead'),
            'Lead Details' => array('es' => 'Detalles del Lead', 'en' => 'Lead Details'),
            'Ver Lead' => array('es' => 'Ver Lead', 'en' => 'View Lead'),
            'Mis Leads' => array('es' => 'Mis Leads', 'en' => 'My Leads'),
            'Volver a Leads' => array('es' => 'Volver a Leads', 'en' => 'Back to Leads'),
            'Cambiar Estado' => array('es' => 'Cambiar Estado', 'en' => 'Change Status'),
            'Cambiar Estado del Lead' => array('es' => 'Cambiar Estado del Lead', 'en' => 'Change Lead Status'),
            'Actualizar Estado' => array('es' => 'Actualizar Estado', 'en' => 'Update Status'),
            'New Status' => array('es' => 'Nuevo Estado', 'en' => 'New Status'),
            'Nuevo Estado:' => array('es' => 'Nuevo Estado:', 'en' => 'New Status:'),
            'Add Note' => array('es' => 'Agregar Nota', 'en' => 'Add Note'),
            'Notes' => array('es' => 'Notas', 'en' => 'Notes'),
            'Notas (Opcional):' => array('es' => 'Notas (Opcional):', 'en' => 'Notes (Optional):'),
            'Additional information about this lead...' => array('es' => 'Información adicional sobre este lead...', 'en' => 'Additional information about this lead...'),
            'Agregar notas sobre el cambio de estado...' => array('es' => 'Agregar notas sobre el cambio de estado...', 'en' => 'Add notes about the status change...'),
            'Reason for status change...' => array('es' => 'Razón del cambio de estado...', 'en' => 'Reason for status change...'),
            'Contactado' => array('es' => 'Contactado', 'en' => 'Contacted'),
            'Contacted' => array('es' => 'Contactado', 'en' => 'Contacted'),
            'Inicial' => array('es' => 'Inicial', 'en' => 'Initial'),
            'Iniciales' => array('es' => 'Iniciales', 'en' => 'Initials'),
            'Ganados' => array('es' => 'Ganados', 'en' => 'Won'),
            'Will Check' => array('es' => 'Verificará', 'en' => 'Will Check'),
            'Awaiting Response' => array('es' => 'Esperando Respuesta', 'en' => 'Awaiting Response'),
            'Awaiting Vendor Response' => array('es' => 'Esperando Respuesta del Vendedor', 'en' => 'Awaiting Vendor Response'),
            'Vendor Responded' => array('es' => 'Vendedor Respondió', 'en' => 'Vendor Responded'),
            'Responded' => array('es' => 'Respondió', 'en' => 'Responded'),
            
            // Order Management
            'Order' => array('es' => 'Orden', 'en' => 'Order'),
            'Orden' => array('es' => 'Orden', 'en' => 'Order'),
            'Mis Órdenes' => array('es' => 'Mis Órdenes', 'en' => 'My Orders'),
            'Total Órdenes' => array('es' => 'Total de Órdenes', 'en' => 'Total Orders'),
            'Order Details' => array('es' => 'Detalles del Pedido', 'en' => 'Order Details'),
            'Order Items' => array('es' => 'Artículos del Pedido', 'en' => 'Order Items'),
            'Order Notes' => array('es' => 'Notas del Pedido', 'en' => 'Order Notes'),
            'Order Status' => array('es' => 'Estado del Pedido', 'en' => 'Order Status'),
            'Back to Orders' => array('es' => 'Volver a Pedidos', 'en' => 'Back to Orders'),
            'Print Order' => array('es' => 'Imprimir Pedido', 'en' => 'Print Order'),
            'Download Invoice' => array('es' => 'Descargar Factura', 'en' => 'Download Invoice'),
            'Send note to customer' => array('es' => 'Enviar nota al cliente', 'en' => 'Send note to customer'),
            'Completadas' => array('es' => 'Completadas', 'en' => 'Completed'),
            'Completar' => array('es' => 'Completar', 'en' => 'Complete'),
            'Canceladas' => array('es' => 'Canceladas', 'en' => 'Cancelled'),
            'Reembolsadas' => array('es' => 'Reembolsadas', 'en' => 'Refunded'),
            'En Proceso' => array('es' => 'En Proceso', 'en' => 'In Process'),
            'En espera' => array('es' => 'En Espera', 'en' => 'On Hold'),
            'Sin pagar' => array('es' => 'Sin Pagar', 'en' => 'Unpaid'),
            'Buscar por número de orden o cliente...' => array('es' => 'Buscar por número de orden o cliente...', 'en' => 'Search by order number or customer...'),
            'Mostrando %d-%d de %d órdenes' => array('es' => 'Mostrando %d-%d de %d órdenes', 'en' => 'Showing %d-%d of %d orders'),
            'No se encontraron órdenes.' => array('es' => 'No se encontraron órdenes.', 'en' => 'No orders found.'),
            'Items' => array('es' => 'Artículos', 'en' => 'Items'),
            'Total' => array('es' => 'Total', 'en' => 'Total'),
            'Subtotal' => array('es' => 'Subtotal', 'en' => 'Subtotal'),
            'Tax' => array('es' => 'Impuesto', 'en' => 'Tax'),
            'Discount' => array('es' => 'Descuento', 'en' => 'Discount'),
            'Status:' => array('es' => 'Estado:', 'en' => 'Status:'),
            'Method:' => array('es' => 'Método:', 'en' => 'Method:'),
            'Shipping Info' => array('es' => 'Información de Envío', 'en' => 'Shipping Info'),
            'Shipping Address' => array('es' => 'Dirección de Envío', 'en' => 'Shipping Address'),
            'No shipping address provided.' => array('es' => 'No se proporcionó dirección de envío.', 'en' => 'No shipping address provided.'),
            'No notes for this order yet.' => array('es' => 'No hay notas para este pedido aún.', 'en' => 'No notes for this order yet.'),
            
            // Analytics & Reports
            'Stats Overview' => array('es' => 'Resumen de Estadísticas', 'en' => 'Stats Overview'),
            'Recent Activity' => array('es' => 'Actividad Reciente', 'en' => 'Recent Activity'),
            'Performance Metrics' => array('es' => 'Métricas de Rendimiento', 'en' => 'Performance Metrics'),
            'Métricas de Rendimiento' => array('es' => 'Métricas de Rendimiento', 'en' => 'Performance Metrics'),
            'Conversion' => array('es' => 'Conversión', 'en' => 'Conversion'),
            'Conversión' => array('es' => 'Conversión', 'en' => 'Conversion'),
            'Tasa de Conversión' => array('es' => 'Tasa de Conversión', 'en' => 'Conversion Rate'),
            'Tasa de Completación' => array('es' => 'Tasa de Completación', 'en' => 'Completion Rate'),
            'Tasa de Devolución' => array('es' => 'Tasa de Devolución', 'en' => 'Return Rate'),
            'Tasa de Respuesta' => array('es' => 'Tasa de Respuesta', 'en' => 'Response Rate'),
            'Tiempo de Respuesta' => array('es' => 'Tiempo de Respuesta', 'en' => 'Response Time'),
            'Tendencia de Ventas' => array('es' => 'Tendencia de Ventas', 'en' => 'Sales Trend'),
            'Ventas Totales' => array('es' => 'Ventas Totales', 'en' => 'Total Sales'),
            'Ventas' => array('es' => 'Ventas', 'en' => 'Sales'),
            'Ingresos' => array('es' => 'Ingresos', 'en' => 'Revenue'),
            'Productos Más Vendidos' => array('es' => 'Productos Más Vendidos', 'en' => 'Best Selling Products'),
            'Vistas de Productos' => array('es' => 'Vistas de Productos', 'en' => 'Product Views'),
            'Total de vistas' => array('es' => 'Total de Vistas', 'en' => 'Total Views'),
            'Total listing views' => array('es' => 'Total de vistas de anuncios', 'en' => 'Total listing views'),
            'Views to sales rate' => array('es' => 'Tasa de vistas a ventas', 'en' => 'Views to sales rate'),
            'valor promedio' => array('es' => 'valor promedio', 'en' => 'average value'),
            'Vistas' => array('es' => 'Vistas', 'en' => 'Views'),
            'Sales Report Frequency' => array('es' => 'Frecuencia de Reporte de Ventas', 'en' => 'Sales Report Frequency'),
            'Inventory Report Frequency' => array('es' => 'Frecuencia de Reporte de Inventario', 'en' => 'Inventory Report Frequency'),
            'Payout Frequency' => array('es' => 'Frecuencia de Pago', 'en' => 'Payout Frequency'),
            'Payout Schedule' => array('es' => 'Cronograma de Pagos', 'en' => 'Payout Schedule'),
            'Payout Date' => array('es' => 'Fecha de Pago', 'en' => 'Payout Date'),
            'Payout Day' => array('es' => 'Día de Pago', 'en' => 'Payout Day'),
            'Reports & Summaries' => array('es' => 'Reportes y Resúmenes', 'en' => 'Reports & Summaries'),
            'Exportar Reporte' => array('es' => 'Exportar Reporte', 'en' => 'Export Report'),
            
            // Customer Information
            'Customer Information' => array('es' => 'Información del Cliente', 'en' => 'Customer Information'),
            'Customer since %s' => array('es' => 'Cliente desde %s', 'en' => 'Customer since %s'),
            'Recent Interactions' => array('es' => 'Interacciones Recientes', 'en' => 'Recent Interactions'),
            'No previous interactions with this customer.' => array('es' => 'No hay interacciones previas con este cliente.', 'en' => 'No previous interactions with this customer.'),
            'Cliente' => array('es' => 'Cliente', 'en' => 'Customer'),
            'Name' => array('es' => 'Nombre', 'en' => 'Name'),
            'Email' => array('es' => 'Email', 'en' => 'Email'),
            'Email:' => array('es' => 'Email:', 'en' => 'Email:'),
            'Phone' => array('es' => 'Teléfono', 'en' => 'Phone'),
            'Phone Number' => array('es' => 'Número de Teléfono', 'en' => 'Phone Number'),
            'Teléfono:' => array('es' => 'Teléfono:', 'en' => 'Phone:'),
            'Empresa:' => array('es' => 'Empresa:', 'en' => 'Company:'),
            'Información de Contacto' => array('es' => 'Información de Contacto', 'en' => 'Contact Information'),
            'Información del Evento' => array('es' => 'Información del Evento', 'en' => 'Event Information'),
            'Evento' => array('es' => 'Evento', 'en' => 'Event'),
            'Evento:' => array('es' => 'Evento:', 'en' => 'Event:'),
            'Fecha' => array('es' => 'Fecha', 'en' => 'Date'),
            'Fecha del Evento:' => array('es' => 'Fecha del Evento:', 'en' => 'Event Date:'),
            'Fecha desde' => array('es' => 'Fecha desde', 'en' => 'Date from'),
            'Fecha hasta' => array('es' => 'Fecha hasta', 'en' => 'Date to'),
            'Ubicación:' => array('es' => 'Ubicación:', 'en' => 'Location:'),
            'Número de Invitados:' => array('es' => 'Número de Invitados:', 'en' => 'Number of Guests:'),
            'Contacto' => array('es' => 'Contacto', 'en' => 'Contact'),
            'Contact Form' => array('es' => 'Formulario de Contacto', 'en' => 'Contact Form'),
            'Contact the Vendor' => array('es' => 'Contactar al Vendedor', 'en' => 'Contact the Vendor'),
            'Llamar' => array('es' => 'Llamar', 'en' => 'Call'),
            'Enviar Email' => array('es' => 'Enviar Email', 'en' => 'Send Email'),
            
            // Common Phrases & Messages
            'No results found' => array('es' => 'No se encontraron resultados', 'en' => 'No results found'),
            'Loading...' => array('es' => 'Cargando...', 'en' => 'Loading...'),
            'Actualizando...' => array('es' => 'Actualizando...', 'en' => 'Updating...'),
            'Error loading data' => array('es' => 'Error al cargar los datos', 'en' => 'Error loading data'),
            'Success!' => array('es' => '¡Éxito!', 'en' => 'Success!'),
            'Are you sure?' => array('es' => '¿Estás seguro?', 'en' => 'Are you sure?'),
            'Are you sure you want to archive this message?' => array('es' => '¿Estás seguro de que quieres archivar este mensaje?', 'en' => 'Are you sure you want to archive this message?'),
            'Limpiar' => array('es' => 'Limpiar', 'en' => 'Clear'),
            'N/A' => array('es' => 'N/A', 'en' => 'N/A'),
            'Other' => array('es' => 'Otro', 'en' => 'Other'),
            'General Inquiry' => array('es' => 'Consulta General', 'en' => 'General Inquiry'),
            'Referral' => array('es' => 'Referido', 'en' => 'Referral'),
            'Website' => array('es' => 'Sitio Web', 'en' => 'Website'),
            'Table View' => array('es' => 'Vista de Tabla', 'en' => 'Table View'),
            'Pipeline View' => array('es' => 'Vista de Pipeline', 'en' => 'Pipeline View'),
            'Previous' => array('es' => 'Anterior', 'en' => 'Previous'),
            'Anterior' => array('es' => 'Anterior', 'en' => 'Previous'),
            'Next' => array('es' => 'Siguiente', 'en' => 'Next'),
            'Siguiente' => array('es' => 'Siguiente', 'en' => 'Next'),
            'Categories' => array('es' => 'Categorías', 'en' => 'Categories'),
            'All Categories' => array('es' => 'Todas las Categorías', 'en' => 'All Categories'),
            'All Statuses' => array('es' => 'Todos los Estados', 'en' => 'All Statuses'),
            'Todos los estados' => array('es' => 'Todos los Estados', 'en' => 'All Statuses'),
            'Filtrar' => array('es' => 'Filtrar', 'en' => 'Filter'),
            'Estado' => array('es' => 'Estado', 'en' => 'Status'),
            'Reservaciones' => array('es' => 'Reservaciones', 'en' => 'Reservations'),
            'Servicio' => array('es' => 'Servicio', 'en' => 'Service'),
            'Rating' => array('es' => 'Calificación', 'en' => 'Rating'),
            'Title' => array('es' => 'Título', 'en' => 'Title'),
            'Subject' => array('es' => 'Asunto', 'en' => 'Subject'),
            'Shipping' => array('es' => 'Envío', 'en' => 'Shipping'),
            'Featured Listing' => array('es' => 'Anuncio Destacado', 'en' => 'Featured Listing'),
            'Mark this listing as featured' => array('es' => 'Marcar este anuncio como destacado', 'en' => 'Mark this listing as featured'),
            'Visibility & Status' => array('es' => 'Visibilidad y Estado', 'en' => 'Visibility & Status'),
            'Features & Attributes' => array('es' => 'Características y Atributos', 'en' => 'Features & Attributes'),
            
            // Time periods
            'Últimos 7 días' => array('es' => 'Últimos 7 días', 'en' => 'Last 7 days'),
            'Últimos 30 días' => array('es' => 'Últimos 30 días', 'en' => 'Last 30 days'),
            'Últimos 3 meses' => array('es' => 'Últimos 3 meses', 'en' => 'Last 3 months'),
            'Últimos 6 meses' => array('es' => 'Últimos 6 meses', 'en' => 'Last 6 months'),
            'Últimos 12 meses' => array('es' => 'Últimos 12 meses', 'en' => 'Last 12 months'),
            '%s sales this month' => array('es' => '%s ventas este mes', 'en' => '%s sales this month'),
            
            // Percentages and performance
            '0%' => array('es' => '0%', 'en' => '0%'),
            '2%' => array('es' => '2%', 'en' => '2%'),
            '5%' => array('es' => '5%', 'en' => '5%'),
            '8%' => array('es' => '8%', 'en' => '8%'),
            '12%' => array('es' => '12%', 'en' => '12%'),
            'Excellent Performance!' => array('es' => '¡Rendimiento Excelente!', 'en' => 'Excellent Performance!'),
            'Good Performance' => array('es' => 'Buen Rendimiento', 'en' => 'Good Performance'),
            'Needs Improvement' => array('es' => 'Necesita Mejorar', 'en' => 'Needs Improvement'),
            'Your store is doing great. Keep up the good work!' => array('es' => 'Tu tienda está funcionando muy bien. ¡Sigue así!', 'en' => 'Your store is doing great. Keep up the good work!'),
            'Your store is performing well. There\'s always room for improvement!' => array('es' => 'Tu tienda está funcionando bien. ¡Siempre hay espacio para mejorar!', 'en' => 'Your store is performing well. There\'s always room for improvement!'),
            'Your store is doing okay. Check out the tips to improve your performance.' => array('es' => 'Tu tienda está funcionando bien. Revisa los consejos para mejorar tu rendimiento.', 'en' => 'Your store is doing okay. Check out the tips to improve your performance.'),
            'There are several areas that need attention. Check out the recommendations below.' => array('es' => 'Hay varias áreas que necesitan atención. Revisa las recomendaciones a continuación.', 'en' => 'There are several areas that need attention. Check out the recommendations below.'),
            
            // Error Messages
            'Error al actualizar el estado.' => array('es' => 'Error al actualizar el estado.', 'en' => 'Error updating status.'),
            'Error al procesar la solicitud.' => array('es' => 'Error al procesar la solicitud.', 'en' => 'Error processing request.'),
            'An error occurred. Please try again.' => array('es' => 'Ocurrió un error. Por favor, inténtalo de nuevo.', 'en' => 'An error occurred. Please try again.'),
            'Security check failed.' => array('es' => 'Falló la verificación de seguridad.', 'en' => 'Security check failed.'),
            'Failed to archive message. Please try again.' => array('es' => 'Error al archivar el mensaje. Por favor, inténtalo de nuevo.', 'en' => 'Failed to archive message. Please try again.'),
            'Failed to mark message as read. Please try again.' => array('es' => 'Error al marcar el mensaje como leído. Por favor, inténtalo de nuevo.', 'en' => 'Failed to mark message as read. Please try again.'),
            'Failed to send reply. Please try again.' => array('es' => 'Error al enviar la respuesta. Por favor, inténtalo de nuevo.', 'en' => 'Failed to send reply. Please try again.'),
            'Please enter a reply.' => array('es' => 'Por favor, ingresa una respuesta.', 'en' => 'Please enter a reply.'),
            'Lead no encontrado.' => array('es' => 'Lead no encontrado.', 'en' => 'Lead not found.'),
            'No tienes permisos para ver este lead.' => array('es' => 'No tienes permisos para ver este lead.', 'en' => 'You don\'t have permission to view this lead.'),
            'Vendor not found.' => array('es' => 'Vendedor no encontrado.', 'en' => 'Vendor not found.'),
            'Vendor profile not found.' => array('es' => 'Perfil de vendedor no encontrado.', 'en' => 'Vendor profile not found.'),
            'Order not found or you do not have permission to view it.' => array('es' => 'Pedido no encontrado o no tienes permisos para verlo.', 'en' => 'Order not found or you do not have permission to view it.'),
            'Message not found or you do not have permission to view it.' => array('es' => 'Mensaje no encontrado o no tienes permisos para verlo.', 'en' => 'Message not found or you do not have permission to view it.'),
            'You must be logged in to access this content.' => array('es' => 'Debes estar conectado para acceder a este contenido.', 'en' => 'You must be logged in to access this content.'),
            'You must be logged in to access the vendor dashboard.' => array('es' => 'Debes estar conectado para acceder al panel de vendedor.', 'en' => 'You must be logged in to access the vendor dashboard.'),
            'You must be a vendor to access this content.' => array('es' => 'Debes ser un vendedor para acceder a este contenido.', 'en' => 'You must be a vendor to access this content.'),
            'You must be a registered vendor to access this dashboard.' => array('es' => 'Debes ser un vendedor registrado para acceder a este panel.', 'en' => 'You must be a registered vendor to access this dashboard.'),
            'You must be registered as a vendor to access this dashboard.' => array('es' => 'Debes estar registrado como vendedor para acceder a este panel.', 'en' => 'You must be registered as a vendor to access this dashboard.'),
            'Please register as a vendor to continue.' => array('es' => 'Por favor, regístrate como vendedor para continuar.', 'en' => 'Please register as a vendor to continue.'),
            'Login Required' => array('es' => 'Inicio de Sesión Requerido', 'en' => 'Login Required'),
            'Vendor Access Required' => array('es' => 'Acceso de Vendedor Requerido', 'en' => 'Vendor Access Required'),
            
            // Empty states
            'No messages found.' => array('es' => 'No se encontraron mensajes.', 'en' => 'No messages found.'),
            'No messages yet. Messages from customers will appear here.' => array('es' => 'No hay mensajes aún. Los mensajes de clientes aparecerán aquí.', 'en' => 'No messages yet. Messages from customers will appear here.'),
            'No messages yet. When you contact a vendor, your messages will appear here.' => array('es' => 'No hay mensajes aún. Cuando contactes a un vendedor, tus mensajes aparecerán aquí.', 'en' => 'No messages yet. When you contact a vendor, your messages will appear here.'),
            'No tienes leads aún. Los leads aparecerán aquí cuando los clientes se interesen en tus servicios.' => array('es' => 'No tienes leads aún. Los leads aparecerán aquí cuando los clientes se interesen en tus servicios.', 'en' => 'You don\'t have any leads yet. Leads will appear here when customers are interested in your services.'),
            'No listings found' => array('es' => 'No se encontraron anuncios', 'en' => 'No listings found'),
            'No listings yet. Add your first listing!' => array('es' => 'No hay anuncios aún. ¡Agrega tu primer anuncio!', 'en' => 'No listings yet. Add your first listing!'),
            'Add Your First Listing' => array('es' => 'Agregar Tu Primer Anuncio', 'en' => 'Add Your First Listing'),
            'Recent Listings' => array('es' => 'Anuncios Recientes', 'en' => 'Recent Listings'),
            'Recent Messages' => array('es' => 'Mensajes Recientes', 'en' => 'Recent Messages'),
            'No hay datos de productos disponibles.' => array('es' => 'No hay datos de productos disponibles.', 'en' => 'No product data available.'),
            'You haven\'t created any listings yet.' => array('es' => 'No has creado ningún anuncio aún.', 'en' => 'You haven\'t created any listings yet.'),
            'You have a new order!' => array('es' => '¡Tienes un nuevo pedido!', 'en' => 'You have a new order!'),
            'You received a new review!' => array('es' => '¡Recibiste una nueva reseña!', 'en' => 'You received a new review!'),
            'No banner uploaded' => array('es' => 'No se subió banner', 'en' => 'No banner uploaded'),
            'No logo uploaded' => array('es' => 'No se subió logo', 'en' => 'No logo uploaded'),
            'No header image uploaded' => array('es' => 'No se subió imagen de encabezado', 'en' => 'No header image uploaded'),
            'No file selected' => array('es' => 'No se seleccionó archivo', 'en' => 'No file selected'),
            
            // Órdenes symbol and related
            'Órdenes' => array('es' => 'Órdenes', 'en' => 'Orders'),
            
            // Quick Replies and Message Templates
            'Quick Replies' => array('es' => 'Respuestas Rápidas', 'en' => 'Quick Replies'),
            'Message Templates' => array('es' => 'Plantillas de Mensaje', 'en' => 'Message Templates'),
            'Email Message Template' => array('es' => 'Plantilla de Mensaje de Email', 'en' => 'Email Message Template'),
            'WhatsApp Message Template' => array('es' => 'Plantilla de Mensaje de WhatsApp', 'en' => 'WhatsApp Message Template'),
            'Thank you for your interest! Shipping usually takes 3-5 business days.' => array('es' => '¡Gracias por tu interés! El envío generalmente toma de 3 a 5 días hábiles.', 'en' => 'Thank you for your interest! Shipping usually takes 3-5 business days.'),
            'Thank you for your message! Yes, this item is still available.' => array('es' => '¡Gracias por tu mensaje! Sí, este artículo aún está disponible.', 'en' => 'Thank you for your message! Yes, this item is still available.'),
            'Thank you for your message! I\'ll get back to you within 24 hours.' => array('es' => '¡Gracias por tu mensaje! Te responderé dentro de 24 horas.', 'en' => 'Thank you for your message! I\'ll get back to you within 24 hours.'),
            'Thank you for your question. Let me look into this and get back to you as soon as possible.' => array('es' => 'Gracias por tu pregunta. Permíteme investigar esto y te responderé lo antes posible.', 'en' => 'Thank you for your question. Let me look into this and get back to you as soon as possible.'),
            'New message from John Doe' => array('es' => 'Nuevo mensaje de John Doe', 'en' => 'New message from John Doe'),
            'Mensaje del Cliente' => array('es' => 'Mensaje del Cliente', 'en' => 'Customer Message'),
            'Available placeholders: {customer_name}, {quote_number}, {total_amount}, {event_date}, {vendor_name}' => array('es' => 'Marcadores disponibles: {customer_name}, {quote_number}, {total_amount}, {event_date}, {vendor_name}', 'en' => 'Available placeholders: {customer_name}, {quote_number}, {total_amount}, {event_date}, {vendor_name}'),
            'You' => array('es' => 'Tú', 'en' => 'You'),
            
            // Additional settings and configuration
            'How often you want to receive sales report emails' => array('es' => 'Qué tan seguido quieres recibir emails de reportes de ventas', 'en' => 'How often you want to receive sales report emails'),
            'How often you want to receive inventory status reports' => array('es' => 'Qué tan seguido quieres recibir reportes de estado de inventario', 'en' => 'How often you want to receive inventory status reports'),
            'How often you want to receive payouts from your sales.' => array('es' => 'Qué tan seguido quieres recibir pagos de tus ventas.', 'en' => 'How often you want to receive payouts from your sales.'),
            'Receive an email when a new order is placed' => array('es' => 'Recibir un email cuando se haga un nuevo pedido', 'en' => 'Receive an email when a new order is placed'),
            'Receive an email when an order status changes' => array('es' => 'Recibir un email cuando cambie el estado de un pedido', 'en' => 'Receive an email when an order status changes'),
            'Receive an email when a customer sends you a message' => array('es' => 'Recibir un email cuando un cliente te envíe un mensaje', 'en' => 'Receive an email when a customer sends you a message'),
            'Receive an email when a customer leaves a review' => array('es' => 'Recibir un email cuando un cliente deje una reseña', 'en' => 'Receive an email when a customer leaves a review'),
            'Receive an email when product stock is running low' => array('es' => 'Recibir un email cuando el stock del producto esté bajo', 'en' => 'Receive an email when product stock is running low'),
            'Receive an email when a payout is processed' => array('es' => 'Recibir un email cuando se procese un pago', 'en' => 'Receive an email when a payout is processed'),
            'Show notifications in the dashboard when a new order is placed' => array('es' => 'Mostrar notificaciones en el panel cuando se haga un nuevo pedido', 'en' => 'Show notifications in the dashboard when a new order is placed'),
            'Show notifications in the dashboard when a customer sends you a message' => array('es' => 'Mostrar notificaciones en el panel cuando un cliente te envíe un mensaje', 'en' => 'Show notifications in the dashboard when a customer sends you a message'),
            'Show notifications in the dashboard when a customer leaves a review' => array('es' => 'Mostrar notificaciones en el panel cuando un cliente deje una reseña', 'en' => 'Show notifications in the dashboard when a customer leaves a review'),
            'Show notifications in the dashboard when a payout is processed' => array('es' => 'Mostrar notificaciones en el panel cuando se procese un pago', 'en' => 'Show notifications in the dashboard when a payout is processed'),
            'Do not send' => array('es' => 'No enviar', 'en' => 'Do not send'),
            'This email will be used for order notifications and is automatically set from your WordPress account.' => array('es' => 'Este email se usará para notificaciones de pedidos y se establece automáticamente desde tu cuenta de WordPress.', 'en' => 'This email will be used for order notifications and is automatically set from your WordPress account.'),
            'To change your email address, please contact support.' => array('es' => 'Para cambiar tu dirección de email, por favor contacta soporte.', 'en' => 'To change your email address, please contact support.'),
            'Tell customers about your store and what makes it special.' => array('es' => 'Cuéntales a los clientes sobre tu tienda y qué la hace especial.', 'en' => 'Tell customers about your store and what makes it special.'),
            'Your contact phone number for customers.' => array('es' => 'Tu número de teléfono de contacto para clientes.', 'en' => 'Your contact phone number for customers.'),
            
            // Password requirements
            'Password requirements:' => array('es' => 'Requisitos de contraseña:', 'en' => 'Password requirements:'),
            'At least 8 characters long' => array('es' => 'Al menos 8 caracteres de largo', 'en' => 'At least 8 characters long'),
            'Contains at least one uppercase letter' => array('es' => 'Contiene al menos una letra mayúscula', 'en' => 'Contains at least one uppercase letter'),
            'Contains at least one lowercase letter' => array('es' => 'Contiene al menos una letra minúscula', 'en' => 'Contains at least one lowercase letter'),
            'Contains at least one number' => array('es' => 'Contiene al menos un número', 'en' => 'Contains at least one number'),
            'Contains at least one special character' => array('es' => 'Contiene al menos un carácter especial', 'en' => 'Contains at least one special character'),
            
            // Additional interface elements
            'From %s reviews' => array('es' => 'De %s reseñas', 'en' => 'From %s reviews'),
            'Search listings...' => array('es' => 'Buscar anuncios...', 'en' => 'Search listings...'),
            'Add Listing' => array('es' => 'Agregar Anuncio', 'en' => 'Add Listing'),
            'Add New Listing' => array('es' => 'Agregar Nuevo Anuncio', 'en' => 'Add New Listing'),
            'Edit Listing' => array('es' => 'Editar Anuncio', 'en' => 'Edit Listing'),
            'View Public Profile' => array('es' => 'Ver Perfil Público', 'en' => 'View Public Profile'),
            'View Vendor Profile' => array('es' => 'Ver Perfil de Vendedor', 'en' => 'View Vendor Profile'),
            'My Store' => array('es' => 'Mi Tienda', 'en' => 'My Store'),
            'Register as Vendor' => array('es' => 'Registrarse como Vendedor', 'en' => 'Register as Vendor'),
            'Vendor Dashboard' => array('es' => 'Panel de Vendedor', 'en' => 'Vendor Dashboard'),
            'Vendor Dashboard Pro' => array('es' => 'Panel de Vendedor Pro', 'en' => 'Vendor Dashboard Pro'),
            'Vendor Information' => array('es' => 'Información del Vendedor', 'en' => 'Vendor Information'),
            'Active Seller' => array('es' => 'Vendedor Activo', 'en' => 'Active Seller'),
            'Inactive Seller' => array('es' => 'Vendedor Inactivo', 'en' => 'Inactive Seller'),
            'Verified Seller' => array('es' => 'Vendedor Verificado', 'en' => 'Verified Seller'),
            'Account ID:' => array('es' => 'ID de Cuenta:', 'en' => 'Account ID:'),
            'Account: ****' => array('es' => 'Cuenta: ****', 'en' => 'Account: ****'),
            'Here you can view and manage your conversations with vendors.' => array('es' => 'Aquí puedes ver y gestionar tus conversaciones con vendedores.', 'en' => 'Here you can view and manage your conversations with vendors.'),
            'Here\'s where all your customer interactions happen.' => array('es' => 'Aquí es donde ocurren todas tus interacciones con clientes.', 'en' => 'Here\'s where all your customer interactions happen.'),
            'Only letters, numbers and hyphens are allowed' => array('es' => 'Solo se permiten letras, números y guiones', 'en' => 'Only letters, numbers and hyphens are allowed'),
            'URL of the service they\'re interested in' => array('es' => 'URL del servicio en el que están interesados', 'en' => 'URL of the service they\'re interested in'),
            'Service URL' => array('es' => 'URL del Servicio', 'en' => 'Service URL'),
            'Recommended size: 200x200 pixels. Maximum file size: 2MB.' => array('es' => 'Tamaño recomendado: 200x200 píxeles. Tamaño máximo de archivo: 2MB.', 'en' => 'Recommended size: 200x200 pixels. Maximum file size: 2MB.'),
            'Recommended size: 1200x300 pixels. Maximum file size: 2MB.' => array('es' => 'Tamaño recomendado: 1200x300 píxeles. Tamaño máximo de archivo: 2MB.', 'en' => 'Recommended size: 1200x300 pixels. Maximum file size: 2MB.'),
            'Recommended image size: 800x600 pixels. Maximum file size: 2MB.' => array('es' => 'Tamaño de imagen recomendado: 800x600 píxeles. Tamaño máximo de archivo: 2MB.', 'en' => 'Recommended image size: 800x600 pixels. Maximum file size: 2MB.'),
            'Upload visual branding for your store' => array('es' => 'Sube la marca visual para tu tienda', 'en' => 'Upload visual branding for your store'),
            
            // Admin and settings integration
            'Social Media' => array('es' => 'Redes Sociales', 'en' => 'Social Media'),
            'Facebook' => array('es' => 'Facebook', 'en' => 'Facebook'),
            'Instagram' => array('es' => 'Instagram', 'en' => 'Instagram'),
            'Google' => array('es' => 'Google', 'en' => 'Google'),
            'Your Facebook page or profile URL' => array('es' => 'La URL de tu página o perfil de Facebook', 'en' => 'Your Facebook page or profile URL'),
            'Your Instagram profile URL' => array('es' => 'La URL de tu perfil de Instagram', 'en' => 'Your Instagram profile URL'),
            'Connect your social media accounts to display on your store profile' => array('es' => 'Conecta tus cuentas de redes sociales para mostrar en tu perfil de tienda', 'en' => 'Connect your social media accounts to display on your store profile'),
            
            // Premium features
            'Premium Feature' => array('es' => 'Función Premium', 'en' => 'Premium Feature'),
            'Upgrade to Premium' => array('es' => 'Actualizar a Premium', 'en' => 'Upgrade to Premium'),
            'Stripe integration is available in the Premium version of Vendor Dashboard Pro.' => array('es' => 'La integración de Stripe está disponible en la versión Premium de Vendor Dashboard Pro.', 'en' => 'Stripe integration is available in the Premium version of Vendor Dashboard Pro.'),
            'El plugin HivePress Bookings es requerido para usar esta funcionalidad.' => array('es' => 'El plugin HivePress Bookings es requerido para usar esta funcionalidad.', 'en' => 'HivePress Bookings plugin is required to use this functionality.'),
            
            // PDF and branding
            'PDF Branding' => array('es' => 'Marca de PDF', 'en' => 'PDF Branding'),
            'PDF Header Image (Optional)' => array('es' => 'Imagen de Encabezado PDF (Opcional)', 'en' => 'PDF Header Image (Optional)'),
            'Custom header image for PDF quotes. Recommended size: 800x150 pixels. Maximum file size: 2MB.' => array('es' => 'Imagen de encabezado personalizada para cotizaciones PDF. Tamaño recomendado: 800x150 píxeles. Tamaño máximo de archivo: 2MB.', 'en' => 'Custom header image for PDF quotes. Recommended size: 800x150 pixels. Maximum file size: 2MB.'),
            'Customize your PDF quotes (Event Quote Cart Plugin)' => array('es' => 'Personaliza tus cotizaciones PDF (Plugin Event Quote Cart)', 'en' => 'Customize your PDF quotes (Event Quote Cart Plugin)'),
            'Customize messages for Event Quote Cart sharing' => array('es' => 'Personaliza mensajes para compartir Event Quote Cart', 'en' => 'Customize messages for Event Quote Cart sharing'),
            
            // Additional comprehensive translations from all templates
            'Exportar' => array('es' => 'Exportar', 'en' => 'Export'),
            'Ingresos Totales' => array('es' => 'Ingresos Totales', 'en' => 'Total Revenue'),
            'Buscar por número de orden o cliente...' => array('es' => 'Buscar por número de orden o cliente...', 'en' => 'Search by order number or customer...'),
            'Ver' => array('es' => 'Ver', 'en' => 'View'),
            'Completar' => array('es' => 'Completar', 'en' => 'Complete'),
            'No se encontraron órdenes.' => array('es' => 'No se encontraron órdenes.', 'en' => 'No orders found.'),
            'Mostrando %d-%d de %d órdenes' => array('es' => 'Mostrando %d-%d de %d órdenes', 'en' => 'Showing %d-%d of %d orders'),
            'Customer since %s' => array('es' => 'Cliente desde %s', 'en' => 'Customer since %s'),
            '%d order' => array('es' => '%d pedido', 'en' => '%d order'),
            '%d orders' => array('es' => '%d pedidos', 'en' => '%d orders'),
            'Enter a note for your reference or to communicate with the customer...' => array('es' => 'Ingrese una nota para su referencia o para comunicarse con el cliente...', 'en' => 'Enter a note for your reference or to communicate with the customer...'),
            'Create your first listing to get started!' => array('es' => '¡Crea tu primer anuncio para empezar!', 'en' => 'Create your first listing to get started!'),
            'Last Name' => array('es' => 'Apellido', 'en' => 'Last Name'),
            '%d item' => array('es' => '%d artículo', 'en' => '%d item'),
            '%d items' => array('es' => '%d artículos', 'en' => '%d items'),
            'Compose New Message' => array('es' => 'Redactar Nuevo Mensaje', 'en' => 'Compose New Message'),
            'Back to Messages' => array('es' => 'Volver a Mensajes', 'en' => 'Back to Messages'),
            'Recipient Name' => array('es' => 'Nombre del Destinatario', 'en' => 'Recipient Name'),
            'Recipient Email' => array('es' => 'Email del Destinatario', 'en' => 'Recipient Email'),
            'Enter message subject...' => array('es' => 'Ingrese el asunto del mensaje...', 'en' => 'Enter message subject...'),
            'Type your message here...' => array('es' => 'Escriba su mensaje aquí...', 'en' => 'Type your message here...'),
            'Sending...' => array('es' => 'Enviando...', 'en' => 'Sending...'),
            'Failed to send message. Please try again.' => array('es' => 'Error al enviar mensaje. Por favor, inténtelo de nuevo.', 'en' => 'Failed to send message. Please try again.'),
            'Network error. Please try again.' => array('es' => 'Error de red. Por favor, inténtelo de nuevo.', 'en' => 'Network error. Please try again.'),
            'Thank you for your message! I\'m sorry, but this item is currently out of stock.' => array('es' => '¡Gracias por su mensaje! Lo siento, pero este artículo está actualmente fuera de stock.', 'en' => 'Thank you for your message! I\'m sorry, but this item is currently out of stock.'),
            'Out of Stock' => array('es' => 'Fuera de Stock', 'en' => 'Out of Stock'),
            'Shipping Info' => array('es' => 'Información de Envío', 'en' => 'Shipping Info'),
            'Will Check' => array('es' => 'Verificaré', 'en' => 'Will Check'),
            'Pending' => array('es' => 'Pendiente', 'en' => 'Pending'),
            'Add Lead' => array('es' => 'Agregar Lead', 'en' => 'Add Lead'),
            'Update Status' => array('es' => 'Actualizar Estado', 'en' => 'Update Status'),
            'Send Message' => array('es' => 'Enviar Mensaje', 'en' => 'Send Message'),
            'Calendário' => array('es' => 'Calendario', 'en' => 'Calendar'),
            'Reservaciones' => array('es' => 'Reservaciones', 'en' => 'Reservations'),
            'Pipeline View' => array('es' => 'Vista de Pipeline', 'en' => 'Pipeline View'),
            'Table View' => array('es' => 'Vista de Tabla', 'en' => 'Table View'),
            'Add New Lead' => array('es' => 'Agregar Nuevo Lead', 'en' => 'Add New Lead'),
            'New Lead' => array('es' => 'Nuevo Lead', 'en' => 'New Lead'),
            'Advanced Filters' => array('es' => 'Filtros Avanzados', 'en' => 'Advanced Filters'),
            'New (Not Contacted)' => array('es' => 'Nuevo (No Contactado)', 'en' => 'New (Not Contacted)'),
            'Contacted & Interested' => array('es' => 'Contactado e Interesado', 'en' => 'Contacted & Interested'),
            'Meeting Scheduled' => array('es' => 'Reunión Agendada', 'en' => 'Meeting Scheduled'),
            'Proposal Sent' => array('es' => 'Propuesta Enviada', 'en' => 'Proposal Sent'),
            'In Negotiation' => array('es' => 'En Negociación', 'en' => 'In Negotiation'),
            'Closed (Won)' => array('es' => 'Cerrado (Ganado)', 'en' => 'Closed (Won)'),
            'Closed (Lost)' => array('es' => 'Cerrado (Perdido)', 'en' => 'Closed (Lost)'),
            'Follow Up' => array('es' => 'En Seguimiento', 'en' => 'Follow Up'),
            'New' => array('es' => 'Nuevo', 'en' => 'New'),
            'Con Cotización' => array('es' => 'Con Cotización', 'en' => 'With Quote'),
            'Por cerrar' => array('es' => 'Por cerrar', 'en' => 'To close'),
            'Con contrato' => array('es' => 'Con contrato', 'en' => 'With contract'),
            'Perdido' => array('es' => 'Perdido', 'en' => 'Lost'),
            'You must be a vendor to access this content.' => array('es' => 'Debes ser un vendedor para acceder a este contenido.', 'en' => 'You must be a vendor to access this content.'),
            'Export functionality coming soon' => array('es' => 'Funcionalidad de exportación próximamente', 'en' => 'Export functionality coming soon'),
            'Funcionalidad de exportación próximamente' => array('es' => 'Funcionalidad de exportación próximamente', 'en' => 'Export functionality coming soon'),
        );
    }
    
    /**
     * Translate text
     */
    public function translate_text($translated, $text, $domain) {
        if ($domain !== 'vendor-dashboard-pro') {
            return $translated;
        }
        
        if (isset($this->translations[$text][$this->current_lang])) {
            return $this->translations[$text][$this->current_lang];
        }
        
        return $translated;
    }
    
    /**
     * Translate plural text
     */
    public function translate_text_plural($translated, $single, $plural, $number, $domain) {
        if ($domain !== 'vendor-dashboard-pro') {
            return $translated;
        }
        
        $text = $number === 1 ? $single : $plural;
        
        if (isset($this->translations[$text][$this->current_lang])) {
            return $this->translations[$text][$this->current_lang];
        }
        
        return $translated;
    }
    
    /**
     * Get translation directly
     */
    public function get($text) {
        if (isset($this->translations[$text][$this->current_lang])) {
            return $this->translations[$text][$this->current_lang];
        }
        return $text;
    }
    
    /**
     * Add custom translation
     */
    public function add_translation($text, $es_translation, $en_translation = null) {
        $this->translations[$text] = array(
            'es' => $es_translation,
            'en' => $en_translation ?: $text
        );
    }
}

// Initialize
VDP_Translations::instance();