<?php
/**
 * Installer Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Installer class for creating database tables and initial setup.
 */
class VDP_Installer {
    /**
     * Database version.
     *
     * @var string
     */
    private static $db_version = '1.0';

    /**
     * Install database tables and set up the plugin.
     */
    public static function install() {
        self::create_tables();
        self::update_version();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables.
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Messages table
        $table_messages = $wpdb->prefix . 'vdp_messages';
        
        $sql_messages = "CREATE TABLE {$table_messages} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            vendor_id BIGINT(20) UNSIGNED NOT NULL,
            sender_id BIGINT(20) UNSIGNED NOT NULL,
            listing_id BIGINT(20) UNSIGNED NOT NULL,
            subject VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            date_created DATETIME NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            is_archived TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id),
            KEY sender_id (sender_id),
            KEY listing_id (listing_id)
        ) $charset_collate;";
        
        // Message replies table
        $table_replies = $wpdb->prefix . 'vdp_message_replies';
        
        $sql_replies = "CREATE TABLE {$table_replies} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            message_id BIGINT(20) UNSIGNED NOT NULL,
            sender_id BIGINT(20) UNSIGNED NOT NULL,
            is_vendor TINYINT(1) NOT NULL DEFAULT 0,
            content TEXT NOT NULL,
            date_created DATETIME NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY message_id (message_id),
            KEY sender_id (sender_id)
        ) $charset_collate;";
        
        // We need to include upgrade.php to use dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create the tables
        dbDelta($sql_messages);
        dbDelta($sql_replies);
    }
    
    /**
     * Update database version.
     */
    private static function update_version() {
        update_option('vdp_db_version', self::$db_version);
    }
    
    /**
     * Check if database update is required.
     *
     * @return bool
     */
    public static function needs_db_update() {
        $current_db_version = get_option('vdp_db_version', '0');
        return version_compare($current_db_version, self::$db_version, '<');
    }
}