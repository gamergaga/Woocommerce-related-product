# Enhanced Algorithm Documentation

## Overview

The Enhanced Algorithm for WooCommerce Related Products Pro is a comprehensive upgrade based on YARPP's proven methodology. It addresses the key issues with the original algorithm and provides significantly better related product recommendations.

## Key Improvements

### 1. Advanced Text Analysis
- **Stop Words Filtering**: Removes common words (the, and, or, etc.) that don't contribute to relevance
- **Word Stemming**: Reduces words to their root form (running → run, books → book)
- **Fuzzy Matching**: Uses Levenshtein distance to find partial word matches
- **Minimum Word Length**: Filters out very short words that are often irrelevant

### 2. Multi-Factor Scoring System
The enhanced algorithm considers multiple factors when calculating relatedness:

#### Title Similarity (Weight: 3.0)
- Uses Jaccard similarity coefficient
- Applies stemming and stop word filtering
- Includes fuzzy matching for partial matches

#### Content Similarity (Weight: 2.0)
- Analyzes product description and excerpt
- Uses advanced text processing
- Considers semantic similarity

#### Category Overlap (Weight: 4.0)
- Highest weighted factor
- Calculates Jaccard similarity between category sets
- Boosts products sharing multiple categories

#### Tag Similarity (Weight: 3.0)
- Analyzes shared tags between products
- Uses set intersection analysis
- Considers tag relevance

#### Attribute Similarity (Weight: 2.0)
- Compares product attributes (color, size, material, etc.)
- Handles both taxonomy-based and custom attributes
- Calculates attribute overlap scores

#### Price Range Similarity (Weight: 1.0)
- Compares price ranges between products
- Gives higher scores to products in similar price brackets
- Uses percentage-based comparison

#### Brand Similarity (Weight: 2.5)
- Checks if products share the same brand
- Significant boost for same-brand products
- Uses product_brand taxonomy if available

### 3. Scoring Boosts
The algorithm applies additional boosts to improve relevance:

#### Temporal Boost
- Products published within 30 days of each other get a 10% score boost
- Helps surface newer, relevant content

#### Popularity Boost
- Products with higher sales counts receive up to 20% score boost
- Capped at 2x the reference product's sales

#### Category Boost
- Products sharing categories get a 20% boost
- Encourages category-based recommendations

### 4. Improved Threshold System
- **Default Threshold**: Reduced from 5.0 to 1.5 for more results
- **Dynamic Threshold**: Can be adjusted per product category
- **Configurable**: Admins can fine-tune threshold settings

### 5. Enhanced Candidate Selection
The algorithm intelligently selects candidate products for comparison:

1. **Primary**: Products from same categories (up to 100)
2. **Secondary**: Products with similar tags (up to 50)
3. **Fallback**: Recent products (up to 30)
4. **Deduplication**: Removes duplicates and reference product
5. **Limiting**: Caps at 50 candidates for performance

## Configuration Options

### Basic Settings
- **Threshold**: Minimum score for related products (default: 1.5)
- **Limit**: Maximum number of related products (default: 12)
- **Cross-Reference**: Enable bidirectional scoring
- **Temporal Boost**: Boost recently published products
- **Popularity Boost**: Boost high-sales products
- **Category Boost**: Boost same-category products

### Scoring Weights
All weights are configurable (0-10 scale):
- **Title Weight**: 3.0 (default)
- **Content Weight**: 2.0 (default)
- **Categories Weight**: 4.0 (default)
- **Tags Weight**: 3.0 (default)
- **Attributes Weight**: 2.0 (default)
- **Price Range Weight**: 1.0 (default)
- **Brand Weight**: 2.5 (default)

### Text Analysis Settings
- **Minimum Word Length**: 3 characters (default)
- **Use Stop Words**: Enabled (default)
- **Use Stemming**: Enabled (default)
- **Fuzzy Matching**: Enabled (default)

## Performance Optimizations

### 1. Enhanced Caching
- **Dedicated Cache Table**: Separate table for enhanced algorithm results
- **Score Details**: Stores detailed scoring information for analysis
- **Batch Processing**: Efficient cache building with progress tracking
- **Smart Invalidation**: Clears cache only when needed

### 2. Query Optimization
- **Candidate Limiting**: Limits comparison to 50 products per reference
- **Efficient SQL**: Optimized database queries with proper indexing
- **Memory Management**: Efficient data structures for scoring calculations

### 3. Fallback Systems
- **Multi-level Fallback**: Enhanced → Original → YARPP → Simple fallback
- **Graceful Degradation**: Continues working even if some components fail
- **Error Handling**: Comprehensive error logging and recovery

## Comparison with Original Algorithm

| Feature | Original Algorithm | Enhanced Algorithm |
|---------|-------------------|-------------------|
| **Text Analysis** | Basic SQL LIKE | Advanced NLP processing |
| **Scoring Factors** | 4 basic factors | 7 comprehensive factors |
| **Threshold** | 5.0 (too high) | 1.5 (balanced) |
| **Results Limit** | 4 products | 12 products |
| **Stop Words** | No | Yes |
| **Stemming** | No | Yes |
| **Fuzzy Matching** | No | Yes |
| **Performance** | Basic queries | Optimized caching |
| **Admin Control** | Limited | Comprehensive |

## Comparison with YARPP

| Feature | YARPP | Enhanced Algorithm |
|---------|-------|-------------------|
| **Text Analysis** | Advanced | Advanced + Fuzzy matching |
| **Scoring Factors** | Configurable | 7 factors with boosts |
| **Caching** | Batch processing | Enhanced caching with details |
| **Admin Interface** | Comprehensive | Comprehensive + Real-time stats |
| **Performance** | Proven | Optimized for WooCommerce |
| **WooCommerce Integration** | Generic | Native integration |
| **Price Consideration** | No | Yes |
| **Brand Support** | Limited | Full support |

## Implementation Details

### File Structure
```
includes/
├── class-wrp-enhanced-algorithm.php    # Core algorithm logic
├── class-wrp-enhanced-cache.php         # Enhanced caching system
└── class-wrp-core.php                   # Updated core integration

admin/
├── class-wrp-enhanced-admin.php          # Admin interface
├── assets/css/wrp-enhanced-admin.css    # Admin styles
└── assets/js/wrp-enhanced-admin.js      # Admin scripts

assets/
├── css/wrp-enhanced-admin.css           # Admin styles
└── js/wrp-enhanced-admin.js             # Admin scripts
```

### Database Schema
```sql
CREATE TABLE wp_wrp_enhanced_cache (
    reference_id bigint(20) NOT NULL,
    related_id bigint(20) NOT NULL,
    score float NOT NULL DEFAULT 0,
    score_details text NOT NULL,
    date datetime NOT NULL,
    PRIMARY KEY (reference_id, related_id),
    KEY score (score),
    KEY related_id (related_id),
    KEY date (date)
);
```

### Algorithm Flow
1. **Input**: Reference product ID and configuration
2. **Candidate Selection**: Get potential related products
3. **Data Extraction**: Gather product data (title, content, categories, etc.)
4. **Text Processing**: Tokenize, stem, and filter text
5. **Scoring**: Calculate similarity scores for each factor
6. **Boosting**: Apply temporal, popularity, and category boosts
7. **Thresholding**: Filter products below minimum score
8. **Ranking**: Sort by score and apply limit
9. **Caching**: Store results for future use
10. **Output**: Array of related product IDs with scores

## Usage Examples

### Basic Usage
```php
// Initialize enhanced algorithm
$algorithm = new WRP_Enhanced_Algorithm();

// Get related products
$config = array(
    'threshold' => 1.5,
    'limit' => 12
);
$related_products = $algorithm->get_related_products($product_id, $config);
```

### Advanced Configuration
```php
$config = array(
    'threshold' => 2.0,
    'limit' => 8,
    'weights' => array(
        'title' => 4.0,
        'content' => 3.0,
        'categories' => 5.0,
        'tags' => 2.0,
        'attributes' => 1.0,
        'price_range' => 0.5,
        'brand' => 3.0
    ),
    'text_analysis' => array(
        'min_word_length' => 4,
        'use_stop_words' => true,
        'use_stemming' => true,
        'fuzzy_matching' => true
    ),
    'scoring' => array(
        'cross_reference' => true,
        'temporal_boost' => true,
        'popularity_boost' => true,
        'category_boost' => true
    )
);
```

### Cache Management
```php
// Initialize enhanced cache
$core = new WRP_Core();
$cache = new WRP_Enhanced_Cache($core);

// Build cache for all products
$result = $cache->build_cache();

// Rebuild cache for specific product
$count = $cache->rebuild_product($product_id);

// Get cache statistics
$stats = $cache->get_stats();
```

## Testing and Validation

### Test Script
Use the provided `test-enhanced-algorithm.php` script to validate the enhanced algorithm:

```bash
php test-enhanced-algorithm.php
```

### Expected Results
- **More Related Products**: Should find 8-12 related products vs 2-3 previously
- **Higher Relevance**: Products should be more contextually relevant
- **Better Performance**: Cache should provide faster response times
- **Comprehensive Scoring**: Multiple factors should contribute to scores

## Troubleshooting

### Common Issues

1. **Few Results**: Lower threshold or check product data quality
2. **Poor Relevance**: Adjust weights or check text processing
3. **Performance Issues**: Build cache or check database indexing
4. **Admin Problems**: Verify file permissions and JavaScript loading

### Debug Mode
Enable debug logging by adding to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check debug log for:
```
tail -f wp-content/debug.log | grep "WRP Enhanced"
```

## Future Enhancements

### Planned Features
1. **Machine Learning**: Integrate ML models for better relevance
2. **Behavioral Analysis**: Consider user browsing patterns
3. **Seasonal Adjustments**: Boost seasonally relevant products
4. **Inventory Awareness**: Consider stock levels
5. **Multi-language Support**: Handle different languages properly

### Optimization Opportunities
1. **Query Caching**: Cache database queries more aggressively
2. **CDN Integration**: Store cache in CDN for better performance
3. **Background Processing**: Async cache building
4. **Memory Caching**: Use Redis/Memcached for faster access

## Conclusion

The Enhanced Algorithm represents a significant improvement over the original implementation, bringing it closer to YARPP's quality while maintaining native WooCommerce integration. With its comprehensive scoring system, advanced text analysis, and robust caching, it provides much better related product recommendations that should significantly improve user engagement and conversion rates.