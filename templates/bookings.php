<?php
/**
 * Bookings Template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Check if HivePress Bookings is active
if (!class_exists('HivePress\\Extensions\\Bookings\\Bookings') && !function_exists('hivepress')) {
    ?>
    <div class="vdp-notice vdp-notice-warning">
        <p><?php esc_html_e('El plugin HivePress Bookings es requerido para usar esta funcionalidad.', 'vendor-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

// Trigger the bookings content hook
do_action('vdp_bookings_content');
?>