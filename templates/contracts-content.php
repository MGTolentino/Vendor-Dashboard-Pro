<?php
/**
 * Contracts content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get current vendor
$vendor = vdp_get_current_vendor();
if (!$vendor) {
    return;
}

// Get contracts module
$contracts_module = VDP_Contracts::get_instance();
$contract_settings = $contracts_module->get_contract_settings($vendor->get_id());
?>

<div class="vdp-contracts-content">
    <div class="vdp-page-header">
        <h1 class="vdp-page-title">
            <i class="fas fa-file-contract"></i>
            <?php esc_html_e('Contract Settings', 'vendor-dashboard-pro'); ?>
        </h1>
        <p class="vdp-page-description">
            <?php esc_html_e('Configure your contract templates, payment schedules, and terms.', 'vendor-dashboard-pro'); ?>
        </p>
    </div>
    
    <div class="vdp-contracts-tabs">
        <div class="vdp-tabs-nav">
            <button class="vdp-tab-btn vdp-active" data-tab="company">
                <i class="fas fa-building"></i> <?php esc_html_e('Company Info', 'vendor-dashboard-pro'); ?>
            </button>
            <button class="vdp-tab-btn" data-tab="payment-templates">
                <i class="fas fa-credit-card"></i> <?php esc_html_e('Payment Templates', 'vendor-dashboard-pro'); ?>
            </button>
            <button class="vdp-tab-btn" data-tab="terms">
                <i class="fas fa-file-alt"></i> <?php esc_html_e('Contract Terms', 'vendor-dashboard-pro'); ?>
            </button>
            <button class="vdp-tab-btn" data-tab="bank-info">
                <i class="fas fa-university"></i> <?php esc_html_e('Bank Information', 'vendor-dashboard-pro'); ?>
            </button>
            <button class="vdp-tab-btn" data-tab="validation">
                <i class="fas fa-shield-alt"></i> <?php esc_html_e('Reglas de Validación', 'vendor-dashboard-pro'); ?>
            </button>
        </div>
        
        <div class="vdp-tabs-content">
            <!-- Company Info Tab -->
            <div class="vdp-tab-content vdp-active" id="company-tab">
                <form id="company-info-form" class="vdp-contracts-form" enctype="multipart/form-data">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Company Information for Contracts', 'vendor-dashboard-pro'); ?></h3>
                            <p class="vdp-section-subtitle"><?php esc_html_e('This information will appear on all generated contracts', 'vendor-dashboard-pro'); ?></p>
                            <button type="button" class="vdp-btn vdp-btn-secondary vdp-btn-sm" id="copy-from-settings">
                                <i class="fas fa-copy"></i> <?php esc_html_e('Copiar desde Configuración de Tienda', 'vendor-dashboard-pro'); ?>
                            </button>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="company-name" class="vdp-form-label"><?php esc_html_e('Company Name', 'vendor-dashboard-pro'); ?> <span class="vdp-required">*</span></label>
                            <input type="text" id="company-name" name="company_name" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['company_data']['name']); ?>" required>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="company-address" class="vdp-form-label"><?php esc_html_e('Company Address', 'vendor-dashboard-pro'); ?> <span class="vdp-required">*</span></label>
                            <textarea id="company-address" name="company_address" class="vdp-form-control" rows="3" required><?php echo esc_textarea($contract_settings['company_data']['address']); ?></textarea>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="company-phone" class="vdp-form-label"><?php esc_html_e('Phone Number', 'vendor-dashboard-pro'); ?> <span class="vdp-required">*</span></label>
                                <input type="tel" id="company-phone" name="company_phone" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['company_data']['phone']); ?>" required>
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="company-email" class="vdp-form-label"><?php esc_html_e('Company Email', 'vendor-dashboard-pro'); ?> <span class="vdp-required">*</span></label>
                                <input type="email" id="company-email" name="company_email" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['company_data']['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="company-rfc" class="vdp-form-label"><?php esc_html_e('Tax ID (RFC)', 'vendor-dashboard-pro'); ?></label>
                            <input type="text" id="company-rfc" name="company_rfc" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['company_data']['rfc']); ?>">
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="contract-logo" class="vdp-form-label"><?php esc_html_e('Contract Logo', 'vendor-dashboard-pro'); ?></label>
                            <div class="vdp-logo-uploader">
                                <div class="vdp-current-logo">
                                    <?php if (!empty($contract_settings['company_data']['logo_url'])): ?>
                                        <img src="<?php echo esc_url($contract_settings['company_data']['logo_url']); ?>" alt="Contract Logo" style="max-width: 200px; max-height: 80px; border: 1px solid #ddd; border-radius: 4px;">
                                    <?php else: ?>
                                        <div class="vdp-logo-placeholder">
                                            <i class="fas fa-image"></i>
                                            <span><?php esc_html_e('No logo uploaded', 'vendor-dashboard-pro'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="vdp-logo-controls">
                                    <input type="file" id="contract-logo" name="logo_file" class="vdp-file-input" accept="image/*" style="display: none;">
                                    <button type="button" class="vdp-btn vdp-btn-outline vdp-logo-btn">
                                        <i class="fas fa-upload"></i> <?php esc_html_e('Upload Logo', 'vendor-dashboard-pro'); ?>
                                    </button>
                                    <?php if (!empty($contract_settings['company_data']['logo_url'])): ?>
                                        <button type="button" class="vdp-btn vdp-btn-outline vdp-remove-logo">
                                            <i class="fas fa-trash"></i> <?php esc_html_e('Remove', 'vendor-dashboard-pro'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="logo-url" name="logo_url" value="<?php echo esc_attr($contract_settings['company_data']['logo_url'] ?? ''); ?>">
                            </div>
                            <div class="vdp-form-help"><?php esc_html_e('Upload a logo that will appear on your contracts. Recommended size: 200x80px', 'vendor-dashboard-pro'); ?></div>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="razon-social" class="vdp-form-label"><?php esc_html_e('Business Name (Razón Social)', 'vendor-dashboard-pro'); ?></label>
                            <input type="text" id="razon-social" name="razon_social" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['company_data']['razon_social'] ?? ''); ?>">
                            <div class="vdp-form-help"><?php esc_html_e('Official business name that will appear in contract bank information', 'vendor-dashboard-pro'); ?></div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Company Info', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Payment Templates Tab -->
            <div class="vdp-tab-content" id="payment-templates-tab">
                <div class="vdp-form-section">
                    <div class="vdp-section-header">
                        <h3 class="vdp-section-title"><?php esc_html_e('Plantillas de Pago', 'vendor-dashboard-pro'); ?></h3>
                        <p class="vdp-section-subtitle"><?php esc_html_e('Crea y gestiona plantillas de esquemas de pago para tus contratos', 'vendor-dashboard-pro'); ?></p>
                    </div>
                    
                    <!-- Smart Templates Based on Event Timeline -->
                    <div class="vdp-smart-templates">
                        <h4><?php esc_html_e('Plantillas Inteligentes', 'vendor-dashboard-pro'); ?></h4>
                        <p class="vdp-help-text"><?php esc_html_e('Estas plantillas se ajustan automáticamente según los días hasta el evento', 'vendor-dashboard-pro'); ?></p>
                        
                        <div class="vdp-smart-templates-grid">
                            <div class="vdp-smart-template" data-template="instant">
                                <div class="template-icon">⚡</div>
                                <h5>Pago Inmediato</h5>
                                <p>100% al contratar</p>
                                <div class="template-timeline">
                                    <div class="timeline-bar">
                                        <div class="payment-segment full" style="width: 100%;">100%</div>
                                    </div>
                                </div>
                                <span class="use-case">Eventos en menos de 15 días</span>
                            </div>
                            
                            <div class="vdp-smart-template active" data-template="50-50">
                                <div class="template-icon">📋</div>
                                <h5>50% - 50%</h5>
                                <p>Mitad al contratar, mitad antes del evento</p>
                                <div class="template-timeline">
                                    <div class="timeline-bar">
                                        <div class="payment-segment first" style="width: 50%;">50%</div>
                                        <div class="payment-segment second" style="width: 50%;">50%</div>
                                    </div>
                                </div>
                                <span class="use-case">Eventos de 15-60 días</span>
                                <div class="default-badge">Por defecto</div>
                            </div>
                            
                            <div class="vdp-smart-template" data-template="flexible">
                                <div class="template-icon">📅</div>
                                <h5>Pagos Flexibles</h5>
                                <p>30% inicial, 40% intermedio, 30% final</p>
                                <div class="template-timeline">
                                    <div class="timeline-bar">
                                        <div class="payment-segment first" style="width: 30%;">30%</div>
                                        <div class="payment-segment second" style="width: 40%;">40%</div>
                                        <div class="payment-segment third" style="width: 30%;">30%</div>
                                    </div>
                                </div>
                                <span class="use-case">Eventos de 60+ días</span>
                            </div>
                            
                            <div class="vdp-smart-template" data-template="monthly">
                                <div class="template-icon">📊</div>
                                <h5>Mensualidades</h5>
                                <p>Pagos mensuales iguales</p>
                                <div class="template-timeline">
                                    <div class="timeline-bar">
                                        <div class="payment-segment equal" style="width: 20%;">20%</div>
                                        <div class="payment-segment equal" style="width: 20%;">20%</div>
                                        <div class="payment-segment equal" style="width: 20%;">20%</div>
                                        <div class="payment-segment equal" style="width: 20%;">20%</div>
                                        <div class="payment-segment equal" style="width: 20%;">20%</div>
                                    </div>
                                </div>
                                <span class="use-case">Eventos de 90+ días</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Existing Custom Templates -->
                    <div class="vdp-custom-templates">
                        <div class="section-header-with-action">
                            <h4><?php esc_html_e('Plantillas Personalizadas', 'vendor-dashboard-pro'); ?></h4>
                            <button class="vdp-btn vdp-btn-primary vdp-btn-sm" id="add-payment-template">
                                <i class="fas fa-plus"></i> <?php esc_html_e('Crear Plantilla', 'vendor-dashboard-pro'); ?>
                            </button>
                        </div>
                    
                        <div class="vdp-payment-templates-list templates-grid">
                        <?php foreach ($contract_settings['payment_templates'] as $template): ?>
                            <div class="vdp-payment-template-card" data-template-id="<?php echo esc_attr($template['id']); ?>">
                                <div class="vdp-template-header">
                                    <div class="vdp-template-info">
                                        <h4 class="vdp-template-name">
                                            <?php echo esc_html($template['name']); ?>
                                            <?php if ($template['is_default']): ?>
                                                <span class="vdp-badge vdp-badge-primary">Por Defecto</span>
                                            <?php endif; ?>
                                        </h4>
                                        <p class="vdp-template-meta">
                                            <?php echo count($template['payments']); ?> pagos
                                            · Mín <?php echo $template['min_days_required']; ?> días
                                        </p>
                                    </div>
                                    <div class="vdp-template-actions">
                                        <button class="vdp-btn vdp-btn-sm vdp-btn-outline edit-template">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <?php if (!in_array($template['id'], ['full_payment', '50_50', '3_months', '6_months'])): ?>
                                            <button class="vdp-btn vdp-btn-sm vdp-btn-danger delete-template">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="vdp-template-payments">
                                    <?php foreach ($template['payments'] as $payment): ?>
                                        <div class="vdp-payment-item">
                                            <span class="vdp-payment-percentage"><?php echo number_format($payment['percentage'], 2); ?>%</span>
                                            <span class="vdp-payment-timing">
                                                <?php if (isset($payment['days_from_contract'])): ?>
                                                    <?php if ($payment['days_from_contract'] == 0): ?>
                                                        Al firmar contrato
                                                    <?php else: ?>
                                                        <?php echo $payment['days_from_contract']; ?> días después del contrato
                                                    <?php endif; ?>
                                                <?php elseif (isset($payment['days_before_event'])): ?>
                                                    <?php echo $payment['days_before_event']; ?> días antes del evento
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contract Terms Tab -->
            <div class="vdp-tab-content" id="terms-tab">
                <form id="contract-terms-form" class="vdp-contracts-form">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Contract Terms and Conditions', 'vendor-dashboard-pro'); ?></h3>
                            <p class="vdp-section-subtitle"><?php esc_html_e('Define the legal terms that will appear in all your contracts', 'vendor-dashboard-pro'); ?></p>
                        </div>
                        
                        <div class="vdp-form-group">
                            <label for="contract-terms" class="vdp-form-label"><?php esc_html_e('Contract Terms', 'vendor-dashboard-pro'); ?></label>
                            <textarea id="contract-terms" name="contract_terms" class="vdp-form-control vdp-contract-terms-editor" rows="20"><?php echo esc_textarea($contract_settings['contract_terms']); ?></textarea>
                            <div class="vdp-form-help">
                                <?php esc_html_e('These terms will be included in every contract. You can use the following placeholders:', 'vendor-dashboard-pro'); ?>
                                <br>
                                <code>{event_date}</code> - <?php esc_html_e('Event date', 'vendor-dashboard-pro'); ?>,
                                <code>{client_name}</code> - <?php esc_html_e('Client name', 'vendor-dashboard-pro'); ?>,
                                <code>{total_amount}</code> - <?php esc_html_e('Contract total', 'vendor-dashboard-pro'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Terms', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Bank Information Tab -->
            <div class="vdp-tab-content" id="bank-info-tab">
                <form id="bank-info-form" class="vdp-contracts-form">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Bank Account Information', 'vendor-dashboard-pro'); ?></h3>
                            <p class="vdp-section-subtitle"><?php esc_html_e('Banking details that will appear on contracts for client payments', 'vendor-dashboard-pro'); ?></p>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="bank-name" class="vdp-form-label"><?php esc_html_e('Bank Name', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="bank-name" name="bank_name" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['bank_data']['bank_name']); ?>">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="account-holder" class="vdp-form-label"><?php esc_html_e('Account Holder', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="account-holder" name="account_holder" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['bank_data']['account_holder']); ?>">
                            </div>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="account-number" class="vdp-form-label"><?php esc_html_e('Account Number', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="account-number" name="account_number" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['bank_data']['account_number']); ?>">
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="clabe" class="vdp-form-label"><?php esc_html_e('CLABE', 'vendor-dashboard-pro'); ?></label>
                                <input type="text" id="clabe" name="clabe" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['bank_data']['clabe']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Bank Info', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Validation Rules Tab -->
            <div class="vdp-tab-content" id="validation-tab">
                <form id="validation-rules-form" class="vdp-contracts-form">
                    <div class="vdp-form-section">
                        <div class="vdp-section-header">
                            <h3 class="vdp-section-title"><?php esc_html_e('Payment Validation Rules', 'vendor-dashboard-pro'); ?></h3>
                            <p class="vdp-section-subtitle"><?php esc_html_e('Configure rules to ensure payment schedules are realistic and enforceable', 'vendor-dashboard-pro'); ?></p>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="min-days-before-event" class="vdp-form-label"><?php esc_html_e('Minimum days before event for final payment', 'vendor-dashboard-pro'); ?></label>
                                <input type="number" id="min-days-before-event" name="min_days_before_event" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['validation_rules']['min_days_before_event']); ?>" min="1" max="30">
                                <div class="vdp-form-help"><?php esc_html_e('Recommended: 7 days', 'vendor-dashboard-pro'); ?></div>
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="force-full-payment-days" class="vdp-form-label"><?php esc_html_e('Force full payment if event is within X days', 'vendor-dashboard-pro'); ?></label>
                                <input type="number" id="force-full-payment-days" name="force_full_payment_days" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['validation_rules']['force_full_payment_days']); ?>" min="7" max="60">
                                <div class="vdp-form-help"><?php esc_html_e('Recommended: 15 days', 'vendor-dashboard-pro'); ?></div>
                            </div>
                        </div>
                        
                        <div class="vdp-form-grid">
                            <div class="vdp-form-group">
                                <label for="min-initial-payment" class="vdp-form-label"><?php esc_html_e('Minimum initial payment percentage', 'vendor-dashboard-pro'); ?></label>
                                <input type="number" id="min-initial-payment" name="min_initial_payment" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['validation_rules']['min_initial_payment']); ?>" min="10" max="100">
                                <div class="vdp-form-help">%</div>
                            </div>
                            
                            <div class="vdp-form-group">
                                <label for="max-payment-terms" class="vdp-form-label"><?php esc_html_e('Maximum payment terms (months)', 'vendor-dashboard-pro'); ?></label>
                                <input type="number" id="max-payment-terms" name="max_payment_terms" class="vdp-form-control" value="<?php echo esc_attr($contract_settings['validation_rules']['max_payment_terms']); ?>" min="1" max="24">
                                <div class="vdp-form-help"><?php esc_html_e('months', 'vendor-dashboard-pro'); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vdp-form-actions">
                        <button type="submit" class="vdp-btn vdp-btn-primary">
                            <i class="fas fa-save"></i> <?php esc_html_e('Save Validation Rules', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Template Modal -->
<div id="vdp-payment-template-modal" class="vdp-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999999; align-items: center; justify-content: center;">
    <div class="vdp-modal-content" style="background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 90vh; overflow-y: auto; position: relative;">
        <span class="vdp-modal-close" style="position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer;">&times;</span>
        <h2 id="template-modal-title"><?php esc_html_e('Add Payment Template', 'vendor-dashboard-pro'); ?></h2>
        
        <form id="payment-template-form">
            <div class="vdp-form-group">
                <label for="template-name" class="vdp-form-label"><?php esc_html_e('Template Name', 'vendor-dashboard-pro'); ?></label>
                <input type="text" id="template-name" name="template_name" class="vdp-form-control" required>
            </div>
            
            <div class="vdp-form-group">
                <label for="min-days" class="vdp-form-label"><?php esc_html_e('Minimum days required before event', 'vendor-dashboard-pro'); ?></label>
                <input type="number" id="min-days" name="min_days_required" class="vdp-form-control" value="0" min="0">
            </div>
            
            <div class="vdp-form-group">
                <label class="vdp-checkbox-label">
                    <input type="checkbox" id="is-default" name="is_default">
                    <?php esc_html_e('Set as default template', 'vendor-dashboard-pro'); ?>
                </label>
            </div>
            
            <div class="vdp-payments-section">
                <h4><?php esc_html_e('Payment Schedule', 'vendor-dashboard-pro'); ?></h4>
                <div id="template-payments">
                    <!-- Payment items will be added here -->
                </div>
                
                <button type="button" class="vdp-btn vdp-btn-outline" id="add-payment-item">
                    <i class="fas fa-plus"></i> <?php esc_html_e('Add Payment', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
            
            <div class="vdp-template-validation">
                <div class="vdp-validation-summary">
                    <span><?php esc_html_e('Total:', 'vendor-dashboard-pro'); ?> <span id="template-total">0%</span></span>
                </div>
            </div>
            
            <div class="vdp-form-actions">
                <button type="button" class="vdp-btn vdp-btn-secondary" id="cancel-template">
                    <?php esc_html_e('Cancel', 'vendor-dashboard-pro'); ?>
                </button>
                <button type="submit" class="vdp-btn vdp-btn-primary">
                    <i class="fas fa-save"></i> <?php esc_html_e('Save Template', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Copy from settings button
    $('#copy-from-settings').on('click', function() {
        if (confirm('¿Desea copiar los datos de la configuración de la tienda? Esto reemplazará los valores actuales.')) {
            $.ajax({
                url: vdp_ajax.url,
                type: 'POST',
                data: {
                    action: 'vdp_get_store_settings',
                    nonce: vdp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        // Fill company info fields
                        $('#company-name').val(data.store_name || '');
                        $('#company-phone').val(data.store_phone || '');
                        $('#company-email').val(data.store_email || '');
                        
                        // Fill bank info if available
                        if (data.bank_name) {
                            $('#bank-name').val(data.bank_name);
                        }
                        if (data.account_holder) {
                            $('#account-holder').val(data.account_holder);
                        }
                        if (data.account_number) {
                            $('#account-number').val(data.account_number);
                        }
                        
                        showNotification('success', 'Datos copiados exitosamente');
                    } else {
                        showNotification('error', 'Error al obtener los datos de la tienda');
                    }
                },
                error: function() {
                    showNotification('error', 'Error de conexión');
                }
            });
        }
    });
    
    // Tab switching
    $('.vdp-tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        
        $('.vdp-tab-btn').removeClass('vdp-active');
        $(this).addClass('vdp-active');
        
        $('.vdp-tab-content').removeClass('vdp-active');
        $('#' + tab + '-tab').addClass('vdp-active');
        
        if (tab === 'preview') {
            generateContractPreview();
        }
    });
    
    // Form submissions
    $('.vdp-contracts-form').on('submit', function(e) {
        e.preventDefault();
        saveContractSettings($(this));
    });
    
    // Payment template actions
    $('#add-payment-template').on('click', function() {
        openPaymentTemplateModal();
    });
    
    $('.edit-template').on('click', function() {
        var templateId = $(this).closest('.vdp-payment-template-card').data('template-id');
        editPaymentTemplate(templateId);
    });
    
    $('.delete-template').on('click', function() {
        var templateId = $(this).closest('.vdp-payment-template-card').data('template-id');
        deletePaymentTemplate(templateId);
    });
    
    // Payment template modal
    $('#add-payment-item').on('click', function() {
        addPaymentItem();
    });
    
    $('#payment-template-form').on('submit', function(e) {
        e.preventDefault();
        savePaymentTemplate();
    });
    
    $('#cancel-template').on('click', function() {
        $('#vdp-payment-template-modal').css('display', 'none');
    });
    
    // Close modal when clicking close button
    $('.vdp-modal-close').on('click', function() {
        $('#vdp-payment-template-modal').css('display', 'none');
    });
    
    // Close modal when clicking outside
    $('#vdp-payment-template-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
        }
    });
    
    // Preview refresh
    $('#refresh-preview').on('click', function() {
        generateContractPreview();
    });
    
    // Logo upload functionality
    $('.vdp-logo-btn').on('click', function() {
        $('#contract-logo').click();
    });

    $('#contract-logo').on('change', function() {
        const file = this.files[0];
        if (file) {
            uploadContractLogo(file);
        }
    });

    $('.vdp-remove-logo').on('click', function() {
        removeContractLogo();
    });
    
    // Functions
    function saveContractSettings($form) {
        // Validate form before sending
        if (!validateContractForm($form)) {
            return false;
        }
        
        var formData = $form.serialize();
        
        // Determine which section is being saved based on form ID
        var section = 'all';
        var formId = $form.attr('id');
        
        if (formId === 'company-info-form') {
            section = 'company';
        } else if (formId === 'bank-info-form') {
            section = 'bank';
        } else if (formId === 'contract-terms-form') {
            section = 'terms';
        } else if (formId === 'validation-rules-form') {
            section = 'validation';
        }
        
        formData += '&action=vdp_save_contract_settings&nonce=' + vdp_vars.nonce + '&section=' + section;
        
        console.log('VDP Contracts - Saving section:', section);
        console.log('VDP Contracts - Form ID:', formId);
        console.log('VDP Contracts - Form data:', formData);
        
        // Show loading state
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.post(vdp_ajax.url, formData, function(response) {
            if (response.success) {
                showNotification('success', 'Settings saved successfully');
            } else {
                showNotification('error', response.data || 'Error saving settings');
            }
        }).fail(function() {
            showNotification('error', 'Network error. Please try again.');
        }).always(function() {
            // Restore button state
            $submitBtn.prop('disabled', false).html(originalText);
        });
    }
    
    function validateContractForm($form) {
        var isValid = true;
        var errors = [];
        
        // Clear previous errors
        $form.find('.vdp-field-error').remove();
        $form.find('.vdp-form-control').removeClass('error');
        
        // Get form ID to determine which fields to validate
        var formId = $form.attr('id');
        
        if (formId === 'company-info-form') {
            // Validate company email
            var email = $form.find('#company-email').val().trim();
            if (email && !isValidEmail(email)) {
                showFieldError($form.find('#company-email'), 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate required fields
            var requiredFields = ['#company-name', '#company-address', '#company-phone', '#company-email'];
            requiredFields.forEach(function(fieldId) {
                var $field = $form.find(fieldId);
                if (!$field.val().trim()) {
                    showFieldError($field, 'This field is required');
                    isValid = false;
                }
            });
        }
        
        if (!isValid) {
            showNotification('error', 'Please fix the errors below before saving');
        }
        
        return isValid;
    }
    
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showFieldError($field, message) {
        $field.addClass('error');
        var $error = $('<div class=\"vdp-field-error\" style=\"color: #dc3545; font-size: 12px; margin-top: 5px;\">' + message + '</div>');
        $field.closest('.vdp-form-group').append($error);
    }
    
    function openPaymentTemplateModal(templateData = null) {
        $('#payment-template-form')[0].reset();
        $('#template-payments').empty();
        
        if (templateData) {
            $('#template-modal-title').text('Edit Payment Template');
            $('#template-name').val(templateData.name);
            $('#min-days').val(templateData.min_days_required);
            $('#is-default').prop('checked', templateData.is_default);
            
            templateData.payments.forEach(function(payment) {
                addPaymentItem(payment);
            });
        } else {
            $('#template-modal-title').text('Add Payment Template');
            addPaymentItem(); // Add one default payment item
        }
        
        $('#vdp-payment-template-modal').css('display', 'flex');
        updateTemplateTotal();
    }
    
    function addPaymentItem(payment = null) {
        var index = $('#template-payments .payment-item').length;
        var html = `
            <div class="payment-item">
                <div class="payment-item-fields">
                    <input type="number" name="percentage[]" placeholder="Percentage" value="${payment ? payment.percentage : ''}" min="0" max="100" step="0.01">
                    <select name="timing_type[]">
                        <option value="days_from_contract" ${payment && payment.days_from_contract !== undefined ? 'selected' : ''}>Days after contract</option>
                        <option value="days_before_event" ${payment && payment.days_before_event !== undefined ? 'selected' : ''}>Days before event</option>
                    </select>
                    <input type="number" name="timing_value[]" placeholder="Days" value="${payment ? (payment.days_from_contract || payment.days_before_event) : ''}" min="0">
                    <input type="text" name="description[]" placeholder="Description" value="${payment ? payment.description : ''}">
                    <button type="button" class="remove-payment"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        
        $('#template-payments').append(html);
        
        // Bind remove event
        $('#template-payments .remove-payment').last().on('click', function() {
            $(this).closest('.payment-item').remove();
            updateTemplateTotal();
        });
        
        // Bind change events for validation
        $('#template-payments input[name="percentage[]"]').on('input', updateTemplateTotal);
    }
    
    function updateTemplateTotal() {
        var total = 0;
        $('#template-payments input[name="percentage[]"]').each(function() {
            var value = parseFloat($(this).val()) || 0;
            total += value;
        });
        
        $('#template-total').text(total.toFixed(2) + '%');
        
        if (Math.abs(total - 100) < 0.01) {
            $('#template-total').removeClass('invalid').addClass('valid');
        } else {
            $('#template-total').removeClass('valid').addClass('invalid');
        }
    }
    
    function savePaymentTemplate() {
        var payments = [];
        $('#template-payments .payment-item').each(function() {
            var $item = $(this);
            var percentage = parseFloat($item.find('input[name="percentage[]"]').val()) || 0;
            var timingType = $item.find('select[name="timing_type[]"]').val();
            var timingValue = parseInt($item.find('input[name="timing_value[]"]').val()) || 0;
            var description = $item.find('input[name="description[]"]').val();
            
            var payment = {
                percentage: percentage,
                description: description
            };
            
            if (timingType === 'days_from_contract') {
                payment.days_from_contract = timingValue;
            } else {
                payment.days_before_event = timingValue;
            }
            
            payments.push(payment);
        });
        
        var data = {
            action: 'vdp_save_payment_template',
            nonce: vdp_vars.nonce,
            template_id: 'custom_' + Date.now(),
            template_name: $('#template-name').val(),
            min_days_required: $('#min-days').val(),
            is_default: $('#is-default').is(':checked'),
            payments: JSON.stringify(payments)
        };
        
        $.post(vdp_ajax.url, data, function(response) {
            if (response.success) {
                showNotification('success', 'Template saved successfully');
                $('#vdp-payment-template-modal').hide();
                location.reload(); // Refresh to show new template
            } else {
                showNotification('error', response.data || 'Error saving template');
            }
        });
    }
    
    function deletePaymentTemplate(templateId) {
        if (!confirm('Are you sure you want to delete this template?')) {
            return;
        }
        
        var data = {
            action: 'vdp_delete_payment_template',
            nonce: vdp_vars.nonce,
            template_id: templateId
        };
        
        $.post(vdp_ajax.url, data, function(response) {
            if (response.success) {
                showNotification('success', 'Template deleted successfully');
                $('[data-template-id="' + templateId + '"]').remove();
            } else {
                showNotification('error', response.data || 'Error deleting template');
            }
        });
    }
    
    function generateContractPreview() {
        $('#contract-preview').html('<div class="vdp-preview-loading"><i class="fas fa-spinner fa-spin"></i> Loading preview...</div>');
        
        // This would generate a sample contract preview
        setTimeout(function() {
            $('#contract-preview').html(`
                <div class="contract-preview-content">
                    <h2>CONTRATO DE SERVICIOS</h2>
                    <p><strong>Fecha:</strong> ${new Date().toLocaleDateString()}</p>
                    
                    <div class="contract-section">
                        <h3>Datos de la Empresa</h3>
                        <p>${$('#company-name').val() || '[Company Name]'}</p>
                        <p>${$('#company-address').val() || '[Company Address]'}</p>
                        <p>Tel: ${$('#company-phone').val() || '[Phone]'}</p>
                        <p>Email: ${$('#company-email').val() || '[Email]'}</p>
                    </div>
                    
                    <div class="contract-section">
                        <h3>Datos del Contratante</h3>
                        <p>[Cliente Ejemplo]</p>
                        <p>[Dirección del Cliente]</p>
                    </div>
                    
                    <div class="contract-section">
                        <h3>Información del Evento</h3>
                        <p><strong>Fecha:</strong> [Fecha del Evento]</p>
                        <p><strong>Lugar:</strong> [Lugar del Evento]</p>
                        <p><strong>Invitados:</strong> [Número de Invitados]</p>
                    </div>
                    
                    <div class="contract-section">
                        <h3>Términos y Condiciones</h3>
                        <div style="white-space: pre-line; font-size: 12px;">${$('#contract-terms').val() || '[Contract Terms]'}</div>
                    </div>
                </div>
            `);
        }, 1000);
    }
    
    function showNotification(type, message) {
        var $notification = $('<div class="vdp-notification vdp-notification-' + type + '">' + message + '</div>');
        $('.vdp-contracts-content').prepend($notification);
        
        setTimeout(function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    function uploadContractLogo(file) {
        console.log('VDP Upload - File selected:', file);
        console.log('VDP Upload - File name:', file.name);
        console.log('VDP Upload - File type:', file.type);
        console.log('VDP Upload - File size:', file.size);
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('error', 'Invalid file type. Only JPG, PNG and GIF are allowed.');
            return;
        }
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            showNotification('error', 'File too large. Maximum size is 2MB.');
            return;
        }
        
        // Show loading state
        $('.vdp-logo-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        
        // Convert file to Base64
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64Data = e.target.result;
            console.log('VDP Upload - File converted to Base64');
            
            // Send as regular POST data instead of FormData
            $.ajax({
                url: vdp_ajax.url,
                type: 'POST',
                data: {
                    action: 'vdp_upload_contract_logo_base64',
                    nonce: vdp_vars.nonce,
                    file_data: base64Data,
                    file_name: file.name,
                    file_type: file.type
                },
                success: function(response) {
                    console.log('VDP Upload - AJAX Response received:', response);
                    if (response.success) {
                        console.log('VDP Upload - Success! URL:', response.data.url);
                        displayContractLogo(response.data.url);
                        $('#logo-url').val(response.data.url);
                        showNotification('success', 'Logo uploaded successfully');
                    } else {
                        console.log('VDP Upload - Error response:', response.data);
                        showNotification('error', response.data || 'Error uploading logo');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('VDP Upload - AJAX Error:', error);
                    console.log('VDP Upload - Status:', status);
                    console.log('VDP Upload - Response:', xhr.responseText);
                    showNotification('error', 'Network error. Please try again.');
                },
                complete: function() {
                    $('.vdp-logo-btn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload Logo');
                }
            });
        };
        
        reader.onerror = function() {
            showNotification('error', 'Error reading file. Please try again.');
            $('.vdp-logo-btn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload Logo');
        };
        
        // Start reading the file
        reader.readAsDataURL(file);
    }
    
    function displayContractLogo(url) {
        const $container = $('.vdp-current-logo');
        $container.html(`<img src="${url}" alt="Contract Logo" style="max-width: 200px; max-height: 80px; border: 1px solid #ddd; border-radius: 4px;">`);
        
        // Show remove button if not already visible
        if (!$('.vdp-remove-logo').is(':visible')) {
            $('.vdp-logo-controls').append(`
                <button type="button" class="vdp-btn vdp-btn-outline vdp-remove-logo">
                    <i class="fas fa-trash"></i> Remove
                </button>
            `);
            
            // Bind remove event to new button
            $('.vdp-remove-logo').on('click', function() {
                removeContractLogo();
            });
        }
    }
    
    function removeContractLogo() {
        const $container = $('.vdp-current-logo');
        $container.html(`
            <div class="vdp-logo-placeholder">
                <i class="fas fa-image"></i>
                <span>No logo uploaded</span>
            </div>
        `);
        $('.vdp-remove-logo').remove();
        $('#logo-url').val('');
        $('#contract-logo').val('');
        showNotification('success', 'Logo removed');
    }
});
</script>

<style>
.vdp-form-control.error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.vdp-field-error {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.vdp-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
/* Smart Templates Styles */
.vdp-smart-templates {
    margin-bottom: 40px;
}

.vdp-smart-templates h4 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #333;
}

.vdp-help-text {
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
}

.vdp-smart-templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.vdp-smart-template {
    position: relative;
    background: #fff;
    border: 2px solid #e1e1e1;
    border-radius: 12px;
    padding: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
}

.vdp-smart-template:hover {
    border-color: #007cba;
    box-shadow: 0 4px 12px rgba(0, 124, 186, 0.15);
    transform: translateY(-2px);
}

.vdp-smart-template.active {
    border-color: #007cba;
    background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
    box-shadow: 0 6px 20px rgba(0, 124, 186, 0.2);
}

.template-icon {
    font-size: 32px;
    margin-bottom: 12px;
    display: block;
}

.vdp-smart-template h5 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.vdp-smart-template p {
    margin: 0 0 16px 0;
    color: #666;
    font-size: 14px;
}

.template-timeline {
    margin: 16px 0;
}

.timeline-bar {
    display: flex;
    height: 24px;
    border-radius: 12px;
    overflow: hidden;
    background: #f0f0f0;
}

.payment-segment {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    position: relative;
}

.payment-segment.full {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
}

.payment-segment.first {
    background: linear-gradient(135deg, #3498db, #5dade2);
}

.payment-segment.second {
    background: linear-gradient(135deg, #e74c3c, #ec7063);
}

.payment-segment.third {
    background: linear-gradient(135deg, #f39c12, #f7dc6f);
}

.payment-segment.equal {
    background: linear-gradient(135deg, #9b59b6, #bb8fce);
}

.use-case {
    display: block;
    font-size: 12px;
    color: #999;
    font-style: italic;
    margin-top: 12px;
}

.default-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #27ae60;
    color: #fff;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.vdp-custom-templates {
    border-top: 1px solid #e1e1e1;
    padding-top: 30px;
}

.section-header-with-action {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header-with-action h4 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.templates-grid .vdp-payment-template-card {
    border: 2px solid #e1e1e1;
    border-radius: 12px;
    background: #fff;
    transition: all 0.3s ease;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

.templates-grid .vdp-payment-template-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #007cba;
    transform: translateY(-2px);
}

.vdp-template-header {
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.vdp-template-info h4.vdp-template-name {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.vdp-template-meta {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.vdp-template-actions {
    display: flex;
    gap: 5px;
    flex-shrink: 0;
}

.vdp-template-payments {
    border-top: 1px solid #f0f0f0;
    padding-top: 15px;
}

.vdp-payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    margin: 5px 0;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #007cba;
}

.vdp-payment-percentage {
    font-weight: 600;
    color: #007cba;
    font-size: 14px;
}

.vdp-payment-timing {
    font-size: 12px;
    color: #666;
    text-align: right;
}

.vdp-badge {
    background: #27ae60;
    color: #fff;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.vdp-badge-primary {
    background: #007cba;
}

@media (max-width: 768px) {
    .vdp-smart-templates-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .vdp-smart-template {
        padding: 20px;
    }
    
    .section-header-with-action {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
}
</style>