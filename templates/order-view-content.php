<?php
/**
 * Order view content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-order-view-content">
    <?php if (!$order) : ?>
        <div class="vdp-notice vdp-notice-error">
            <p><?php esc_html_e('Order not found or you do not have permission to view it.', 'vendor-dashboard-pro'); ?></p>
        </div>
    <?php else : ?>
        <!-- Order Details Header -->
        <div class="vdp-section vdp-order-header-section">
            <div class="vdp-order-header">
                <div class="vdp-order-header-meta">
                    <div class="vdp-order-number">
                        <?php esc_html_e('Order', 'vendor-dashboard-pro'); ?> #<?php echo esc_html($order['order_number']); ?>
                    </div>
                    <div class="vdp-order-date">
                        <?php echo esc_html(vdp_format_date($order['date'])); ?> <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($order['date']))); ?>
                    </div>
                </div>
                <div class="vdp-order-status-large">
                    <span class="vdp-status-badge <?php echo esc_attr(VDP_Orders::get_status_class($order['status'])); ?>">
                        <?php echo esc_html(VDP_Orders::get_status_label($order['status'])); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="vdp-section vdp-order-details-section">
            <div class="vdp-order-grid">
                <div class="vdp-order-customer-col">
                    <div class="vdp-info-box">
                        <div class="vdp-info-box-header">
                            <h3 class="vdp-info-box-title"><?php esc_html_e('Customer Information', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        <div class="vdp-info-box-content">
                            <div class="vdp-customer-info">
                                <div class="vdp-customer-name">
                                    <i class="fas fa-user"></i> <?php echo esc_html($order['customer_name']); ?>
                                </div>
                                <div class="vdp-customer-email">
                                    <i class="fas fa-envelope"></i> <?php echo esc_html($order['customer_email']); ?>
                                </div>
                                <?php if (!empty($order['customer_phone'])) : ?>
                                    <div class="vdp-customer-phone">
                                        <i class="fas fa-phone"></i> <?php echo esc_html($order['customer_phone']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vdp-order-shipping-col">
                    <div class="vdp-info-box">
                        <div class="vdp-info-box-header">
                            <h3 class="vdp-info-box-title"><?php esc_html_e('Shipping Address', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        <div class="vdp-info-box-content">
                            <?php if (!empty($order['shipping_address'])) : ?>
                                <div class="vdp-address">
                                    <p><?php echo nl2br(esc_html($order['shipping_address'])); ?></p>
                                </div>
                            <?php else : ?>
                                <div class="vdp-no-shipping">
                                    <p><?php esc_html_e('No shipping address provided.', 'vendor-dashboard-pro'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="vdp-order-payment-col">
                    <div class="vdp-info-box">
                        <div class="vdp-info-box-header">
                            <h3 class="vdp-info-box-title"><?php esc_html_e('Payment Information', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        <div class="vdp-info-box-content">
                            <div class="vdp-payment-info">
                                <div class="vdp-payment-method">
                                    <strong><?php esc_html_e('Method:', 'vendor-dashboard-pro'); ?></strong> <?php echo esc_html($order['payment_method']); ?>
                                </div>
                                <div class="vdp-payment-status">
                                    <strong><?php esc_html_e('Status:', 'vendor-dashboard-pro'); ?></strong> <?php echo esc_html($order['payment_status']); ?>
                                </div>
                                <div class="vdp-payment-date">
                                    <strong><?php esc_html_e('Date:', 'vendor-dashboard-pro'); ?></strong> <?php echo esc_html(vdp_format_date($order['payment_date'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="vdp-section vdp-order-items-section">
            <div class="vdp-section-header">
                <h2 class="vdp-section-title"><?php esc_html_e('Order Items', 'vendor-dashboard-pro'); ?></h2>
            </div>
            
            <div class="vdp-table-responsive">
                <table class="vdp-table vdp-order-items-table">
                    <thead>
                        <tr>
                            <th class="vdp-column-product"><?php esc_html_e('Product', 'vendor-dashboard-pro'); ?></th>
                            <th class="vdp-column-quantity"><?php esc_html_e('Quantity', 'vendor-dashboard-pro'); ?></th>
                            <th class="vdp-column-price"><?php esc_html_e('Price', 'vendor-dashboard-pro'); ?></th>
                            <th class="vdp-column-total"><?php esc_html_e('Total', 'vendor-dashboard-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item) : ?>
                            <tr>
                                <td class="vdp-column-product">
                                    <div class="vdp-order-product">
                                        <?php if (!empty($item['image'])) : ?>
                                            <div class="vdp-product-image">
                                                <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
                                            </div>
                                        <?php else : ?>
                                            <div class="vdp-product-image">
                                                <div class="vdp-image-placeholder">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="vdp-product-info">
                                            <div class="vdp-product-name">
                                                <?php if (!empty($item['url'])) : ?>
                                                    <a href="<?php echo esc_url($item['url']); ?>" target="_blank">
                                                        <?php echo esc_html($item['name']); ?>
                                                    </a>
                                                <?php else : ?>
                                                    <?php echo esc_html($item['name']); ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($item['sku'])) : ?>
                                                <div class="vdp-product-sku">
                                                    <?php esc_html_e('SKU:', 'vendor-dashboard-pro'); ?> <?php echo esc_html($item['sku']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="vdp-column-quantity">
                                    <?php echo esc_html($item['quantity']); ?>
                                </td>
                                <td class="vdp-column-price">
                                    <?php echo esc_html(vdp_format_price($item['price'])); ?>
                                </td>
                                <td class="vdp-column-total">
                                    <?php echo esc_html(vdp_format_price($item['total'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="vdp-order-subtotal">
                            <th colspan="3"><?php esc_html_e('Subtotal', 'vendor-dashboard-pro'); ?></th>
                            <td><?php echo esc_html(vdp_format_price($order['subtotal'])); ?></td>
                        </tr>
                        <?php if (!empty($order['tax'])) : ?>
                            <tr class="vdp-order-tax">
                                <th colspan="3"><?php esc_html_e('Tax', 'vendor-dashboard-pro'); ?></th>
                                <td><?php echo esc_html(vdp_format_price($order['tax'])); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($order['shipping'])) : ?>
                            <tr class="vdp-order-shipping">
                                <th colspan="3"><?php esc_html_e('Shipping', 'vendor-dashboard-pro'); ?></th>
                                <td><?php echo esc_html(vdp_format_price($order['shipping'])); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($order['discount'])) : ?>
                            <tr class="vdp-order-discount">
                                <th colspan="3"><?php esc_html_e('Discount', 'vendor-dashboard-pro'); ?></th>
                                <td>-<?php echo esc_html(vdp_format_price($order['discount'])); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="vdp-order-total">
                            <th colspan="3"><?php esc_html_e('Total', 'vendor-dashboard-pro'); ?></th>
                            <td><?php echo esc_html(vdp_format_price($order['total'])); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Order Notes & Status Update -->
        <div class="vdp-section vdp-order-actions-section">
            <div class="vdp-order-actions-grid">
                <div class="vdp-order-notes-col">
                    <div class="vdp-section-header">
                        <h2 class="vdp-section-title"><?php esc_html_e('Order Notes', 'vendor-dashboard-pro'); ?></h2>
                    </div>
                    
                    <div class="vdp-order-notes-list">
                        <?php if (empty($order['notes'])) : ?>
                            <div class="vdp-empty-notes">
                                <p><?php esc_html_e('No notes for this order yet.', 'vendor-dashboard-pro'); ?></p>
                            </div>
                        <?php else : ?>
                            <?php foreach ($order['notes'] as $note) : ?>
                                <div class="vdp-order-note <?php echo esc_attr($note['type']); ?>">
                                    <div class="vdp-order-note-header">
                                        <span class="vdp-note-author"><?php echo esc_html($note['author']); ?></span>
                                        <span class="vdp-note-date"><?php echo esc_html(vdp_time_ago($note['date'])); ?></span>
                                    </div>
                                    <div class="vdp-order-note-content">
                                        <?php echo wp_kses_post($note['content']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="vdp-add-note-form">
                        <div class="vdp-form-group">
                            <label for="order_note" class="vdp-form-label"><?php esc_html_e('Add Note', 'vendor-dashboard-pro'); ?></label>
                            <textarea id="order_note" class="vdp-form-control" rows="3" placeholder="<?php esc_attr_e('Enter a note for your reference or to communicate with the customer...', 'vendor-dashboard-pro'); ?>"></textarea>
                        </div>
                        <div class="vdp-form-row">
                            <div class="vdp-form-check">
                                <input type="checkbox" id="note_to_customer" class="vdp-form-check-input">
                                <label for="note_to_customer" class="vdp-form-check-label"><?php esc_html_e('Send note to customer', 'vendor-dashboard-pro'); ?></label>
                            </div>
                            <button type="button" class="vdp-btn vdp-btn-primary vdp-add-note-btn" data-order-id="<?php echo esc_attr($order['id']); ?>">
                                <?php esc_html_e('Add Note', 'vendor-dashboard-pro'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="vdp-order-status-col">
                    <div class="vdp-section-header">
                        <h2 class="vdp-section-title"><?php esc_html_e('Order Status', 'vendor-dashboard-pro'); ?></h2>
                    </div>
                    
                    <div class="vdp-update-status-form">
                        <div class="vdp-form-group">
                            <label for="order_status" class="vdp-form-label"><?php esc_html_e('Update Status', 'vendor-dashboard-pro'); ?></label>
                            <select id="order_status" class="vdp-form-control">
                                <option value="pending" <?php selected($order['status'], 'pending'); ?>><?php esc_html_e('Pending', 'vendor-dashboard-pro'); ?></option>
                                <option value="processing" <?php selected($order['status'], 'processing'); ?>><?php esc_html_e('Processing', 'vendor-dashboard-pro'); ?></option>
                                <option value="on-hold" <?php selected($order['status'], 'on-hold'); ?>><?php esc_html_e('On Hold', 'vendor-dashboard-pro'); ?></option>
                                <option value="completed" <?php selected($order['status'], 'completed'); ?>><?php esc_html_e('Completed', 'vendor-dashboard-pro'); ?></option>
                                <option value="cancelled" <?php selected($order['status'], 'cancelled'); ?>><?php esc_html_e('Cancelled', 'vendor-dashboard-pro'); ?></option>
                            </select>
                        </div>
                        <div class="vdp-form-row">
                            <button type="button" class="vdp-btn vdp-btn-primary vdp-update-status-btn" data-order-id="<?php echo esc_attr($order['id']); ?>">
                                <?php esc_html_e('Update', 'vendor-dashboard-pro'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="vdp-order-bottom-actions">
            <a href="<?php echo esc_url(vdp_get_dashboard_url('orders')); ?>" class="vdp-btn vdp-btn-text">
                <i class="fas fa-arrow-left"></i> <?php esc_html_e('Back to Orders', 'vendor-dashboard-pro'); ?>
            </a>
            
            <div class="vdp-order-action-buttons">
                <?php if ($order['status'] !== 'completed') : ?>
                    <button type="button" class="vdp-btn vdp-btn-success vdp-mark-completed-btn" data-order-id="<?php echo esc_attr($order['id']); ?>">
                        <i class="fas fa-check-circle"></i> <?php esc_html_e('Mark Completed', 'vendor-dashboard-pro'); ?>
                    </button>
                <?php endif; ?>
                
                <button type="button" class="vdp-btn vdp-btn-outline vdp-print-order-btn">
                    <i class="fas fa-print"></i> <?php esc_html_e('Print Order', 'vendor-dashboard-pro'); ?>
                </button>
                
                <a href="#" class="vdp-btn vdp-btn-outline vdp-download-invoice-btn" data-order-id="<?php echo esc_attr($order['id']); ?>">
                    <i class="fas fa-file-invoice"></i> <?php esc_html_e('Download Invoice', 'vendor-dashboard-pro'); ?>
                </a>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Print Order
    $('.vdp-print-order-btn').on('click', function() {
        window.print();
    });
    
    // Update status
    $('.vdp-update-status-btn').on('click', function() {
        var orderId = $(this).data('order-id');
        var status = $('#order_status').val();
        
        // Show loading state
        $(this).addClass('vdp-btn-loading').prop('disabled', true);
        
        // AJAX request would go here in a real implementation
        setTimeout(function() {
            // Display success message (simulated for demo)
            var notification = $('<div class="vdp-notification vdp-notification-success">Order status updated successfully!</div>');
            $('.vdp-order-view-content').prepend(notification);
            
            // Update status badge
            var statusClass = 'vdp-status-' + status;
            var statusLabel = $('#order_status option:selected').text();
            $('.vdp-order-status-large .vdp-status-badge')
                .removeClass()
                .addClass('vdp-status-badge ' + statusClass)
                .text(statusLabel);
            
            // Reset button state
            $('.vdp-update-status-btn').removeClass('vdp-btn-loading').prop('disabled', false);
            
            // Hide notification after 3 seconds
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }, 1000);
    });
    
    // Add note
    $('.vdp-add-note-btn').on('click', function() {
        var orderId = $(this).data('order-id');
        var note = $('#order_note').val();
        var toCustomer = $('#note_to_customer').is(':checked');
        
        if (!note) {
            return;
        }
        
        // Show loading state
        $(this).addClass('vdp-btn-loading').prop('disabled', true);
        
        // AJAX request would go here in a real implementation
        setTimeout(function() {
            // Add the new note to the list (simulated for demo)
            var noteType = toCustomer ? 'vdp-customer-note' : 'vdp-private-note';
            var noteHtml = '<div class="vdp-order-note ' + noteType + '">' +
                           '<div class="vdp-order-note-header">' +
                           '<span class="vdp-note-author">You</span>' +
                           '<span class="vdp-note-date">Just now</span>' +
                           '</div>' +
                           '<div class="vdp-order-note-content">' + note + '</div>' +
                           '</div>';
            
            $('.vdp-order-notes-list .vdp-empty-notes').remove();
            $('.vdp-order-notes-list').append(noteHtml);
            
            // Clear the form
            $('#order_note').val('');
            $('#note_to_customer').prop('checked', false);
            
            // Reset button state
            $('.vdp-add-note-btn').removeClass('vdp-btn-loading').prop('disabled', false);
        }, 1000);
    });
    
    // Mark as completed
    $('.vdp-mark-completed-btn').on('click', function() {
        var orderId = $(this).data('order-id');
        
        // Show loading state
        $(this).addClass('vdp-btn-loading').prop('disabled', true);
        
        // AJAX request would go here in a real implementation
        setTimeout(function() {
            // Display success message (simulated for demo)
            var notification = $('<div class="vdp-notification vdp-notification-success">Order marked as completed!</div>');
            $('.vdp-order-view-content').prepend(notification);
            
            // Update status badge
            $('.vdp-order-status-large .vdp-status-badge')
                .removeClass()
                .addClass('vdp-status-badge vdp-status-completed')
                .text('Completed');
            
            // Update select
            $('#order_status').val('completed');
            
            // Hide the mark as completed button
            $('.vdp-mark-completed-btn').fadeOut();
            
            // Hide notification after 3 seconds
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }, 1000);
    });
    
    // Download invoice (dummy implementation)
    $('.vdp-download-invoice-btn').on('click', function(e) {
        e.preventDefault();
        alert('In a real implementation, this would generate and download an invoice PDF.');
    });
});
</script>