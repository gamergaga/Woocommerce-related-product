=== WooCommerce Related Products Pro ===
Contributors: yourname
Tags: woocommerce, related products, ecommerce, products, cross-sell, upsell, add to cart, buy now, carousel, grid, list
Requires at least: WordPress 5.0
Tested up to: WordPress 6.7
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display related WooCommerce products with add to cart and buy now buttons using an advanced algorithm optimized specifically for products.

== Description ==

WooCommerce Related Products Pro is a powerful plugin that displays related products on your WooCommerce store with an intelligent algorithm that analyzes product titles, descriptions, categories, and tags to find the most relevant matches. 

### Key Features

*   **Advanced Algorithm**: Uses a sophisticated algorithm optimized specifically for WooCommerce products, considering titles, descriptions, categories, and tags with configurable weights
*   **Multiple Display Templates**: Choose from grid, list, or carousel layouts to match your store's design
*   **Add to Cart & Buy Now Buttons**: Increase conversions with prominent add to cart and buy now buttons directly on related products
*   **Smart Caching**: Built-in caching system ensures fast performance even with large product catalogs
*   **Fully Customizable**: Extensive options to control how related products are selected and displayed
*   **Responsive Design**: All templates are fully responsive and work perfectly on all devices
*   **SEO Friendly**: Helps reduce bounce rate and increase page views by showing relevant products
*   **Admin Panel**: Comprehensive admin interface with cache management and detailed statistics
*   **Shortcode Support**: Display related products anywhere using shortcodes
*   **Widget Support**: Add related products to any widget area
*   **WooCommerce Integration**: Seamlessly integrates with WooCommerce features like variations, stock management, and pricing

### Perfect For

*   **E-commerce Stores**: Show customers products they're likely to be interested in
*   **Product Catalogs**: Help customers discover similar items
*   **Cross-selling**: Increase average order value by showing complementary products
*   **Upselling**: Display higher-end alternatives to products customers are viewing
*   **Reducing Bounce Rate**: Keep customers engaged with relevant product suggestions

== Installation ==

### Automatic Installation

1.  In your WordPress admin, go to **Plugins > Add New**
2.  Search for "WooCommerce Related Products Pro"
3.  Click **Install Now**
4.  Activate the plugin
5.  Configure the settings in **WooCommerce > Related Products**

### Manual Installation

1.  Download the plugin ZIP file
2.  In your WordPress admin, go to **Plugins > Add New > Upload Plugin**
3.  Choose the ZIP file and click **Install Now**
4.  Activate the plugin
5.  Configure the settings in **WooCommerce > Related Products**

### Requirements

*   WordPress 5.0 or higher
*   PHP 7.2 or higher
*   WooCommerce 3.0 or higher
*   MySQL 5.6 or higher (for full-text search support)

== Frequently Asked Questions ==

### How does the algorithm work?

The algorithm analyzes multiple factors to determine related products:

*   **Content Analysis**: Examines product titles, descriptions, and short descriptions using full-text search
*   **Taxonomy Matching**: Considers shared categories and tags with configurable importance
*   **Scoring System**: Each factor is weighted and combined to create a relevance score
*   **Threshold Filtering**: Only products above a minimum score threshold are displayed

This ensures that only truly relevant products are shown to customers.

### Can I customize how related products are selected?

Yes! The plugin offers extensive customization options:

*   **Content Weights**: Adjust the importance of titles, descriptions, and short descriptions
*   **Taxonomy Weights**: Control how much categories and tags affect relatedness
*   **Taxonomy Requirements**: Require products to share a minimum number of categories or tags
*   **Time Filters**: Limit results to recently published products
*   **Exclusion Options**: Exclude specific categories or tags
*   **Stock Management**: Choose whether to include out-of-stock products

### What display templates are available?

The plugin includes three professionally designed templates:

*   **Grid Layout**: Clean grid layout perfect for product catalogs
*   **List Layout**: Vertical list layout with detailed product information
*   **Carousel Layout**: Horizontal scrolling carousel for space-efficient display

Each template is fully responsive and can be customized with your own CSS.

### How do I add related products to different areas of my site?

You can display related products in several ways:

*   **Automatic Display**: Automatically show related products on product pages
*   **Shortcode**: Use `[related_products]` or `[wrp_related]` anywhere in your content
*   **Widget**: Add the Related Products widget to any sidebar or widget area
*   **PHP Function**: Use `wrp_display_related_products()` in your theme files

### Does the plugin work with product variations?

Yes! The plugin works with all WooCommerce product types, including:

*   Simple products
*   Variable products
*   Grouped products
*   External products
*   Virtual and downloadable products

### Is the plugin performance optimized?

Absolutely! The plugin includes several performance optimizations:

*   **Smart Caching**: Related products are cached to avoid repeated database queries
*   **Database Indexes**: Full-text indexes on product content for fast searching
*   **Efficient Queries**: Optimized SQL queries for minimal database load
*   **Cache Management**: Built-in tools to monitor and manage cache performance

### Can I customize the appearance?

Yes, the plugin is highly customizable:

*   **Template System**: Override templates in your theme for complete control
*   **CSS Classes**: Comprehensive CSS classes for easy styling
*   **Display Options**: Control which elements are shown (price, rating, excerpt, etc.)
*   **Image Sizes**: Choose from any registered WordPress image size
*   **Responsive Design**: All templates work perfectly on all devices

== Screenshots ==

1.  Grid layout showing related products with add to cart and buy now buttons
2.  List layout with detailed product information and action buttons
3.  Carousel layout for space-efficient display
4.  Admin settings page with comprehensive configuration options
5.  Algorithm settings for fine-tuning related product selection
6.  Cache management page with statistics and management tools

== Configuration ==

### General Settings

*   **Match Threshold**: Minimum score required for products to be considered related
*   **Number of Products**: Maximum number of related products to display
*   **Excerpt Length**: Number of words to show in product excerpts
*   **Auto Display**: Automatically display related products on product pages
*   **Display Position**: Choose where to show related products on product pages
*   **Enable Caching**: Enable caching for better performance
*   **Cache Timeout**: Set how long cached results should be stored

### Display Settings

*   **Show Price**: Display product prices
*   **Show Rating**: Display product ratings and reviews
*   **Show Add to Cart**: Display add to cart buttons
*   **Show Buy Now**: Display buy now buttons
*   **Template**: Choose between grid, list, or carousel layouts
*   **Columns**: Number of columns for grid layout
*   **Image Size**: Choose the product image size
*   **Show Excerpt**: Display product excerpts

### Algorithm Settings

*   **Content Weights**: Adjust importance of titles, descriptions, and short descriptions
*   **Taxonomy Weights**: Control importance of categories and tags
*   **Taxonomy Requirements**: Set minimum shared terms required
*   **Exclude Terms**: Comma-separated list of term IDs to exclude
*   **Recent Products Only**: Only show products published recently
*   **Recent Period**: Time period for recent products filter
*   **Include Out of Stock**: Choose whether to include out-of-stock products

== Shortcode Usage ==

### Basic Usage

```
[related_products]
```

### With Custom Parameters

```
[related_products limit="6" template="grid" columns="3" show_price="true" show_rating="true"]
```

### All Available Parameters

*   `id`: Specific product ID (defaults to current product)
*   `limit`: Number of products to show (default: 4)
*   `columns`: Number of columns for grid layout (default: 4)
*   `template`: Template style - grid, list, or carousel (default: grid)
*   `show_price`: Show product prices (default: true)
*   `show_rating`: Show product ratings (default: true)
*   `show_add_to_cart`: Show add to cart buttons (default: true)
*   `show_buy_now`: Show buy now buttons (default: true)
*   `show_excerpt`: Show product excerpts (default: false)
*   `excerpt_length`: Excerpt length in words (default: 10)
*   `image_size`: Product image size (default: woocommerce_thumbnail)
*   `threshold`: Minimum match score (default: 1)
*   `exclude`: Comma-separated term IDs to exclude
*   `recent_only`: Only show recent products (default: false)
*   `recent_period`: Time period for recent products (default: 30 day)
*   `include_out_of_stock`: Include out-of-stock products (default: false)
*   `weight_title`: Weight for title matching (default: 2)
*   `weight_description`: Weight for description matching (default: 1)
*   `weight_short_description`: Weight for short description matching (default: 1)
*   `weight_categories`: Weight for category matching (default: 3)
*   `weight_tags`: Weight for tag matching (default: 2)
*   `require_categories`: Minimum shared categories required (default: 1)
*   `require_tags`: Minimum shared tags required (default: 0)

### Examples

**Show 6 products in a 3-column grid:**
```
[related_products limit="6" columns="3"]
```

**Show only products with high category relevance:**
```
[related_products weight_categories="5" require_categories="2"]
```

**Show recent products only:**
```
[related_products recent_only="true" recent_period="7 day"]
```

**Customize display options:**
```
[related_products show_price="false" show_rating="false" show_excerpt="true" excerpt_length="15"]
```

== Template Customization ==

You can override the default templates by copying them to your theme:

1.  Create a folder in your theme called `wrp-templates`
2.  Copy template files from `woocommerce-related-products/templates/` to your theme's `wrp-templates/` folder
3.  Modify the templates as needed

Available templates:
*   `wrp-template-grid.php` - Grid layout
*   `wrp-template-list.php` - List layout
*   `wrp-template-carousel.php` - Carousel layout

== Hooks & Filters ==

The plugin provides numerous hooks and filters for developers:

### General Filters

*   `wrp_related_products_heading`: Filter the heading text
*   `wrp_show_heading`: Control whether to show the heading
*   `wrp_get_option`: Filter plugin options
*   `wrp_product_keywords`: Filter extracted keywords

### Display Filters

*   `wrp_product_image_html`: Filter product image HTML
*   `wrp_product_price_html`: Filter product price HTML
*   `wrp_product_rating_html`: Filter product rating HTML
*   `wrp_product_excerpt_html`: Filter product excerpt HTML
*   `wrp_add_to_cart_button_html`: Filter add to cart button HTML
*   `wrp_buy_now_button_html`: Filter buy now button HTML
*   `wrp_sale_badge_html`: Filter sale badge HTML

### Algorithm Filters

*   `wrp_related_score`: Filter the relatedness score between products
*   `wrp_keywords_overused_words`: Filter stop words list
*   `wrp_extract_keywords`: Filter keyword extraction process

### Action Hooks

*   `wrp_before_related_products`: Before related products display
*   `wrp_after_related_products`: After related products display
*   `wrp_cache_cleared`: After cache is cleared
*   `wrp_cache_rebuilt`: After cache is rebuilt

== Performance ==

The plugin is designed for optimal performance:

*   **Database Optimization**: Uses full-text indexes for fast content searching
*   **Smart Caching**: Results are cached to minimize database queries
*   **Efficient Queries**: Optimized SQL queries for minimal database load
*   **Cache Management**: Built-in tools to monitor and optimize cache performance
*   **Lazy Loading**: Related products are only computed when needed

For large catalogs (10,000+ products), we recommend:

1.  Enable caching with a reasonable timeout (1-4 hours)
2.  Use the "Rebuild Cache" feature during off-peak hours
3.  Monitor cache performance regularly
4.  Consider increasing server resources if needed

== Compatibility ==

### WordPress Version

*   Minimum: WordPress 5.0
*   Tested up to: WordPress 6.7
*   Recommended: Latest stable version

### PHP Version

*   Minimum: PHP 7.2
*   Recommended: PHP 8.0 or higher

### WooCommerce Version

*   Minimum: WooCommerce 3.0
*   Tested up to: WooCommerce 8.0
*   Recommended: Latest stable version

### Database

*   MySQL 5.6+ or MariaDB 10.1+ recommended for full-text search
*   InnoDB or MyISAM storage engine supported

### Browser Compatibility

*   Chrome 60+
*   Firefox 55+
*   Safari 11+
*   Edge 79+
*   Mobile browsers (iOS Safari, Chrome Mobile, etc.)

== Support ==

### Documentation

Comprehensive documentation is available at:
[https://yourwebsite.com/documentation/](https://yourwebsite.com/documentation/)

### Support Forum

Get help from the community:
[https://wordpress.org/support/plugin/woocommerce-related-products-pro/](https://wordpress.org/support/plugin/woocommerce-related-products-pro/)

### Premium Support

For priority support, please visit:
[https://yourwebsite.com/support](https://yourwebsite.com/support)

### Reporting Bugs

Found a bug? Please report it:
[https://github.com/yourusername/woocommerce-related-products-pro/issues](https://github.com/yourusername/woocommerce-related-products-pro/issues)

### Feature Requests

Have an idea for a new feature? Let us know:
[https://github.com/yourusername/woocommerce-related-products-pro/issues](https://github.com/yourusername/woocommerce-related-products-pro/issues)

== Changelog ==

### 1.0.0
*   Initial release
*   Advanced algorithm for finding related products
*   Grid, list, and carousel display templates
*   Add to cart and buy now button functionality
*   Comprehensive admin settings panel
*   Smart caching system
*   Shortcode and widget support
*   Full WooCommerce integration
*   Responsive design
*   Performance optimized

== Upgrade Notice ==

### 1.0.0
This is the initial release. No upgrade path is available.

== License ==

This plugin is released under the GPLv2 license. You are free to use, modify, and distribute this plugin under the terms of this license.

For more information, please see:
[https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

== Credits ==

*   **Developer**: Your Name
*   **Design**: Your Design Team
*   **Algorithm**: Advanced content analysis and relationship scoring system
*   **Icons**: Font Awesome and custom SVG icons
*   **Testing**: WordPress testing community

== Disclaimer ==

This plugin is provided "as is" without warranty of any kind, express or implied. In no event shall the authors be liable for any claim, damages or other liability, whether in an action of contract, tort or otherwise, arising from, out of or in connection with the software or the use or other dealings in the software.