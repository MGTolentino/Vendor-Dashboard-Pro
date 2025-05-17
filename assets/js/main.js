/**
 * Main JavaScript for Vendor Dashboard Pro
 *
 * @package Vendor Dashboard Pro
 */

(function($) {
    'use strict';

    /**
     * Vendor Dashboard Pro Main Object
     */
    var VDP = {
        /**
         * Initialize the application
         */
        init: function() {
            this.initNotifications();
            this.initCharts();
            this.initProducts();
            this.initMessages();
            this.initSettings();
            this.initExpandableText();
            this.initMobileMenu();
            this.initForms();
            this.initAjaxLoading();
        },

        /**
         * Initialize notifications
         */
        initNotifications: function() {
            // Toggle notification dropdown
            $('.vdp-notification-toggle').on('click', function(e) {
                e.preventDefault();
                $('.vdp-notification-dropdown').toggleClass('vdp-show');
                e.stopPropagation();
            });

            // Close notification dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.vdp-notifications').length) {
                    $('.vdp-notification-dropdown').removeClass('vdp-show');
                }
            });
        },

        /**
         * Initialize charts
         */
        initCharts: function() {
            // Check if we're on dashboard page and Chart.js is loaded
            if (!$('.vdp-dashboard-content').length || typeof Chart === 'undefined') {
                return;
            }

            // Helper to create gradient
            function createGradient(ctx, startColor, endColor) {
                var gradient = ctx.createLinearGradient(0, 0, 0, 160);
                gradient.addColorStop(0, startColor);
                gradient.addColorStop(1, endColor);
                return gradient;
            }

            // Load chart data via AJAX
            this.loadChartData('sales', '30days', function(response) {
                if (response.success && response.data.chart_data) {
                    VDP.renderChart('salesChart', 'Sales', response.data.chart_data, '#3483fa');
                }
            });

            this.loadChartData('views', '30days', function(response) {
                if (response.success && response.data.chart_data) {
                    VDP.renderChart('viewsChart', 'Views', response.data.chart_data, '#39b54a');
                }
            });

            this.loadChartData('conversion', '30days', function(response) {
                if (response.success && response.data.chart_data) {
                    VDP.renderChart('conversionChart', 'Conversion', response.data.chart_data, '#f5a623');
                }
            });
        },

        /**
         * Load chart data via AJAX
         * 
         * @param {string} metric Metric name
         * @param {string} period Period
         * @param {function} callback Callback function
         */
        loadChartData: function(metric, period, callback) {
            $.ajax({
                url: vdpSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vdp_get_chart_data',
                    nonce: vdpSettings.nonce,
                    metric: metric,
                    period: period
                },
                success: callback
            });
        },

        /**
         * Render chart
         * 
         * @param {string} chartId Chart canvas ID
         * @param {string} label Chart label
         * @param {array} data Chart data
         * @param {string} color Chart color
         */
        renderChart: function(chartId, label, data, color) {
            var ctx = document.getElementById(chartId);
            if (!ctx) return;

            ctx = ctx.getContext('2d');
            
            // Extract dates and values
            var dates = [];
            var values = [];
            
            data.forEach(function(item) {
                dates.push(item.date);
                values.push(item.value);
            });
            
            // Create chart
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: label,
                        data: values,
                        borderColor: color,
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 0,
                        fill: true,
                        backgroundColor: function(context) {
                            var ctx = context.chart.ctx;
                            return createGradient(ctx, color + '40', color + '00');
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#fff',
                            titleColor: '#000',
                            bodyColor: '#000',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            cornerRadius: 4,
                            titleFont: {
                                weight: 'bold'
                            },
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed.y;
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false,
                            min: 0
                        }
                    },
                    animation: {
                        duration: 1000
                    }
                }
            });
        },

        /**
         * Initialize products
         */
        initProducts: function() {
            // Check if we're on products page
            if (!$('.vdp-products-content').length) {
                return;
            }

            // Init product filters
            $('.vdp-filter-select').on('change', function() {
                VDP.filterProducts();
            });

            $('.vdp-search-products').on('input', function() {
                VDP.filterProducts();
            });

            // Delete product
            $('.vdp-delete-product').on('click', function(e) {
                e.preventDefault();
                
                var productId = $(this).data('product-id');
                
                if (!productId) {
                    return;
                }
                
                if (confirm(vdpSettings.i18n.confirmDelete)) {
                    VDP.deleteProduct(productId);
                }
            });

            // Product form submission
            $('#vdp-product-form').on('submit', function(e) {
                e.preventDefault();
                VDP.saveProduct($(this));
            });
        },

        /**
         * Filter products
         */
        filterProducts: function() {
            var category = $('.vdp-filter-select[name="category"]').val();
            var status = $('.vdp-filter-select[name="status"]').val();
            var search = $('.vdp-search-products').val().toLowerCase();
            
            $('.vdp-product-row').each(function() {
                var $row = $(this);
                var productCategory = $row.data('category');
                var productStatus = $row.data('status');
                var productTitle = $row.find('.vdp-product-title').text().toLowerCase();
                
                var categoryMatch = !category || category === productCategory;
                var statusMatch = !status || status === productStatus;
                var searchMatch = !search || productTitle.indexOf(search) !== -1;
                
                if (categoryMatch && statusMatch && searchMatch) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        },

        /**
         * Delete product
         * 
         * @param {int} productId Product ID
         */
        deleteProduct: function(productId) {
            $.ajax({
                url: vdpSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vdp_delete_listing',
                    nonce: vdpSettings.nonce,
                    listing_id: productId
                },
                beforeSend: function() {
                    VDP.showLoading();
                },
                success: function(response) {
                    if (response.success) {
                        VDP.showNotice(response.data.message, 'success');
                        // Remove product row
                        $('.vdp-product-row[data-product-id="' + productId + '"]').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        VDP.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    VDP.showNotice(vdpSettings.i18n.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                }
            });
        },

        /**
         * Save product
         * 
         * @param {object} $form Form jQuery object
         */
        saveProduct: function($form) {
            var formData = new FormData($form[0]);
            formData.append('action', 'vdp_save_listing');
            formData.append('nonce', vdpSettings.nonce);
            
            $.ajax({
                url: vdpSettings.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    VDP.showLoading();
                    $form.find('button[type="submit"]').prop('disabled', true).html(vdpSettings.i18n.saving);
                },
                success: function(response) {
                    if (response.success) {
                        VDP.showNotice(response.data.message, 'success');
                        
                        // Redirect after a short delay
                        if (response.data.redirect) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect;
                            }, 1000);
                        }
                    } else {
                        VDP.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    VDP.showNotice(vdpSettings.i18n.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                    $form.find('button[type="submit"]').prop('disabled', false).html(vdpSettings.i18n.saveChanges);
                }
            });
        },

        /**
         * Initialize messages
         */
        initMessages: function() {
            // Check if we're on messages page
            if (!$('.vdp-messages-content').length) {
                return;
            }
            
            // Message filters
            $('.vdp-filter-select').on('change', function() {
                VDP.filterMessages();
            });
            
            $('.vdp-search-messages').on('input', function() {
                VDP.filterMessages();
            });
            
            // Message reply
            $('#vdp-message-reply-form').on('submit', function(e) {
                e.preventDefault();
                VDP.replyMessage($(this));
            });
        },

        /**
         * Filter messages
         */
        filterMessages: function() {
            var status = $('.vdp-filter-select[name="status"]').val();
            var search = $('.vdp-search-messages').val().toLowerCase();
            
            $('.vdp-message-row').each(function() {
                var $row = $(this);
                var messageStatus = $row.data('status');
                var messageSender = $row.find('.vdp-message-sender-name').text().toLowerCase();
                var messageContent = $row.find('.vdp-message-content').text().toLowerCase();
                
                var statusMatch = !status || status === messageStatus;
                var searchMatch = !search || messageSender.indexOf(search) !== -1 || messageContent.indexOf(search) !== -1;
                
                if (statusMatch && searchMatch) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        },

        /**
         * Reply to message
         * 
         * @param {object} $form Form jQuery object
         */
        replyMessage: function($form) {
            var formData = new FormData($form[0]);
            formData.append('action', 'vdp_reply_message');
            formData.append('nonce', vdpSettings.nonce);
            
            $.ajax({
                url: vdpSettings.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    VDP.showLoading();
                    $form.find('button[type="submit"]').prop('disabled', true).html(vdpSettings.i18n.sending);
                },
                success: function(response) {
                    if (response.success) {
                        VDP.showNotice(response.data.message, 'success');
                        
                        // Add reply to messages
                        if (response.data.reply_html) {
                            $('.vdp-message-thread').append(response.data.reply_html);
                            $form.find('textarea').val('');
                        }
                    } else {
                        VDP.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    VDP.showNotice(vdpSettings.i18n.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                    $form.find('button[type="submit"]').prop('disabled', false).html(vdpSettings.i18n.sendReply);
                }
            });
        },

        /**
         * Initialize settings
         */
        initSettings: function() {
            // Check if we're on settings page
            if (!$('.vdp-settings-content').length) {
                return;
            }
            
            // Settings form submission
            $('#vdp-settings-form').on('submit', function(e) {
                e.preventDefault();
                VDP.saveSettings($(this));
            });
        },

        /**
         * Save settings
         * 
         * @param {object} $form Form jQuery object
         */
        saveSettings: function($form) {
            var formData = new FormData($form[0]);
            formData.append('action', 'vdp_save_vendor_settings');
            formData.append('nonce', vdpSettings.nonce);
            
            $.ajax({
                url: vdpSettings.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    VDP.showLoading();
                    $form.find('button[type="submit"]').prop('disabled', true).html(vdpSettings.i18n.saving);
                },
                success: function(response) {
                    if (response.success) {
                        VDP.showNotice(response.data.message, 'success');
                    } else {
                        VDP.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    VDP.showNotice(vdpSettings.i18n.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                    $form.find('button[type="submit"]').prop('disabled', false).html(vdpSettings.i18n.saveChanges);
                }
            });
        },

        /**
         * Initialize expandable text
         */
        initExpandableText: function() {
            $('.vdp-expand-toggle').on('click', function() {
                var $expandable = $(this).closest('.vdp-expandable-text');
                $expandable.toggleClass('vdp-expanded');
            });
        },

        /**
         * Initialize mobile menu
         */
        initMobileMenu: function() {
            $('.vdp-mobile-menu-toggle').on('click', function() {
                $('.vdp-sidebar').toggleClass('vdp-mobile-open');
            });
            
            // Close mobile menu when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.vdp-sidebar, .vdp-mobile-menu-toggle').length) {
                    $('.vdp-sidebar').removeClass('vdp-mobile-open');
                }
            });
        },

        /**
         * Initialize forms
         */
        initForms: function() {
            // File input
            $('.vdp-file-input').each(function() {
                var $input = $(this);
                var $fileBtn = $input.siblings('.vdp-file-btn');
                var $fileLabel = $input.siblings('.vdp-file-label');
                
                $fileBtn.on('click', function() {
                    $input.trigger('click');
                });
                
                $input.on('change', function() {
                    var fileName = '';
                    
                    if (this.files && this.files.length > 1) {
                        fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}', this.files.length);
                    } else if (this.files && this.files.length === 1) {
                        fileName = this.files[0].name;
                    }
                    
                    if (fileName) {
                        $fileLabel.text(fileName);
                    } else {
                        $fileLabel.text($fileLabel.data('default') || 'No file selected');
                    }
                });
            });
        },

        /**
         * Initialize AJAX loading
         */
        initAjaxLoading: function() {
            // Add loading container if not exists
            if (!$('.vdp-loading').length) {
                $('body').append('<div class="vdp-loading"><div class="vdp-loading-spinner"></div></div>');
            }
            
            // Add notice container if not exists
            if (!$('.vdp-notices').length) {
                $('body').append('<div class="vdp-notices"></div>');
            }
        },

        /**
         * Show loading spinner
         */
        showLoading: function() {
            $('.vdp-loading').addClass('vdp-active');
        },

        /**
         * Hide loading spinner
         */
        hideLoading: function() {
            $('.vdp-loading').removeClass('vdp-active');
        },

        /**
         * Show notice
         * 
         * @param {string} message Notice message
         * @param {string} type Notice type (success, error, warning, info)
         */
        showNotice: function(message, type) {
            type = type || 'info';
            
            var $notice = $('<div class="vdp-notice vdp-notice-' + type + '">' + message + '</div>');
            $('.vdp-notices').append($notice);
            
            setTimeout(function() {
                $notice.addClass('vdp-notice-visible');
            }, 10);
            
            setTimeout(function() {
                $notice.removeClass('vdp-notice-visible');
                
                setTimeout(function() {
                    $notice.remove();
                }, 300);
            }, 3000);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        VDP.init();
    });

})(jQuery);