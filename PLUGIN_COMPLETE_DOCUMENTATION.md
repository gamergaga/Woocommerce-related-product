# WooCommerce Related Products Pro - Complete Plugin Documentation

## Overview

WooCommerce Related Products Pro is a premium WordPress plugin designed to display related WooCommerce products with advanced algorithmic matching, add to cart functionality, and buy now buttons. The plugin uses sophisticated content analysis and taxonomy matching to provide highly relevant product recommendations that can increase cross-selling and improve user experience.

## Key Features

### 1. Advanced Algorithm
- **Content Analysis**: Analyzes product titles, descriptions, and short descriptions
- **Taxonomy Matching**: Considers product categories and tags with customizable weights
- **Price Range Similarity**: Includes products in similar price ranges
- **Keyword Extraction**: Advanced keyword processing with stop-word filtering and position-based scoring
- **Multi-language Support**: Built-in internationalization capabilities

### 2. Display Options
- **Multiple Templates**: Grid, List, and Carousel layouts
- **Responsive Design**: Mobile-friendly layouts that work on all devices
- **Customizable Elements**: Show/hide prices, ratings, add to cart buttons, buy now buttons
- **Flexible Positioning**: Display before content, after content, or after add to cart form
- **Image Size Control**: Configurable product image sizes

### 3. Caching System
- **Performance Optimized**: Advanced caching system using custom database tables
- **Bulk Operations**: Efficient cache building and clearing
- **Cache Statistics**: Detailed cache status and performance metrics
- **Automatic Management**: Cache updates on product changes
- **Fallback Mechanism**: Graceful degradation when cache fails

### 4. Admin Interface
- **Intuitive Dashboard**: Clean, modern admin interface with tabbed navigation
- **Visual Template Selector**: Interactive template preview and selection
- **Real-time Statistics**: Live cache status and product metrics
- **Bulk Operations**: One-click cache rebuild, clear, and optimize
- **Progress Tracking**: Visual progress indicators for long-running operations

### 5. Integration Features
- **Shortcode Support**: Manual placement with `[related_products]` shortcode
- **Widget Support**: Display related products in widget areas
- **Theme Compatibility**: Works with most WooCommerce-compatible themes
- **AJAX Functionality**: Dynamic add to cart without page refresh
- **Hooks and Filters**: Extensible for developers

## Technical Architecture

### Core Components

#### 1. WRP_Core (Main Class)
- **Purpose**: Central controller for plugin functionality
- **Key Methods**:
  - `get_related_products()`: Main method for retrieving related products
  - `display_related_products()`: Handles template rendering
  - `get_cache_stats()`: Provides cache statistics
  - `get_cache()`: Returns cache instance

#### 2. Cache System
- **Base Class**: `WRP_Cache` (Abstract)
- **Implementation**: `WRP_Cache_Tables`
- **Features**:
  - Custom database table storage
  - Object caching integration
  - Bulk insert operations with fallback
  - Automatic cache invalidation

#### 3. Algorithm Engine
- **Scoring System**: Multi-factor relevance scoring
- **Weight Configuration**: Customizable weights for different factors
- **Query Builder**: Dynamic SQL generation for related products
- **Fallback System**: Multiple fallback levels when no matches found

#### 4. Template System
- **Built-in Templates**: Grid, List, Carousel
- **Theme Override**: Custom template support in themes
- **Responsive Design**: Mobile-first approach
- **Customizable Elements**: Modular component system

### Database Schema

#### Cache Table (`wp_wrp_related_cache`)
```sql
CREATE TABLE wp_wrp_related_cache (
    reference_id bigint(20) NOT NULL,
    related_id bigint(20) NOT NULL,
    score float NOT NULL,
    date datetime NOT NULL,
    PRIMARY KEY  (reference_id, related_id),
    KEY score (score),
    KEY related_id (related_id),
    KEY date (date)
);
```

### Configuration Options

#### General Settings
- **Match Threshold**: Minimum relevance score (0.5-10)
- **Number of Products**: Products to display (1-20)
- **Excerpt Length**: Words in product excerpts (5-50)
- **Auto Display**: Enable automatic display on product pages
- **Display Position**: Where to show related products
- **Cache Settings**: Enable/disable caching and timeout

#### Display Settings
- **Show Price**: Display product prices
- **Show Rating**: Display product ratings
- **Show Add to Cart**: Display add to cart buttons
- **Show Buy Now**: Display buy now buttons
- **Template Selection**: Choose display template
- **Columns**: Number of columns in grid layout
- **Image Size**: Product image size

#### Algorithm Settings
- **Content Weights**: Title, description, short description weights
- **Taxonomy Weights**: Category and tag weights
- **Requirements**: Minimum category/tag matches
- **Exclusions**: Exclude specific terms
- **Time Filters**: Recent products only
- **Stock Filter**: Include/exclude out of stock products

## Installation and Setup

### Requirements
- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

### Installation Steps
1. Download the plugin ZIP file
2. Upload to WordPress via Plugins → Add New → Upload Plugin
3. Activate the plugin
4. Configure settings in Related Products → Settings
5. Build initial cache via Related Products → Cache Status

### Initial Configuration
1. **General Settings**: Set basic display options
2. **Display Settings**: Choose template and layout
3. **Algorithm Settings**: Configure matching weights
4. **Build Cache**: Click "Rebuild Cache" to populate related products

## Usage Guide

### Automatic Display
The plugin automatically displays related products on WooCommerce product pages when:
- Auto Display is enabled in settings
- Products have sufficient content for matching
- Cache is populated with related products

### Manual Display via Shortcode
```shortcode
[related_products]
```

#### Shortcode Attributes
- `id`: Specific product ID
- `limit`: Number of products to show
- `columns`: Number of columns
- `template`: Template type (grid, list, carousel)
- `show_price`: Show/hide prices
- `show_rating`: Show/hide ratings
- `show_add_to_cart`: Show/hide add to cart buttons
- `show_buy_now`: Show/hide buy now buttons
- `threshold`: Match threshold

#### Examples
```shortcode
[related_products limit="6" template="grid" columns="3"]
[related_products id="123" template="carousel" show_price="false"]
[related_products threshold="2" show_excerpt="true"]
```

### Manual Display via PHP
```php
<?php
// Display related products for current product
wrp_display_related_products();

// Display for specific product with custom options
wrp_display_related_products(123, array(
    'limit' => 6,
    'template' => 'list',
    'show_price' => true
));

// Check if related products exist
if (wrp_has_related_products()) {
    wrp_display_related_products();
}
?>
```

### Widget Usage
1. Go to Appearance → Widgets
2. Add "Related Products" widget to sidebar
3. Configure widget options
4. Save changes

## Troubleshooting

### Common Issues

#### 1. Related Products Not Showing
**Symptoms**: No related products appear on product pages
**Solutions**:
- Check if auto-display is enabled in settings
- Verify cache is built (visit Cache Status page)
- Ensure products have categories/tags and descriptive content
- Check theme compatibility with WooCommerce hooks
- Try using shortcode to test functionality

#### 2. Cache Not Building
**Symptoms**: Rebuild cache process gets stuck or shows errors
**Solutions**:
- Check PHP memory limit and execution time
- Verify database permissions for cache table creation
- Check for plugin conflicts
- Increase PHP timeout settings if needed
- Look for error messages in debug log

#### 3. Admin Page Errors
**Symptoms**: Fatal errors or blank pages in admin
**Solutions**:
- Check PHP error logs for specific errors
- Verify all plugin files are uploaded correctly
- Check for plugin conflicts
- Ensure WooCommerce is active and updated
- Verify user has sufficient permissions

#### 4. Performance Issues
**Symptoms**: Slow page loads or high server load
**Solutions**:
- Enable caching in plugin settings
- Optimize database tables
- Reduce number of products displayed
- Check server resources and hosting plan
- Consider using object caching plugins

### Debug Mode
Enable WordPress debug mode to troubleshoot issues:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check debug log in `wp-content/debug.log` for error messages.

## Developer Guide

### Hooks and Filters

#### Actions
- `wrp_before_related_products`: Before displaying related products
- `wrp_after_related_products`: After displaying related products
- `wrp_cache_cleared`: After cache is cleared
- `wrp_cache_built`: After cache is built

#### Filters
- `wrp_related_products_args`: Modify arguments for getting related products
- `wrp_product_image_html`: Modify product image HTML
- `wrp_product_price_html`: Modify product price HTML
- `wrp_product_rating_html`: Modify product rating HTML
- `wrp_add_to_cart_button_html`: Modify add to cart button HTML
- `wrp_buy_now_button_html`: Modify buy now button HTML

### Custom Templates
Create custom templates in your theme:

1. Create folder: `your-theme/wrp-templates/`
2. Copy template file: `wrp-template-custom.php`
3. Modify as needed
4. Select custom template in plugin settings

#### Template Structure
```php
<?php
/**
 * Custom Related Products Template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="wrp-related-products wrp-template-custom">
    <h2><?php _e( 'Related Products', 'woocommerce-related-products' ); ?></h2>
    
    <div class="wrp-products">
        <?php foreach ( $related_products as $product ) : ?>
            <div class="wrp-product">
                <?php echo $product->get_image(); ?>
                <h3><?php echo $product->get_name(); ?></h3>
                <?php echo $product->get_price_html(); ?>
                <?php echo $product->add_to_cart_url(); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

### API Functions

#### Core Functions
- `wrp_get_related_products( $product_id, $args )`: Get related products
- `wrp_display_related_products( $product_id, $args, $echo )`: Display related products
- `wrp_has_related_products( $product_id, $args )`: Check if related products exist
- `wrp_get_product_keywords( $product_id, $type )`: Get product keywords
- `wrp_clear_cache( $product_ids )`: Clear cache for products

#### Option Functions
- `wrp_get_option( $option, $default )`: Get plugin option
- `wrp_update_option( $option, $value )`: Update plugin option
- `wrp_delete_option( $option )`: Delete plugin option

## Performance Optimization

### Caching Strategy
- **Database Caching**: Custom table for fast related product lookups
- **Object Caching**: WordPress object cache integration
- **Query Caching**: Cached database queries with expiration
- **Static Asset Caching**: CSS and JavaScript files with versioning

### Database Optimization
- **Indexing**: Proper database indexes for fast queries
- **Bulk Operations**: Efficient bulk insert and update operations
- **Table Optimization**: Regular table optimization commands
- **Query Optimization**: Efficient SQL queries with proper joins

### Frontend Optimization
- **Lazy Loading**: Load related products only when needed
- **Minified Assets**: Compressed CSS and JavaScript files
- **Responsive Images**: Optimized image loading for different devices
- **AJAX Operations**: Dynamic content loading without page refresh

## Security Considerations

### Data Validation
- **Input Sanitization**: All user inputs are properly sanitized
- **Output Escaping**: All outputs are properly escaped
- **Nonce Verification**: AJAX requests use WordPress nonces
- **Capability Checks**: Admin functions require proper permissions

### Database Security
- **Prepared Statements**: All database queries use prepared statements
- **SQL Injection Prevention**: Proper escaping of database inputs
- **Table Prefixing**: Uses WordPress table prefix for security
- **Permission Checks**: Verifies database write permissions

### File Security
- **Direct Access Prevention**: Blocks direct file access
- **Capability Verification**: Checks user capabilities
- **File Validation**: Validates file uploads and includes
- **Path Security**: Secure file path handling

## Internationalization

### Translation Ready
- **Text Domain**: `woocommerce-related-products`
- **Translation Files**: Included POT file for translation
- **Multilingual Support**: Compatible with WPML and Polylang
- **RTL Support**: Right-to-left language support

### Supported Languages
- English (default)
- Translation files for multiple languages
- Easy to add new languages

## Browser Compatibility

### Supported Browsers
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)
- Opera (latest 2 versions)

### Mobile Support
- iOS Safari (latest 2 versions)
- Android Chrome (latest 2 versions)
- Responsive design for all screen sizes

## Support and Maintenance

### Documentation
- Complete inline documentation
- User guide and developer documentation
- Code comments and examples
- Troubleshooting guide

### Updates
- Regular updates for compatibility
- Security patches and bug fixes
- Feature improvements and enhancements
- WooCommerce compatibility updates

### Support Channels
- WordPress.org support forums
- Email support for premium customers
- Documentation and knowledge base
- Video tutorials and guides

## License and Terms

### License
- GNU General Public License v2.0 or later
- Premium features may require additional licensing
- Third-party libraries have their own licenses

### Terms of Use
- Use on unlimited websites
- Lifetime updates and support
- No hidden fees or subscriptions
- 30-day money-back guarantee

## Changelog

### Version 1.0.0
- Initial release
- Advanced related products algorithm
- Multiple display templates
- Caching system with database tables
- Admin interface with visual template selector
- Shortcode and widget support
- AJAX functionality for add to cart
- Complete internationalization support

### Future Roadmap
- Machine learning recommendations
- Advanced analytics and reporting
- A/B testing capabilities
- Integration with external recommendation engines
- Enhanced mobile experience
- Performance improvements
- Additional templates and layouts

## Conclusion

WooCommerce Related Products Pro is a comprehensive solution for displaying related products in WooCommerce stores. With its advanced algorithm, flexible display options, and robust caching system, it provides an excellent user experience while maintaining high performance. The plugin is designed to be both user-friendly for store owners and extensible for developers, making it a valuable addition to any WooCommerce-powered online store.