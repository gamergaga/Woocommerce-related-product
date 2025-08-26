<?php
/**
 * Simple Cache Page for WooCommerce Related Products
 * 
 * This file handles the display and management of the simple cache system.
 * 
 * @package WooCommerceRelatedProducts
 * @subpackage Admin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get current cache status
$cache_status = get_option('wrp_cache_status', 'empty');
$cache_count = get_option('wrp_cache_count', 0);
$total_products = wp_count_posts('product')->publish;
$cache_percentage = $total_products > 0 ? round(($cache_count / $total_products) * 100, 1) : 0;
$last_updated = get_option('wrp_cache_last_updated', __('Never', 'woocommerce-related-products'));
?>

<div class="wrap wrp-cache-page">
    <h1><?php _e('WooCommerce Related Products Cache', 'woocommerce-related-products'); ?></h1>
    
    <div class="wrp-cache-container">
        <div class="wrp-cache-status-card">
            <h2><?php _e('Cache Status', 'woocommerce-related-products'); ?></h2>
            <div class="wrp-status-indicator <?php echo esc_attr($cache_status); ?>">
                <span class="wrp-status-icon"></span>
                <span class="wrp-status-text">
                    <?php 
                    switch ($cache_status) {
                        case 'complete':
                            _e('Complete', 'woocommerce-related-products');
                            break;
                        case 'building':
                            _e('Building', 'woocommerce-related-products');
                            break;
                        case 'empty':
                        default:
                            _e('Empty', 'woocommerce-related-products');
                            break;
                    }
                    ?>
                </span>
            </div>
            <div class="wrp-cache-stats">
                <div class="wrp-stat-item">
                    <span class="wrp-stat-label"><?php _e('Products Cached:', 'woocommerce-related-products'); ?></span>
                    <span class="wrp-stat-value"><?php echo esc_html($cache_count); ?> / <?php echo esc_html($total_products); ?></span>
                </div>
                <div class="wrp-stat-item">
                    <span class="wrp-stat-label"><?php _e('Completion:', 'woocommerce-related-products'); ?></span>
                    <span class="wrp-stat-value"><?php echo esc_html($cache_percentage); ?>%</span>
                </div>
                <div class="wrp-stat-item">
                    <span class="wrp-stat-label"><?php _e('Last Updated:', 'woocommerce-related-products'); ?></span>
                    <span class="wrp-stat-value"><?php echo esc_html($last_updated); ?></span>
                </div>
            </div>
        </div>
        
        <div class="wrp-cache-actions-card">
            <h2><?php _e('Cache Actions', 'woocommerce-related-products'); ?></h2>
            <div class="wrp-action-buttons">
                <button id="wrp-build-cache" class="button button-primary">
                    <?php _e('Build Cache', 'woocommerce-related-products'); ?>
                </button>
                <button id="wrp-clear-cache" class="button">
                    <?php _e('Clear Cache', 'woocommerce-related-products'); ?>
                </button>
            </div>
            
            <div id="wrp-progress-container" class="wrp-progress-container" style="display: none;">
                <div class="wrp-progress-bar">
                    <div class="wrp-progress-fill"></div>
                </div>
                <div class="wrp-progress-info">
                    <span class="wrp-progress-text"><?php _e('Starting...', 'woocommerce-related-products'); ?></span>
                    <span class="wrp-progress-percentage">0%</span>
                </div>
            </div>
            
            <div id="wrp-message-container"></div>
        </div>
        
        <div class="wrp-cache-settings-card">
            <h2><?php _e('Cache Settings', 'woocommerce-related-products'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('wrp_cache_settings');
                do_settings_sections('wrp_cache_settings');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Auto Rebuild Cache', 'woocommerce-related-products'); ?></th>
                        <td>
                            <input type="checkbox" name="wrp_auto_rebuild_cache" value="1" 
                                <?php checked(1, get_option('wrp_auto_rebuild_cache', 0)); ?> />
                            <label for="wrp_auto_rebuild_cache"><?php _e('Automatically rebuild cache when products are updated', 'woocommerce-related-products'); ?></label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Cache Expiry (hours)', 'woocommerce-related-products'); ?></th>
                        <td>
                            <input type="number" name="wrp_cache_expiry" value="<?php echo esc_attr(get_option('wrp_cache_expiry', 24)); ?>" 
                                min="1" max="720" class="small-text" />
                            <p class="description"><?php _e('How long cache entries remain valid (1-720 hours)', 'woocommerce-related-products'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Related Products Limit', 'woocommerce-related-products'); ?></th>
                        <td>
                            <input type="number" name="wrp_related_limit" value="<?php echo esc_attr(get_option('wrp_related_limit', 5)); ?>" 
                                min="1" max="20" class="small-text" />
                            <p class="description"><?php _e('Maximum number of related products to display (1-20)', 'woocommerce-related-products'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div class="wrp-cache-help-card">
            <h2><?php _e('Help & Information', 'woocommerce-related-products'); ?></h2>
            <div class="wrp-help-content">
                <h3><?php _e('About the Cache System', 'woocommerce-related-products'); ?></h3>
                <p><?php _e('The cache system stores related product information to improve performance on your store. When a customer views a product, related products are displayed based on similarities in title, description, categories, and tags.', 'woocommerce-related-products'); ?></p>
                
                <h3><?php _e('Building the Cache', 'woocommerce-related-products'); ?></h3>
                <p><?php _e('Click "Build Cache" to analyze all your products and create the related products cache. This process may take some time depending on the number of products in your store.', 'woocommerce-related-products'); ?></p>
                
                <h3><?php _e('Clearing the Cache', 'woocommerce-related-products'); ?></h3>
                <p><?php _e('Click "Clear Cache" to remove all cached related product data. This may temporarily slow down your store until the cache is rebuilt.', 'woocommerce-related-products'); ?></p>
                
                <h3><?php _e('Troubleshooting', 'woocommerce-related-products'); ?></h3>
                <ul>
                    <li><?php _e('If the cache build process fails, try clearing the cache and building it again.', 'woocommerce-related-products'); ?></li>
                    <li><?php _e('Ensure your server has enough memory and execution time to process all products.', 'woocommerce-related-products'); ?></li>
                    <li><?php _e('Check your PHP error logs for detailed information about any issues.', 'woocommerce-related-products'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Build cache button
    $('#wrp-build-cache').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('<?php _e('Are you sure you want to build the cache? This may take some time.', 'woocommerce-related-products'); ?>')) {
            $('#wrp-progress-container').show();
            $('#wrp-message-container').html('');
            $('#wrp-build-cache').prop('disabled', true);
            $('#wrp-clear-cache').prop('disabled', true);
            
            // Start the cache building process
            wrp_build_cache_step(0);
        }
    });
    
    // Clear cache button
    $('#wrp-clear-cache').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('<?php _e('Are you sure you want to clear the cache? This will remove all related product data.', 'woocommerce-related-products'); ?>')) {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'wrp_clear_cache',
                    nonce: '<?php echo wp_create_nonce('wrp_admin_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#wrp-message-container').html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                        // Reload the page after a short delay to show updated status
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('An error occurred. Please try again.', 'woocommerce-related-products'); ?>';
                        $('#wrp-message-container').html('<div class="notice notice-error is-dismissible"><p>' + errorMsg + '</p></div>');
                    }
                },
                error: function() {
                    $('#wrp-message-container').html('<div class="notice notice-error is-dismissible"><p><?php _e('An error occurred. Please try again.', 'woocommerce-related-products'); ?></p></div>');
                }
            });
        }
    });
    
    // Function to build cache step by step
    function wrp_build_cache_step(offset) {
        // Start the cache rebuild process
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'wrp_rebuild_cache',
                nonce: '<?php echo wp_create_nonce('wrp_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Update progress to 100% since it's processed all at once
                    $('.wrp-progress-fill').css('width', '100%');
                    $('.wrp-progress-text').text(response.data.message);
                    $('.wrp-progress-percentage').text('100%');
                    
                    // Cache building is complete
                    $('#wrp-message-container').html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                    $('#wrp-build-cache').prop('disabled', false);
                    $('#wrp-clear-cache').prop('disabled', false);
                    
                    // Reload the page after a short delay to show updated status
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    // Error occurred
                    var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('An error occurred. Please try again.', 'woocommerce-related-products'); ?>';
                    $('#wrp-message-container').html('<div class="notice notice-error is-dismissible"><p>' + errorMsg + '</p></div>');
                    $('#wrp-build-cache').prop('disabled', false);
                    $('#wrp-clear-cache').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Cache rebuild error:', xhr, status, error);
                $('#wrp-message-container').html('<div class="notice notice-error is-dismissible"><p><?php _e('An error occurred. Please try again.', 'woocommerce-related-products'); ?></p></div>');
                $('#wrp-build-cache').prop('disabled', false);
                $('#wrp-clear-cache').prop('disabled', false);
            }
        });
        
        // Start checking progress
        wrp_check_progress();
    }
    
    // Function to check progress
    function wrp_check_progress() {
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'wrp_cache_progress',
                nonce: '<?php echo wp_create_nonce('wrp_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    var progress = response.data.progress || 0;
                    var message = response.data.message || '<?php _e('Processing...', 'woocommerce-related-products'); ?>';
                    
                    $('.wrp-progress-fill').css('width', progress + '%');
                    $('.wrp-progress-text').text(message);
                    $('.wrp-progress-percentage').text(progress + '%');
                    
                    // If not complete, check again
                    if (progress < 100) {
                        setTimeout(wrp_check_progress, 1000);
                    }
                } else {
                    // If no progress data, assume complete
                    $('.wrp-progress-fill').css('width', '100%');
                    $('.wrp-progress-text').text('<?php _e('Complete', 'woocommerce-related-products'); ?>');
                    $('.wrp-progress-percentage').text('100%');
                }
            },
            error: function(xhr, status, error) {
                console.log('Progress check failed:', xhr, status, error);
                // Stop checking progress on error, but don't break the main process
                // The main cache rebuild will complete and update the UI
            }
        });
    }
});
</script>

<style type="text/css">
.wrp-cache-page {
    max-width: 1200px;
    margin: 0 auto;
}

.wrp-cache-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

@media screen and (max-width: 782px) {
    .wrp-cache-container {
        grid-template-columns: 1fr;
    }
}

.wrp-cache-status-card,
.wrp-cache-actions-card,
.wrp-cache-settings-card,
.wrp-cache-help-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    padding: 20px;
    border-radius: 4px;
}

.wrp-cache-help-card {
    grid-column: 1 / -1;
}

.wrp-status-indicator {
    display: flex;
    align-items: center;
    margin: 15px 0;
    font-weight: bold;
}

.wrp-status-indicator.empty {
    color: #dc3232;
}

.wrp-status-indicator.building {
    color: #f56e28;
}

.wrp-status-indicator.complete {
    color: #46b450;
}

.wrp-status-icon {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
    display: inline-block;
}

.wrp-status-indicator.empty .wrp-status-icon {
    background-color: #dc3232;
}

.wrp-status-indicator.building .wrp-status-icon {
    background-color: #f56e28;
}

.wrp-status-indicator.complete .wrp-status-icon {
    background-color: #46b450;
}

.wrp-cache-stats {
    margin-top: 15px;
}

.wrp-stat-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.wrp-stat-item:last-child {
    border-bottom: none;
}

.wrp-stat-label {
    font-weight: 500;
}

.wrp-stat-value {
    font-weight: bold;
}

.wrp-action-buttons {
    margin-bottom: 20px;
}

.wrp-action-buttons .button {
    margin-right: 10px;
}

.wrp-progress-container {
    margin: 20px 0;
}

.wrp-progress-bar {
    width: 100%;
    height: 20px;
    background-color: #f1f1f1;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.wrp-progress-fill {
    height: 100%;
    background-color: #0073aa;
    width: 0%;
    transition: width 0.3s ease;
}

.wrp-progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
}

.wrp-help-content h3 {
    margin-top: 20px;
    margin-bottom: 10px;
}

.wrp-help-content h3:first-child {
    margin-top: 0;
}

.wrp-help-content ul {
    margin-left: 20px;
}
</style>