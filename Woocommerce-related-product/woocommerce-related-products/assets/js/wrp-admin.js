/**
 * WooCommerce Related Products Pro - Admin JavaScript
 *
 * @package WRP
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Initialize WRP Admin
    var WRP_Admin = {
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.debugInit();
        },

        debugInit: function() {
            // Debug information
            console.log('WRP_Admin initialized');
            console.log('jQuery version:', $.fn.jquery);
            console.log('wrp_admin_vars:', wrp_admin_vars);
            
            // Check if test mode button exists
            var $testModeButton = $('#wrp-test-mode');
            if ($testModeButton.length) {
                console.log('Test mode button found');
                $('#wrp-debug-info').show();
                $('#wrp-debug-content').html('Test mode button found and ready');
                
                // Add direct click handler for testing - calls the actual test mode function
                $testModeButton.on('click.test', function(e) {
                    e.preventDefault();
                    console.log('Direct click handler triggered - calling actual test mode');
                    WRP_Admin.handleTestMode.call(this, e);
                });
                
                // Test if button is actually clickable
                setTimeout(function() {
                    var isClickable = $testModeButton.css('pointer-events') !== 'none' && 
                                     !$testModeButton.prop('disabled') &&
                                     $testModeButton.is(':visible');
                    console.log('Button clickable check:', {
                        pointerEvents: $testModeButton.css('pointer-events'),
                        disabled: $testModeButton.prop('disabled'),
                        visible: $testModeButton.is(':visible'),
                        isClickable: isClickable
                    });
                    $('#wrp-debug-content').append('<br>Button clickable: ' + (isClickable ? 'YES' : 'NO'));
                }, 1000);
                
            } else {
                console.log('Test mode button NOT found');
                $('#wrp-debug-info').show();
                $('#wrp-debug-content').html('Test mode button NOT found');
            }
            
            // Test jQuery event binding
            $(document).on('click', '#wrp-test-mode', function(e) {
                console.log('Document-level click handler triggered - calling actual test mode');
                e.preventDefault();
                WRP_Admin.handleTestMode.call(this, e);
            });
        },

        bindEvents: function() {
            // Tab navigation
            $(document).on('click', '.wrp-tabs .nav-tab', this.handleTabClick);
            
            // Template selector
            $(document).on('change', '.wrp-template-select', this.handleTemplateChange);
            $(document).on('click', '.wrp-preview', this.handlePreviewClick);
            
            // Cache actions
            $(document).on('click', '#wrp-clear-cache', this.handleClearCache);
            $(document).on('click', '#wrp-rebuild-cache', this.handleRebuildCache);
            $(document).on('click', '#wrp-optimize-cache', this.handleOptimizeCache);
            $(document).on('click', '#wrp-test-mode', this.handleTestMode);
            
            // Form validation
            $(document).on('submit', '.wrap form', this.handleFormSubmit);
        },

        initTabs: function() {
            // Check for hash in URL
            var hash = window.location.hash;
            if (hash) {
                this.showTab(hash.substring(1));
            }
        },

        handleTabClick: function(e) {
            e.preventDefault();
            
            var $tab = $(this);
            var tabId = $tab.attr('href').substring(1);
            
            // Update URL hash
            window.location.hash = tabId;
            
            // Show tab
            WRP_Admin.showTab(tabId);
        },

        showTab: function(tabId) {
            // Update tab navigation
            $('.wrp-tabs .nav-tab').removeClass('nav-tab-active');
            $('.wrp-tabs .nav-tab[href="#' + tabId + '"]').addClass('nav-tab-active');
            
            // Update tab content
            $('.wrp-tabs .tab-pane').removeClass('active');
            $('#' + tabId).addClass('active');
        },

        handleTemplateChange: function(e) {
            var template = $(this).val();
            WRP_Admin.updateTemplatePreview(template);
        },

        handlePreviewClick: function(e) {
            e.preventDefault();
            var $preview = $(this);
            var template = $preview.data('template');
            
            if (!template) {
                // Extract template from class
                if ($preview.hasClass('wrp-preview-grid')) {
                    template = 'grid';
                } else if ($preview.hasClass('wrp-preview-list')) {
                    template = 'list';
                } else if ($preview.hasClass('wrp-preview-carousel')) {
                    template = 'carousel';
                }
            }
            
            // Update select
            $('.wrp-template-select').val(template);
            
            // Update preview
            WRP_Admin.updateTemplatePreview(template);
        },

        updateTemplatePreview: function(template) {
            // Update active preview
            $('.wrp-preview').removeClass('active');
            $('.wrp-preview-' + template).addClass('active');
        },

        handleClearCache: function(e) {
            e.preventDefault();
            
            if (!confirm(wrp_admin_vars.i18n.clear_cache_confirm)) {
                return;
            }

            var $button = $(this);
            $button.prop('disabled', true).addClass('loading');

            wp.ajax.send('wrp_clear_cache', {
                data: {
                    nonce: wrp_admin_vars.nonce
                },
                success: function(response) {
                    WRP_Admin.showNotice('success', response.message);
                    // Reload page to show updated stats
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                },
                error: function(response) {
                    WRP_Admin.showNotice('error', response.data.message);
                },
                complete: function() {
                    $button.prop('disabled', false).removeClass('loading');
                }
            });
        },

        handleRebuildCache: function(e) {
            e.preventDefault();
            
            if (!confirm(wrp_admin_vars.i18n.rebuild_cache_confirm)) {
                return;
            }

            var $button = $(this);
            var $progress = $('.wrp-cache-progress');
            var $progressFill = $progress.find('.wrp-progress-fill');
            var $progressText = $progress.find('.wrp-progress-text');
            
            $button.prop('disabled', true).addClass('loading');
            $progress.show();
            $progressFill.css('width', '0%');
            $progressText.text('0%');

            // Start the rebuild process
            wp.ajax.send('wrp_rebuild_cache', {
                data: {
                    nonce: wrp_admin_vars.nonce
                },
                success: function(response) {
                    // Update progress to 100%
                    $progressFill.css('width', '100%');
                    $progressText.text('100%');
                    
                    // Show success message
                    WRP_Admin.showNotice('success', response.message);
                    $button.prop('disabled', false).removeClass('loading');
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(response) {
                    WRP_Admin.showNotice('error', response.data.message);
                    $button.prop('disabled', false).removeClass('loading');
                    $progress.hide();
                }
            });

            // Start polling for progress updates
            var progressInterval = setInterval(function() {
                wp.ajax.send('wrp_cache_progress', {
                    data: {
                        nonce: wrp_admin_vars.nonce
                    },
                    success: function(response) {
                        $progressFill.css('width', response.progress + '%');
                        $progressText.text(response.progress + '%');
                        
                        // Stop polling when complete
                        if (response.progress >= 100) {
                            clearInterval(progressInterval);
                        }
                    },
                    error: function() {
                        // Stop polling on error
                        clearInterval(progressInterval);
                    }
                });
            }, 1000); // Poll every second
        },

        handleOptimizeCache: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $button.prop('disabled', true).addClass('loading');

            $.ajax({
                type: 'POST',
                url: wrp_admin_vars.ajax_url,
                data: {
                    action: 'wrp_optimize_cache',
                    nonce: wrp_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WRP_Admin.showNotice('success', response.data.message);
                    } else {
                        WRP_Admin.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    WRP_Admin.showNotice('error', 'An error occurred while optimizing the cache table.');
                },
                complete: function() {
                    $button.prop('disabled', false).removeClass('loading');
                }
            });
        },

        handleTestMode: function(e) {
            e.preventDefault();
            
            console.log('Test mode button clicked via jQuery');
            $('#wrp-debug-content').append('<br>Button clicked via jQuery at: ' + new Date().toISOString());
            
            if (!confirm('Test mode will rebuild the cache using a simple algorithm to find any related products. This is for debugging purposes only. Continue?')) {
                console.log('User cancelled test mode');
                $('#wrp-debug-content').append('<br>User cancelled test mode');
                return;
            }

            var $button = $(this);
            var $progress = $('.wrp-cache-progress');
            var $progressFill = $progress.find('.wrp-progress-fill');
            var $progressText = $progress.find('.wrp-progress-text');
            
            console.log('Starting test mode via jQuery...');
            $('#wrp-debug-content').append('<br>Starting test mode via jQuery...');
            
            $button.prop('disabled', true).addClass('loading');
            $progress.show();
            $progressFill.css('width', '0%');
            $progressText.text('0%');

            // Start the test mode process
            $.ajax({
                type: 'POST',
                url: wrp_admin_vars.ajax_url,
                data: {
                    action: 'wrp_test_mode',
                    nonce: wrp_admin_vars.nonce
                },
                success: function(response) {
                    console.log('Test mode success via jQuery:', response);
                    $('#wrp-debug-content').append('<br>Test mode success via jQuery: ' + JSON.stringify(response));
                    
                    // Update progress to 100%
                    $progressFill.css('width', '100%');
                    $progressText.text('100%');
                    
                    // Show success message
                    if (response.success) {
                        alert('Success: ' + response.data.message);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                    
                    $button.prop('disabled', false).removeClass('loading');
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    console.error('Test mode error via jQuery:', xhr, status, error);
                    $('#wrp-debug-content').append('<br>Test mode error via jQuery: ' + error);
                    
                    var errorMessage = 'An error occurred while running test mode.';
                    if (xhr.responseJSON && xhr.responseJSON.data) {
                        errorMessage = xhr.responseJSON.data.message;
                    }
                    
                    alert('Error: ' + errorMessage);
                    $button.prop('disabled', false).removeClass('loading');
                    $progress.hide();
                }
            });
        },

        handleFormSubmit: function(e) {
            var $form = $(this);
            var $submit = $form.find('input[type="submit"]');
            
            // Validate numeric inputs
            var isValid = true;
            $form.find('input[type="number"]').each(function() {
                var $input = $(this);
                var min = parseFloat($input.attr('min'));
                var max = parseFloat($input.attr('max'));
                var value = parseFloat($input.val());
                
                if (isNaN(value) || value < min || value > max) {
                    isValid = false;
                    $input.addClass('error');
                } else {
                    $input.removeClass('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                WRP_Admin.showNotice('error', 'Please correct the errors in the form.');
                $submit.prop('disabled', false).removeClass('loading');
            } else {
                $submit.prop('disabled', true).addClass('loading');
            }
        },

        showNotice: function(type, message) {
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            // Insert notice
            var $container = $('.wrap h1').first();
            if ($container.length) {
                $container.after($notice);
            } else {
                $('body').prepend($notice);
            }

            // Make dismissible
            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            });

            // Auto-hide after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        WRP_Admin.init();
    });

})(jQuery);