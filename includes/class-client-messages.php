<?php
/**
 * Client Messages Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class for handling client-side messaging
 */
class VDP_Client_Messages {
    /**
     * Instance of this class.
     *
     * @var VDP_Client_Messages
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Client_Messages
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
        // Registrar actions para AJAX
        add_action('wp_ajax_vdp_client_get_messages', array($this, 'ajax_get_messages'));
        add_action('wp_ajax_vdp_client_get_message', array($this, 'ajax_get_message'));
        add_action('wp_ajax_vdp_client_send_reply', array($this, 'ajax_send_reply'));
        
        // Registrar shortcode para el área de mensajes del cliente
        add_shortcode('vdp_client_messages', array($this, 'client_messages_shortcode'));
        
        // Registrar assets
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
    }
    
    /**
     * Register and enqueue assets for client messages
     */
    public function register_assets() {
        // Solo cargar en la página que contiene el shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'vdp_client_messages')) {
            // CSS
            wp_enqueue_style('vdp-messages', VDP_PLUGIN_URL . 'assets/css/messages.css', array(), VDP_VERSION);
            
            // JavaScript
            wp_enqueue_script('vdp-client-messages', VDP_PLUGIN_URL . 'assets/js/client-messages.js', array('jquery'), VDP_VERSION, true);
            
            // Localizar script
            wp_localize_script('vdp-client-messages', 'vdp_client_messages', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('vdp-ajax-nonce'),
                'messages_url' => get_permalink(),
                'texts' => array(
                    'reply_sent' => __('Your reply has been sent!', 'vendor-dashboard-pro'),
                    'error' => __('An error occurred. Please try again.', 'vendor-dashboard-pro'),
                    'loading' => __('Loading...', 'vendor-dashboard-pro'),
                ),
            ));
        }
    }
    
    /**
     * Shortcode callback for client messages
     */
    public function client_messages_shortcode($atts) {
        // Verificar si el usuario está logueado
        if (!is_user_logged_in()) {
            return '<div class="vdp-login-required">' . 
                   '<p>' . __('You must be logged in to view your messages.', 'vendor-dashboard-pro') . '</p>' .
                   '</div>';
        }
        
        // Obtener parámetros
        $atts = shortcode_atts(array(
            'limit' => 10,
        ), $atts, 'vdp_client_messages');
        
        // Iniciar buffer de salida
        ob_start();
        
        // Obtener ID del mensaje si está en la URL
        $message_id = isset($_GET['message_id']) ? intval($_GET['message_id']) : 0;
        
        // Si hay un ID de mensaje, mostrar vista detallada
        if ($message_id > 0) {
            $this->render_message_view($message_id);
        } else {
            // Sino, mostrar lista de mensajes
            $this->render_messages_list($atts['limit']);
        }
        
        // Devolver contenido
        return ob_get_clean();
    }
    
    /**
     * Render the messages list for the client
     */
    private function render_messages_list($limit = 10) {
        // Obtener usuario actual
        $user_id = get_current_user_id();
        
        // Obtener mensajes del usuario
        $messages = $this->get_client_messages($user_id, $limit);
        
        // Incluir template
        include VDP_PLUGIN_DIR . 'templates/client-messages-list.php';
    }
    
    /**
     * Render the message view for the client
     */
    private function render_message_view($message_id) {
        // Obtener usuario actual
        $user_id = get_current_user_id();
        
        // Obtener mensaje
        $message = $this->get_client_message($message_id, $user_id);
        
        if (!$message) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Message not found or you do not have permission to view it.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Marcar como leído cualquier respuesta no leída
        $this->mark_replies_as_read($message_id, $user_id);
        
        // Incluir template
        include VDP_PLUGIN_DIR . 'templates/client-message-view.php';
    }
    
    /**
     * Get messages for a client user
     */
    public function get_client_messages($user_id, $limit = 10, $page = 1) {
        global $wpdb;
        
        $offset = ($page - 1) * $limit;
        $messages = array();
        
        // Verificar si la tabla existe
        $table_messages = $wpdb->prefix . 'vdp_messages';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_messages}'") === $table_messages;
        
        if (!$table_exists) {
            return $messages;
        }
        
        // Obtener mensajes donde el usuario es el remitente
        $query = $wpdb->prepare(
            "SELECT m.*, 
                   COUNT(r.id) > 0 AS has_response,
                   v.post_title AS vendor_name,
                   p.post_title AS listing_title
            FROM {$table_messages} m
            LEFT JOIN {$wpdb->prefix}vdp_message_replies r ON m.id = r.message_id
            LEFT JOIN {$wpdb->posts} v ON m.vendor_id = v.ID
            LEFT JOIN {$wpdb->posts} p ON m.listing_id = p.ID
            WHERE m.sender_id = %d
            AND m.is_archived = 0
            GROUP BY m.id
            ORDER BY m.date_created DESC
            LIMIT %d OFFSET %d",
            $user_id, $limit, $offset
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // Formatear los mensajes
        if (!empty($results)) {
            foreach ($results as $result) {
                $vendor_avatar = get_avatar_url($result['vendor_id'], array('size' => 96));
                
                $messages[] = array(
                    'id' => $result['id'],
                    'vendor_id' => $result['vendor_id'],
                    'vendor_name' => $result['vendor_name'],
                    'vendor_avatar' => $vendor_avatar,
                    'subject' => $result['subject'],
                    'content' => $result['content'],
                    'date' => $result['date_created'],
                    'listing_id' => $result['listing_id'],
                    'listing_title' => $result['listing_title'],
                    'has_response' => (bool) $result['has_response'],
                );
            }
        }
        
        return $messages;
    }
    
    /**
     * Get a specific message for a client user with its replies
     */
    public function get_client_message($message_id, $user_id) {
        global $wpdb;
        
        // Verificar si las tablas existen
        $table_messages = $wpdb->prefix . 'vdp_messages';
        $table_replies = $wpdb->prefix . 'vdp_message_replies';
        
        $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$table_messages}'") === $table_messages && 
                      $wpdb->get_var("SHOW TABLES LIKE '{$table_replies}'") === $table_replies;
        
        if (!$tables_exist) {
            return null;
        }
        
        // Obtener el mensaje principal
        $query = $wpdb->prepare(
            "SELECT m.*, 
                   v.post_title AS vendor_name,
                   p.post_title AS listing_title
            FROM {$table_messages} m
            LEFT JOIN {$wpdb->posts} v ON m.vendor_id = v.ID
            LEFT JOIN {$wpdb->posts} p ON m.listing_id = p.ID
            WHERE m.id = %d AND m.sender_id = %d",
            $message_id, $user_id
        );
        
        $message = $wpdb->get_row($query, ARRAY_A);
        
        if (!$message) {
            return null;
        }
        
        // Obtener avatar del vendedor
        $vendor_avatar = get_avatar_url($message['vendor_id'], array('size' => 96));
        
        // Obtener las respuestas
        $replies_query = $wpdb->prepare(
            "SELECT r.*, u.display_name AS name
            FROM {$table_replies} r
            LEFT JOIN {$wpdb->users} u ON r.sender_id = u.ID
            WHERE r.message_id = %d
            ORDER BY r.date_created ASC",
            $message_id
        );
        
        $replies_results = $wpdb->get_results($replies_query, ARRAY_A);
        
        // Formatear las respuestas
        $replies = array();
        
        if (!empty($replies_results)) {
            foreach ($replies_results as $reply) {
                $avatar = get_avatar_url($reply['sender_id'], array('size' => 96));
                
                $replies[] = array(
                    'id' => $reply['id'],
                    'content' => $reply['content'],
                    'date' => $reply['date_created'],
                    'is_vendor' => (bool) $reply['is_vendor'],
                    'name' => $reply['name'],
                    'avatar' => $avatar,
                    'is_read' => (bool) $reply['is_read'],
                );
            }
        }
        
        // Formatear el mensaje completo
        $formatted_message = array(
            'id' => $message['id'],
            'vendor_id' => $message['vendor_id'],
            'vendor_name' => $message['vendor_name'],
            'vendor_avatar' => $vendor_avatar,
            'subject' => $message['subject'],
            'content' => $message['content'],
            'date' => $message['date_created'],
            'listing_id' => $message['listing_id'],
            'listing_title' => $message['listing_title'],
            'has_response' => !empty($replies),
            'replies' => $replies,
        );
        
        return $formatted_message;
    }
    
    /**
     * Mark replies as read for a specific message
     */
    private function mark_replies_as_read($message_id, $user_id) {
        global $wpdb;
        
        $table_replies = $wpdb->prefix . 'vdp_message_replies';
        
        // Verificar si la tabla existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_replies}'") === $table_replies;
        
        if (!$table_exists) {
            return false;
        }
        
        // Marcar como leídas todas las respuestas del vendedor
        $result = $wpdb->update(
            $table_replies,
            array('is_read' => 1),
            array(
                'message_id' => $message_id,
                'is_vendor' => 1, // Solo las respuestas del vendedor
                'is_read' => 0, // Solo las no leídas
            ),
            array('%d'),
            array('%d', '%d', '%d')
        );
        
        return $result !== false;
    }
    
    /**
     * AJAX callback to get client messages
     */
    public function ajax_get_messages() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vdp-ajax-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
        }
        
        // Verificar usuario logueado
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to view messages.', 'vendor-dashboard-pro')));
        }
        
        // Obtener usuario actual
        $user_id = get_current_user_id();
        
        // Obtener parámetros
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        
        // Obtener mensajes
        $messages = $this->get_client_messages($user_id, $limit, $page);
        
        // Enviar respuesta
        wp_send_json_success(array(
            'messages' => $messages,
        ));
    }
    
    /**
     * AJAX callback to get a specific client message
     */
    public function ajax_get_message() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vdp-ajax-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
        }
        
        // Verificar usuario logueado
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to view messages.', 'vendor-dashboard-pro')));
        }
        
        // Obtener parámetros
        $message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
        
        if (empty($message_id)) {
            wp_send_json_error(array('message' => __('Invalid message ID.', 'vendor-dashboard-pro')));
        }
        
        // Obtener usuario actual
        $user_id = get_current_user_id();
        
        // Obtener mensaje
        $message = $this->get_client_message($message_id, $user_id);
        
        if (!$message) {
            wp_send_json_error(array('message' => __('Message not found or you do not have permission to view it.', 'vendor-dashboard-pro')));
        }
        
        // Marcar respuestas como leídas
        $this->mark_replies_as_read($message_id, $user_id);
        
        // Enviar respuesta
        wp_send_json_success(array(
            'message' => $message,
        ));
    }
    
    /**
     * AJAX callback to send a reply to a vendor
     */
    public function ajax_send_reply() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vdp-ajax-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
        }
        
        // Verificar usuario logueado
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to send messages.', 'vendor-dashboard-pro')));
        }
        
        // Obtener parámetros
        $message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';
        
        if (empty($message_id) || empty($content)) {
            wp_send_json_error(array('message' => __('Message ID and content are required.', 'vendor-dashboard-pro')));
        }
        
        // Obtener usuario actual
        $user_id = get_current_user_id();
        
        // Verificar que el mensaje pertenece al usuario
        global $wpdb;
        $table_messages = $wpdb->prefix . 'vdp_messages';
        
        $message = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_messages} WHERE id = %d AND sender_id = %d",
            $message_id, $user_id
        ));
        
        if (!$message) {
            wp_send_json_error(array('message' => __('Message not found or you do not have permission to reply.', 'vendor-dashboard-pro')));
        }
        
        // Insertar respuesta
        $table_replies = $wpdb->prefix . 'vdp_message_replies';
        
        $result = $wpdb->insert(
            $table_replies,
            array(
                'message_id' => $message_id,
                'sender_id' => $user_id,
                'is_vendor' => 0, // 0 = cliente (no es vendedor)
                'content' => $content,
                'date_created' => current_time('mysql'),
                'is_read' => 0,
            ),
            array('%d', '%d', '%d', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to send reply. Please try again.', 'vendor-dashboard-pro')));
        }
        
        // Obtener información del usuario para la respuesta
        $user = get_userdata($user_id);
        $user_name = $user ? $user->display_name : __('Customer', 'vendor-dashboard-pro');
        $user_avatar = get_avatar_url($user_id, array('size' => 96));
        
        // Devolver éxito
        wp_send_json_success(array(
            'reply_id' => $wpdb->insert_id,
            'message' => __('Your reply has been sent!', 'vendor-dashboard-pro'),
            'reply' => array(
                'id' => $wpdb->insert_id,
                'content' => $content,
                'date' => current_time('mysql'),
                'is_vendor' => false,
                'name' => $user_name,
                'avatar' => $user_avatar,
                'formatted_date' => vdp_format_date(current_time('mysql')) . ' ' . 
                    date_i18n(get_option('time_format'), current_time('timestamp'))
            )
        ));
    }
}

// Inicializar la clase
VDP_Client_Messages::instance();