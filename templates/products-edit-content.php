<?php
/**
 * Product edit content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-product-edit-content">
    <form id="vdp-product-form" class="vdp-form" enctype="multipart/form-data">
        <?php
        // Add hidden field for listing ID if editing
        if ($listing) {
            echo '<input type="hidden" name="listing_id" value="' . esc_attr($listing->get_id()) . '">';
        }
        ?>
        
        <div class="vdp-form-row vdp-form-row-2-columns">
            <div class="vdp-form-column">
                <!-- Basic Info -->
                <div class="vdp-form-section">
                    <div class="vdp-section-header">
                        <h3 class="vdp-section-title"><?php esc_html_e('Basic Information', 'vendor-dashboard-pro'); ?></h3>
                    </div>
                    
                    <div class="vdp-form-group">
                        <label for="listing_title" class="vdp-form-label"><?php esc_html_e('Title', 'vendor-dashboard-pro'); ?> <span class="vdp-required">*</span></label>
                        <input type="text" id="listing_title" name="listing_data[title]" class="vdp-form-control" value="<?php echo $listing ? esc_attr($listing->get_title()) : ''; ?>" required>
                    </div>
                    
                    <div class="vdp-form-group">
                        <label for="listing_description" class="vdp-form-label"><?php esc_html_e('Description', 'vendor-dashboard-pro'); ?></label>
                        <textarea id="listing_description" name="listing_data[description]" class="vdp-form-control" rows="8"><?php echo $listing ? esc_textarea($listing->get_description()) : ''; ?></textarea>
                    </div>
                    
                    <div class="vdp-form-group">
                        <label for="listing_price" class="vdp-form-label"><?php esc_html_e('Price', 'vendor-dashboard-pro'); ?> <span class="vdp-required">*</span></label>
                        <div class="vdp-input-group">
                            <span class="vdp-input-group-text">$</span>
                            <input type="number" id="listing_price" name="listing_data[price]" class="vdp-form-control" value="<?php echo $listing ? esc_attr($listing->get_price()) : ''; ?>" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="vdp-form-group">
                        <label for="listing_categories" class="vdp-form-label"><?php esc_html_e('Categories', 'vendor-dashboard-pro'); ?></label>
                        <select id="listing_categories" name="listing_data[categories][]" class="vdp-form-control" multiple>
                            <?php 
                            $selected_categories = [];
                            
                            if ($listing && taxonomy_exists('hp_listing_category')) {
                                $terms = get_the_terms($listing->get_id(), 'hp_listing_category');
                                
                                if (!empty($terms) && !is_wp_error($terms)) {
                                    foreach ($terms as $term) {
                                        $selected_categories[] = $term->term_id;
                                    }
                                }
                            }
                            
                            foreach ($categories as $id => $name) :
                            ?>
                                <option value="<?php echo esc_attr($id); ?>" <?php echo in_array($id, $selected_categories) ? 'selected' : ''; ?>><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Features & Attributes -->
                <div class="vdp-form-section">
                    <div class="vdp-section-header">
                        <h3 class="vdp-section-title"><?php esc_html_e('Features & Attributes', 'vendor-dashboard-pro'); ?></h3>
                    </div>
                    
                    <div class="vdp-form-group">
                        <label class="vdp-form-label"><?php esc_html_e('Featured Product', 'vendor-dashboard-pro'); ?></label>
                        <div class="vdp-toggle-control">
                            <label class="vdp-toggle">
                                <input type="checkbox" name="listing_data[featured]" value="1" <?php echo ($listing && $listing->is_featured()) ? 'checked' : ''; ?>>
                                <span class="vdp-toggle-switch"></span>
                            </label>
                            <span class="vdp-toggle-label"><?php esc_html_e('Mark this product as featured', 'vendor-dashboard-pro'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Custom fields would go here - would be populated dynamically based on HivePress field configuration -->
                    <div class="vdp-custom-fields">
                        <!-- This is a placeholder for custom fields that would be populated dynamically -->
                        <!-- In a real implementation, you would loop through the custom fields defined in HivePress -->
                    </div>
                </div>
            </div>
            
            <div class="vdp-form-column">
                <!-- Images -->
                <div class="vdp-form-section">
                    <div class="vdp-section-header">
                        <h3 class="vdp-section-title"><?php esc_html_e('Images', 'vendor-dashboard-pro'); ?></h3>
                    </div>
                    
                    <div class="vdp-form-group">
                        <label class="vdp-form-label"><?php esc_html_e('Main Image', 'vendor-dashboard-pro'); ?></label>
                        
                        <div class="vdp-image-uploader">
                            <div class="vdp-image-preview">
                                <?php if (!empty($images)) : ?>
                                    <div class="vdp-image-item">
                                        <img src="<?php echo esc_url($images[0]['url']); ?>" alt="">
                                        <input type="hidden" name="listing_data[image_id]" value="<?php echo esc_attr($images[0]['id']); ?>">
                                        <button type="button" class="vdp-image-remove"><i class="fas fa-times"></i></button>
                                    </div>
                                <?php else : ?>
                                    <div class="vdp-image-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span><?php esc_html_e('Drag & drop your image here or click to browse', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="vdp-image-controls">
                                <input type="file" id="listing_image" name="listing_image" class="vdp-file-input" accept="image/*">
                                <button type="button" class="vdp-btn vdp-btn-outline vdp-file-btn">
                                    <i class="fas fa-upload"></i> <?php esc_html_e('Choose Image', 'vendor-dashboard-pro'); ?>
                                </button>
                                <span class="vdp-file-label" data-default="<?php esc_attr_e('No file selected', 'vendor-dashboard-pro'); ?>">
                                    <?php esc_html_e('No file selected', 'vendor-dashboard-pro'); ?>
                                </span>
                            </div>
                            
                            <div class="vdp-image-help">
                                <p class="vdp-help-text">
                                    <?php esc_html_e('Recommended image size: 800x600 pixels. Maximum file size: 2MB.', 'vendor-dashboard-pro'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gallery images would go here in a real implementation -->
                </div>
                
                <!-- Visibility & Status -->
                <div class="vdp-form-section">
                    <div class="vdp-section-header">
                        <h3 class="vdp-section-title"><?php esc_html_e('Visibility & Status', 'vendor-dashboard-pro'); ?></h3>
                    </div>
                    
                    <div class="vdp-form-group">
                        <label for="listing_status" class="vdp-form-label"><?php esc_html_e('Status', 'vendor-dashboard-pro'); ?></label>
                        <select id="listing_status" name="listing_data[status]" class="vdp-form-control">
                            <option value="publish" <?php echo ($listing && $listing->get_status() === 'publish') ? 'selected' : ''; ?>><?php esc_html_e('Published', 'vendor-dashboard-pro'); ?></option>
                            <option value="draft" <?php echo ($listing && $listing->get_status() === 'draft') ? 'selected' : ''; ?>><?php esc_html_e('Draft', 'vendor-dashboard-pro'); ?></option>
                        </select>
                    </div>
                    
                    <!-- Other visibility options would go here in a real implementation -->
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="vdp-form-actions">
            <button type="submit" class="vdp-btn vdp-btn-primary vdp-btn-lg">
                <i class="fas fa-save"></i> <?php esc_html_e('Save Product', 'vendor-dashboard-pro'); ?>
            </button>
            
            <a href="<?php echo esc_url(vdp_get_dashboard_url('products')); ?>" class="vdp-btn vdp-btn-text">
                <?php esc_html_e('Cancel', 'vendor-dashboard-pro'); ?>
            </a>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Image preview functionality
    $('#listing_image').on('change', function(e) {
        var file = e.target.files[0];
        
        if (file) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var html = '<div class="vdp-image-item">' +
                            '<img src="' + e.target.result + '" alt="">' +
                            '<button type="button" class="vdp-image-remove"><i class="fas fa-times"></i></button>' +
                          '</div>';
                
                $('.vdp-image-preview').html(html);
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // Remove image
    $(document).on('click', '.vdp-image-remove', function() {
        var placeholder = '<div class="vdp-image-placeholder">' +
                            '<i class="fas fa-cloud-upload-alt"></i>' +
                            '<span><?php esc_html_e('Drag & drop your image here or click to browse', 'vendor-dashboard-pro'); ?></span>' +
                          '</div>';
        
        $('.vdp-image-preview').html(placeholder);
        
        // Reset file input
        $('#listing_image').val('');
        $('.vdp-file-label').text($('.vdp-file-label').data('default'));
    });
});
</script>