<?php
/**
 * Settings content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-settings-content">
    <div class="vdp-tabs-container">
        <div class="vdp-tabs-nav">
            <button class="vdp-tab-btn vdp-active" data-tab="store">
                <i class="fas fa-store"></i> <?php esc_html_e('Store Settings', 'vendor-dashboard-pro'); ?>
            </button>
            <button class="vdp-tab-btn" data-tab="account">
                <i class="fas fa-user-circle"></i> <?php esc_html_e('Account', 'vendor-dashboard-pro'); ?>
            </button>
            <button class="vdp-tab-btn" data-tab="payment">
                <i class="fas fa-credit-card"></i> <?php esc_html_e('Payment Methods', 'vendor-dashboard-pro'); ?>
            </button>
            <button class="vdp-tab-btn" data-tab="shipping">
                <i class="fas fa-shipping-fast"></i> <?php esc_html_e('Shipping', 'vendor-dashboard-pro'); ?>
            </button>
            <button class="vdp-tab-btn" data-tab="notifications">
                <i class="fas fa-bell"></i> <?php esc_html_e('Notifications', 'vendor-dashboard-pro'); ?>
            </button>
        </div>
        
        <div class="vdp-tabs-content">
            <!-- Store Settings Tab -->
            <div class="vdp-tab-content vdp-active" id="store-tab">
                <form id="store-settings-form" class="vdp-settings-form">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Store Information', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="store_name" class="vdp-form-label"><?php esc_html_e('Store Name', 'vendor-dashboard-pro'); ?> <span class="vdp-required">*</span></label>
                                <input type="text" id="store_name" name="store_name" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_name()); ?>" required>
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="store_slug" class="vdp-form-label"><?php esc_html_e('Store URL', 'vendor-dashboard-pro'); ?></label>
                                <div class="vdp-input-group">
                                    <span class="vdp-input-group-text"><?php echo esc_html(home_url('vendor/')); ?></span>
                                    <input type="text" id="store_slug" name="store_slug" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_slug()); ?>" pattern="[a-zA-Z0-9\-]+" title="<?php esc_attr_e('Only letters, numbers and hyphens are allowed', 'vendor-dashboard-pro'); ?>">
                                </div>
                                <div class="vdp-form-help"><?php esc_html_e('Only letters, numbers and hyphens are allowed', 'vendor-dashboard-pro'); ?></div>
                            </div>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="store_description" class="vdp-form-label"><?php esc_html_e('Store Description', 'vendor-dashboard-pro'); ?></label>
                            <textarea id="store_description" name="store_description" class="vdp-form-control" rows="4"><?php echo esc_textarea($vendor->get_description()); ?></textarea>
                            <div class="vdp-form-help"><?php esc_html_e('Tell customers about your store and what makes it special.', 'vendor-dashboard-pro'); ?></div>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="store_email" class="vdp-form-label"><?php esc_html_e('Store Email', 'vendor-dashboard-pro'); ?> <span class="vdp-required">*</span></label>
                            <input type="email" id="store_email" name="store_email" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_email()); ?>" required>
                            <div class="vdp-form-help"><?php esc_html_e('This email will be used for order notifications.', 'vendor-dashboard-pro'); ?></div>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="store_phone" class="vdp-form-label"><?php esc_html_e('Phone Number', 'vendor-dashboard-pro'); ?></label>
                                <input type="tel" id="store_phone" name="store_phone" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_phone()); ?>">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="store_website" class="vdp-form-label"><?php esc_html_e('Website', 'vendor-dashboard-pro'); ?></label>
                                <input type="url" id="store_website" name="store_website" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_website()); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Store Logo & Banner', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-grid vdp-form-grid-2">
                            <div class="vdp-form-group">
                                <label class="vdp-form-label"><?php esc_html_e('Store Logo', 'vendor-dashboard-pro'); ?></label>
                                <div class="vdp-image-uploader vdp-logo-uploader">
                                    <div class="vdp-current-image">
                                        <?php if ($vendor->get_image__url('thumbnail')) : ?>
                                            <img src="<?php echo esc_url($vendor->get_image__url('thumbnail')); ?>" alt="<?php esc_attr_e('Store Logo', 'vendor-dashboard-pro'); ?>">
                                        <?php else : ?>
                                            <div class="vdp-image-placeholder">
                                                <i class="fas fa-store"></i>
                                                <span><?php esc_html_e('No logo uploaded', 'vendor-dashboard-pro'); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="vdp-image-controls">
                                        <input type="file" id="store_logo" name="store_logo" class="vdp-file-input" accept="image/*">
                                        <button type="button" class="vdp-btn vdp-btn-outline vdp-file-btn">
                                            <i class="fas fa-upload"></i> <?php esc_html_e('Upload Logo', 'vendor-dashboard-pro'); ?>
                                        </button>
                                        <?php if ($vendor->get_image__url('thumbnail')) : ?>
                                            <button type="button" class="vdp-btn vdp-btn-danger vdp-btn-sm vdp-remove-image-btn">
                                                <i class="fas fa-trash-alt"></i> <?php esc_html_e('Remove', 'vendor-dashboard-pro'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="vdp-form-help">
                                        <?php esc_html_e('Recommended size: 200x200 pixels. Maximum file size: 2MB.', 'vendor-dashboard-pro'); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-form-group">
                                <label class="vdp-form-label"><?php esc_html_e('Store Banner', 'vendor-dashboard-pro'); ?></label>
                                <div class="vdp-image-uploader vdp-banner-uploader">
                                    <div class="vdp-current-image vdp-banner-image">
                                        <?php if ($vendor->get_banner__url()) : ?>
                                            <img src="<?php echo esc_url($vendor->get_banner__url()); ?>" alt="<?php esc_attr_e('Store Banner', 'vendor-dashboard-pro'); ?>">
                                        <?php else : ?>
                                            <div class="vdp-image-placeholder">
                                                <i class="fas fa-image"></i>
                                                <span><?php esc_html_e('No banner uploaded', 'vendor-dashboard-pro'); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="vdp-image-controls">
                                        <input type="file" id="store_banner" name="store_banner" class="vdp-file-input" accept="image/*">
                                        <button type="button" class="vdp-btn vdp-btn-outline vdp-file-btn">
                                            <i class="fas fa-upload"></i> <?php esc_html_e('Upload Banner', 'vendor-dashboard-pro'); ?>
                                        </button>
                                        <?php if ($vendor->get_banner__url()) : ?>
                                            <button type="button" class="vdp-btn vdp-btn-danger vdp-btn-sm vdp-remove-image-btn">
                                                <i class="fas fa-trash-alt"></i> <?php esc_html_e('Remove', 'vendor-dashboard-pro'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="vdp-form-help">
                                        <?php esc_html_e('Recommended size: 1200x300 pixels. Maximum file size: 2MB.', 'vendor-dashboard-pro'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Social Media', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="social_facebook" class="vdp-form-label"><i class="fab fa-facebook"></i> <?php esc_html_e('Facebook', 'vendor-dashboard-pro'); ?></label>
                                <input type="url" id="social_facebook" name="social_facebook" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_social('facebook')); ?>" placeholder="https://facebook.com/yourstorepage">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="social_instagram" class="vdp-form-label"><i class="fab fa-instagram"></i> <?php esc_html_e('Instagram', 'vendor-dashboard-pro'); ?></label>
                                <input type="url" id="social_instagram" name="social_instagram" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_social('instagram')); ?>" placeholder="https://instagram.com/yourstorepage">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="social_twitter" class="vdp-form-label"><i class="fab fa-twitter"></i> <?php esc_html_e('Twitter', 'vendor-dashboard-pro'); ?></label>
                                <input type="url" id="social_twitter" name="social_twitter" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_social('twitter')); ?>" placeholder="https://twitter.com/yourstorepage">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="social_youtube" class="vdp-form-label"><i class="fab fa-youtube"></i> <?php esc_html_e('YouTube', 'vendor-dashboard-pro'); ?></label>
                                <input type="url" id="social_youtube" name="social_youtube" class="vdp-form-control" value="<?php echo esc_attr($vendor->get_social('youtube')); ?>" placeholder="https://youtube.com/channel/your-channel">
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary vdp-btn-lg">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Store Settings', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Account Tab -->
            <div class="vdp-tab-content" id="account-tab">
                <form id="account-settings-form" class="vdp-settings-form">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Account Information', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="first_name" class="vdp-form-label"><?php esc_html_e('First Name', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="first_name" name="first_name" class="vdp-form-control" value="<?php echo esc_attr($user->first_name); ?>">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="last_name" class="vdp-form-label"><?php esc_html_e('Last Name', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="last_name" name="last_name" class="vdp-form-control" value="<?php echo esc_attr($user->last_name); ?>">
                            </div>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="email" class="vdp-form-label"><?php esc_html_e('Email Address', 'vendor-dashboard-pro'); ?></label>
                            <input type="email" id="email" name="email" class="vdp-form-control" value="<?php echo esc_attr($user->user_email); ?>" readonly>
                            <div class="vdp-form-help"><?php esc_html_e('To change your email address, please contact support.', 'vendor-dashboard-pro'); ?></div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Change Password', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="current_password" class="vdp-form-label"><?php esc_html_e('Current Password', 'vendor-dashboard-pro'); ?></label>
                            <input type="password" id="current_password" name="current_password" class="vdp-form-control">
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="new_password" class="vdp-form-label"><?php esc_html_e('New Password', 'vendor-dashboard-pro'); ?></label>
                                <input type="password" id="new_password" name="new_password" class="vdp-form-control">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="confirm_password" class="vdp-form-label"><?php esc_html_e('Confirm New Password', 'vendor-dashboard-pro'); ?></label>
                                <input type="password" id="confirm_password" name="confirm_password" class="vdp-form-control">
                            </div>
                        </div>
                        
                        <div class="vdp-form-help">
                            <p><?php esc_html_e('Password requirements:', 'vendor-dashboard-pro'); ?></p>
                            <ul class="vdp-password-requirements">
                                <li><?php esc_html_e('At least 8 characters long', 'vendor-dashboard-pro'); ?></li>
                                <li><?php esc_html_e('Contains at least one uppercase letter', 'vendor-dashboard-pro'); ?></li>
                                <li><?php esc_html_e('Contains at least one lowercase letter', 'vendor-dashboard-pro'); ?></li>
                                <li><?php esc_html_e('Contains at least one number', 'vendor-dashboard-pro'); ?></li>
                                <li><?php esc_html_e('Contains at least one special character', 'vendor-dashboard-pro'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Two-Factor Authentication', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-group">
                            <div class="vdp-toggle-control">
                                <label class="vdp-toggle">
                                    <input type="checkbox" name="enable_2fa" value="1" <?php checked($user->enable_2fa); ?>>
                                    <span class="vdp-toggle-switch"></span>
                                </label>
                                <span class="vdp-toggle-label"><?php esc_html_e('Enable Two-Factor Authentication', 'vendor-dashboard-pro'); ?></span>
                            </div>
                            <div class="vdp-form-help"><?php esc_html_e('Adds an extra layer of security to your account by requiring a verification code on login.', 'vendor-dashboard-pro'); ?></div>
                        </div>
                        
                        <?php if (!$user->enable_2fa) : ?>
                            <div class="vdp-setup-2fa vdp-hidden">
                                <div class="vdp-qr-code-container">
                                    <div class="vdp-qr-code-placeholder">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                </div>
                                <div class="vdp-form-help">
                                    <p><?php esc_html_e('Scan the QR code with your authenticator app (like Google Authenticator, Authy, etc.).', 'vendor-dashboard-pro'); ?></p>
                                    <p><?php esc_html_e('Then enter the verification code from your app below to enable 2FA.', 'vendor-dashboard-pro'); ?></p>
                                </div>
                                <div class="vdp-form-group">
                                    <label for="auth_code" class="vdp-form-label"><?php esc_html_e('Verification Code', 'vendor-dashboard-pro'); ?></label>
                                    <input type="text" id="auth_code" name="auth_code" class="vdp-form-control" pattern="[0-9]{6}" maxlength="6" placeholder="000000">
                                </div>
                                <button type="button" class="vdp-btn vdp-btn-primary vdp-verify-2fa-btn">
                                    <?php esc_html_e('Verify & Enable 2FA', 'vendor-dashboard-pro'); ?>
                                </button>
                            </div>
                        <?php else : ?>
                            <div class="vdp-2fa-enabled">
                                <div class="vdp-notice vdp-notice-success">
                                    <p><?php esc_html_e('Two-factor authentication is currently enabled for your account.', 'vendor-dashboard-pro'); ?></p>
                                </div>
                                <button type="button" class="vdp-btn vdp-btn-danger vdp-disable-2fa-btn">
                                    <?php esc_html_e('Disable 2FA', 'vendor-dashboard-pro'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary vdp-btn-lg">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Account Settings', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Payment Methods Tab -->
            <div class="vdp-tab-content" id="payment-tab">
                <form id="payment-settings-form" class="vdp-settings-form">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Payment Accounts', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-payment-methods">
                            <div class="vdp-payment-method">
                                <div class="vdp-payment-method-header">
                                    <div class="vdp-payment-method-icon">
                                        <i class="fab fa-paypal"></i>
                                    </div>
                                    <div class="vdp-payment-method-title">
                                        <h4><?php esc_html_e('PayPal', 'vendor-dashboard-pro'); ?></h4>
                                        <p><?php esc_html_e('Connect your PayPal account to receive payments', 'vendor-dashboard-pro'); ?></p>
                                    </div>
                                    <div class="vdp-payment-method-status">
                                        <?php if ($payment_methods['paypal']['connected']) : ?>
                                            <span class="vdp-status-badge vdp-status-connected">
                                                <i class="fas fa-check-circle"></i> <?php esc_html_e('Connected', 'vendor-dashboard-pro'); ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="vdp-status-badge vdp-status-disconnected">
                                                <i class="fas fa-times-circle"></i> <?php esc_html_e('Not Connected', 'vendor-dashboard-pro'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="vdp-payment-method-content">
                                    <?php if ($payment_methods['paypal']['connected']) : ?>
                                        <div class="vdp-connected-account">
                                            <div class="vdp-account-info">
                                                <span class="vdp-account-email"><?php echo esc_html($payment_methods['paypal']['email']); ?></span>
                                                <span class="vdp-account-id"><?php esc_html_e('Account ID:', 'vendor-dashboard-pro'); ?> <?php echo esc_html($payment_methods['paypal']['account_id']); ?></span>
                                            </div>
                                            <div class="vdp-account-actions">
                                                <button type="button" class="vdp-btn vdp-btn-outline vdp-btn-sm vdp-disconnect-btn" data-method="paypal">
                                                    <?php esc_html_e('Disconnect', 'vendor-dashboard-pro'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div class="vdp-form-group">
                                            <label for="paypal_email" class="vdp-form-label"><?php esc_html_e('PayPal Email Address', 'vendor-dashboard-pro'); ?></label>
                                            <input type="email" id="paypal_email" name="paypal_email" class="vdp-form-control" placeholder="your@email.com">
                                        </div>
                                        <button type="button" class="vdp-btn vdp-btn-primary vdp-connect-btn" data-method="paypal">
                                            <?php esc_html_e('Connect PayPal', 'vendor-dashboard-pro'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="vdp-payment-method">
                                <div class="vdp-payment-method-header">
                                    <div class="vdp-payment-method-icon">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="vdp-payment-method-title">
                                        <h4><?php esc_html_e('Bank Account', 'vendor-dashboard-pro'); ?></h4>
                                        <p><?php esc_html_e('Set up direct bank transfers', 'vendor-dashboard-pro'); ?></p>
                                    </div>
                                    <div class="vdp-payment-method-status">
                                        <?php if ($payment_methods['bank']['connected']) : ?>
                                            <span class="vdp-status-badge vdp-status-connected">
                                                <i class="fas fa-check-circle"></i> <?php esc_html_e('Connected', 'vendor-dashboard-pro'); ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="vdp-status-badge vdp-status-disconnected">
                                                <i class="fas fa-times-circle"></i> <?php esc_html_e('Not Connected', 'vendor-dashboard-pro'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="vdp-payment-method-content">
                                    <?php if ($payment_methods['bank']['connected']) : ?>
                                        <div class="vdp-connected-account">
                                            <div class="vdp-account-info">
                                                <span class="vdp-account-holder"><?php echo esc_html($payment_methods['bank']['account_holder']); ?></span>
                                                <span class="vdp-account-number"><?php esc_html_e('Account: ****', 'vendor-dashboard-pro'); ?><?php echo esc_html($payment_methods['bank']['account_last4']); ?></span>
                                            </div>
                                            <div class="vdp-account-actions">
                                                <button type="button" class="vdp-btn vdp-btn-outline vdp-btn-sm vdp-edit-btn" data-method="bank">
                                                    <?php esc_html_e('Edit', 'vendor-dashboard-pro'); ?>
                                                </button>
                                                <button type="button" class="vdp-btn vdp-btn-outline vdp-btn-sm vdp-disconnect-btn" data-method="bank">
                                                    <?php esc_html_e('Disconnect', 'vendor-dashboard-pro'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div class="vdp-form-grid">
                                            <div class="vdp-form-group">
                                                <label for="bank_account_holder" class="vdp-form-label"><?php esc_html_e('Account Holder Name', 'vendor-dashboard-pro'); ?></label>
                                                <input type="text" id="bank_account_holder" name="bank_account_holder" class="vdp-form-control">
                                            </div>
                                            
                                            <div class="vdp-form-group">
                                                <label for="bank_account_number" class="vdp-form-label"><?php esc_html_e('Account Number', 'vendor-dashboard-pro'); ?></label>
                                                <input type="text" id="bank_account_number" name="bank_account_number" class="vdp-form-control">
                                            </div>
                                        </div>
                                        
                                        <div class="vdp-form-grid">
                                            <div class="vdp-form-group">
                                                <label for="bank_routing_number" class="vdp-form-label"><?php esc_html_e('Routing Number', 'vendor-dashboard-pro'); ?></label>
                                                <input type="text" id="bank_routing_number" name="bank_routing_number" class="vdp-form-control">
                                            </div>
                                            
                                            <div class="vdp-form-group">
                                                <label for="bank_name" class="vdp-form-label"><?php esc_html_e('Bank Name', 'vendor-dashboard-pro'); ?></label>
                                                <input type="text" id="bank_name" name="bank_name" class="vdp-form-control">
                                            </div>
                                        </div>
                                        
                                        <button type="button" class="vdp-btn vdp-btn-primary vdp-connect-btn" data-method="bank">
                                            <?php esc_html_e('Connect Bank Account', 'vendor-dashboard-pro'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="vdp-payment-method">
                                <div class="vdp-payment-method-header">
                                    <div class="vdp-payment-method-icon">
                                        <i class="fab fa-stripe"></i>
                                    </div>
                                    <div class="vdp-payment-method-title">
                                        <h4><?php esc_html_e('Stripe', 'vendor-dashboard-pro'); ?></h4>
                                        <p><?php esc_html_e('Connect your Stripe account for direct payments', 'vendor-dashboard-pro'); ?></p>
                                    </div>
                                    <div class="vdp-payment-method-status">
                                        <span class="vdp-status-badge vdp-status-premium">
                                            <i class="fas fa-crown"></i> <?php esc_html_e('Premium Feature', 'vendor-dashboard-pro'); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="vdp-payment-method-content">
                                    <div class="vdp-premium-feature">
                                        <p><?php esc_html_e('Stripe integration is available in the Premium version of Vendor Dashboard Pro.', 'vendor-dashboard-pro'); ?></p>
                                        <a href="#" class="vdp-btn vdp-btn-accent">
                                            <?php esc_html_e('Upgrade to Premium', 'vendor-dashboard-pro'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Payout Schedule', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="payout_schedule" class="vdp-form-label"><?php esc_html_e('Payout Frequency', 'vendor-dashboard-pro'); ?></label>
                            <select id="payout_schedule" name="payout_schedule" class="vdp-form-control">
                                <option value="daily" <?php selected($payment_settings['payout_schedule'], 'daily'); ?>><?php esc_html_e('Daily', 'vendor-dashboard-pro'); ?></option>
                                <option value="weekly" <?php selected($payment_settings['payout_schedule'], 'weekly'); ?>><?php esc_html_e('Weekly', 'vendor-dashboard-pro'); ?></option>
                                <option value="biweekly" <?php selected($payment_settings['payout_schedule'], 'biweekly'); ?>><?php esc_html_e('Bi-Weekly', 'vendor-dashboard-pro'); ?></option>
                                <option value="monthly" <?php selected($payment_settings['payout_schedule'], 'monthly'); ?>><?php esc_html_e('Monthly', 'vendor-dashboard-pro'); ?></option>
                            </select>
                            <div class="vdp-form-help"><?php esc_html_e('How often you want to receive payouts from your sales.', 'vendor-dashboard-pro'); ?></div>
                        </div>
                        
                        <div class="vdp-form-group vdp-weekly-options <?php echo ($payment_settings['payout_schedule'] === 'weekly' || $payment_settings['payout_schedule'] === 'biweekly') ? '' : 'vdp-hidden'; ?>">
                            <label for="payout_day" class="vdp-form-label"><?php esc_html_e('Payout Day', 'vendor-dashboard-pro'); ?></label>
                            <select id="payout_day" name="payout_day" class="vdp-form-control">
                                <option value="1" <?php selected($payment_settings['payout_day'], 1); ?>><?php esc_html_e('Monday', 'vendor-dashboard-pro'); ?></option>
                                <option value="2" <?php selected($payment_settings['payout_day'], 2); ?>><?php esc_html_e('Tuesday', 'vendor-dashboard-pro'); ?></option>
                                <option value="3" <?php selected($payment_settings['payout_day'], 3); ?>><?php esc_html_e('Wednesday', 'vendor-dashboard-pro'); ?></option>
                                <option value="4" <?php selected($payment_settings['payout_day'], 4); ?>><?php esc_html_e('Thursday', 'vendor-dashboard-pro'); ?></option>
                                <option value="5" <?php selected($payment_settings['payout_day'], 5); ?>><?php esc_html_e('Friday', 'vendor-dashboard-pro'); ?></option>
                                <option value="6" <?php selected($payment_settings['payout_day'], 6); ?>><?php esc_html_e('Saturday', 'vendor-dashboard-pro'); ?></option>
                                <option value="0" <?php selected($payment_settings['payout_day'], 0); ?>><?php esc_html_e('Sunday', 'vendor-dashboard-pro'); ?></option>
                            </select>
                        </div>
                        
                        <div class="vdp-form-group vdp-monthly-options <?php echo $payment_settings['payout_schedule'] === 'monthly' ? '' : 'vdp-hidden'; ?>">
                            <label for="payout_date" class="vdp-form-label"><?php esc_html_e('Payout Date', 'vendor-dashboard-pro'); ?></label>
                            <select id="payout_date" name="payout_date" class="vdp-form-control">
                                <?php for ($i = 1; $i <= 28; $i++) : ?>
                                    <option value="<?php echo esc_attr($i); ?>" <?php selected($payment_settings['payout_date'], $i); ?>><?php echo esc_html($i); ?></option>
                                <?php endfor; ?>
                                <option value="last" <?php selected($payment_settings['payout_date'], 'last'); ?>><?php esc_html_e('Last day of month', 'vendor-dashboard-pro'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary vdp-btn-lg">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Payment Settings', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Shipping Tab -->
            <div class="vdp-tab-content" id="shipping-tab">
                <form id="shipping-settings-form" class="vdp-settings-form">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Shipping Address', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="shipping_name" class="vdp-form-label"><?php esc_html_e('Full Name / Company Name', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="shipping_name" name="shipping_name" class="vdp-form-control" value="<?php echo esc_attr($shipping['name']); ?>">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="shipping_phone" class="vdp-form-label"><?php esc_html_e('Phone Number', 'vendor-dashboard-pro'); ?></label>
                                <input type="tel" id="shipping_phone" name="shipping_phone" class="vdp-form-control" value="<?php echo esc_attr($shipping['phone']); ?>">
                            </div>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="shipping_address1" class="vdp-form-label"><?php esc_html_e('Address Line 1', 'vendor-dashboard-pro'); ?></label>
                            <input type="text" id="shipping_address1" name="shipping_address1" class="vdp-form-control" value="<?php echo esc_attr($shipping['address1']); ?>">
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="shipping_address2" class="vdp-form-label"><?php esc_html_e('Address Line 2', 'vendor-dashboard-pro'); ?></label>
                            <input type="text" id="shipping_address2" name="shipping_address2" class="vdp-form-control" value="<?php echo esc_attr($shipping['address2']); ?>">
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="shipping_city" class="vdp-form-label"><?php esc_html_e('City', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="shipping_city" name="shipping_city" class="vdp-form-control" value="<?php echo esc_attr($shipping['city']); ?>">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="shipping_state" class="vdp-form-label"><?php esc_html_e('State / Province', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="shipping_state" name="shipping_state" class="vdp-form-control" value="<?php echo esc_attr($shipping['state']); ?>">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="shipping_postal_code" class="vdp-form-label"><?php esc_html_e('Postal / ZIP Code', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="shipping_postal_code" name="shipping_postal_code" class="vdp-form-control" value="<?php echo esc_attr($shipping['postal_code']); ?>">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="shipping_country" class="vdp-form-label"><?php esc_html_e('Country', 'vendor-dashboard-pro'); ?></label>
                                <select id="shipping_country" name="shipping_country" class="vdp-form-control">
                                    <?php foreach ($countries as $code => $name) : ?>
                                        <option value="<?php echo esc_attr($code); ?>" <?php selected($shipping['country'], $code); ?>><?php echo esc_html($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Shipping Options', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-shipping-options">
                            <div class="vdp-shipping-option">
                                <div class="vdp-shipping-option-header">
                                    <div class="vdp-toggle-control">
                                        <label class="vdp-toggle">
                                            <input type="checkbox" name="enable_standard" value="1" <?php checked($shipping['methods']['standard']['enabled']); ?>>
                                            <span class="vdp-toggle-switch"></span>
                                        </label>
                                    </div>
                                    <div class="vdp-shipping-option-info">
                                        <h4><?php esc_html_e('Standard Shipping', 'vendor-dashboard-pro'); ?></h4>
                                        <p><?php esc_html_e('3-5 business days', 'vendor-dashboard-pro'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="vdp-shipping-option-content">
                                    <div class="vdp-form-grid">
                                        <div class="vdp-form-group">
                                            <label for="standard_price" class="vdp-form-label"><?php esc_html_e('Price', 'vendor-dashboard-pro'); ?></label>
                                            <div class="vdp-input-group">
                                                <span class="vdp-input-group-text">$</span>
                                                <input type="number" id="standard_price" name="standard_price" class="vdp-form-control" value="<?php echo esc_attr($shipping['methods']['standard']['price']); ?>" min="0" step="0.01">
                                            </div>
                                        </div>
                                        
                                        <div class="vdp-form-group">
                                            <label for="standard_free_threshold" class="vdp-form-label"><?php esc_html_e('Free Shipping Threshold', 'vendor-dashboard-pro'); ?></label>
                                            <div class="vdp-input-group">
                                                <span class="vdp-input-group-text">$</span>
                                                <input type="number" id="standard_free_threshold" name="standard_free_threshold" class="vdp-form-control" value="<?php echo esc_attr($shipping['methods']['standard']['free_threshold']); ?>" min="0" step="0.01">
                                            </div>
                                            <div class="vdp-form-help"><?php esc_html_e('Orders above this amount qualify for free shipping. Set to 0 to disable.', 'vendor-dashboard-pro'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-shipping-option">
                                <div class="vdp-shipping-option-header">
                                    <div class="vdp-toggle-control">
                                        <label class="vdp-toggle">
                                            <input type="checkbox" name="enable_express" value="1" <?php checked($shipping['methods']['express']['enabled']); ?>>
                                            <span class="vdp-toggle-switch"></span>
                                        </label>
                                    </div>
                                    <div class="vdp-shipping-option-info">
                                        <h4><?php esc_html_e('Express Shipping', 'vendor-dashboard-pro'); ?></h4>
                                        <p><?php esc_html_e('1-2 business days', 'vendor-dashboard-pro'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="vdp-shipping-option-content">
                                    <div class="vdp-form-grid">
                                        <div class="vdp-form-group">
                                            <label for="express_price" class="vdp-form-label"><?php esc_html_e('Price', 'vendor-dashboard-pro'); ?></label>
                                            <div class="vdp-input-group">
                                                <span class="vdp-input-group-text">$</span>
                                                <input type="number" id="express_price" name="express_price" class="vdp-form-control" value="<?php echo esc_attr($shipping['methods']['express']['price']); ?>" min="0" step="0.01">
                                            </div>
                                        </div>
                                        
                                        <div class="vdp-form-group">
                                            <label for="express_free_threshold" class="vdp-form-label"><?php esc_html_e('Free Shipping Threshold', 'vendor-dashboard-pro'); ?></label>
                                            <div class="vdp-input-group">
                                                <span class="vdp-input-group-text">$</span>
                                                <input type="number" id="express_free_threshold" name="express_free_threshold" class="vdp-form-control" value="<?php echo esc_attr($shipping['methods']['express']['free_threshold']); ?>" min="0" step="0.01">
                                            </div>
                                            <div class="vdp-form-help"><?php esc_html_e('Orders above this amount qualify for free shipping. Set to 0 to disable.', 'vendor-dashboard-pro'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-shipping-option">
                                <div class="vdp-shipping-option-header">
                                    <div class="vdp-toggle-control">
                                        <label class="vdp-toggle">
                                            <input type="checkbox" name="enable_local" value="1" <?php checked($shipping['methods']['local_pickup']['enabled']); ?>>
                                            <span class="vdp-toggle-switch"></span>
                                        </label>
                                    </div>
                                    <div class="vdp-shipping-option-info">
                                        <h4><?php esc_html_e('Local Pickup', 'vendor-dashboard-pro'); ?></h4>
                                        <p><?php esc_html_e('Customers can pick up orders from your location', 'vendor-dashboard-pro'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="vdp-shipping-option-content">
                                    <div class="vdp-form-grid">
                                        <div class="vdp-form-group">
                                            <label for="local_price" class="vdp-form-label"><?php esc_html_e('Price', 'vendor-dashboard-pro'); ?></label>
                                            <div class="vdp-input-group">
                                                <span class="vdp-input-group-text">$</span>
                                                <input type="number" id="local_price" name="local_price" class="vdp-form-control" value="<?php echo esc_attr($shipping['methods']['local_pickup']['price']); ?>" min="0" step="0.01">
                                            </div>
                                        </div>
                                        
                                        <div class="vdp-form-group">
                                            <label for="local_instructions" class="vdp-form-label"><?php esc_html_e('Pickup Instructions', 'vendor-dashboard-pro'); ?></label>
                                            <textarea id="local_instructions" name="local_instructions" class="vdp-form-control" rows="3"><?php echo esc_textarea($shipping['methods']['local_pickup']['instructions']); ?></textarea>
                                            <div class="vdp-form-help"><?php esc_html_e('Instructions for customers who choose local pickup (business hours, location details, etc.)', 'vendor-dashboard-pro'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary vdp-btn-lg">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Shipping Settings', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Notifications Tab -->
            <div class="vdp-tab-content" id="notifications-tab">
                <form id="notifications-settings-form" class="vdp-settings-form">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Email Notifications', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-notification-options">
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_new_order" value="1" <?php checked($notifications['email']['new_order']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('New Order Notifications', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Receive an email when a new order is placed', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_order_status" value="1" <?php checked($notifications['email']['order_status']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('Order Status Changes', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Receive an email when an order status changes', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_new_message" value="1" <?php checked($notifications['email']['new_message']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('New Messages', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Receive an email when a customer sends you a message', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_new_review" value="1" <?php checked($notifications['email']['new_review']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('New Reviews', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Receive an email when a customer leaves a review', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_low_stock" value="1" <?php checked($notifications['email']['low_stock']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('Low Stock Alerts', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Receive an email when product stock is running low', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_payout" value="1" <?php checked($notifications['email']['payout']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('Payout Notifications', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Receive an email when a payout is processed', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Dashboard Notifications', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-notification-options">
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_dashboard_new_order" value="1" <?php checked($notifications['dashboard']['new_order']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('New Order Notifications', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Show notifications in the dashboard when a new order is placed', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_dashboard_new_message" value="1" <?php checked($notifications['dashboard']['new_message']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('New Messages', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Show notifications in the dashboard when a customer sends you a message', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_dashboard_new_review" value="1" <?php checked($notifications['dashboard']['new_review']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('New Reviews', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Show notifications in the dashboard when a customer leaves a review', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="vdp-notification-option">
                                <div class="vdp-toggle-control">
                                    <label class="vdp-toggle">
                                        <input type="checkbox" name="notify_dashboard_payout" value="1" <?php checked($notifications['dashboard']['payout']); ?>>
                                        <span class="vdp-toggle-switch"></span>
                                    </label>
                                    <div class="vdp-toggle-info">
                                        <span class="vdp-toggle-label"><?php esc_html_e('Payout Notifications', 'vendor-dashboard-pro'); ?></span>
                                        <span class="vdp-toggle-desc"><?php esc_html_e('Show notifications in the dashboard when a payout is processed', 'vendor-dashboard-pro'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Reports & Summaries', 'vendor-dashboard-pro'); ?></h3>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="sales_report_frequency" class="vdp-form-label"><?php esc_html_e('Sales Report Frequency', 'vendor-dashboard-pro'); ?></label>
                            <select id="sales_report_frequency" name="sales_report_frequency" class="vdp-form-control">
                                <option value="none" <?php selected($notifications['reports']['sales_frequency'], 'none'); ?>><?php esc_html_e('Do not send', 'vendor-dashboard-pro'); ?></option>
                                <option value="daily" <?php selected($notifications['reports']['sales_frequency'], 'daily'); ?>><?php esc_html_e('Daily', 'vendor-dashboard-pro'); ?></option>
                                <option value="weekly" <?php selected($notifications['reports']['sales_frequency'], 'weekly'); ?>><?php esc_html_e('Weekly', 'vendor-dashboard-pro'); ?></option>
                                <option value="monthly" <?php selected($notifications['reports']['sales_frequency'], 'monthly'); ?>><?php esc_html_e('Monthly', 'vendor-dashboard-pro'); ?></option>
                            </select>
                            <div class="vdp-form-help"><?php esc_html_e('How often you want to receive sales report emails', 'vendor-dashboard-pro'); ?></div>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="inventory_report_frequency" class="vdp-form-label"><?php esc_html_e('Inventory Report Frequency', 'vendor-dashboard-pro'); ?></label>
                            <select id="inventory_report_frequency" name="inventory_report_frequency" class="vdp-form-control">
                                <option value="none" <?php selected($notifications['reports']['inventory_frequency'], 'none'); ?>><?php esc_html_e('Do not send', 'vendor-dashboard-pro'); ?></option>
                                <option value="weekly" <?php selected($notifications['reports']['inventory_frequency'], 'weekly'); ?>><?php esc_html_e('Weekly', 'vendor-dashboard-pro'); ?></option>
                                <option value="biweekly" <?php selected($notifications['reports']['inventory_frequency'], 'biweekly'); ?>><?php esc_html_e('Bi-Weekly', 'vendor-dashboard-pro'); ?></option>
                                <option value="monthly" <?php selected($notifications['reports']['inventory_frequency'], 'monthly'); ?>><?php esc_html_e('Monthly', 'vendor-dashboard-pro'); ?></option>
                            </select>
                            <div class="vdp-form-help"><?php esc_html_e('How often you want to receive inventory status reports', 'vendor-dashboard-pro'); ?></div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary vdp-btn-lg">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Notification Settings', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.vdp-tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        
        // Update active tab button
        $('.vdp-tab-btn').removeClass('vdp-active');
        $(this).addClass('vdp-active');
        
        // Show active tab content
        $('.vdp-tab-content').removeClass('vdp-active');
        $('#' + tab + '-tab').addClass('vdp-active');
        
        // Update URL hash
        window.location.hash = tab;
    });
    
    // Check URL hash on page load
    if (window.location.hash) {
        var hash = window.location.hash.substring(1);
        $('.vdp-tab-btn[data-tab="' + hash + '"]').click();
    }
    
    // File input preview
    $('.vdp-file-input').on('change', function() {
        var file = this.files[0];
        var $uploader = $(this).closest('.vdp-image-uploader');
        
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $uploader.find('.vdp-current-image').html('<img src="' + e.target.result + '" alt="">');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // File button click
    $('.vdp-file-btn').on('click', function() {
        $(this).siblings('.vdp-file-input').click();
    });
    
    // Remove image
    $('.vdp-remove-image-btn').on('click', function() {
        var $uploader = $(this).closest('.vdp-image-uploader');
        
        if ($uploader.hasClass('vdp-logo-uploader')) {
            $uploader.find('.vdp-current-image').html(
                '<div class="vdp-image-placeholder">' +
                '<i class="fas fa-store"></i>' +
                '<span><?php esc_html_e('No logo uploaded', 'vendor-dashboard-pro'); ?></span>' +
                '</div>'
            );
        } else if ($uploader.hasClass('vdp-banner-uploader')) {
            $uploader.find('.vdp-current-image').html(
                '<div class="vdp-image-placeholder">' +
                '<i class="fas fa-image"></i>' +
                '<span><?php esc_html_e('No banner uploaded', 'vendor-dashboard-pro'); ?></span>' +
                '</div>'
            );
        }
        
        // Reset file input
        $uploader.find('.vdp-file-input').val('');
        
        // Remove the remove button
        $(this).remove();
    });
    
    // Toggle 2FA setup
    $('input[name="enable_2fa"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('.vdp-setup-2fa').removeClass('vdp-hidden');
        } else {
            $('.vdp-setup-2fa').addClass('vdp-hidden');
        }
    });
    
    // Show/hide payment schedule options
    $('#payout_schedule').on('change', function() {
        var schedule = $(this).val();
        
        if (schedule === 'weekly' || schedule === 'biweekly') {
            $('.vdp-weekly-options').removeClass('vdp-hidden');
            $('.vdp-monthly-options').addClass('vdp-hidden');
        } else if (schedule === 'monthly') {
            $('.vdp-weekly-options').addClass('vdp-hidden');
            $('.vdp-monthly-options').removeClass('vdp-hidden');
        } else {
            $('.vdp-weekly-options, .vdp-monthly-options').addClass('vdp-hidden');
        }
    });
    
    // Payment method connect buttons
    $('.vdp-connect-btn').on('click', function() {
        var method = $(this).data('method');
        var $btn = $(this);
        
        // Show loading state
        $btn.addClass('vdp-btn-loading').prop('disabled', true);
        
        // Simulate connection process
        setTimeout(function() {
            // Display success message
            var notification = $('<div class="vdp-notification vdp-notification-success">' + method + ' account connected successfully!</div>');
            $('.vdp-settings-content').prepend(notification);
            
            // Hide notification after 3 seconds
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
            
            // Refresh the page to show connected state (in a real implementation)
            // For demo, we'll just reload the page
            window.location.reload();
        }, 2000);
    });
    
    // Form submission handling
    $('.vdp-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        
        // Show loading state
        $submitBtn.addClass('vdp-btn-loading').prop('disabled', true);
        
        // Simulate saving settings
        setTimeout(function() {
            // Display success message
            var notification = $('<div class="vdp-notification vdp-notification-success">Settings saved successfully!</div>');
            $('.vdp-settings-content').prepend(notification);
            
            // Reset button state
            $submitBtn.removeClass('vdp-btn-loading').prop('disabled', false);
            
            // Hide notification after 3 seconds
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }, 1500);
    });
});
</script>