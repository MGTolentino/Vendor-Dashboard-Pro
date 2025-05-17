<?php
/**
 * Products content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-products-content">
    <!-- Filters -->
    <div class="vdp-filters">
        <div class="vdp-filter-group">
            <div class="vdp-search-box">
                <input type="text" class="vdp-search-input vdp-search-products" placeholder="<?php esc_attr_e('Search products...', 'vendor-dashboard-pro'); ?>">
                <button type="button" class="vdp-search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <div class="vdp-filter-group">
            <select class="vdp-filter-select" name="category">
                <option value=""><?php esc_html_e('All Categories', 'vendor-dashboard-pro'); ?></option>
                <?php foreach ($categories as $id => $name) : ?>
                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                <?php endforeach; ?>
            </select>
            
            <select class="vdp-filter-select" name="status">
                <option value=""><?php esc_html_e('All Statuses', 'vendor-dashboard-pro'); ?></option>
                <option value="publish"><?php esc_html_e('Published', 'vendor-dashboard-pro'); ?></option>
                <option value="draft"><?php esc_html_e('Draft', 'vendor-dashboard-pro'); ?></option>
                <option value="pending"><?php esc_html_e('Pending', 'vendor-dashboard-pro'); ?></option>
            </select>
        </div>
        
        <div class="vdp-filter-actions">
            <a href="<?php echo esc_url(vdp_get_dashboard_url('products/add')); ?>" class="vdp-btn vdp-btn-primary">
                <i class="fas fa-plus"></i>
                <?php esc_html_e('Add New Product', 'vendor-dashboard-pro'); ?>
            </a>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="vdp-table-responsive">
        <table class="vdp-table vdp-products-table">
            <thead>
                <tr>
                    <th class="vdp-column-image"><?php esc_html_e('Image', 'vendor-dashboard-pro'); ?></th>
                    <th class="vdp-column-title"><?php esc_html_e('Product', 'vendor-dashboard-pro'); ?></th>
                    <th class="vdp-column-price"><?php esc_html_e('Price', 'vendor-dashboard-pro'); ?></th>
                    <th class="vdp-column-category"><?php esc_html_e('Category', 'vendor-dashboard-pro'); ?></th>
                    <th class="vdp-column-status"><?php esc_html_e('Status', 'vendor-dashboard-pro'); ?></th>
                    <th class="vdp-column-date"><?php esc_html_e('Date', 'vendor-dashboard-pro'); ?></th>
                    <th class="vdp-column-actions"><?php esc_html_e('Actions', 'vendor-dashboard-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listings)) : ?>
                    <tr>
                        <td colspan="7" class="vdp-no-items">
                            <div class="vdp-empty-state">
                                <div class="vdp-empty-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <p><?php esc_html_e('No products yet. Add your first product!', 'vendor-dashboard-pro'); ?></p>
                                <a href="<?php echo esc_url(vdp_get_dashboard_url('products/add')); ?>" class="vdp-btn vdp-btn-primary vdp-btn-sm">
                                    <i class="fas fa-plus"></i> <?php esc_html_e('Add Product', 'vendor-dashboard-pro'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($listings as $listing) : ?>
                        <?php
                        // Get category
                        $category_id = 0;
                        $category_name = '';
                        
                        if (taxonomy_exists('hp_listing_category')) {
                            $terms = get_the_terms($listing->get_id(), 'hp_listing_category');
                            if (!empty($terms) && !is_wp_error($terms)) {
                                $category_id = $terms[0]->term_id;
                                $category_name = $terms[0]->name;
                            }
                        }
                        
                        // Get status
                        $status = $listing->get_status();
                        $status_label = VDP_Products::get_status_label($status);
                        $status_class = VDP_Products::get_status_class($status);
                        ?>
                        <tr class="vdp-product-row" data-product-id="<?php echo esc_attr($listing->get_id()); ?>" data-category="<?php echo esc_attr($category_id); ?>" data-status="<?php echo esc_attr($status); ?>">
                            <td class="vdp-column-image">
                                <div class="vdp-product-image">
                                    <?php if ($listing->get_image__url('thumbnail')) : ?>
                                        <img src="<?php echo esc_url($listing->get_image__url('thumbnail')); ?>" alt="<?php echo esc_attr($listing->get_title()); ?>">
                                    <?php else : ?>
                                        <div class="vdp-image-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($listing->is_featured()) : ?>
                                        <span class="vdp-featured-badge" title="<?php esc_attr_e('Featured Product', 'vendor-dashboard-pro'); ?>">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="vdp-column-title">
                                <div class="vdp-product-title">
                                    <a href="<?php echo esc_url(get_permalink($listing->get_id())); ?>" target="_blank">
                                        <?php echo esc_html($listing->get_title()); ?>
                                    </a>
                                </div>
                                <div class="vdp-product-views">
                                    <i class="fas fa-eye"></i> <?php echo esc_html(number_format(rand(10, 1000))); ?> <?php esc_html_e('views', 'vendor-dashboard-pro'); ?>
                                </div>
                            </td>
                            <td class="vdp-column-price">
                                <div class="vdp-product-price">
                                    <?php echo esc_html(vdp_format_price($listing->get_price())); ?>
                                </div>
                            </td>
                            <td class="vdp-column-category">
                                <?php if (!empty($category_name)) : ?>
                                    <div class="vdp-product-category">
                                        <?php echo esc_html($category_name); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="vdp-product-category vdp-product-no-category">
                                        <?php esc_html_e('Uncategorized', 'vendor-dashboard-pro'); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="vdp-column-status">
                                <div class="vdp-status-badge <?php echo esc_attr($status_class); ?>">
                                    <?php echo esc_html($status_label); ?>
                                </div>
                            </td>
                            <td class="vdp-column-date">
                                <div class="vdp-product-date">
                                    <?php echo esc_html(vdp_format_date($listing->get_created_date())); ?>
                                </div>
                                <div class="vdp-product-time">
                                    <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($listing->get_created_date()))); ?>
                                </div>
                            </td>
                            <td class="vdp-column-actions">
                                <div class="vdp-table-actions">
                                    <a href="<?php echo esc_url(vdp_get_dashboard_url('products/edit/' . $listing->get_id())); ?>" class="vdp-btn vdp-btn-sm vdp-btn-icon" title="<?php esc_attr_e('Edit', 'vendor-dashboard-pro'); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo esc_url(get_permalink($listing->get_id())); ?>" class="vdp-btn vdp-btn-sm vdp-btn-icon" target="_blank" title="<?php esc_attr_e('View', 'vendor-dashboard-pro'); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="vdp-btn vdp-btn-sm vdp-btn-icon vdp-delete-product" data-product-id="<?php echo esc_attr($listing->get_id()); ?>" title="<?php esc_attr_e('Delete', 'vendor-dashboard-pro'); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1) : ?>
        <div class="vdp-pagination">
            <ul class="vdp-pagination-list">
                <?php if ($paged > 1) : ?>
                    <li class="vdp-pagination-item">
                        <a href="<?php echo esc_url(add_query_arg('paged', $paged - 1, vdp_get_dashboard_url('products'))); ?>" class="vdp-pagination-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <li class="vdp-pagination-item <?php echo $i === $paged ? 'vdp-active' : ''; ?>">
                        <a href="<?php echo esc_url(add_query_arg('paged', $i, vdp_get_dashboard_url('products'))); ?>" class="vdp-pagination-link">
                            <?php echo esc_html($i); ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($paged < $total_pages) : ?>
                    <li class="vdp-pagination-item">
                        <a href="<?php echo esc_url(add_query_arg('paged', $paged + 1, vdp_get_dashboard_url('products'))); ?>" class="vdp-pagination-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>