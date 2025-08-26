# WooCommerce Related Products Pro - Documentation

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Display Options](#display-options)
5. [Algorithm Settings](#algorithm-settings)
6. [Shortcode Usage](#shortcode-usage)
7. [Widget Usage](#widget-usage)
8. [Template Customization](#template-customization)
9. [Hooks and Filters](#hooks-and-filters)
10. [Performance Optimization](#performance-optimization)
11. [Troubleshooting](#troubleshooting)
12. [FAQ](#faq)

## Introduction

WooCommerce Related Products Pro is a powerful plugin designed to display related products on your WooCommerce store with an intelligent algorithm that analyzes product content to find the most relevant matches.

### Key Features

- **Advanced Algorithm**: Uses sophisticated content analysis optimized for WooCommerce products
- **Multiple Templates**: Grid, list, and carousel layouts
- **Action Buttons**: Add to cart and buy now buttons for increased conversions
- **Smart Caching**: Built-in caching for optimal performance
- **Highly Customizable**: Extensive configuration options
- **Responsive Design**: Works perfectly on all devices
- **SEO Friendly**: Helps reduce bounce rate and increase engagement

### Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- WooCommerce 3.0 or higher
- MySQL 5.6+ or MariaDB 10.1+ (for full-text search)

## Installation

### Automatic Installation

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "WooCommerce Related Products Pro"
4. Click **Install Now**
5. Activate the plugin
6. Configure settings in **WooCommerce > Related Products**

### Manual Installation

1. Download the plugin ZIP file
2. In WordPress admin, go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin
5. Configure settings in **WooCommerce > Related Products**

## Configuration

After installation, you can configure the plugin by navigating to **WooCommerce > Related Products** in your WordPress admin.

### General Settings

- **Match Threshold**: Minimum score required for products to be considered related (0-10)
- **Number of Products**: Maximum number of related products to display (1-20)
- **Excerpt Length**: Number of words to show in product excerpts (5-50)
- **Auto Display**: Automatically show related products on product pages
- **Display Position**: Where to show related products (before content, after content, after add to cart)
- **Enable Caching**: Enable caching for better performance
- **Cache Timeout**: How long cached results should be stored (in seconds)

### Display Settings

- **Show Price**: Display product prices
- **Show Rating**: Display product ratings and reviews
- **Show Add to Cart**: Display add to cart buttons
- **Show Buy Now**: Display buy now buttons
- **Template**: Choose between grid, list, or carousel layouts
- **Columns**: Number of columns for grid layout (1-6)
- **Image Size**: Choose from registered WordPress image sizes
- **Show Excerpt**: Display product excerpts

### Algorithm Settings

- **Content Weights**: Adjust importance of titles, descriptions, and short descriptions
- **Taxonomy Weights**: Control importance of categories and tags
- **Taxonomy Requirements**: Set minimum shared terms required
- **Exclude Terms**: Comma-separated list of term IDs to exclude
- **Recent Products Only**: Only show products published recently
- **Recent Period**: Time period for recent products filter
- **Include Out of Stock**: Choose whether to include out-of-stock products

## Display Options

The plugin offers three built-in templates:

### Grid Template

The grid template displays products in a responsive grid layout. It's perfect for showcasing multiple products with images and key information.

**Features:**
- Responsive grid layout
- Product images with hover effects
- Product titles, prices, and ratings
- Add to cart and buy now buttons
- Sale badges for discounted products

### List Template

The list template displays products in a vertical list format with more detailed information.

**Features:**
- Vertical list layout
- Larger product images
- Detailed product information
- Excerpts with customizable length
- Action buttons for easy purchasing

### Carousel Template

The carousel template displays products in a horizontal scrolling carousel, perfect for space-constrained areas.

**Features:**
- Horizontal scrolling carousel
- Navigation arrows
- Touch/swipe support on mobile
- Auto-scroll option
- Responsive design

## Algorithm Settings

The plugin uses a sophisticated algorithm to determine related products based on multiple factors:

### Content Analysis

The algorithm analyzes product content using full-text search:

- **Product Titles**: Weighted importance of product names
- **Product Descriptions**: Long descriptions and detailed information
- **Short Descriptions**: Brief product summaries

### Taxonomy Matching

The algorithm considers shared taxonomy terms:

- **Product Categories**: Primary product classification
- **Product Tags**: Additional product descriptors
- **Custom Taxonomies**: Any custom product taxonomies

### Scoring System

Each factor is weighted and combined to create a relevance score:

```
Total Score = (Title Score × Title Weight) + 
              (Description Score × Description Weight) + 
              (Short Description Score × Short Description Weight) + 
              (Category Score × Category Weight) + 
              (Tag Score × Tag Weight)
```

Only products with scores above the threshold are displayed.

## Shortcode Usage

You can display related products anywhere using shortcodes.

### Basic Usage

```shortcode
[related_products]
```

### With Custom Parameters

```shortcode
[related_products limit="6" template="grid" columns="3" show_price="true"]
```

### All Available Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | integer | current | Specific product ID |
| `limit` | integer | 4 | Number of products to show |
| `columns` | integer | 4 | Number of columns for grid layout |
| `template` | string | "grid" | Template style: grid, list, carousel |
| `show_price` | boolean | true | Show product prices |
| `show_rating` | boolean | true | Show product ratings |
| `show_add_to_cart` | boolean | true | Show add to cart buttons |
| `show_buy_now` | boolean | true | Show buy now buttons |
| `show_excerpt` | boolean | false | Show product excerpts |
| `excerpt_length` | integer | 10 | Excerpt length in words |
| `image_size` | string | "woocommerce_thumbnail" | Product image size |
| `threshold` | float | 1 | Minimum match score |
| `exclude` | string | "" | Comma-separated term IDs to exclude |
| `recent_only` | boolean | false | Only show recent products |
| `recent_period` | string | "30 day" | Time period for recent products |
| `include_out_of_stock` | boolean | false | Include out-of-stock products |
| `weight_title` | float | 2 | Weight for title matching |
| `weight_description` | float | 1 | Weight for description matching |
| `weight_short_description` | float | 1 | Weight for short description matching |
| `weight_categories` | float | 3 | Weight for category matching |
| `weight_tags` | float | 2 | Weight for tag matching |
| `require_categories` | integer | 1 | Minimum shared categories required |
| `require_tags` | integer | 0 | Minimum shared tags required |

### Examples

**Show 6 products in a 3-column grid:**
```shortcode
[related_products limit="6" columns="3"]
```

**Show only products with high category relevance:**
```shortcode
[related_products weight_categories="5" require_categories="2"]
```

**Show recent products only:**
```shortcode
[related_products recent_only="true" recent_period="7 day"]
```

**Customize display options:**
```shortcode
[related_products show_price="false" show_rating="false" show_excerpt="true"]
```

## Widget Usage

The plugin includes a widget for displaying related products in any widget area.

### Adding the Widget

1. Go to **Appearance > Widgets** in WordPress admin
2. Find the "Related Products (WRP)" widget
3. Drag it to your desired widget area
4. Configure the widget settings

### Widget Settings

- **Title**: Widget title (default: "Related Products")
- **Number of products**: How many products to show (1-10)
- **Columns**: Number of columns for grid layout (1-4)
- **Template**: Template style (list, grid, carousel)
- **Image Size**: Product image size
- **Match Threshold**: Minimum match score
- **Show Price**: Display product prices
- **Show Rating**: Display product ratings
- **Show Add to Cart**: Display add to cart buttons
- **Show Buy Now**: Display buy now buttons
- **Show Excerpt**: Display product excerpts
- **Excerpt Length**: Excerpt length in words

## Template Customization

You can customize the appearance of related products by overriding the default templates.

### Template Structure

Templates are located in `/woocommerce-related-products/templates/`:

- `wrp-template-grid.php` - Grid layout
- `wrp-template-list.php` - List layout
- `wrp-template-carousel.php` - Carousel layout

### Overriding Templates

1. Create a folder in your theme called `wrp-templates`
2. Copy the template file(s) you want to modify
3. Paste them in your theme's `wrp-templates/` folder
4. Modify the templates as needed

### Template Variables

Each template receives the following variables:

- `$related_products`: Array of WC_Product objects
- `$reference_product`: The current product being viewed
- `$args`: Display arguments and settings

### Example Template Override

```php
<?php
/**
 * Custom Grid Template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

extract( $args );

if ( empty( $related_products ) ) {
    return;
}
?>

<div class="my-custom-related-products">
    <h3><?php _e( 'You Might Also Like', 'my-theme' ); ?></h3>
    
    <div class="my-products-grid">
        <?php foreach ( $related_products as $product ) : ?>
            <div class="my-product-item">
                <!-- Your custom product markup here -->
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

## Hooks and Filters

The plugin provides numerous hooks and filters for developers.

### General Filters

```php
// Filter the heading text
add_filter( 'wrp_related_products_heading', function( $heading ) {
    return 'Recommended for You';
} );

// Control whether to show the heading
add_filter( 'wrp_show_heading', function( $show, $args ) {
    return false; // Hide heading
}, 10, 2 );

// Filter plugin options
add_filter( 'wrp_get_option', function( $value, $option ) {
    if ( $option === 'limit' ) {
        return 6; // Always show 6 products
    }
    return $value;
}, 10, 2 );
```

### Display Filters

```php
// Filter product image HTML
add_filter( 'wrp_product_image_html', function( $image, $product, $size, $attr ) {
    // Add custom attributes
    $attr['loading'] = 'lazy';
    return $product->get_image( $size, $attr );
}, 10, 4 );

// Filter product price HTML
add_filter( 'wrp_product_price_html', function( $price, $product ) {
    // Add custom price formatting
    return '<span class="custom-price">' . $price . '</span>';
}, 10, 2 );

// Filter add to cart button HTML
add_filter( 'wrp_add_to_cart_button_html', function( $button, $product ) {
    // Custom button text
    return str_replace( 'Add to cart', 'Buy Now', $button );
}, 10, 2 );
```

### Algorithm Filters

```php
// Filter relatedness score
add_filter( 'wrp_related_score', function( $score, $reference_id, $related_id ) {
    // Boost score for products in same category
    if ( has_term( 'featured', 'product_cat', $reference_id ) && 
         has_term( 'featured', 'product_cat', $related_id ) ) {
        $score *= 1.5;
    }
    return $score;
}, 10, 3 );

// Filter stop words list
add_filter( 'wrp_keywords_overused_words', function( $words ) {
    // Add custom stop words
    $words[] = 'custom';
    $words[] = 'brand';
    return $words;
} );
```

### Action Hooks

```php
// Before related products display
add_action( 'wrp_before_related_products', function( $reference_product, $args ) {
    echo '<div class="related-products-intro">';
    echo '<p>Based on your interest in ' . esc_html( $reference_product->get_name() ) . '</p>';
    echo '</div>';
}, 10, 2 );

// After related products display
add_action( 'wrp_after_related_products', function() {
    echo '<div class="view-all-products">';
    echo '<a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">View All Products</a>';
    echo '</div>';
} );
```

## Performance Optimization

The plugin is designed for optimal performance, but there are several things you can do to ensure it runs smoothly on large sites.

### Caching

The plugin includes a sophisticated caching system:

- **Database Caching**: Related products are cached in custom database tables
- **Object Caching**: WordPress object cache is used for frequently accessed data
- **Cache Management**: Built-in tools to monitor and manage cache performance

### Database Optimization

For optimal performance:

1. **Enable Full-Text Search**: Ensure your database supports full-text indexes
2. **Regular Maintenance**: Optimize database tables regularly
3. **Index Optimization**: Ensure proper indexes are in place

### Large Catalogs

For stores with 10,000+ products:

1. **Enable Caching**: Make sure caching is enabled with reasonable timeouts
2. **Off-Peak Rebuilding**: Rebuild cache during off-peak hours
3. **Monitor Performance**: Use the cache status page to monitor performance
4. **Server Resources**: Ensure adequate server resources (CPU, RAM, storage)

### Best Practices

- **Reasonable Limits**: Don't show too many related products (4-8 is optimal)
- **Appropriate Threshold**: Use appropriate match thresholds to avoid irrelevant results
- **Regular Updates**: Keep the plugin and WordPress/WooCommerce updated
- **Monitoring**: Monitor site performance after installation

## Troubleshooting

### Common Issues

#### Related Products Not Showing

**Possible Causes:**
- Plugin not activated
- WooCommerce not activated
- No related products found
- Cache not built yet

**Solutions:**
1. Ensure both WooCommerce and the plugin are activated
2. Check that products have categories, tags, or descriptive content
3. Rebuild the cache from the admin panel
4. Lower the match threshold in settings

#### Poor Related Product Matches

**Possible Causes:**
- Match threshold too high
- Insufficient product content
- Incorrect weight settings

**Solutions:**
1. Lower the match threshold
2. Add more descriptive content to products
3. Adjust content and taxonomy weights
4. Ensure products have appropriate categories and tags

#### Performance Issues

**Possible Causes:**
- Large product catalog
- Caching disabled
- Server resource constraints

**Solutions:**
1. Enable caching with appropriate timeout
2. Rebuild cache during off-peak hours
3. Optimize database tables
4. Consider upgrading server resources

#### Styling Issues

**Possible Causes:**
- Theme conflicts
- CSS overrides
- Missing stylesheets

**Solutions:**
1. Check browser console for CSS errors
2. Use theme template overrides
3. Add custom CSS as needed
4. Test with default theme

### Debug Mode

Enable debug mode to troubleshoot issues:

```php
// Add to wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Check the debug log for errors:
`/wp-content/debug.log`

## FAQ

### Q: How is this different from WooCommerce's built-in related products?

A: WooCommerce's built-in related products rely on manually assigned relationships or simple category matching. Our plugin uses an advanced algorithm that analyzes product content (titles, descriptions) and taxonomy relationships to find truly relevant matches using sophisticated content analysis.

### Q: Does the plugin work with product variations?

A: Yes! The plugin works with all WooCommerce product types including simple, variable, grouped, external, virtual, and downloadable products.

### Q: Can I customize the appearance of related products?

A: Absolutely! The plugin includes three built-in templates (grid, list, carousel) and you can create custom templates by overriding the default templates in your theme.

### Q: How does the algorithm determine related products?

A: The algorithm analyzes multiple factors including product titles, descriptions, short descriptions, categories, and tags. Each factor is weighted and combined to create a relevance score. Only products above the minimum threshold are displayed.

### Q: Is the plugin performance optimized for large catalogs?

A: Yes! The plugin includes smart caching, database optimization, and efficient queries. For stores with 10,000+ products, we recommend enabling caching and rebuilding during off-peak hours.

### Q: Can I exclude certain products or categories from being shown as related?

A: Yes! You can exclude specific categories or tags by entering their IDs in the algorithm settings. You can also control which products are included through the various filter options.

### Q: Does the plugin work with page builders?

A: Yes! The plugin includes shortcode support so you can display related products anywhere on your site, including pages built with page builders.

### Q: Can I show related products on non-product pages?

A: Yes! Using the shortcode with a specific product ID, you can show related products for any product anywhere on your site.

### Q: How often does the cache need to be rebuilt?

A: The cache automatically updates when products are created, updated, or deleted. For manual control, you can rebuild the cache from the admin panel or set up automatic rebuilding during off-peak hours.

### Q: Is the plugin translation ready?

A: Yes! The plugin includes translation files and is fully compatible with WPML and other multilingual plugins.

## Support

For additional support, please visit:

- **Documentation**: [Plugin Documentation](https://yourwebsite.com/documentation)
- **Support Forum**: [WordPress.org Support](https://wordpress.org/support/plugin/woocommerce-related-products-pro/)
- **Premium Support**: [Priority Support](https://yourwebsite.com/support)
- **Bug Reports**: [GitHub Issues](https://github.com/yourusername/woocommerce-related-products-pro/issues)

## License

This plugin is released under the GPLv2 license. See LICENSE file for details.