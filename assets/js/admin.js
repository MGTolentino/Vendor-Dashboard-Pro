/**
 * Admin Scripts for Vendor Dashboard Pro
 *
 * @package Vendor Dashboard Pro
 */

(function($) {
    'use strict';

    /**
     * Admin Object
     */
    var VDPAdmin = {
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.initTabs();
            this.initForms();
            this.initModals();
            this.initTooltips();
            this.initLicenseManager();
            this.initAPITester();
            this.initImportExport();
            this.initDashboardWidgets();
        },

        /**
         * Initialize tabs
         */
        initTabs: function() {
            $('.vdp-admin-tab').on('click', function() {
                var target = $(this).data('tab');

                // Update active tab
                $('.vdp-admin-tab').removeClass('vdp-active');
                $(this).addClass('vdp-active');

                // Update active tab content
                $('.vdp-admin-tab-content').removeClass('vdp-active');
                $('#' + target + '-tab-content').addClass('vdp-active');

                // Update URL hash
                window.location.hash = target;
            });

            // Check URL hash on page load
            if (window.location.hash) {
                var hash = window.location.hash.substring(1);
                $('.vdp-admin-tab[data-tab="' + hash + '"]').trigger('click');
            }
        },

        /**
         * Initialize forms
         */
        initForms: function() {
            // Form submission
            $('.vdp-admin-form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $submitBtn = $form.find('button[type="submit"]');

                // Disable button and show loading state
                $submitBtn.prop('disabled', true).addClass('vdp-admin-loading');

                // Gather form data
                var formData = new FormData($form[0]);
                formData.append('action', 'vdp_admin_save_settings');
                formData.append('_wpnonce', vdp_admin_vars.nonce);

                // Send AJAX request
                $.ajax({
                    url: vdp_admin_vars.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            VDPAdmin.showNotice('success', response.data.message);
                        } else {
                            VDPAdmin.showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        VDPAdmin.showNotice('error', vdp_admin_vars.error_message);
                    },
                    complete: function() {
                        // Enable button and remove loading state
                        $submitBtn.prop('disabled', false).removeClass('vdp-admin-loading');
                    }
                });
            });

            // Color picker
            if ($.fn.wpColorPicker) {
                $('.vdp-admin-color-picker').wpColorPicker();
            }

            // Date picker
            if ($.fn.datepicker) {
                $('.vdp-admin-date-picker').datepicker({
                    dateFormat: 'yy-mm-dd'
                });
            }

            // Select2
            if ($.fn.select2) {
                $('.vdp-admin-select2').select2({
                    width: '100%'
                });
            }

            // Media uploader
            $('.vdp-admin-media-upload').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $field = $button.siblings('.vdp-admin-media-input');
                var $preview = $button.siblings('.vdp-admin-media-preview');

                var frame = wp.media({
                    title: vdp_admin_vars.media_title,
                    multiple: false,
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: vdp_admin_vars.media_button
                    }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $field.val(attachment.id);
                    
                    if (attachment.sizes && attachment.sizes.thumbnail) {
                        $preview.html('<img src="' + attachment.sizes.thumbnail.url + '" alt="" />');
                    } else {
                        $preview.html('<img src="' + attachment.url + '" alt="" />');
                    }

                    $button.siblings('.vdp-admin-media-remove').show();
                });

                frame.open();
            });

            // Media remove
            $('.vdp-admin-media-remove').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $field = $button.siblings('.vdp-admin-media-input');
                var $preview = $button.siblings('.vdp-admin-media-preview');

                $field.val('');
                $preview.html('');
                $button.hide();
            });
        },

        /**
         * Initialize modals
         */
        initModals: function() {
            // Open modal
            $('.vdp-admin-modal-open').on('click', function(e) {
                e.preventDefault();
                var target = $(this).data('modal');
                $('#' + target).fadeIn(200);
            });

            // Close modal
            $('.vdp-admin-modal-close, .vdp-admin-modal-cancel').on('click', function(e) {
                e.preventDefault();
                $(this).closest('.vdp-admin-modal-overlay').fadeOut(200);
            });

            // Close modal when clicking on overlay
            $('.vdp-admin-modal-overlay').on('click', function(e) {
                if (e.target === this) {
                    $(this).fadeOut(200);
                }
            });

            // Prevent propagation in modal content
            $('.vdp-admin-modal').on('click', function(e) {
                e.stopPropagation();
            });
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            if ($.fn.tipsy) {
                $('.vdp-admin-tooltip').tipsy({
                    gravity: 's',
                    html: true
                });
            }
        },

        /**
         * Initialize license manager
         */
        initLicenseManager: function() {
            // Activate license
            $('#vdp-activate-license').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var license = $('#vdp-license-key').val();

                if (!license) {
                    VDPAdmin.showNotice('error', vdp_admin_vars.license_empty);
                    return;
                }

                // Disable button and show loading state
                $button.prop('disabled', true).addClass('vdp-admin-loading');

                // Send AJAX request
                $.ajax({
                    url: vdp_admin_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'vdp_activate_license',
                        license: license,
                        _wpnonce: vdp_admin_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            VDPAdmin.showNotice('success', response.data.message);
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            VDPAdmin.showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        VDPAdmin.showNotice('error', vdp_admin_vars.error_message);
                    },
                    complete: function() {
                        // Enable button and remove loading state
                        $button.prop('disabled', false).removeClass('vdp-admin-loading');
                    }
                });
            });

            // Deactivate license
            $('#vdp-deactivate-license').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);

                if (!confirm(vdp_admin_vars.license_deactivate_confirm)) {
                    return;
                }

                // Disable button and show loading state
                $button.prop('disabled', true).addClass('vdp-admin-loading');

                // Send AJAX request
                $.ajax({
                    url: vdp_admin_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'vdp_deactivate_license',
                        _wpnonce: vdp_admin_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            VDPAdmin.showNotice('success', response.data.message);
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            VDPAdmin.showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        VDPAdmin.showNotice('error', vdp_admin_vars.error_message);
                    },
                    complete: function() {
                        // Enable button and remove loading state
                        $button.prop('disabled', false).removeClass('vdp-admin-loading');
                    }
                });
            });
        },

        /**
         * Initialize API tester
         */
        initAPITester: function() {
            // Test API endpoint
            $('.vdp-admin-api-test').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $endpoint = $button.closest('.vdp-admin-api-endpoint');
                var $form = $endpoint.find('.vdp-admin-api-endpoint-form');
                var $response = $endpoint.find('.vdp-admin-api-response');
                var endpoint = $button.data('endpoint');
                var method = $button.data('method');

                // Disable button and show loading state
                $button.prop('disabled', true).addClass('vdp-admin-loading');

                // Gather form data
                var formData = new FormData($form[0]);
                formData.append('action', 'vdp_test_api');
                formData.append('endpoint', endpoint);
                formData.append('method', method);
                formData.append('_wpnonce', vdp_admin_vars.nonce);

                // Send AJAX request
                $.ajax({
                    url: vdp_admin_vars.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $response.html('<pre>' + JSON.stringify(response.data.result, null, 2) + '</pre>');
                            $response.show();
                        } else {
                            VDPAdmin.showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        VDPAdmin.showNotice('error', vdp_admin_vars.error_message);
                    },
                    complete: function() {
                        // Enable button and remove loading state
                        $button.prop('disabled', false).removeClass('vdp-admin-loading');
                    }
                });
            });
        },

        /**
         * Initialize import/export
         */
        initImportExport: function() {
            // Export settings
            $('#vdp-export-settings').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);

                // Disable button and show loading state
                $button.prop('disabled', true).addClass('vdp-admin-loading');

                // Send AJAX request
                $.ajax({
                    url: vdp_admin_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'vdp_export_settings',
                        _wpnonce: vdp_admin_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Create download link
                            var blob = new Blob([JSON.stringify(response.data.settings)], {type: 'application/json'});
                            var url = URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'vdp-settings-' + response.data.date + '.json';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        } else {
                            VDPAdmin.showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        VDPAdmin.showNotice('error', vdp_admin_vars.error_message);
                    },
                    complete: function() {
                        // Enable button and remove loading state
                        $button.prop('disabled', false).removeClass('vdp-admin-loading');
                    }
                });
            });

            // Import settings
            $('#vdp-import-settings').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $file = $('#vdp-import-file');

                if (!$file[0].files.length) {
                    VDPAdmin.showNotice('error', vdp_admin_vars.import_file_empty);
                    return;
                }

                // Confirm import
                if (!confirm(vdp_admin_vars.import_confirm)) {
                    return;
                }

                // Disable button and show loading state
                $button.prop('disabled', true).addClass('vdp-admin-loading');

                // Gather form data
                var formData = new FormData();
                formData.append('action', 'vdp_import_settings');
                formData.append('settings_file', $file[0].files[0]);
                formData.append('_wpnonce', vdp_admin_vars.nonce);

                // Send AJAX request
                $.ajax({
                    url: vdp_admin_vars.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            VDPAdmin.showNotice('success', response.data.message);
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            VDPAdmin.showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        VDPAdmin.showNotice('error', vdp_admin_vars.error_message);
                    },
                    complete: function() {
                        // Enable button and remove loading state
                        $button.prop('disabled', false).removeClass('vdp-admin-loading');
                    }
                });
            });
        },

        /**
         * Initialize dashboard widgets
         */
        initDashboardWidgets: function() {
            // Check if we're on the dashboard
            if (!$('#dashboard-widgets').length) {
                return;
            }

            // Refresh dashboard widget
            $('.vdp-admin-dashboard-refresh').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $widget = $button.closest('.vdp-admin-dashboard-widget');
                var $content = $widget.find('.vdp-admin-dashboard-widget-content');
                var widget = $button.data('widget');

                // Disable button and show loading state
                $button.prop('disabled', true).addClass('vdp-admin-loading');

                // Send AJAX request
                $.ajax({
                    url: vdp_admin_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'vdp_refresh_dashboard_widget',
                        widget: widget,
                        _wpnonce: vdp_admin_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $content.html(response.data.content);
                        } else {
                            VDPAdmin.showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        VDPAdmin.showNotice('error', vdp_admin_vars.error_message);
                    },
                    complete: function() {
                        // Enable button and remove loading state
                        $button.prop('disabled', false).removeClass('vdp-admin-loading');
                    }
                });
            });
        },

        /**
         * Show admin notice
         *
         * @param {string} type Notice type (success, error, warning, info)
         * @param {string} message Notice message
         */
        showNotice: function(type, message) {
            var $notice = $('<div class="vdp-admin-notice vdp-admin-notice-' + type + '"><p>' + message + '</p></div>');
            $('.vdp-admin-notices').prepend($notice);

            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        VDPAdmin.init();
    });

})(jQuery);