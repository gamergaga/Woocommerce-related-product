jQuery(document).ready(function($) {
    'use strict';

    // Cache Build
    $('#wrp-yarpp-build-cache').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $status = $('#wrp-yarpp-status');
        var $progress = $('#wrp-yarpp-progress');
        
        if ($button.hasClass('disabled')) {
            return;
        }
        
        // Disable button and show loading
        $button.addClass('disabled').prop('disabled', true);
        $button.html('<span class="dashicons dashicons-update spinning"></span> Building Cache...');
        $status.html('<div class="wrp-loading">Starting cache build...</div>');
        $progress.show();
        
        // Reset progress
        updateProgress(0);
        
        // Start progress simulation
        var progressInterval = setInterval(function() {
            var currentProgress = parseInt($progress.find('.progress-bar').width() / $progress.find('.progress-bar-container').width() * 100);
            if (currentProgress < 95) {
                updateProgress(currentProgress + Math.random() * 5);
            }
        }, 1000);
        
        // Send AJAX request
        $.ajax({
            url: wrp_yarpp_admin_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wrp_yarpp_build_cache',
                nonce: wrp_yarpp_admin_vars.nonce
            },
            success: function(response) {
                clearInterval(progressInterval);
                updateProgress(100);
                
                if (response.success) {
                    var message = response.data.message;
                    if (response.data.errors > 0) {
                        message += ' <span class="wrp-warning">(' + response.data.errors + ' errors occurred)</span>';
                    }
                    $status.html('<div class="wrp-success">' + message + '</div>');
                    updateCacheStatus();
                } else {
                    $status.html('<div class="wrp-error">Error: ' + response.data.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                
                // Try to parse response as JSON to get more details
                var responseText = xhr.responseText;
                var errorMessage = 'AJAX Error: ' + error;
                
                if (responseText) {
                    // Check if response contains HTML
                    if (responseText.indexOf('<') === 0) {
                        errorMessage += ' - Server returned HTML instead of JSON';
                        console.error('Server response:', responseText);
                    } else {
                        try {
                            var json = JSON.parse(responseText);
                            if (json.message) {
                                errorMessage = 'Server Error: ' + json.message;
                            }
                        } catch (e) {
                            errorMessage += ' - Invalid JSON response';
                            console.error('Invalid JSON:', responseText);
                        }
                    }
                }
                
                $status.html('<div class="wrp-error">' + errorMessage + '</div>');
            },
            complete: function() {
                setTimeout(function() {
                    $button.removeClass('disabled').prop('disabled', false);
                    $button.html('<span class="dashicons dashicons-update"></span> Build Cache');
                }, 2000);
            }
        });
    });
    
    // Cache Status
    $('#wrp-yarpp-check-status').on('click', function(e) {
        e.preventDefault();
        updateCacheStatus();
    });
    
    // Clear Cache
    $('#wrp-yarpp-clear-cache').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to clear all cached related products? This action cannot be undone.')) {
            return;
        }
        
        var $button = $(this);
        var $status = $('#wrp-yarpp-status');
        
        $button.addClass('disabled').prop('disabled', true);
        $button.html('<span class="dashicons dashicons-update spinning"></span> Clearing...');
        $status.html('<div class="wrp-loading">Clearing cache...</div>');
        
        $.ajax({
            url: wrp_yarpp_admin_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wrp_yarpp_clear_cache',
                nonce: wrp_yarpp_admin_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<div class="wrp-success">' + response.data.message + '</div>');
                    updateCacheStatus();
                } else {
                    $status.html('<div class="wrp-error">Error: ' + response.data.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                var responseText = xhr.responseText;
                var errorMessage = 'AJAX Error: ' + error;
                
                if (responseText) {
                    if (responseText.indexOf('<') === 0) {
                        errorMessage += ' - Server returned HTML instead of JSON';
                        console.error('Server response:', responseText);
                    } else {
                        try {
                            var json = JSON.parse(responseText);
                            if (json.message) {
                                errorMessage = 'Server Error: ' + json.message;
                            }
                        } catch (e) {
                            errorMessage += ' - Invalid JSON response';
                        }
                    }
                }
                
                $status.html('<div class="wrp-error">' + errorMessage + '</div>');
            },
            complete: function() {
                $button.removeClass('disabled').prop('disabled', false);
                $button.html('<span class="dashicons dashicons-trash"></span> Clear Cache');
            }
        });
    });
    
    // Update cache status
    function updateCacheStatus() {
        var $status = $('#wrp-yarpp-status');
        
        $status.html('<div class="wrp-loading">Checking cache status...</div>');
        
        $.ajax({
            url: wrp_yarpp_admin_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wrp_yarpp_cache_status',
                nonce: wrp_yarpp_admin_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    var stats = response.data.stats;
                    var status = response.data.table_status;
                    
                    var statusClass = 'wrp-status-' + status;
                    var statusHtml = '<div class="wrp-status ' + statusClass + '">';
                    statusHtml += '<h4>Cache Status</h4>';
                    statusHtml += '<p><strong>Table:</strong> ' + status.charAt(0).toUpperCase() + status.slice(1) + '</p>';
                    statusHtml += '<p><strong>Products:</strong> ' + stats.cached_products + ' / ' + stats.total_products + ' (' + stats.cache_percentage + '%)</p>';
                    statusHtml += '<p><strong>Relations:</strong> ' + stats.total_relations + '</p>';
                    statusHtml += '<p><strong>Average:</strong> ' + stats.avg_relations + ' relations per product</p>';
                    statusHtml += '<p><strong>Avg Score:</strong> ' + stats.avg_score + '</p>';
                    
                    // Add recommendations based on status
                    if (status === 'missing') {
                        statusHtml += '<div class="wrp-recommendation wrp-warning">Cache table is missing. Click "Build Cache" to create it.</div>';
                    } else if (status === 'empty') {
                        statusHtml += '<div class="wrp-recommendation wrp-info">Cache is empty. Click "Build Cache" to populate it.</div>';
                    } else if (stats.cache_percentage < 100) {
                        statusHtml += '<div class="wrp-recommendation wrp-info">Cache is partially built. Consider rebuilding for complete coverage.</div>';
                    } else {
                        statusHtml += '<div class="wrp-recommendation wrp-success">Cache is fully built and ready to use.</div>';
                    }
                    
                    statusHtml += '</div>';
                    
                    $status.html(statusHtml);
                } else {
                    $status.html('<div class="wrp-error">Error: ' + response.data.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                var responseText = xhr.responseText;
                var errorMessage = 'AJAX Error: ' + error;
                
                if (responseText) {
                    if (responseText.indexOf('<') === 0) {
                        errorMessage += ' - Server returned HTML instead of JSON';
                        console.error('Server response:', responseText);
                    } else {
                        try {
                            var json = JSON.parse(responseText);
                            if (json.message) {
                                errorMessage = 'Server Error: ' + json.message;
                            }
                        } catch (e) {
                            errorMessage += ' - Invalid JSON response';
                        }
                    }
                }
                
                $status.html('<div class="wrp-error">' + errorMessage + '</div>');
            }
        });
    }
    
    // Update progress bar
    function updateProgress(percent) {
        var $progressBar = $('#wrp-yarpp-progress .progress-bar');
        var $progressText = $('#wrp-yarpp-progress .progress-text');
        
        percent = Math.min(100, Math.max(0, percent));
        
        $progressBar.width(percent + '%');
        $progressText.text(Math.round(percent) + '%');
        
        if (percent >= 100) {
            $progressBar.addClass('progress-bar-complete');
        }
    }
    
    // Initialize status on page load
    updateCacheStatus();
});