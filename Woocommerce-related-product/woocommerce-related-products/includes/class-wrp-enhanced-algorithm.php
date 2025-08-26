<?php
/**
 * Enhanced Algorithm for WooCommerce Related Products Pro
 * Based on YARPP's proven methodology with advanced scoring and text analysis
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Enhanced_Algorithm {

    /**
     * Default algorithm configuration
     */
    private $default_config = array(
        'threshold' => 1.5, // Lower threshold for more results
        'limit' => 12, // Show more products like YARPP
        'weights' => array(
            'title' => 3.0,
            'content' => 2.0,
            'categories' => 4.0,
            'tags' => 3.0,
            'attributes' => 2.0,
            'price_range' => 1.0,
            'brand' => 2.5
        ),
        'text_analysis' => array(
            'min_word_length' => 3,
            'use_stop_words' => true,
            'use_stemming' => true,
            'fuzzy_matching' => true
        ),
        'scoring' => array(
            'cross_reference' => true,
            'temporal_boost' => true,
            'popularity_boost' => true,
            'category_boost' => true
        ),
        'require_tax' => array(
            'product_cat' => 1
        )
    );

    /**
     * Stop words list
     */
    private $stop_words = array(
        'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
        'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had',
        'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must',
        'can', 'this', 'that', 'these', 'those', 'a', 'an', 'as', 'if', 'when', 'where',
        'how', 'why', 'what', 'which', 'who', 'whom', 'whose', 'your', 'you', 'i', 'me',
        'my', 'mine', 'our', 'ours', 'their', 'them', 'they', 'he', 'him', 'his', 'she',
        'her', 'hers', 'it', 'its', 'we', 'us', 'some', 'any', 'no', 'not', 'yes', 'no'
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize algorithm
    }

    /**
     * Get related products using enhanced algorithm
     */
    public function get_related_products( $reference_id, $config = array() ) {
        global $wpdb;

        // Merge configuration
        $config = wp_parse_args( $config, $this->default_config );
        
        error_log("WRP Enhanced Algorithm: Finding related products for product ID: $reference_id");

        // Get reference product
        $reference_product = wc_get_product( $reference_id );
        if ( ! $reference_product ) {
            error_log("WRP Enhanced Algorithm: Reference product not found for ID: $reference_id");
            return array();
        }

        // Get reference product data
        $reference_data = $this->get_product_data( $reference_id );
        
        // Get all candidate products
        $candidate_products = $this->get_candidate_products( $reference_id, $config );
        
        if ( empty( $candidate_products ) ) {
            error_log("WRP Enhanced Algorithm: No candidate products found");
            return array();
        }

        // Calculate scores for each candidate
        $scores = array();
        foreach ( $candidate_products as $candidate_id ) {
            $candidate_data = $this->get_product_data( $candidate_id );
            $score = $this->calculate_similarity_score( $reference_data, $candidate_data, $config );
            
            if ( $score >= $config['threshold'] ) {
                $scores[ $candidate_id ] = $score;
            }
        }

        // Sort by score (highest first)
        arsort( $scores );
        
        // Apply limit
        $related_products = array_slice( $scores, 0, $config['limit'], true );
        
        error_log("WRP Enhanced Algorithm: Found " . count($related_products) . " related products for product ID: $reference_id");
        
        return $related_products;
    }

    /**
     * Get candidate products for comparison
     */
    private function get_candidate_products( $reference_id, $config ) {
        global $wpdb;
        
        $candidate_ids = array();
        
        // Get products from same categories first
        $categories = get_the_terms( $reference_id, 'product_cat' );
        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            $category_ids = wp_list_pluck( $categories, 'term_id' );
            
            $category_products = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
                    LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE p.post_type = 'product' AND p.post_status = 'publish'
                    AND tt.term_id IN (" . implode( ',', array_fill( 0, count( $category_ids ), '%d' ) ) . ")
                    AND p.ID != %d
                    LIMIT 100",
                    array_merge( $category_ids, array( $reference_id ) )
                )
            );
            
            $candidate_ids = array_merge( $candidate_ids, $category_products );
        }
        
        // Get products with similar tags
        $tags = get_the_terms( $reference_id, 'product_tag' );
        if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) {
            $tag_ids = wp_list_pluck( $tags, 'term_id' );
            
            $tag_products = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
                    LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE p.post_type = 'product' AND p.post_status = 'publish'
                    AND tt.term_id IN (" . implode( ',', array_fill( 0, count( $tag_ids ), '%d' ) ) . ")
                    AND p.ID != %d
                    LIMIT 50",
                    array_merge( $tag_ids, array( $reference_id ) )
                )
            );
            
            $candidate_ids = array_merge( $candidate_ids, $tag_products );
        }
        
        // If we still need more candidates, get recent products
        if ( count( $candidate_ids ) < 20 ) {
            $recent_products = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT p.ID FROM {$wpdb->posts} p
                    WHERE p.post_type = 'product' AND p.post_status = 'publish'
                    AND p.ID != %d
                    ORDER BY p.post_date DESC
                    LIMIT 30",
                    $reference_id
                )
            );
            
            $candidate_ids = array_merge( $candidate_ids, $recent_products );
        }
        
        // Remove duplicates and reference product
        $candidate_ids = array_unique( $candidate_ids );
        $candidate_ids = array_diff( $candidate_ids, array( $reference_id ) );
        
        // Limit to reasonable number for performance
        return array_slice( $candidate_ids, 0, 50 );
    }

    /**
     * Get comprehensive product data for analysis
     */
    private function get_product_data( $product_id ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return array();
        }

        $post = get_post( $product_id );
        
        $data = array(
            'id' => $product_id,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'price' => $product->get_price(),
            'categories' => array(),
            'tags' => array(),
            'attributes' => array(),
            'brand' => '',
            'date' => $post->post_date,
            'sales_count' => $this->get_product_sales_count( $product_id )
        );

        // Get categories
        $categories = get_the_terms( $product_id, 'product_cat' );
        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            $data['categories'] = wp_list_pluck( $categories, 'name' );
        }

        // Get tags
        $tags = get_the_terms( $product_id, 'product_tag' );
        if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) {
            $data['tags'] = wp_list_pluck( $tags, 'name' );
        }

        // Get attributes
        $attributes = $product->get_attributes();
        foreach ( $attributes as $attribute ) {
            if ( $attribute->is_taxonomy() ) {
                $terms = get_the_terms( $product_id, $attribute->get_name() );
                if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                    $data['attributes'][ $attribute->get_name() ] = wp_list_pluck( $terms, 'name' );
                }
            } else {
                $values = $product->get_attribute( $attribute->get_name() );
                if ( $values ) {
                    $data['attributes'][ $attribute->get_name() ] = array_map( 'trim', explode( ',', $values ) );
                }
            }
        }

        // Get brand (if available)
        if ( taxonomy_exists( 'product_brand' ) ) {
            $brand = get_the_terms( $product_id, 'product_brand' );
            if ( ! is_wp_error( $brand ) && ! empty( $brand ) ) {
                $data['brand'] = $brand[0]->name;
            }
        }

        return $data;
    }

    /**
     * Calculate comprehensive similarity score
     */
    private function calculate_similarity_score( $reference_data, $candidate_data, $config ) {
        $score = 0;
        $weights = $config['weights'];

        // Title similarity
        if ( isset( $weights['title'] ) && $weights['title'] > 0 ) {
            $title_score = $this->calculate_text_similarity( 
                $reference_data['title'], 
                $candidate_data['title'], 
                $config['text_analysis'] 
            );
            $score += $title_score * $weights['title'];
        }

        // Content similarity
        if ( isset( $weights['content'] ) && $weights['content'] > 0 ) {
            $content_text = $reference_data['content'] . ' ' . $reference_data['excerpt'];
            $candidate_content_text = $candidate_data['content'] . ' ' . $candidate_data['excerpt'];
            $content_score = $this->calculate_text_similarity( 
                $content_text, 
                $candidate_content_text, 
                $config['text_analysis'] 
            );
            $score += $content_score * $weights['content'];
        }

        // Category similarity
        if ( isset( $weights['categories'] ) && $weights['categories'] > 0 ) {
            $category_score = $this->calculate_taxonomy_similarity(
                $reference_data['categories'],
                $candidate_data['categories']
            );
            $score += $category_score * $weights['categories'];
        }

        // Tag similarity
        if ( isset( $weights['tags'] ) && $weights['tags'] > 0 ) {
            $tag_score = $this->calculate_taxonomy_similarity(
                $reference_data['tags'],
                $candidate_data['tags']
            );
            $score += $tag_score * $weights['tags'];
        }

        // Attribute similarity
        if ( isset( $weights['attributes'] ) && $weights['attributes'] > 0 ) {
            $attribute_score = $this->calculate_attribute_similarity(
                $reference_data['attributes'],
                $candidate_data['attributes']
            );
            $score += $attribute_score * $weights['attributes'];
        }

        // Brand similarity
        if ( isset( $weights['brand'] ) && $weights['brand'] > 0 ) {
            if ( ! empty( $reference_data['brand'] ) && ! empty( $candidate_data['brand'] ) ) {
                $brand_score = ( $reference_data['brand'] === $candidate_data['brand'] ) ? 1.0 : 0.0;
                $score += $brand_score * $weights['brand'];
            }
        }

        // Price range similarity
        if ( isset( $weights['price_range'] ) && $weights['price_range'] > 0 ) {
            $price_score = $this->calculate_price_similarity(
                $reference_data['price'],
                $candidate_data['price']
            );
            $score += $price_score * $weights['price_range'];
        }

        // Apply scoring boosts
        $score = $this->apply_scoring_boosts( $score, $reference_data, $candidate_data, $config['scoring'] );

        return $score;
    }

    /**
     * Calculate advanced text similarity
     */
    private function calculate_text_similarity( $text1, $text2, $text_config ) {
        // Tokenize and clean text
        $tokens1 = $this->tokenize_text( $text1, $text_config );
        $tokens2 = $this->tokenize_text( $text2, $text_config );

        if ( empty( $tokens1 ) || empty( $tokens2 ) ) {
            return 0;
        }

        // Calculate intersection and union
        $intersection = array_intersect( $tokens1, $tokens2 );
        $union = array_unique( array_merge( $tokens1, $tokens2 ) );

        // Jaccard similarity
        $jaccard_score = count( $intersection ) / count( $union );

        // Apply fuzzy matching if enabled
        if ( $text_config['fuzzy_matching'] ) {
            $fuzzy_score = $this->calculate_fuzzy_similarity( $tokens1, $tokens2 );
            $jaccard_score = ( $jaccard_score + $fuzzy_score ) / 2;
        }

        return $jaccard_score;
    }

    /**
     * Tokenize text with advanced processing
     */
    private function tokenize_text( $text, $text_config ) {
        // Remove HTML tags
        $text = strip_tags( $text );
        
        // Convert to lowercase
        $text = strtolower( $text );
        
        // Remove special characters but keep spaces
        $text = preg_replace( '/[^a-z0-9\s]/', ' ', $text );
        
        // Split into words
        $words = preg_split( '/\s+/', $text );
        
        // Filter by minimum length
        if ( $text_config['min_word_length'] > 0 ) {
            $words = array_filter( $words, function( $word ) use ( $text_config ) {
                return strlen( $word ) >= $text_config['min_word_length'];
            } );
        }
        
        // Remove stop words
        if ( $text_config['use_stop_words'] ) {
            $words = array_diff( $words, $this->stop_words );
        }
        
        // Apply stemming (simplified version)
        if ( $text_config['use_stemming'] ) {
            $words = array_map( array( $this, 'stem_word' ), $words );
        }
        
        return array_values( array_filter( $words ) );
    }

    /**
     * Simple word stemming (English only)
     */
    private function stem_word( $word ) {
        // Basic stemming rules
        $word = preg_replace( '/ing$/', '', $word );
        $word = preg_replace( '/ed$/', '', $word );
        $word = preg_replace( '/s$/', '', $word );
        $word = preg_replace( '/es$/', '', $word );
        $word = preg_replace( '/er$/', '', $word );
        $word = preg_replace( '/est$/', '', $word );
        $word = preg_replace( '/ly$/', '', $word );
        $word = preg_replace( '/ment$/', '', $word );
        $word = preg_replace( '/ness$/', '', $word );
        $word = preg_replace( '/tion$/', '', $word );
        
        return $word;
    }

    /**
     * Calculate fuzzy similarity for partial matches
     */
    private function calculate_fuzzy_similarity( $tokens1, $tokens2 ) {
        $total_score = 0;
        $comparisons = 0;

        foreach ( $tokens1 as $token1 ) {
            foreach ( $tokens2 as $token2 ) {
                $similarity = $this->levenshtein_similarity( $token1, $token2 );
                if ( $similarity > 0.7 ) { // 70% similarity threshold
                    $total_score += $similarity;
                    $comparisons++;
                }
            }
        }

        return $comparisons > 0 ? $total_score / $comparisons : 0;
    }

    /**
     * Calculate Levenshtein similarity
     */
    private function levenshtein_similarity( $str1, $str2 ) {
        $len1 = strlen( $str1 );
        $len2 = strlen( $str2 );
        
        if ( $len1 === 0 || $len2 === 0 ) {
            return 0;
        }
        
        $distance = levenshtein( $str1, $str2 );
        $max_len = max( $len1, $len2 );
        
        return 1 - ( $distance / $max_len );
    }

    /**
     * Calculate taxonomy similarity
     */
    private function calculate_taxonomy_similarity( $terms1, $terms2 ) {
        if ( empty( $terms1 ) || empty( $terms2 ) ) {
            return 0;
        }

        $intersection = array_intersect( $terms1, $terms2 );
        $union = array_unique( array_merge( $terms1, $terms2 ) );

        return count( $intersection ) / count( $union );
    }

    /**
     * Calculate attribute similarity
     */
    private function calculate_attribute_similarity( $attributes1, $attributes2 ) {
        $total_score = 0;
        $total_attributes = 0;

        // Compare common attributes
        $common_attributes = array_intersect_key( $attributes1, $attributes2 );
        
        foreach ( $common_attributes as $attribute => $values1 ) {
            $values2 = $attributes2[ $attribute ];
            $similarity = $this->calculate_taxonomy_similarity( $values1, $values2 );
            $total_score += $similarity;
            $total_attributes++;
        }

        return $total_attributes > 0 ? $total_score / $total_attributes : 0;
    }

    /**
     * Calculate price similarity
     */
    private function calculate_price_similarity( $price1, $price2 ) {
        if ( empty( $price1 ) || empty( $price2 ) ) {
            return 0;
        }

        // Calculate price difference ratio
        $diff = abs( $price1 - $price2 );
        $avg_price = ( $price1 + $price2 ) / 2;
        
        if ( $avg_price === 0 ) {
            return 0;
        }

        $ratio = $diff / $avg_price;
        
        // Return score based on how close prices are
        if ( $ratio <= 0.2 ) { // Within 20%
            return 1.0;
        } elseif ( $ratio <= 0.5 ) { // Within 50%
            return 0.5;
        } else {
            return 0.1;
        }
    }

    /**
     * Apply scoring boosts
     */
    private function apply_scoring_boosts( $score, $reference_data, $candidate_data, $scoring_config ) {
        // Temporal boost (newer products get slight preference)
        if ( $scoring_config['temporal_boost'] ) {
            $ref_date = strtotime( $reference_data['date'] );
            $cand_date = strtotime( $candidate_data['date'] );
            $date_diff = abs( $ref_date - $cand_date );
            
            // Boost products published within 30 days of reference
            if ( $date_diff <= 30 * DAY_IN_SECONDS ) {
                $score *= 1.1;
            }
        }

        // Popularity boost (products with more sales get preference)
        if ( $scoring_config['popularity_boost'] ) {
            $ref_sales = $reference_data['sales_count'];
            $cand_sales = $candidate_data['sales_count'];
            
            if ( $ref_sales > 0 && $cand_sales > 0 ) {
                $sales_ratio = min( $cand_sales / $ref_sales, 2.0 ); // Cap at 2x
                $score *= ( 1 + ( $sales_ratio * 0.1 ) ); // Max 20% boost
            }
        }

        // Category boost (products in same category get preference)
        if ( $scoring_config['category_boost'] ) {
            $common_categories = array_intersect( $reference_data['categories'], $candidate_data['categories'] );
            if ( ! empty( $common_categories ) ) {
                $score *= 1.2; // 20% boost for shared categories
            }
        }

        return $score;
    }

    /**
     * Get product sales count
     */
    private function get_product_sales_count( $product_id ) {
        global $wpdb;
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(meta_value) FROM {$wpdb->postmeta} 
                WHERE meta_key = '_product_sales_count' AND post_id = %d",
                $product_id
            )
        );
        
        return $count ? intval( $count ) : 0;
    }
}
?>