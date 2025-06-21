<?php
/**
 * Contact modal template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-contact-modal" id="vdp-contact-modal">
    <div class="vdp-modal-content">
        <div class="vdp-modal-header">
            <h3 class="vdp-modal-title"><?php esc_html_e('Contact the Vendor', 'vendor-dashboard-pro'); ?></h3>
            <button type="button" class="vdp-modal-close" id="vdp-modal-close">&times;</button>
        </div>
        
        <div class="vdp-modal-body">
            <div id="vdp-contact-form-messages"></div>
            
            <form id="vdp-contact-form" method="post">
                <div class="vdp-form-group">
                    <label for="vdp-contact-subject" class="vdp-field-label">
                        <?php esc_html_e('Subject', 'vendor-dashboard-pro'); ?>
                    </label>
                    <input type="text" id="vdp-contact-subject" name="subject" class="vdp-form-control" required>
                </div>
                
                <div class="vdp-form-group">
                    <label for="vdp-contact-message" class="vdp-field-label">
                        <?php esc_html_e('Message', 'vendor-dashboard-pro'); ?>
                    </label>
                    <textarea id="vdp-contact-message" name="message" class="vdp-form-control" rows="5" required></textarea>
                </div>
                
                <input type="hidden" id="vdp-contact-listing-id" name="listing_id" value="">
                <input type="hidden" id="vdp-contact-vendor-id" name="vendor_id" value="">
            </form>
        </div>
        
        <div class="vdp-modal-footer">
            <button type="button" class="vdp-btn vdp-btn-text" id="vdp-modal-cancel">
                <?php esc_html_e('Cancel', 'vendor-dashboard-pro'); ?>
            </button>
            <button type="button" class="vdp-btn vdp-btn-primary" id="vdp-send-message-btn">
                <?php esc_html_e('Send Message', 'vendor-dashboard-pro'); ?>
            </button>
        </div>
    </div>
</div>