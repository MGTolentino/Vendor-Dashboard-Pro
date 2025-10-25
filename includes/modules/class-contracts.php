<?php
/**
 * Contracts Module
 *
 * @package Vendor Dashboard Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class VDP_Contracts {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Register AJAX handlers
        add_action('wp_ajax_vdp_save_contract_settings', array($this, 'save_contract_settings'));
        add_action('wp_ajax_vdp_get_contract_templates', array($this, 'get_contract_templates'));
        add_action('wp_ajax_vdp_save_payment_template', array($this, 'save_payment_template'));
        add_action('wp_ajax_vdp_delete_payment_template', array($this, 'delete_payment_template'));
    }
    
    /**
     * Get contract settings for a vendor
     */
    public function get_contract_settings($vendor_id) {
        $settings = get_post_meta($vendor_id, 'vdp_contract_settings', true);
        
        if (empty($settings)) {
            $settings = $this->get_default_contract_settings();
        }
        
        return $settings;
    }
    
    /**
     * Get default contract settings
     */
    private function get_default_contract_settings() {
        return array(
            'company_data' => array(
                'name' => '',
                'address' => '',
                'phone' => '',
                'email' => '',
                'rfc' => ''
            ),
            'bank_data' => array(
                'bank_name' => '',
                'account_number' => '',
                'clabe' => '',
                'account_holder' => ''
            ),
            'contract_terms' => $this->get_default_contract_terms(),
            'payment_templates' => $this->get_default_payment_templates(),
            'validation_rules' => array(
                'min_days_before_event' => 7,
                'force_full_payment_days' => 15,
                'min_initial_payment' => 30,
                'max_payment_terms' => 12
            )
        );
    }
    
    /**
     * Get default contract terms
     */
    private function get_default_contract_terms() {
        return "1. Este contrato es de carácter forzoso y el CONTRATANTE entiende esto de manera ficta positiva y en caso de que el CONTRATANTE opte por cancelar faltando 60 días hábiles ó más para la fecha del evento indicada en el punto número 2 del presente contrato titulado, \"DESCRIPCIÓN DE SERVICIOS CONTRATADOS\", el CONTRATANTE deberá pagar el 50% del valor total estipulado en este contrato y el 75% cuando falten menos de 60 días hábiles. La cancelación se deberá presentar por escrito liquidando el pago al momento de la notificación de la misma.

2. La descripción de los productos y servicios contratados, la cantidad total a pagar, la fecha de Evento, El número de Invitados y Graduados quedan estipulados en el punto No.2 titulado \"DESCRIPCIÓN DE SERVICIOS CONTRATADOS\".

3. La prestación de los servicios contratados, son única y exclusivamente para la fecha y horario estipulados en este contrato y en caso de que EL CONTRATANTE opte por cambiar la fecha u horario EL CONTRATANTE se compromete a pagar el 30% adicional del valor total estipulado en este contrato, liquidándolo al momento de la notificación el cual estará sujeto a disponibilidad.

4. Las fechas en que se realizarán los pagos, así como las cantidades a pagar por el CONTRATANTE, quedan estipuladas en el punto No.3 titulado: \"FORMA DE PAGO\".

5. El incumplimiento de los pagos en las fechas pactadas por parte del EL CONTRATANTE cancela los descuentos, promociones u obsequios otorgados como beneficios durante la contratación.

6. LA EMPRESA es responsable de comercializar las características cualitativas del servicio y en ningún momento el personal que en ella labore.

7. EL CONTRATANTE será responsable de los daños y perjuicios ocasionados al personal, equipo electrónico, instrumentos musicales, y/o de trabajo, que se susciten durante el tiempo convenido para la celebración del presente contrato, ocasionados por desórdenes, pleitos y actos de vandalismo que resulten durante la celebración del evento motivo de este contrato, comprometiéndose al pago de los desperfectos ocasionados, y la reposición de los mismos en caso necesario.

8. El CONTRATANTE está obligado a liquidar el saldo restante del valor estipulado en este contrato 15 días antes de la fecha del evento estipulada en el punto No. 2 Titulado \"DESCRIPCIÓN DE SERVICIOS CONTRATADOS\".

9. Ambas partes renuncian expresamente al fuero de sus domicilios presentes y futuros, por lo que en caso de controversia en la interpretación del presente contrato se sujetan a la jurisdicción de los tribunales de la ciudad de Monterrey, Nuevo León México.

10. He leído el presente contrato y su información, estoy de acuerdo con las condiciones acordadas y acepto todas y cada una de las cláusulas anteriores, con el solo hecho de firmar.";
    }
    
    /**
     * Get default payment templates
     */
    private function get_default_payment_templates() {
        return array(
            array(
                'id' => 'full_payment',
                'name' => 'Pago Completo (100%)',
                'min_days_required' => 0,
                'is_default' => false,
                'payments' => array(
                    array('percentage' => 100, 'days_from_contract' => 0, 'description' => 'Pago completo al firmar contrato')
                )
            ),
            array(
                'id' => '50_50',
                'name' => '50% - 50%',
                'min_days_required' => 15,
                'is_default' => true,
                'payments' => array(
                    array('percentage' => 50, 'days_from_contract' => 0, 'description' => 'Pago inicial 50%'),
                    array('percentage' => 50, 'days_before_event' => 7, 'description' => 'Pago final 50%')
                )
            ),
            array(
                'id' => '3_months',
                'name' => '3 Pagos Mensuales',
                'min_days_required' => 90,
                'is_default' => false,
                'payments' => array(
                    array('percentage' => 33.34, 'days_from_contract' => 0, 'description' => 'Primer pago'),
                    array('percentage' => 33.33, 'days_from_contract' => 30, 'description' => 'Segundo pago'),
                    array('percentage' => 33.33, 'days_from_contract' => 60, 'description' => 'Tercer pago')
                )
            ),
            array(
                'id' => '6_months',
                'name' => '6 Pagos Mensuales',
                'min_days_required' => 180,
                'is_default' => false,
                'payments' => array(
                    array('percentage' => 16.67, 'days_from_contract' => 0, 'description' => 'Primer pago'),
                    array('percentage' => 16.67, 'days_from_contract' => 30, 'description' => 'Segundo pago'),
                    array('percentage' => 16.67, 'days_from_contract' => 60, 'description' => 'Tercer pago'),
                    array('percentage' => 16.67, 'days_from_contract' => 90, 'description' => 'Cuarto pago'),
                    array('percentage' => 16.66, 'days_from_contract' => 120, 'description' => 'Quinto pago'),
                    array('percentage' => 16.66, 'days_from_contract' => 150, 'description' => 'Sexto pago')
                )
            )
        );
    }
    
    /**
     * Save contract settings via AJAX
     */
    public function save_contract_settings() {
        check_ajax_referer('vdp_nonce', 'nonce');
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error('Vendor not found');
        }
        
        // Get existing settings first to preserve data
        $existing_settings = $this->get_contract_settings($vendor->get_id());
        
        // Determine which section is being saved based on submitted fields
        $section = sanitize_text_field($_POST['section'] ?? 'all');
        
        // Start with existing settings to preserve all data
        $settings = $existing_settings;
        
        // Update only the relevant section
        if ($section === 'company' || isset($_POST['company_name'])) {
            $settings['company_data'] = array(
                'name' => sanitize_text_field($_POST['company_name'] ?? ''),
                'address' => sanitize_textarea_field($_POST['company_address'] ?? ''),
                'phone' => sanitize_text_field($_POST['company_phone'] ?? ''),
                'email' => sanitize_email($_POST['company_email'] ?? ''),
                'rfc' => sanitize_text_field($_POST['company_rfc'] ?? '')
            );
        }
        
        if ($section === 'bank' || isset($_POST['bank_name'])) {
            $settings['bank_data'] = array(
                'bank_name' => sanitize_text_field($_POST['bank_name'] ?? ''),
                'account_number' => sanitize_text_field($_POST['account_number'] ?? ''),
                'clabe' => sanitize_text_field($_POST['clabe'] ?? ''),
                'account_holder' => sanitize_text_field($_POST['account_holder'] ?? '')
            );
        }
        
        if ($section === 'terms' || isset($_POST['contract_terms'])) {
            $settings['contract_terms'] = wp_kses_post($_POST['contract_terms'] ?? '');
        }
        
        if ($section === 'validation' || isset($_POST['min_days_before_event'])) {
            $settings['validation_rules'] = array(
                'min_days_before_event' => intval($_POST['min_days_before_event'] ?? 7),
                'force_full_payment_days' => intval($_POST['force_full_payment_days'] ?? 15),
                'min_initial_payment' => intval($_POST['min_initial_payment'] ?? 30),
                'max_payment_terms' => intval($_POST['max_payment_terms'] ?? 12)
            );
        }
        
        // If all sections are being saved at once
        if ($section === 'all') {
            $settings = array(
                'company_data' => array(
                    'name' => sanitize_text_field($_POST['company_name'] ?? ''),
                    'address' => sanitize_textarea_field($_POST['company_address'] ?? ''),
                    'phone' => sanitize_text_field($_POST['company_phone'] ?? ''),
                    'email' => sanitize_email($_POST['company_email'] ?? ''),
                    'rfc' => sanitize_text_field($_POST['company_rfc'] ?? '')
                ),
                'bank_data' => array(
                    'bank_name' => sanitize_text_field($_POST['bank_name'] ?? ''),
                    'account_number' => sanitize_text_field($_POST['account_number'] ?? ''),
                    'clabe' => sanitize_text_field($_POST['clabe'] ?? ''),
                    'account_holder' => sanitize_text_field($_POST['account_holder'] ?? '')
                ),
                'contract_terms' => wp_kses_post($_POST['contract_terms'] ?? ''),
                'validation_rules' => array(
                    'min_days_before_event' => intval($_POST['min_days_before_event'] ?? 7),
                    'force_full_payment_days' => intval($_POST['force_full_payment_days'] ?? 15),
                    'min_initial_payment' => intval($_POST['min_initial_payment'] ?? 30),
                    'max_payment_terms' => intval($_POST['max_payment_terms'] ?? 12)
                ),
                // Always preserve payment templates
                'payment_templates' => $existing_settings['payment_templates'] ?? $this->get_default_payment_templates()
            );
        }
        
        // Always ensure payment templates are preserved
        if (!isset($settings['payment_templates']) || empty($settings['payment_templates'])) {
            $settings['payment_templates'] = $existing_settings['payment_templates'] ?? $this->get_default_payment_templates();
        }
        
        update_post_meta($vendor->get_id(), 'vdp_contract_settings', $settings);
        
        wp_send_json_success('Settings saved successfully');
    }
    
    /**
     * Get contract templates via AJAX
     */
    public function get_contract_templates() {
        check_ajax_referer('vdp_nonce', 'nonce');
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error('Vendor not found');
        }
        
        $settings = $this->get_contract_settings($vendor->get_id());
        wp_send_json_success($settings['payment_templates']);
    }
    
    /**
     * Save payment template via AJAX
     */
    public function save_payment_template() {
        check_ajax_referer('vdp_nonce', 'nonce');
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error('Vendor not found');
        }
        
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $template_name = sanitize_text_field($_POST['template_name'] ?? '');
        $min_days_required = intval($_POST['min_days_required'] ?? 0);
        $is_default = isset($_POST['is_default']) && $_POST['is_default'] === 'true';
        $payments = json_decode(stripslashes($_POST['payments'] ?? ''), true);
        
        if (!$template_id || !$template_name || !$payments) {
            wp_send_json_error('Invalid template data');
        }
        
        $settings = $this->get_contract_settings($vendor->get_id());
        
        // If setting as default, remove default from other templates
        if ($is_default) {
            foreach ($settings['payment_templates'] as &$template) {
                $template['is_default'] = false;
            }
        }
        
        // Find existing template or create new one
        $template_found = false;
        foreach ($settings['payment_templates'] as &$template) {
            if ($template['id'] === $template_id) {
                $template['name'] = $template_name;
                $template['min_days_required'] = $min_days_required;
                $template['is_default'] = $is_default;
                $template['payments'] = $payments;
                $template_found = true;
                break;
            }
        }
        
        if (!$template_found) {
            $settings['payment_templates'][] = array(
                'id' => $template_id,
                'name' => $template_name,
                'min_days_required' => $min_days_required,
                'is_default' => $is_default,
                'payments' => $payments
            );
        }
        
        update_post_meta($vendor->get_id(), 'vdp_contract_settings', $settings);
        
        wp_send_json_success('Template saved successfully');
    }
    
    /**
     * Delete payment template via AJAX
     */
    public function delete_payment_template() {
        check_ajax_referer('vdp_nonce', 'nonce');
        
        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error('Vendor not found');
        }
        
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        if (!$template_id) {
            wp_send_json_error('Template ID required');
        }
        
        $settings = $this->get_contract_settings($vendor->get_id());
        
        // Remove template
        $settings['payment_templates'] = array_filter($settings['payment_templates'], function($template) use ($template_id) {
            return $template['id'] !== $template_id;
        });
        
        // Re-index array
        $settings['payment_templates'] = array_values($settings['payment_templates']);
        
        update_post_meta($vendor->get_id(), 'vdp_contract_settings', $settings);
        
        wp_send_json_success('Template deleted successfully');
    }
    
    /**
     * Validate payment schedule against event date
     */
    public function validate_payment_schedule($event_date, $payment_schedule, $validation_rules = null) {
        if (!$validation_rules) {
            $vendor = vdp_get_current_vendor();
            if ($vendor) {
                $settings = $this->get_contract_settings($vendor->get_id());
                $validation_rules = $settings['validation_rules'];
            } else {
                $validation_rules = $this->get_default_contract_settings()['validation_rules'];
            }
        }
        
        $today = new DateTime();
        $event = new DateTime($event_date);
        $days_until_event = $today->diff($event)->days;
        
        $errors = array();
        $warnings = array();
        
        // Validation 1: Event very close
        if ($days_until_event <= $validation_rules['force_full_payment_days']) {
            if (count($payment_schedule) > 1) {
                $errors[] = sprintf(
                    __('Event in %d days - Only full payment allowed', 'vendor-dashboard-pro'),
                    $days_until_event
                );
            }
        }
        
        // Validation 2: No payments after event
        foreach ($payment_schedule as $payment) {
            $payment_date = new DateTime($payment['date']);
            if ($payment_date > $event) {
                $errors[] = __('Payment scheduled after event date', 'vendor-dashboard-pro');
            }
        }
        
        // Validation 3: Warnings for events close
        if ($days_until_event <= 30 && count($payment_schedule) > 2) {
            $warnings[] = __('Event is close - Maximum 2 payments recommended', 'vendor-dashboard-pro');
        }
        
        // Validation 4: Last payment should be before event
        if (!empty($payment_schedule)) {
            $last_payment = end($payment_schedule);
            $last_payment_date = new DateTime($last_payment['date']);
            $days_before_event = $last_payment_date->diff($event)->days;
            
            if ($days_before_event < $validation_rules['min_days_before_event']) {
                $warnings[] = sprintf(
                    __('Last payment very close to event (less than %d days)', 'vendor-dashboard-pro'),
                    $validation_rules['min_days_before_event']
                );
            }
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'days_until_event' => $days_until_event
        );
    }
}

// Initialize the module
VDP_Contracts::get_instance();