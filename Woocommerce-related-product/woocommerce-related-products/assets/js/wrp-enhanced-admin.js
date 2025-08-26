/**
 * Enhanced Admin JavaScript for WooCommerce Related Products Pro
 */

(function($) {
    'use strict';

    // Initialize enhanced admin functionality
    $(document).ready(function() {
        WRP_Enhanced_Admin.init();
    });

    var WRP_Enhanced_Admin = {
        init: function() {
            this.bindEvents();
            this.loadInitialStats();
        },

        bindEvents: function() {
            // Tab navigation
            $('.nav-tab').on('click', this.handleTabClick);
            
            // Cache management buttons
            $('#wrp-enhanced-build-cache').on('click', this.buildCache);
            $('#wrp-enhanced-check-status').on('click', this.checkStatus);
            $('#wrp-enhanced-clear-cache').on('click', this.clearCache);
            
            // Form submissions
            $('#wrp-enhanced-settings-form').on('submit', this.saveSettings);
            $('#wrp-enhanced-weights-form').on('submit', this.saveWeights);
            $('#wrp-enhanced-text-form').on('submit', this.saveTextSettings);
            
            // Auto-refresh stats
            setInterval(this.loadStats.bind(this), 30000); // Every 30 seconds
        },

        handleTabClick: function(e) {
            e.preventDefault();
            
            var $tab = $(this);
            var target = $tab.attr('href').substring(1);
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');
            
            // Update active pane
            $('.tab-pane').removeClass('active');
            $('#' + target).addClass('active');
            
            // Load stats if stats tab
            if (target === 'stats') {
                WRP_Enhanced_Admin.loadStats();
            }
        },

        buildCache: function(e) {
            e.preventDefault();
            
            var $button = $('#wrp-enhanced-build-cache');
            var $progress = $('#wrp-enhanced-progress');
            var $status = $('#wrp-enhanced-status');
            
            // Show loading state
            $button.prop('disabled', true);
            $button.find('.dashicons').addClass('wrp-enhanced-loading');
            $progress.addClass('active');
            $progress.find('.progress-bar').css('width', '0%');
            $progress.find('.progress-text').text('0%');
            $status.html('<div class="wrp-enhanced-message info">Building enhanced cache... This may take several minutes.</div>');
            
            // Start building cache
            $.ajax({
                url: wrp_enhanced_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wrp_enhanced_build_cache',
                    nonce: wrp_enhanced_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<div class="wrp-enhanced-message success">' + response.data.message + '</div>');
                        WRP_Enhanced_Admin.loadStats();
                    } else {
                        $status.html('<div class="wrp-enhanced-message error">' + response.data.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<div class="wrp-enhanced-message error">Error building cache: ' + error + '</div>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $button.find('.dashicons').removeClass('wrp-enhanced-loading');
                    $progress.removeClass('active');
                }
            });
            
            // Simulate progress (since we don't have real progress updates)
            WRP_Enhanced_Admin.simulateProgress();
        },

        simulateProgress: function() {
            var $progressBar = $('#wrp-enhanced-progress .progress-bar');
            var $progressText = $('#wrp-enhanced-progress .progress-text');
            var progress = 0;
            
            var interval = setInterval(function() {
                progress += Math.random() * 10;
                if (progress > 95) progress = 95;
                
                $progressBar.css('width', progress + '%');
                $progressText.text(Math.round(progress) + '%');
                
                if (progress >= 95) {
                    clearInterval(interval);
                }
            }, 1000);
        },

        checkStatus: function(e) {
            e.preventDefault();
            
            var $button = $('#wrp-enhanced-check-status');
            var $status = $('#wrp-enhanced-status');
            
            // Show loading state
            $button.prop('disabled', true);
            $button.find('.dashicons').addClass('wrp-enhanced-loading');
            $status.html('<div class="wrp-enhanced-message info">Checking cache status...</div>');
            
            // Check status
            $.ajax({
                url: wrp_enhanced_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wrp_enhanced_cache_status',
                    nonce: wrp_enhanced_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<div class="wrp-enhanced-message success">' + response.data.message + '</div>');
                        WRP_Enhanced_Admin.updateStatsDisplay(response.data.stats);
                    } else {
                        $status.html('<div class="wrp-enhanced-message error">' + response.data.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<div class="wrp-enhanced-message error">Error checking status: ' + error + '</div>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $button.find('.dashicons').removeClass('wrp-enhanced-loading');
                }
            });
        },

        clearCache: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear the enhanced cache? This will remove all cached related products.')) {
                return;
            }
            
            var $button = $('#wrp-enhanced-clear-cache');
            var $status = $('#wrp-enhanced-status');
            
            // Show loading state
            $button.prop('disabled', true);
            $button.find('.dashicons').addClass('wrp-enhanced-loading');
            $status.html('<div class="wrp-enhanced-message info">Clearing enhanced cache...</div>');
            
            // Clear cache
            $.ajax({
                url: wrp_enhanced_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wrp_enhanced_clear_cache',
                    nonce: wrp_enhanced_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<div class="wrp-enhanced-message success">' + response.data.message + '</div>');
                        WRP_Enhanced_Admin.loadStats();
                    } else {
                        $status.html('<div class="wrp-enhanced-message error">' + response.data.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<div class="wrp-enhanced-message error">Error clearing cache: ' + error + '</div>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $button.find('.dashicons').removeClass('wrp-enhanced-loading');
                }
            });
        },

        saveSettings: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submit = $form.find('button[type="submit"]');
            
            // Show loading state
            $submit.prop('disabled', true);
            $submit.html('<span class="dashicons dashicons-update wrp-enhanced-loading"></span> Saving...');
            
            // Save settings
            $.ajax({
                url: wrp_enhanced_admin_vars.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=wrp_enhanced_save_settings&nonce=' + wrp_enhanced_admin_vars.nonce,
                success: function(response) {
                    if (response.success) {
                        WRP_Enhanced_Admin.showMessage($form, response.data.message, 'success');
                    } else {
                        WRP_Enhanced_Admin.showMessage($form, response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    WRP_Enhanced_Admin.showMessage($form, 'Error saving settings: ' + error, 'error');
                },
                complete: function() {
                    $submit.prop('disabled', false);
                    $submit.html('Save Settings');
                }
            });
        },

        saveWeights: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submit = $form.find('button[type="submit"]');
            
            // Show loading state
            $submit.prop('disabled', true);
            $submit.html('<span class="dashicons dashicons-update wrp-enhanced-loading"></span> Saving Weights...');
            
            // Save weights
            $.ajax({
                url: wrp_enhanced_admin_vars.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=wrp_enhanced_save_settings&nonce=' + wrp_enhanced_admin_vars.nonce,
                success: function(response) {
                    if (response.success) {
                        WRP_Enhanced_Admin.showMessage($form, response.data.message, 'success');
                    } else {
                        WRP_Enhanced_Admin.showMessage($form, response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    WRP_Enhanced_Admin.showMessage($form, 'Error saving weights: ' + error, 'error');
                },
                complete: function() {
                    $submit.prop('disabled', false);
                    $submit.html('Save Weights');
                }
            });
        },

        saveTextSettings: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submit = $form.find('button[type="submit"]');
            
            // Show loading state
            $submit.prop('disabled', true);
            $submit.html('<span class="dashicons dashicons-update wrp-enhanced-loading"></span> Saving Text Settings...');
            
            // Save text settings
            $.ajax({
                url: wrp_enhanced_admin_vars.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=wrp_enhanced_save_settings&nonce=' + wrp_enhanced_admin_vars.nonce,
                success: function(response) {
                    if (response.success) {
                        WRP_Enhanced_Admin.showMessage($form, response.data.message, 'success');
                    } else {
                        WRP_Enhanced_Admin.showMessage($form, response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    WRP_Enhanced_Admin.showMessage($form, 'Error saving text settings: ' + error, 'error');
                },
                complete: function() {
                    $submit.prop('disabled', false);
                    $submit.html('Save Text Settings');
                }
            });
        },

        showMessage: function($form, message, type) {
            var $message = $('<div class="wrp-enhanced-message ' + type + '">' + message + '</div>');
            
            // Remove existing messages
            $form.find('.wrp-enhanced-message').remove();
            
            // Add new message
            $form.prepend($message);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $message.fadeOut(function() {
                    $message.remove();
                });
            }, 5000);
        },

        loadInitialStats: function() {
            // Load stats if stats tab is active
            if ($('#stats').hasClass('active')) {
                this.loadStats();
            }
        },

        loadStats: function() {
            var $statsContent = $('#wrp-enhanced-stats-content');
            
            $statsContent.html('<div class="wrp-enhanced-message info">Loading statistics...</div>');
            
            $.ajax({
                url: wrp_enhanced_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wrp_enhanced_cache_status',
                    nonce: wrp_enhanced_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WRP_Enhanced_Admin.updateStatsDisplay(response.data.stats);
                        $statsContent.html(WRP_Enhanced_Admin.generateStatsHTML(response.data.stats));
                    } else {
                        $statsContent.html('<div class="wrp-enhanced-message error">' + response.data.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $statsContent.html('<div class="wrp-enhanced-message error">Error loading statistics: ' + error + '</div>');
                }
            });
        },

        updateStatsDisplay: function(stats) {
            // Update performance metrics
            $('#avg-query-time').text('-- ms');
            $('#cache-hit-rate').text('-- %');
            $('#avg-related-products').text(stats.avg_relations);
            $('#avg-score').text(stats.avg_score);
        },

        generateStatsHTML: function(stats) {
            var html = '';
            
            html += '<div class="stats-grid">';
            html += '<div class="stat-item">';
            html += '<h4>Total Products</h4>';
            html += '<div class="stat-value">' + stats.total_products + '</div>';
            html += '</div>';
            html += '<div class="stat-item">';
            html += '<h4>Cached Products</h4>';
            html += '<div class="stat-value">' + stats.cached_products + '</div>';
            html += '</div>';
            html += '<div class="stat-item">';
            html += '<h4>Cache Coverage</h4>';
            html += '<div class="stat-value">' + stats.cache_percentage + '%</div>';
            html += '</div>';
            html += '<div class="stat-item">';
            html += '<h4>Total Relations</h4>';
            html += '<div class="stat-value">' + stats.total_relations + '</div>';
            html += '</div>';
            html += '<div class="stat-item">';
            html += '<h4>Average Relations</h4>';
            html += '<div class="stat-value">' + stats.avg_relations + '</div>';
            html += '</div>';
            html += '<div class="stat-item">';
            html += '<h4>Average Score</h4>';
            html += '<div class="stat-value">' + stats.avg_score + '</div>';
            html += '</div>';
            html += '<div class="stat-item">';
            html += '<h4>High Score Relations</h4>';
            html += '<div class="stat-value">' + stats.high_score_relations + '</div>';
            html += '</div>';
            html += '<div class="stat-item">';
            html += '<h4>High Score Percentage</h4>';
            html += '<div class="stat-value">' + stats.high_score_percentage + '%</div>';
            html += '</div>';
            html += '</div>';
            
            return html;
        }
    };

    // Make it available globally
    window.WRP_Enhanced_Admin = WRP_Enhanced_Admin;

})(jQuery);