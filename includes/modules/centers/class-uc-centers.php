<?php
/**
 * Centers management.
 *
 * Handles center CRUD operations.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/centers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Centers class.
 */
class UC_Centers {

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
     * Create a new center.
     *
     * @param array $data Center data.
     * @return int|false Center ID or false on failure.
     */
    public function create( $data ) {
        if ( empty( $data['name'] ) ) {
            return false;
        }

        $center_data = array(
            'name'         => sanitize_text_field( $data['name'] ),
            'type'         => isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'sub',
            'parent_id'    => isset( $data['parent_id'] ) ? absint( $data['parent_id'] ) : 0,
            'address'      => isset( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '',
            'contact_info' => isset( $data['contact_info'] ) ? sanitize_textarea_field( $data['contact_info'] ) : '',
            'status'       => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'active',
        );

        $center_id = $this->database->insert( 'centers', $center_data );

        if ( $center_id ) {
            do_action( 'u_commerce_center_created', $center_id, $center_data );
        }

        return $center_id;
    }

    /**
     * Update a center.
     *
     * @param int   $center_id Center ID.
     * @param array $data      Updated data.
     * @return bool True on success.
     */
    public function update( $center_id, $data ) {
        $center_data = array();

        if ( isset( $data['name'] ) ) {
            $center_data['name'] = sanitize_text_field( $data['name'] );
        }

        if ( isset( $data['type'] ) ) {
            $center_data['type'] = sanitize_text_field( $data['type'] );
        }

        if ( isset( $data['parent_id'] ) ) {
            $center_data['parent_id'] = absint( $data['parent_id'] );
        }

        if ( isset( $data['address'] ) ) {
            $center_data['address'] = sanitize_textarea_field( $data['address'] );
        }

        if ( isset( $data['contact_info'] ) ) {
            $center_data['contact_info'] = sanitize_textarea_field( $data['contact_info'] );
        }

        if ( isset( $data['status'] ) ) {
            $center_data['status'] = sanitize_text_field( $data['status'] );
        }

        return $this->database->update(
            'centers',
            $center_data,
            array( 'id' => $center_id )
        ) !== false;
    }

    /**
     * Delete a center.
     *
     * @param int $center_id Center ID.
     * @return bool True on success.
     */
    public function delete( $center_id ) {
        return $this->database->delete(
            'centers',
            array( 'id' => $center_id )
        ) !== false;
    }

    /**
     * Get a center by ID.
     *
     * @param int $center_id Center ID.
     * @return object|null Center object or null.
     */
    public function get( $center_id ) {
        return $this->database->get_row(
            'centers',
            array( 'id' => $center_id )
        );
    }

    /**
     * Get all centers.
     *
     * @param array $args Query arguments.
     * @return array Array of centers.
     */
    public function get_all( $args = array() ) {
        $defaults = array(
            'where'    => array( 'status' => 'active' ),
            'orderby'  => 'name',
            'order'    => 'ASC',
            'limit'    => null,
            'offset'   => null,
        );

        $args = wp_parse_args( $args, $defaults );

        return $this->database->get_results( 'centers', $args );
    }

    /**
     * Get main center.
     *
     * @return object|null Main center object or null.
     */
    public function get_main_center() {
        return $this->database->get_row(
            'centers',
            array(
                'type'   => 'main',
                'status' => 'active',
            )
        );
    }

    /**
     * Get sub centers.
     *
     * @param int $parent_id Parent center ID.
     * @return array Array of sub centers.
     */
    public function get_sub_centers( $parent_id = 0 ) {
        $where = array(
            'type'   => 'sub',
            'status' => 'active',
        );

        if ( $parent_id ) {
            $where['parent_id'] = $parent_id;
        }

        return $this->database->get_results(
            'centers',
            array( 'where' => $where )
        );
    }

    /**
     * Get center count.
     *
     * @param array $where WHERE conditions.
     * @return int Center count.
     */
    public function get_count( $where = array() ) {
        if ( empty( $where ) ) {
            $where = array( 'status' => 'active' );
        }

        return $this->database->get_count( 'centers', $where );
    }
}
