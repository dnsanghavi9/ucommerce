<?php
/**
 * Product categories management.
 *
 * Handles category CRUD operations.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/products
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Categories class.
 */
class UC_Categories {

    /**
     * Database handler.
     *
     * @var UC_Database
     */
    private $database;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->database = new UC_Database();
    }

    /**
     * Create a new category.
     *
     * @param array $data Category data.
     * @return int|false Category ID or false on failure.
     */
    public function create( $data ) {
        if ( empty( $data['name'] ) ) {
            return false;
        }

        // Check if name already exists
        if ( $this->name_exists( $data['name'] ) ) {
            return false;
        }

        // Generate slug
        $slug = isset( $data['slug'] ) ? sanitize_title( $data['slug'] ) : sanitize_title( $data['name'] );

        // Ensure unique slug
        $slug = $this->get_unique_slug( $slug );

        $category_data = array(
            'name'        => sanitize_text_field( $data['name'] ),
            'slug'        => $slug,
            'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
            'parent_id'   => isset( $data['parent_id'] ) ? absint( $data['parent_id'] ) : 0,
            'is_default'  => isset( $data['is_default'] ) ? absint( $data['is_default'] ) : 0,
        );

        return $this->database->insert( 'categories', $category_data );
    }

    /**
     * Update a category.
     *
     * @param int   $category_id Category ID.
     * @param array $data        Updated data.
     * @return bool True on success.
     */
    public function update( $category_id, $data ) {
        $category_data = array();

        if ( isset( $data['name'] ) ) {
            // Check if name already exists (excluding current category)
            if ( $this->name_exists( $data['name'], $category_id ) ) {
                return false;
            }
            $category_data['name'] = sanitize_text_field( $data['name'] );
        }

        if ( isset( $data['slug'] ) ) {
            $slug = sanitize_title( $data['slug'] );
            $category_data['slug'] = $this->get_unique_slug( $slug, $category_id );
        }

        if ( isset( $data['description'] ) ) {
            $category_data['description'] = sanitize_textarea_field( $data['description'] );
        }

        if ( isset( $data['parent_id'] ) ) {
            $category_data['parent_id'] = absint( $data['parent_id'] );
        }

        if ( isset( $data['is_default'] ) ) {
            $category_data['is_default'] = absint( $data['is_default'] );
        }

        return $this->database->update(
            'categories',
            $category_data,
            array( 'id' => $category_id )
        ) !== false;
    }

    /**
     * Delete a category.
     *
     * @param int $category_id Category ID.
     * @return bool True on success.
     */
    public function delete( $category_id ) {
        global $wpdb;

        // Check if this is the default category
        $category = $this->get( $category_id );
        if ( $category && $category->is_default ) {
            return false; // Cannot delete default category
        }

        // Get default category
        $default_category = $this->get_default_category();
        if ( ! $default_category ) {
            return false; // Must have a default category before deleting others
        }

        // Reassign products to default category
        $products_table = $this->database->get_table( 'products' );
        $wpdb->update(
            $products_table,
            array( 'category_id' => $default_category->id ),
            array( 'category_id' => $category_id ),
            array( '%d' ),
            array( '%d' )
        );

        return $this->database->delete(
            'categories',
            array( 'id' => $category_id )
        ) !== false;
    }

    /**
     * Get a category by ID.
     *
     * @param int $category_id Category ID.
     * @return object|null Category object or null.
     */
    public function get( $category_id ) {
        return $this->database->get_row(
            'categories',
            array( 'id' => $category_id )
        );
    }

    /**
     * Get category by slug.
     *
     * @param string $slug Category slug.
     * @return object|null Category object or null.
     */
    public function get_by_slug( $slug ) {
        return $this->database->get_row(
            'categories',
            array( 'slug' => $slug )
        );
    }

    /**
     * Get all categories.
     *
     * @param array $args Query arguments.
     * @return array Array of categories.
     */
    public function get_all( $args = array() ) {
        $defaults = array(
            'where'    => array(),
            'orderby'  => 'created_at',
            'order'    => 'DESC',
            'limit'    => null,
            'offset'   => null,
        );

        $args = wp_parse_args( $args, $defaults );

        return $this->database->get_results( 'categories', $args );
    }

    /**
     * Get child categories.
     *
     * @param int $parent_id Parent category ID.
     * @return array Array of child categories.
     */
    public function get_children( $parent_id ) {
        return $this->get_all(
            array(
                'where' => array( 'parent_id' => $parent_id ),
            )
        );
    }

    /**
     * Get unique slug.
     *
     * @param string $slug       Desired slug.
     * @param int    $exclude_id Category ID to exclude.
     * @return string Unique slug.
     */
    private function get_unique_slug( $slug, $exclude_id = 0 ) {
        global $wpdb;

        $table_name = $this->database->get_table( 'categories' );
        $original_slug = $slug;
        $counter = 1;

        while ( true ) {
            if ( $exclude_id ) {
                $exists = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM {$table_name} WHERE slug = %s AND id != %d",
                        $slug,
                        $exclude_id
                    )
                );
            } else {
                $exists = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM {$table_name} WHERE slug = %s",
                        $slug
                    )
                );
            }

            if ( ! $exists ) {
                break;
            }

            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if category name exists.
     *
     * @param string $name       Category name.
     * @param int    $exclude_id Category ID to exclude.
     * @return bool True if exists.
     */
    public function name_exists( $name, $exclude_id = 0 ) {
        global $wpdb;

        $table_name = $this->database->get_table( 'categories' );

        if ( $exclude_id ) {
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} WHERE name = %s AND id != %d",
                    $name,
                    $exclude_id
                )
            );
        } else {
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} WHERE name = %s",
                    $name
                )
            );
        }

        return $exists > 0;
    }

    /**
     * Get default category.
     *
     * @return object|null Default category or null.
     */
    public function get_default_category() {
        return $this->database->get_row(
            'categories',
            array( 'is_default' => 1 )
        );
    }

    /**
     * Set default category.
     *
     * @param int $category_id Category ID.
     * @return bool True on success.
     */
    public function set_default_category( $category_id ) {
        global $wpdb;
        $table_name = $this->database->get_table( 'categories' );

        // First, remove default flag from all categories
        $wpdb->update(
            $table_name,
            array( 'is_default' => 0 ),
            array(),
            array( '%d' ),
            array()
        );

        // Then set the new default
        return $this->database->update(
            'categories',
            array( 'is_default' => 1 ),
            array( 'id' => $category_id )
        ) !== false;
    }

    /**
     * Get product count for a category.
     *
     * @param int $category_id Category ID.
     * @return int Product count.
     */
    public function get_product_count( $category_id ) {
        global $wpdb;

        $products_table = $this->database->get_table( 'products' );

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$products_table} WHERE category_id = %d",
                $category_id
            )
        );
    }

    /**
     * Get all categories with product counts.
     *
     * @param array $args Query arguments.
     * @return array Array of categories with product_count property.
     */
    public function get_all_with_counts( $args = array() ) {
        $categories = $this->get_all( $args );

        if ( $categories ) {
            foreach ( $categories as $category ) {
                $category->product_count = $this->get_product_count( $category->id );
            }
        }

        return $categories;
    }
}
