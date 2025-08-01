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
            this.initNavigation();
        },

        initNavigation: function() {
            $('.vdp-sidebar-nav a').on('click', function(e) {
                e.preventDefault();
                var action = $(this).data('action');
                var url = VDP.buildDashboardUrl(action);
                VDP.loadContent(url, action);
            });
            
            $(document).on('click', '.vdp-add-listing-btn', function(e) {
                e.preventDefault();
                VDP.triggerAddListingForm();
            });
            
            $(document).on('click', 'a[data-action="products"][data-item="add"]', function(e) {
                e.preventDefault();
                VDP.triggerAddListingForm();
            });
            
            $(document).on('click', 'a[href*="vdp-action=products&vdp-item=add"]', function(e) {
                if (!$(this).hasClass('vdp-add-listing-btn') && !$(this).data('action')) {
                    e.preventDefault();
                    VDP.triggerAddListingForm();
                }
            });
            
            $(document).on('click', '.vdp-content-area a.vdp-ajax-link', function(e) {
                if ($(this).hasClass('direct-link') || 
                    $(this).hasClass('vdp-message-view-btn') || 
                    $(this).parent().hasClass('vdp-message-actions') ||
                    $(this).text().trim() === 'View') {
                    
                    return true;
                }
                
                e.preventDefault();
                var url = $(this).attr('href');
                var action = $(this).data('action');
                var item = $(this).data('item');
                var paged = $(this).data('paged');
                
                if (url && action) {
                    VDP.loadContent(url, action, item, true, paged);
                }
            });
            
            $(window).on('popstate', function(e) {
                if (e.originalEvent.state) {
                    var state = e.originalEvent.state;
                    var url = state.url;
                    var action = state.action;
                    var item = state.item;
                    
                    VDP.loadContent(url, action, item, false);
                }
            });
        },
        
        buildDashboardUrl: function(action, item) {
            var url = vdp_vars.dashboard_url;
            var separator = url.indexOf('?') !== -1 ? '&' : '?';
            
            if (action && action !== 'dashboard') {
                url += separator + 'vdp-action=' + action;
                separator = '&';
            } else {
                url = url.split('?')[0];
                separator = '?';
            }
            
            if (item) {
                url += separator + 'vdp-item=' + item;
            }
            
            return url;
        },
        
        loadContent: function(url, action, item, updateHistory, paged) {
            updateHistory = (updateHistory !== false);
            
            var cacheKey = 'vdp_cache_' + action + (item ? '_' + item : '') + (paged ? '_page_' + paged : '');
            var cachedContent = sessionStorage.getItem(cacheKey);
            var cachedTimestamp = parseInt(sessionStorage.getItem(cacheKey + '_timestamp') || '0', 10);
            var now = new Date().getTime();
            var cacheExpiry = 60000;
            
            $('.vdp-nav-item').removeClass('vdp-active');
            $('#vdp-nav-' + action).addClass('vdp-active');
            
            $('.vdp-sidebar-nav a').removeClass('vdp-active');
            $('.vdp-sidebar-nav a[data-action="' + action + '"]').addClass('vdp-active');
            
            
            if (cachedContent && (now - cachedTimestamp < cacheExpiry)) {
                
                if (action === 'dashboard') {
                    VDP.destroyExistingCharts();
                }
                
                $('.vdp-content-area').html(cachedContent);
                
                if (action === 'dashboard') {
                    VDP.initCharts();
                } else if (action === 'products') {
                    VDP.initProducts();
                } else if (action === 'messages') {
                    VDP.initMessages();
                } else if (action === 'settings') {
                    VDP.initSettings();
                } else if (action === 'leads') {
                    VDP.initLeads();
                }
                
                if (updateHistory) {
                    var state = {
                        url: url,
                        action: action,
                        item: item
                    };
                    
                    var title = 'Vendor Dashboard - ' + action.charAt(0).toUpperCase() + action.slice(1);
                    window.history.pushState(state, title, url);
                }
                
                window.scrollTo(0, 0);
                
                return;
            }
            
            VDP.showLoading();
            
            $.ajax({
                url: vdp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_load_content',
                    nonce: vdp_vars.nonce,
                    section: action,
                    item: item,
                    paged: paged || 1
                },
                success: function(response) {
                    if (!response || !response.success) {
                        VDP.showNotice("Error loading content", 'error');
                        return;
                    }
                    
                    if (action === 'dashboard') {
                        VDP.destroyExistingCharts();
                    }
                    
                    $('.vdp-content-area').html(response.data.content);
                    
                    if (response.data.title) {
                        $('.vdp-header-title h1').text(response.data.title);
                    }
                    
                    
                    var cacheKey = 'vdp_cache_' + action + (item ? '_' + item : '') + (paged ? '_page_' + paged : '');
                    try {
                        sessionStorage.setItem(cacheKey, response.data.content);
                        sessionStorage.setItem(cacheKey + '_timestamp', new Date().getTime().toString());
                    } catch (e) {
                    }
                    
                    if (action === 'dashboard') {
                        VDP.initCharts();
                    } else if (action === 'products') {
                        VDP.initProducts();
                    } else if (action === 'messages') {
                        VDP.initMessages();
                    } else if (action === 'settings') {
                        VDP.initSettings();
                        // Trigger event for settings tabs
                        $(document).trigger('vdp_content_loaded', [action]);
                    } else if (action === 'leads') {
                        VDP.initLeads();
                    }
                    
                    if (updateHistory) {
                        var state = {
                            url: url,
                            action: action,
                            item: item
                        };
                        
                        var title = 'Vendor Dashboard - ' + action.charAt(0).toUpperCase() + action.slice(1);
                        window.history.pushState(state, title, url);
                    }
                    
                    window.scrollTo(0, 0);
                },
                error: function(xhr, status, error) {
                    VDP.showNotice(vdp_vars.texts.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                }
            });
        },

        initNotifications: function() {
            $('.vdp-notification-toggle').on('click', function(e) {
                e.preventDefault();
                $('.vdp-notification-dropdown').toggleClass('vdp-show');
                e.stopPropagation();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.vdp-notifications').length) {
                    $('.vdp-notification-dropdown').removeClass('vdp-show');
                }
            });
        },

        initCharts: function() {
            if (!$('.vdp-dashboard-content').length || typeof Chart === 'undefined') {
                return;
            }
            
            
            this.destroyExistingCharts();
            
            var salesData = [];
            var viewsData = [];
            var conversionData = [];
            
            for (var i = 0; i < 14; i++) {
                salesData.push({date: 'Day ' + (i+1), value: 30 + Math.floor(Math.random() * 60)});
                viewsData.push({date: 'Day ' + (i+1), value: 300 + Math.floor(Math.random() * 300)});
                conversionData.push({date: 'Day ' + (i+1), value: 2 + Math.random() * 2.5});
            }
            
            setTimeout(function() {
                if (!Chart.getChart('salesChart')) {
                    VDP.renderChart('salesChart', 'Sales', salesData, '#3483fa');
                }
                
                if (!Chart.getChart('viewsChart')) {
                    VDP.renderChart('viewsChart', 'Views', viewsData, '#39b54a');
                }
                
                if (!Chart.getChart('conversionChart')) {
                    VDP.renderChart('conversionChart', 'Conversion', conversionData, '#f5a623');
                }
            }, 100);
        },

        loadChartData: function(metric, period, callback) {
            $.ajax({
                url: vdp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_get_chart_data',
                    nonce: vdp_vars.nonce,
                    metric: metric,
                    period: period
                },
                success: callback
            });
        },

        destroyExistingCharts: function() {
            var allCharts = Object.values(Chart.instances || {});
            
            if (allCharts.length) {
                allCharts.forEach(function(chart) {
                    if (chart && typeof chart.destroy === 'function') {
                        chart.destroy();
                    }
                });
            } else {
                var chartIds = ['salesChart', 'viewsChart', 'conversionChart'];
                
                chartIds.forEach(function(chartId) {
                    var canvas = document.getElementById(chartId);
                    if (canvas) {
                        var existingChart = Chart.getChart(canvas);
                        if (existingChart) {
                            existingChart.destroy();
                        }
                    }
                });
            }
        },

        createGradient: function(ctx, startColor, endColor) {
            var gradient = ctx.createLinearGradient(0, 0, 0, 160);
            gradient.addColorStop(0, startColor);
            gradient.addColorStop(1, endColor);
            return gradient;
        },

        renderChart: function(chartId, label, data, color) {
            var canvas = document.getElementById(chartId);
            if (!canvas) return;
            
            var existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }

            var ctx = canvas.getContext('2d');
            
            var dates = [];
            var values = [];
            
            data.forEach(function(item) {
                dates.push(item.date);
                values.push(item.value);
            });
            
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
                            return VDP.createGradient(ctx, color + '40', color + '00');
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

        initProducts: function() {
            if (!$('.vdp-products-content').length) {
                return;
            }

            $('.vdp-filter-select').on('change', function() {
                VDP.filterProducts();
            });

            var searchTimeout;
            $('.vdp-search-products').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    VDP.filterProducts();
                }, 300);
            });

            $(document).on('click', '.vdp-delete-product, .vdp-delete-listing', function(e) {
                e.preventDefault();
                
                var itemId = $(this).data('product-id') || $(this).data('listing-id');
                
                if (!itemId) {
                    return;
                }
                
                if (confirm(vdp_vars.texts.confirm_delete)) {
                    VDP.deleteProduct(itemId);
                }
            });

            $('#vdp-listing-form').on('submit', function(e) {
                e.preventDefault();
                VDP.saveProduct($(this));
            });
        },

        filterProducts: function() {
            var category = $('.vdp-filter-select[name="category"]').val();
            var status = $('.vdp-filter-select[name="status"]').val();
            var search = $('.vdp-search-products').val().toLowerCase();
            
            var $items = $('.vdp-product-row, .vdp-listing-card');
            
            $items.each(function() {
                var $item = $(this);
                var itemCategory = $item.data('category');
                var itemStatus = $item.data('status');
                var itemTitle = $item.find('.vdp-product-title, .vdp-listing-title').text().toLowerCase();
                
                var categoryMatch = !category || category === itemCategory;
                var statusMatch = !status || status === itemStatus;
                var searchMatch = !search || itemTitle.indexOf(search) !== -1;
                
                if (categoryMatch && statusMatch && searchMatch) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
        },

        deleteProduct: function(productId) {
            $.ajax({
                url: vdp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_delete_listing',
                    nonce: vdp_vars.nonce,
                    listing_id: productId
                },
                beforeSend: function() {
                    VDP.showLoading();
                },
                success: function(response) {
                    if (response.success) {
                        VDP.showNotice(response.data.message, 'success');
                        $('.vdp-product-row[data-product-id="' + productId + '"], .vdp-listing-card[data-id="' + productId + '"]').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        VDP.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    VDP.showNotice(vdp_vars.texts.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                }
            });
        },

        saveProduct: function($form) {
            var formData = new FormData($form[0]);
            formData.append('action', 'vdp_save_listing');
            formData.append('nonce', vdp_vars.nonce);
            
            $.ajax({
                url: vdp_vars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    VDP.showLoading();
                    $form.find('button[type="submit"]').prop('disabled', true).html(vdp_vars.texts.loading);
                },
                success: function(response) {
                    if (response.success) {
                        VDP.showNotice(response.data.message, 'success');
                        
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
                    VDP.showNotice(vdp_vars.texts.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                    $form.find('button[type="submit"]').prop('disabled', false).html(vdp_vars.texts.loading);
                }
            });
        },

        initMessages: function() {
            if (!$('.vdp-messages-content').length && !$('.vdp-message-view-content').length) {
                return;
            }
            
            
            $('.vdp-filter-select').on('change', function() {
                VDP.filterMessages();
            });
            
            var messagesSearchTimeout;
            $('.vdp-search-messages').on('input', function() {
                clearTimeout(messagesSearchTimeout);
                messagesSearchTimeout = setTimeout(function() {
                    VDP.filterMessages();
                }, 300);
            });
            
            $('#vdp-message-reply-form').on('submit', function(e) {
                e.preventDefault();
                VDP.replyMessage($(this));
            });
            
            $(document).on('click', '.vdp-message-view-btn, .direct-link', function(e) {
                return true;
            });
        },
        
        loadMessageView: function(messageId) {
            if (!messageId) return;
            
            var url = VDP.buildDashboardUrl('messages', messageId);
            
            window.location.href = url;
            
        },

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

        replyMessage: function($form) {
            var formData = new FormData($form[0]);
            formData.append('action', 'vdp_reply_message');
            formData.append('nonce', vdp_vars.nonce);
            
            $.ajax({
                url: vdp_vars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    VDP.showLoading();
                    $form.find('button[type="submit"]').prop('disabled', true).html(vdp_vars.texts.loading);
                },
                success: function(response) {
                    if (response.success) {
                        VDP.showNotice(response.data.message, 'success');
                        
                        if (response.data.reply_html) {
                            $('.vdp-message-thread').append(response.data.reply_html);
                            $form.find('textarea').val('');
                        }
                    } else {
                        VDP.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    VDP.showNotice(vdp_vars.texts.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                    $form.find('button[type="submit"]').prop('disabled', false).html(vdp_vars.texts.loading);
                }
            });
        },

        initSettings: function() {
            if (!$('.vdp-settings-content').length) {
                return;
            }
            
            $('#vdp-settings-form').on('submit', function(e) {
                e.preventDefault();
                VDP.saveSettings($(this));
            });
        },

        saveSettings: function($form) {
            var formData = new FormData($form[0]);
            formData.append('action', 'vdp_save_vendor_settings');
            formData.append('nonce', vdp_vars.nonce);
            
            $.ajax({
                url: vdp_vars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    VDP.showLoading();
                    $form.find('button[type="submit"]').prop('disabled', true).html(vdp_vars.texts.loading);
                },
                success: function(response) {
                    if (response.success) {
                        VDP.showNotice(response.data.message, 'success');
                    } else {
                        VDP.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    VDP.showNotice(vdp_vars.texts.error, 'error');
                },
                complete: function() {
                    VDP.hideLoading();
                    $form.find('button[type="submit"]').prop('disabled', false).html(vdp_vars.texts.loading);
                }
            });
        },

        initExpandableText: function() {
            $('.vdp-expand-toggle').on('click', function() {
                var $expandable = $(this).closest('.vdp-expandable-text');
                $expandable.toggleClass('vdp-expanded');
            });
        },

        initMobileMenu: function() {
            $('.vdp-mobile-menu-toggle').on('click', function() {
                $('.vdp-sidebar').toggleClass('vdp-mobile-open');
            });
            
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.vdp-sidebar, .vdp-mobile-menu-toggle').length) {
                    $('.vdp-sidebar').removeClass('vdp-mobile-open');
                }
            });
        },

        initForms: function() {
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

        initAjaxLoading: function() {
            if (!$('.vdp-loading').length) {
                $('body').append('<div class="vdp-loading"><div class="vdp-loading-spinner"></div></div>');
            }
            
            if (!$('.vdp-progress').length) {
                $('body').append('<div class="vdp-progress"></div>');
            }
            
            if (!$('.vdp-notices').length) {
                $('body').append('<div class="vdp-notices"></div>');
            }
        },

        showLoading: function() {
            $('.vdp-progress').addClass('vdp-active');
            
            this.loadingTimeout = setTimeout(function() {
                $('.vdp-loading').addClass('vdp-active');
            }, 500);
        },

        hideLoading: function() {
            $('.vdp-loading').removeClass('vdp-active');
            $('.vdp-progress').removeClass('vdp-active');
            
            setTimeout(function() {
                $('.vdp-progress').css('width', '0%');
            }, 300);
            
            if (this.loadingTimeout) {
                clearTimeout(this.loadingTimeout);
                this.loadingTimeout = null;
            }
        },

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
        },
        
        initLeads: function() {
            if (!$('.vdp-leads-content').length) {
                return;
            }
            
            if (window.VDPLeads && typeof window.VDPLeads.init === 'function') {
                window.VDPLeads.init();
            }
        },
        
        triggerAddListingForm: function() {
            window.open('/submit-listing/details/', '_blank');
        }
    };

    $(document).ready(function() {
        VDP.init();
    });

})(jQuery);