/**
 * WooCommerce Related Products Pro - Public JavaScript
 *
 * @package WRP
 * @version 1.1.0
 */

(function($) {
    'use strict';

    // Initialize WRP
    var WRP = {
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initCarousels();
            this.debugLayouts();
        },

        bindEvents: function() {
            // Add to cart AJAX
            $(document).on('click', '.wrp-add-to-cart', this.handleAddToCart);
            
            // Buy now button
            $(document).on('click', '.wrp-buy-now', this.handleBuyNow);
            
            // View product button
            $(document).on('click', '.wrp-view-product', this.handleViewProduct);
            
            // Carousel navigation
            $(document).on('click', '.wrp-carousel-nav', this.handleCarouselNav);
            
            // Template change handler for debugging
            $(document).on('change', '.wrp-template-select', this.handleTemplateChange);
        },

        initCarousels: function() {
            var self = this;
            
            // Initialize each carousel
            $('.wrp-template-carousel .wrp-carousel-container').each(function() {
                self.initCarousel($(this));
            });
            
            // Reinitialize carousels on window resize
            var resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    $('.wrp-template-carousel .wrp-carousel-container').each(function() {
                        self.initCarousel($(this));
                    });
                }, 250);
            });
        },

        initCarousel: function($container) {
            var $track = $container.find('.wrp-carousel-track');
            var $items = $container.find('.wrp-product-item');
            var $prev = $container.find('.wrp-carousel-prev');
            var $next = $container.find('.wrp-carousel-next');
            
            if ($items.length === 0) {
                return;
            }
            
            // Calculate dimensions
            var containerWidth = $container.width();
            var itemWidth = $items.first().outerWidth(true);
            var itemMargin = parseInt($items.first().css('margin-right')) || 0;
            var visibleItems = Math.floor(containerWidth / itemWidth);
            var totalItems = $items.length;
            
            // Store carousel data
            $container.data('carousel', {
                currentPosition: 0,
                itemWidth: itemWidth,
                visibleItems: visibleItems,
                totalItems: totalItems,
                maxPosition: Math.max(0, totalItems - visibleItems)
            });
            
            // Show/hide navigation buttons
            this.updateCarouselNavigation($container);
            
            // Enable touch/swipe support
            this.initCarouselTouch($container);
            
            // Initialize carousel position
            this.updateCarouselPosition($container);
        },

        updateCarouselNavigation: function($container) {
            var data = $container.data('carousel');
            if (!data) {
                return;
            }
            
            var $prev = $container.find('.wrp-carousel-prev');
            var $next = $container.find('.wrp-carousel-next');
            
            $prev.toggleClass('disabled', data.currentPosition === 0);
            $next.toggleClass('disabled', data.currentPosition >= data.maxPosition);
            
            // Hide navigation if not needed
            var showNav = data.totalItems > data.visibleItems;
            $prev.toggle(showNav);
            $next.toggle(showNav);
        },

        updateCarouselPosition: function($container) {
            var data = $container.data('carousel');
            if (!data) {
                return;
            }
            
            var $track = $container.find('.wrp-carousel-track');
            var offset = -(data.currentPosition * data.itemWidth);
            
            $track.css({
                'transform': 'translateX(' + offset + 'px)',
                'transition': 'transform 0.3s ease'
            });
            
            this.updateCarouselNavigation($container);
        },

        handleCarouselNav: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $container = $button.closest('.wrp-carousel-container');
            var data = $container.data('carousel');
            
            if (!data) {
                return;
            }
            
            var isPrev = $button.hasClass('wrp-carousel-prev');
            var newPosition = isPrev ? data.currentPosition - 1 : data.currentPosition + 1;
            
            // Validate new position
            newPosition = Math.max(0, Math.min(newPosition, data.maxPosition));
            
            if (newPosition !== data.currentPosition) {
                data.currentPosition = newPosition;
                $container.data('carousel', data);
                
                // Update carousel position
                WRP.updateCarouselPosition($container);
            }
        },

        initCarouselTouch: function($container) {
            var $track = $container.find('.wrp-carousel-track');
            var data = $container.data('carousel');
            
            if (!data) {
                return;
            }
            
            var startX = 0;
            var startY = 0;
            var currentX = 0;
            var isDragging = false;
            
            $track.on('touchstart', function(e) {
                var touch = e.originalEvent.touches[0];
                startX = touch.clientX;
                startY = touch.clientY;
                currentX = startX;
                isDragging = true;
                
                // Remove transition during drag
                $track.css('transition', 'none');
            });
            
            $track.on('touchmove', function(e) {
                if (!isDragging) {
                    return;
                }
                
                var touch = e.originalEvent.touches[0];
                currentX = touch.clientX;
                var diffX = currentX - startX;
                var diffY = touch.clientY - startY;
                
                // Prevent horizontal scroll if vertical movement is minimal
                if (Math.abs(diffX) > Math.abs(diffY)) {
                    e.preventDefault();
                    
                    // Calculate drag position
                    var dragOffset = diffX;
                    var currentOffset = -data.currentPosition * data.itemWidth;
                    var newOffset = currentOffset + dragOffset;
                    
                    // Apply drag offset
                    $track.css('transform', 'translateX(' + newOffset + 'px)');
                }
            });
            
            $track.on('touchend', function(e) {
                if (!isDragging) {
                    return;
                }
                
                isDragging = false;
                var diffX = currentX - startX;
                var threshold = data.itemWidth / 4; // 25% of item width
                
                // Determine if we should change position
                if (Math.abs(diffX) > threshold) {
                    var direction = diffX > 0 ? -1 : 1;
                    var newPosition = data.currentPosition + direction;
                    newPosition = Math.max(0, Math.min(newPosition, data.maxPosition));
                    
                    if (newPosition !== data.currentPosition) {
                        data.currentPosition = newPosition;
                        $container.data('carousel', data);
                    }
                }
                
                // Restore transition and animate to final position
                $track.css('transition', 'transform 0.3s ease');
                var finalOffset = -data.currentPosition * data.itemWidth;
                $track.css('transform', 'translateX(' + finalOffset + 'px)');
                
                // Update navigation
                WRP.updateCarouselNavigation($container);
            });
        },

        handleAddToCart: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var productId = $button.data('product-id');
            var nonce = $button.data('nonce');
            
            if (!productId || !nonce) {
                console.log('WRP: Missing product ID or nonce');
                return;
            }

            // Show loading state
            $button.addClass('loading');
            $button.prop('disabled', true);
            $button.text(wrp_public_vars.i18n.adding_to_cart || 'Adding...');

            // AJAX request
            $.ajax({
                type: 'POST',
                url: wrp_public_vars.ajax_url,
                data: {
                    action: 'wrp_add_to_cart',
                    product_id: productId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update cart fragment
                        $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, $button]);
                        
                        // Show success message
                        WRP.showNotice('success', response.data.message);
                        
                        // Optional: Redirect to cart
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    } else {
                        WRP.showNotice('error', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('WRP: AJAX error', error);
                    WRP.showNotice('error', wrp_public_vars.i18n.cart_error || 'Error adding product to cart.');
                },
                complete: function() {
                    $button.removeClass('loading');
                    $button.prop('disabled', false);
                    $button.text($button.data('original-text') || 'Add to Cart');
                }
            });
        },

        handleBuyNow: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var href = $button.attr('href');
            
            if (href) {
                // Add loading state
                $button.addClass('loading');
                $button.text('Processing...');
                
                // Redirect to cart/checkout
                setTimeout(function() {
                    window.location.href = href;
                }, 300);
            }
        },

        handleViewProduct: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var href = $button.attr('href');
            
            if (href) {
                // Add loading state
                $button.addClass('loading');
                $button.text('Loading...');
                
                // Redirect to product page
                setTimeout(function() {
                    window.location.href = href;
                }, 300);
            }
        },

        handleTemplateChange: function(e) {
            var template = $(this).val();
            console.log('WRP: Template changed to', template);
        },

        showNotice: function(type, message) {
            // Remove existing notices
            $('.woocommerce-error, .woocommerce-message, .woocommerce-info').remove();

            // Create notice element
            var $notice = $('<div class="woocommerce-' + type + '" style="display: none;"></div>');
            $notice.html(message);
            
            // Insert notice
            var $container = $('.wrp-related-products').first();
            if ($container.length) {
                $container.before($notice);
            } else {
                $('body').prepend($notice);
            }

            // Show notice with animation
            $notice.slideDown();

            // Auto-hide after 5 seconds
            setTimeout(function() {
                $notice.slideUp(function() {
                    $notice.remove();
                });
            }, 5000);
        },

        initTooltips: function() {
            // Initialize tooltips if needed
            $('.wrp-product-title').each(function() {
                var $title = $(this);
                var title = $title.find('a').attr('title');
                
                if (title && title.length > 50) {
                    $title.find('a').attr('title', '');
                    $title.attr('title', title);
                }
            });
        },

        debugLayouts: function() {
            // Debug information
            if (window.location.hash === '#wrp-debug') {
                console.log('WRP Debug Info:');
                console.log('Grid template:', $('.wrp-template-grid').length);
                console.log('List template:', $('.wrp-template-list').length);
                console.log('Carousel template:', $('.wrp-template-carousel').length);
                console.log('Carousel containers:', $('.wrp-carousel-container').length);
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        WRP.init();
    });

    // Handle AJAX add to cart response
    $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        // Update cart widget if exists
        if ($('.widget_shopping_cart').length) {
            $('.widget_shopping_cart').trigger('wc_update_cart');
        }
    });

    // Reinitialize carousels when related products are dynamically loaded
    $(document).on('wrp_products_loaded', function() {
        WRP.initCarousels();
    });

})(jQuery);