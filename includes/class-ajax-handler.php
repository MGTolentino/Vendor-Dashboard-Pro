<?php
/**
 * Ajax Handler Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax Handler class for processing Ajax requests.
 */
class VDP_Ajax_Handler {
    /**
     * Initialize Ajax handler.
     */
    public static function init() {
        add_action('wp_ajax_vdp_save_listing', array(__CLASS__, 'save_listing'));
        add_action('wp_ajax_vdp_delete_listing', array(__CLASS__, 'delete_listing'));
        add_action('wp_ajax_vdp_get_dashboard_data', array(__CLASS__, 'get_dashboard_data'));
        add_action('wp_ajax_vdp_save_vendor_settings', array(__CLASS__, 'save_vendor_settings'));
        add_action('wp_ajax_vdp_save_settings', array(__CLASS__, 'save_settings'));
        add_action('wp_ajax_vdp_get_chart_data', array(__CLASS__, 'get_chart_data'));
        add_action('wp_ajax_vdp_trigger_listing_form', array(__CLASS__, 'trigger_listing_form'));
        
        add_action('wp_ajax_vdp_send_message', array(__CLASS__, 'send_message'));
        add_action('wp_ajax_vdp_mark_message_read', array(__CLASS__, 'mark_message_read'));
        add_action('wp_ajax_vdp_archive_message', array(__CLASS__, 'archive_message'));
        add_action('wp_ajax_vdp_send_reply', array(__CLASS__, 'send_reply'));
        add_action('wp_ajax_vdp_upload_pdf_header', array(__CLASS__, 'upload_pdf_header'));
        add_action('wp_ajax_vdp_remove_pdf_header', array(__CLASS__, 'remove_pdf_header'));
    }

    /**
     * Verify Ajax request.
     *
     * @param string $nonce_action Nonce action.
     * @return bool
     */
    private static function verify_ajax_request($nonce_action = 'vdp-ajax-nonce') {
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to perform this action.', 'vendor-dashboard-pro')));
            return false;
        }
        
        if (!check_ajax_referer($nonce_action, 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
            return false;
        }
        
        // Check if user is a vendor
        if (!vdp_is_user_vendor()) {
            wp_send_json_error(array('message' => __('You must be a vendor to perform this action.', 'vendor-dashboard-pro')));
            return false;
        }
        
        return true;
    }

    /**
     * Save listing Ajax handler.
     */
    public static function save_listing() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get listing data
        $listing_data = isset($_POST['listing_data']) ? $_POST['listing_data'] : array();
        $listing_id = isset($_POST['listing_id']) ? absint($_POST['listing_id']) : 0;
        
        // Validate required fields
        if (empty($listing_data['title'])) {
            wp_send_json_error(array('message' => __('Title is required.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Sanitize data
        $sanitized_data = array();
        
        // Basic fields
        $sanitized_data['title'] = sanitize_text_field($listing_data['title']);
        $sanitized_data['description'] = wp_kses_post($listing_data['description']);
        $sanitized_data['price'] = floatval($listing_data['price']);
        $sanitized_data['featured'] = !empty($listing_data['featured']);
        
        // Categories
        if (!empty($listing_data['categories'])) {
            $sanitized_data['categories'] = array_map('absint', (array) $listing_data['categories']);
        }
        
        // Save listing
        $result = vdp_api()->save_listing($sanitized_data, $listing_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success(array(
            'listing_id' => $result,
            'message' => __('Listing saved successfully.', 'vendor-dashboard-pro'),
            'redirect' => vdp_get_dashboard_url('products'),
        ));
    }

    /**
     * Delete listing Ajax handler.
     */
    public static function delete_listing() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get listing ID
        $listing_id = isset($_POST['listing_id']) ? absint($_POST['listing_id']) : 0;
        
        if (empty($listing_id)) {
            wp_send_json_error(array('message' => __('Invalid listing ID.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Delete listing
        $result = vdp_api()->delete_listing($listing_id);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to delete listing.', 'vendor-dashboard-pro')));
            return;
        }
        
        wp_send_json_success(array(
            'message' => __('Listing deleted successfully.', 'vendor-dashboard-pro'),
        ));
    }

    /**
     * Get dashboard data Ajax handler.
     */
    public static function get_dashboard_data() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get vendor statistics
        $statistics = vdp_api()->get_vendor_statistics($vendor->get_id());
        
        // Get recent listings
        $recent_listings = vdp_api()->get_vendor_listings($vendor->get_id(), array(
            'limit' => 5,
        ));
        
        // Format listings for response
        $formatted_listings = array();
        
        foreach ($recent_listings as $listing) {
            $formatted_listings[] = array(
                'id' => $listing->get_id(),
                'title' => $listing->get_title(),
                'price' => $listing->get_price(),
                'featured' => $listing->is_featured(),
                'image' => $listing->get_image__url('thumbnail'),
                'url' => get_permalink($listing->get_id()),
                'edit_url' => vdp_get_dashboard_url('products/edit/' . $listing->get_id()),
            );
        }
        
        // Get recent messages
        $recent_messages = vdp_api()->get_vendor_messages($vendor->get_id(), array(
            'limit' => 5,
        ));
        
        // Format messages for response
        $formatted_messages = array();
        
        foreach ($recent_messages as $message) {
            $formatted_messages[] = array(
                'id' => $message->id,
                'sender' => $message->sender,
                'content' => $message->content,
                'date' => vdp_time_ago($message->date),
                'is_read' => $message->is_read,
                'listing_title' => $message->listing_title,
                'view_url' => vdp_get_dashboard_url('messages/view/' . $message->id),
            );
        }
        
        // Send response
        wp_send_json_success(array(
            'statistics' => $statistics,
            'recent_listings' => $formatted_listings,
            'recent_messages' => $formatted_messages,
        ));
    }

    /**
     * Save vendor settings Ajax handler.
     */
    public static function save_vendor_settings() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get vendor data
        $vendor_data = isset($_POST['vendor_data']) ? $_POST['vendor_data'] : array();
        
        // Validate required fields
        if (empty($vendor_data['name'])) {
            wp_send_json_error(array('message' => __('Name is required.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Sanitize data
        $sanitized_data = array();
        
        // Basic fields
        $sanitized_data['name'] = sanitize_text_field($vendor_data['name']);
        $sanitized_data['description'] = wp_kses_post($vendor_data['description']);
        
        // Save vendor profile
        $result = vdp_api()->update_vendor_profile($sanitized_data);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to update vendor profile.', 'vendor-dashboard-pro')));
            return;
        }
        
        wp_send_json_success(array(
            'message' => __('Vendor profile updated successfully.', 'vendor-dashboard-pro'),
        ));
    }

    /**
     * Get chart data Ajax handler.
     */
    public static function get_chart_data() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get parameters
        $metric = isset($_POST['metric']) ? sanitize_key($_POST['metric']) : 'sales';
        $period = isset($_POST['period']) ? sanitize_key($_POST['period']) : '30days';
        
        // Determine number of days based on period
        switch ($period) {
            case '7days':
                $days = 7;
                break;
                
            case '90days':
                $days = 90;
                break;
                
            case '30days':
            default:
                $days = 30;
                break;
        }
        
        // Get chart data
        $chart_data = vdp_get_demo_chart_data($metric, $days);
        
        wp_send_json_success(array(
            'chart_data' => $chart_data,
        ));
    }
    
    /**
     * Trigger HivePress listing form Ajax handler.
     */
    public static function trigger_listing_form() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Try to get HivePress listing submit URL
        $form_url = '';
        
        if (function_exists('hivepress')) {
            try {
                // Try different possible page names for listing submission
                $possible_pages = array('listing_submit_page', 'listing_add_page', 'submit_listing_page');
                
                foreach ($possible_pages as $page_name) {
                    try {
                        $form_url = hivepress()->router->get_url($page_name);
                        if (!empty($form_url)) {
                            break;
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }
            } catch (Exception $e) {
                $form_url = '';
            }
        }
        
        // If still no URL, try to find listing submit page in WordPress
        if (empty($form_url)) {
            $submit_page = get_option('hp_listing_submit_page');
            if ($submit_page) {
                $form_url = get_permalink($submit_page);
            }
        }
        
        if (!empty($form_url)) {
            wp_send_json_success(array(
                'form_url' => $form_url,
                'message' => __('Redirecting to listing form...', 'vendor-dashboard-pro'),
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Listing form not available.', 'vendor-dashboard-pro'),
            ));
        }
    }
    
    /**
     * Send message Ajax handler.
     */
    public static function send_message() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to send messages.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Verify nonce
        if (!check_ajax_referer('vdp-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get form data
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $message = isset($_POST['message']) ? wp_kses_post($_POST['message']) : '';
        $listing_id = isset($_POST['listing_id']) ? absint($_POST['listing_id']) : 0;
        $vendor_id = isset($_POST['vendor_id']) ? absint($_POST['vendor_id']) : 0;
        
        // Validate required fields
        if (empty($subject) || empty($message) || empty($listing_id) || empty($vendor_id)) {
            wp_send_json_error(array('message' => __('All fields are required.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Validate listing and vendor
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'hp_listing') {
            wp_send_json_error(array('message' => __('Invalid listing.', 'vendor-dashboard-pro')));
            return;
        }
        
        $vendor = get_post($vendor_id);
        if (!$vendor || $vendor->post_type !== 'hp_vendor') {
            wp_send_json_error(array('message' => __('Invalid vendor.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get current user ID
        $sender_id = get_current_user_id();
        
        // Insert message into database
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vdp_messages';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'vendor_id' => $vendor_id,
                'sender_id' => $sender_id,
                'listing_id' => $listing_id,
                'subject' => $subject,
                'content' => $message,
                'date_created' => current_time('mysql'),
                'is_read' => 0,
                'is_archived' => 0,
            ),
            array('%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to send message. Please try again.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get the message ID
        $message_id = $wpdb->insert_id;
        
        // Get vendor user ID for notification (if needed)
        $vendor_user_id = get_post_field('post_author', $vendor_id);
        
        // Send notification email to vendor
        $sender_user = get_userdata($sender_id);
        $vendor_user = get_userdata($vendor_user_id);
        
        
        if ($sender_user && $vendor_user && !empty($vendor_user->user_email)) {
            $sender_name = $sender_user->display_name ?: $sender_user->user_login;
            $sender_email = $sender_user->user_email;
            $vendor_email = $vendor_user->user_email;
            $vendor_name = $vendor_user->display_name ?: $vendor_user->user_login;
            
            
            // Email subject
            $email_subject = sprintf(
                __('[%s] New message about: %s', 'vendor-dashboard-pro'),
                get_bloginfo('name'),
                $listing->post_title
            );
            
            // Email content
            $email_message = sprintf(
                __("Hello %s,\n\nYou have received a new message about your listing \"%s\".\n\nFrom: %s (%s)\nSubject: %s\n\nMessage:\n%s\n\nYou can view and respond to this message in your vendor dashboard:\n%s\n\nBest regards,\n%s", 'vendor-dashboard-pro'),
                $vendor_name,
                $listing->post_title,
                $sender_name,
                $sender_email,
                $subject,
                $message,
                vdp_get_dashboard_url('messages'),
                get_bloginfo('name')
            );
            
            // Email headers
            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . sanitize_text_field($sender_name) . ' <' . sanitize_email($sender_email) . '>'
            );
            
            
            // Send email
            $mail_result = wp_mail($vendor_email, $email_subject, $email_message, $headers);
        }
        
        wp_send_json_success(array(
            'message_id' => $message_id,
            'message' => __('Your message has been sent!', 'vendor-dashboard-pro'),
        ));
    }
    
    /**
     * Mark message as read Ajax handler.
     */
    public static function mark_message_read() {
        // Verify request
        if (!self::verify_ajax_request('vdp-ajax-nonce')) {
            return;
        }
        
        // Get message ID
        $message_id = isset($_POST['message_id']) ? absint($_POST['message_id']) : 0;
        
        if (empty($message_id)) {
            wp_send_json_error(array('message' => __('Invalid message ID.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get current vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor || !method_exists($vendor, 'get_id')) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
            return;
        }
        
        $vendor_id = $vendor->get_id();
        
        // Update message status
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vdp_messages';
        
        $result = $wpdb->update(
            $table_name,
            array('is_read' => 1),
            array('id' => $message_id, 'vendor_id' => $vendor_id),
            array('%d'),
            array('%d', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to mark message as read.', 'vendor-dashboard-pro')));
            return;
        }
        
        wp_send_json_success(array(
            'message' => __('Message marked as read.', 'vendor-dashboard-pro'),
        ));
    }
    
    /**
     * Archive message Ajax handler.
     */
    public static function archive_message() {
        // Verify request
        if (!self::verify_ajax_request('vdp-ajax-nonce')) {
            return;
        }
        
        // Get message ID
        $message_id = isset($_POST['message_id']) ? absint($_POST['message_id']) : 0;
        
        if (empty($message_id)) {
            wp_send_json_error(array('message' => __('Invalid message ID.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get current vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor || !method_exists($vendor, 'get_id')) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
            return;
        }
        
        $vendor_id = $vendor->get_id();
        
        // Update message status
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vdp_messages';
        
        $result = $wpdb->update(
            $table_name,
            array('is_archived' => 1),
            array('id' => $message_id, 'vendor_id' => $vendor_id),
            array('%d'),
            array('%d', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to archive message.', 'vendor-dashboard-pro')));
            return;
        }
        
        wp_send_json_success(array(
            'message' => __('Message archived.', 'vendor-dashboard-pro'),
        ));
    }
    
    /**
     * Send reply Ajax handler.
     */
    public static function send_reply() {
        // Verify request
        if (!self::verify_ajax_request('vdp-ajax-nonce')) {
            return;
        }
        
        // Get form data
        $message_id = isset($_POST['message_id']) ? absint($_POST['message_id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        
        // Validate required fields
        if (empty($message_id) || empty($content)) {
            wp_send_json_error(array('message' => __('All fields are required.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get current vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor || !method_exists($vendor, 'get_id')) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
            return;
        }
        
        $vendor_id = $vendor->get_id();
        $user_id = get_current_user_id();
        
        // Verify that the message belongs to the vendor
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'vdp_messages';
        
        $message = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_messages} WHERE id = %d AND vendor_id = %d",
            $message_id,
            $vendor_id
        ));
        
        if (!$message) {
            wp_send_json_error(array('message' => __('Message not found or you do not have permission to reply.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Insert reply into database
        $table_replies = $wpdb->prefix . 'vdp_message_replies';
        
        $result = $wpdb->insert(
            $table_replies,
            array(
                'message_id' => $message_id,
                'sender_id' => $user_id,
                'is_vendor' => 1, // 1 = vendor, 0 = customer
                'content' => $content,
                'date_created' => current_time('mysql'),
                'is_read' => 0,
            ),
            array('%d', '%d', '%d', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to send reply. Please try again.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Get the reply ID
        $reply_id = $wpdb->insert_id;
        
        // Update message to mark as having a response
        $wpdb->update(
            $table_messages,
            array('is_read' => 1), // También marcamos el mensaje como leído
            array('id' => $message_id),
            array('%d'),
            array('%d')
        );
        
        // Send notification email to customer
        $customer_user = get_userdata($message->sender_id);
        $vendor_user = get_userdata($user_id);
        
        
        if ($customer_user && $vendor_user && !empty($customer_user->user_email)) {
            $customer_name = $customer_user->display_name ?: $customer_user->user_login;
            $customer_email = $customer_user->user_email;
            $vendor_name = $vendor_user->display_name ?: $vendor_user->user_login;
            $vendor_email = $vendor_user->user_email;
            
            
            // Get listing info
            $listing = get_post($message->listing_id);
            $listing_title = $listing ? $listing->post_title : 'Unknown Listing';
            
            // Email subject
            $email_subject = sprintf(
                __('[%s] Reply to your message about: %s', 'vendor-dashboard-pro'),
                get_bloginfo('name'),
                $listing_title
            );
            
            // Email content
            $email_message = sprintf(
                __("Hello %s,\n\n%s has replied to your message about \"%s\".\n\nOriginal Subject: %s\n\nReply:\n%s\n\nYou can view the full conversation or send another message by visiting:\n%s\n\nBest regards,\n%s", 'vendor-dashboard-pro'),
                $customer_name,
                $vendor_name,
                $listing_title,
                $message->subject,
                $content,
                get_permalink($message->listing_id),
                get_bloginfo('name')
            );
            
            // Email headers
            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . $vendor_name . ' <' . $vendor_email . '>'
            );
            
            
            // Send email
            $mail_result = wp_mail($customer_email, $email_subject, $email_message, $headers);
            
            // Send email
            $mail_result = wp_mail($customer_email, $email_subject, $email_message, $headers);
        }
        
        wp_send_json_success(array(
            'reply_id' => $reply_id,
            'message' => __('Your reply has been sent!', 'vendor-dashboard-pro'),
            'date' => current_time('mysql'),
        ));
    }

    /**
     * Upload PDF header image Ajax handler.
     */
    public static function upload_pdf_header() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['pdf_header_image']) || $_FILES['pdf_header_image']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => __('No file uploaded or upload failed.', 'vendor-dashboard-pro')));
            return;
        }

        $file = $_FILES['pdf_header_image'];

        // Validate file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/jpg');
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(array('message' => __('Only JPG, JPEG, and PNG files are allowed.', 'vendor-dashboard-pro')));
            return;
        }

        // Validate file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            wp_send_json_error(array('message' => __('File size must not exceed 2MB.', 'vendor-dashboard-pro')));
            return;
        }

        // Get current vendor
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
            return;
        }

        // Include WordPress file functions
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        // Set upload overrides
        $upload_overrides = array(
            'test_form' => false,
            'unique_filename_callback' => function($dir, $name, $ext) use ($vendor) {
                return 'pdf-header-vendor-' . $vendor->get_id() . '-' . time() . $ext;
            }
        );

        // Upload file
        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            // Create attachment
            $attachment = array(
                'post_mime_type' => $movefile['type'],
                'post_title'     => sanitize_file_name($file['name']),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $movefile['file']);

            if (!is_wp_error($attach_id)) {
                // Generate metadata
                if (!function_exists('wp_generate_attachment_metadata')) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                }
                $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);

                // Remove old PDF header if exists
                $old_header_id = get_post_meta($vendor->get_id(), 'pdf_header_image_id', true);
                if ($old_header_id) {
                    wp_delete_attachment($old_header_id, true);
                }

                // Save attachment ID to vendor meta
                update_post_meta($vendor->get_id(), 'pdf_header_image_id', $attach_id);
                update_post_meta($vendor->get_id(), 'pdf_header_image_url', $movefile['url']);

                wp_send_json_success(array(
                    'message' => __('PDF header image uploaded successfully.', 'vendor-dashboard-pro'),
                    'image_id' => $attach_id,
                    'image_url' => $movefile['url'],
                ));
            } else {
                wp_send_json_error(array('message' => __('Failed to create attachment.', 'vendor-dashboard-pro')));
            }
        } else {
            wp_send_json_error(array('message' => $movefile['error']));
        }
    }

    /**
     * Remove PDF header image Ajax handler.
     */
    public static function remove_pdf_header() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }

        // Get current vendor
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
            return;
        }

        // Get current PDF header image
        $header_id = get_post_meta($vendor->get_id(), 'pdf_header_image_id', true);
        
        if ($header_id) {
            // Delete attachment
            wp_delete_attachment($header_id, true);
            
            // Remove meta data
            delete_post_meta($vendor->get_id(), 'pdf_header_image_id');
            delete_post_meta($vendor->get_id(), 'pdf_header_image_url');
        }

        wp_send_json_success(array(
            'message' => __('PDF header image removed successfully.', 'vendor-dashboard-pro'),
        ));
    }
    
    /**
     * Save settings Ajax handler.
     */
    public static function save_settings() {
        // Verify request
        if (!self::verify_ajax_request()) {
            return;
        }
        
        // Get current vendor
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
            return;
        }
        
        // Handle store settings
        if (isset($_POST['store_name'])) {
            wp_update_post(array(
                'ID' => $vendor->get_id(),
                'post_title' => sanitize_text_field($_POST['store_name'])
            ));
        }
        
        if (isset($_POST['store_slug'])) {
            wp_update_post(array(
                'ID' => $vendor->get_id(),
                'post_name' => sanitize_title($_POST['store_slug'])
            ));
        }
        
        if (isset($_POST['store_description'])) {
            wp_update_post(array(
                'ID' => $vendor->get_id(),
                'post_content' => wp_kses_post($_POST['store_description'])
            ));
        }
        
        // Handle meta fields
        $meta_fields = array(
            'store_email' => 'email',
            'store_phone' => 'text',
            'store_website' => 'url',
            'social_facebook' => 'url',
            'social_instagram' => 'url', 
            'social_twitter' => 'url',
            'social_youtube' => 'url'
        );
        
        foreach ($meta_fields as $field => $type) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                
                switch ($type) {
                    case 'email':
                        $value = sanitize_email($value);
                        break;
                    case 'url':
                        $value = esc_url_raw($value);
                        break;
                    default:
                        $value = sanitize_text_field($value);
                        break;
                }
                
                update_post_meta($vendor->get_id(), $field, $value);
            }
        }
        
        // Handle file uploads
        if (!empty($_FILES)) {
            foreach ($_FILES as $field_name => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $upload = wp_handle_upload($file, array('test_form' => false));
                    
                    if (!isset($upload['error'])) {
                        // Resize image for PDF headers to match Event Quote Cart dimensions (2463x525)
                        $resized_file = $upload['file'];
                        if ($field_name === 'store_banner' || strpos($field_name, 'pdf') !== false || strpos($field_name, 'banner') !== false) {
                            $resized_file = self::resize_image_for_pdf($upload['file'], 2463, 525);
                            if ($resized_file) {
                                $upload['file'] = $resized_file;
                                $upload['url'] = str_replace(basename($upload['url']), basename($resized_file), $upload['url']);
                            }
                        }
                        
                        $attachment_id = wp_insert_attachment(array(
                            'post_mime_type' => $upload['type'],
                            'post_title' => sanitize_file_name($file['name']),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        ), $upload['file'], $vendor->get_id());
                        
                        if (!is_wp_error($attachment_id)) {
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
                            
                            update_post_meta($vendor->get_id(), $field_name . '_id', $attachment_id);
                            update_post_meta($vendor->get_id(), $field_name . '_url', $upload['url']);
                        }
                    }
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => __('Settings saved successfully.', 'vendor-dashboard-pro')
        ));
    }
    
    /**
     * Resize image for PDF headers to match Event Quote Cart dimensions.
     *
     * @param string $file_path Path to the uploaded file.
     * @param int    $width     Target width.
     * @param int    $height    Target height.
     * @return string|false Path to resized file or false on failure.
     */
    private static function resize_image_for_pdf($file_path, $width, $height) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Get image info
        $image_info = getimagesize($file_path);
        if (!$image_info) {
            return false;
        }
        
        $mime_type = $image_info['mime'];
        
        // Create image resource from file
        switch ($mime_type) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $source = imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($file_path);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Get current dimensions
        $current_width = imagesx($source);
        $current_height = imagesy($source);
        
        // Skip if already the right dimensions
        if ($current_width == $width && $current_height == $height) {
            imagedestroy($source);
            return $file_path;
        }
        
        // Create new image with target dimensions
        $destination = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG
        if ($mime_type === 'image/png') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefill($destination, 0, 0, $transparent);
        }
        
        // Resize image (this will stretch to fit exact dimensions)
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $width, $height, $current_width, $current_height);
        
        // Generate new filename
        $path_info = pathinfo($file_path);
        $new_file_path = $path_info['dirname'] . '/' . $path_info['filename'] . '-resized.' . $path_info['extension'];
        
        // Save resized image
        $saved = false;
        switch ($mime_type) {
            case 'image/jpeg':
                $saved = imagejpeg($destination, $new_file_path, 90);
                break;
            case 'image/png':
                $saved = imagepng($destination, $new_file_path, 6);
                break;
            case 'image/gif':
                $saved = imagegif($destination, $new_file_path);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($destination);
        
        if ($saved) {
            // Remove original file
            unlink($file_path);
            return $new_file_path;
        }
        
        return false;
    }
}