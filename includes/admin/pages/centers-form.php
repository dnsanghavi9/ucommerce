<?php
/**
 * Centers add/edit form.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_edit = isset( $center ) && $center;
$page_title = $is_edit ? __( 'Edit Center', 'u-commerce' ) : __( 'Add New Center', 'u-commerce' );

// Get all centers for parent dropdown
$all_centers = $centers_handler->get_all( array( 'where' => array() ) );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-centers' ) ); ?>" class="page-title-action">
        <?php esc_html_e( '← Back to Centers', 'u-commerce' ); ?>
    </a>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field( 'uc_center_save', 'uc_center_nonce' ); ?>

        <div class="uc-card">
            <h2><?php esc_html_e( 'Center Information', 'u-commerce' ); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="center_name">
                            <?php esc_html_e( 'Center Name', 'u-commerce' ); ?>
                            <span style="color: red;">*</span>
                        </label>
                    </th>
                    <td>
                        <input type="text"
                               name="name"
                               id="center_name"
                               class="regular-text"
                               value="<?php echo $is_edit ? esc_attr( $center->name ) : ''; ?>"
                               required>
                        <p class="description"><?php esc_html_e( 'Enter the name of the center or warehouse', 'u-commerce' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="center_type">
                            <?php esc_html_e( 'Center Type', 'u-commerce' ); ?>
                            <span style="color: red;">*</span>
                        </label>
                    </th>
                    <td>
                        <select name="type" id="center_type" class="regular-text" required>
                            <option value="main" <?php echo ( $is_edit && $center->type === 'main' ) ? 'selected' : ''; ?>>
                                <?php esc_html_e( 'Main Center', 'u-commerce' ); ?>
                            </option>
                            <option value="sub" <?php echo ( ! $is_edit || $center->type === 'sub' ) ? 'selected' : ''; ?>>
                                <?php esc_html_e( 'Sub Center', 'u-commerce' ); ?>
                            </option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Main centers can have sub-centers under them', 'u-commerce' ); ?></p>
                    </td>
                </tr>

                <tr id="parent_center_row" style="<?php echo ( ! $is_edit || $center->type === 'main' ) ? 'display: none;' : ''; ?>">
                    <th scope="row">
                        <label for="center_parent">
                            <?php esc_html_e( 'Parent Center', 'u-commerce' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="parent_id" id="center_parent" class="regular-text">
                            <option value="0"><?php esc_html_e( 'None', 'u-commerce' ); ?></option>
                            <?php foreach ( $all_centers as $c ) : ?>
                                <?php if ( $c->type === 'main' && ( ! $is_edit || $c->id != $center->id ) ) : ?>
                                    <option value="<?php echo esc_attr( $c->id ); ?>"
                                            <?php echo ( $is_edit && $center->parent_id == $c->id ) ? 'selected' : ''; ?>>
                                        <?php echo esc_html( $c->name ); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Select the parent center (only for sub-centers)', 'u-commerce' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="center_address">
                            <?php esc_html_e( 'Address', 'u-commerce' ); ?>
                        </label>
                    </th>
                    <td>
                        <textarea name="address"
                                  id="center_address"
                                  class="large-text"
                                  rows="3"><?php echo $is_edit ? esc_textarea( $center->address ) : ''; ?></textarea>
                        <p class="description"><?php esc_html_e( 'Physical address of the center', 'u-commerce' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="center_contact">
                            <?php esc_html_e( 'Contact Information', 'u-commerce' ); ?>
                        </label>
                    </th>
                    <td>
                        <textarea name="contact_info"
                                  id="center_contact"
                                  class="large-text"
                                  rows="3"><?php echo $is_edit ? esc_textarea( $center->contact_info ) : ''; ?></textarea>
                        <p class="description"><?php esc_html_e( 'Phone, email, manager name, etc.', 'u-commerce' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="center_status">
                            <?php esc_html_e( 'Status', 'u-commerce' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="status" id="center_status" class="regular-text">
                            <option value="active" <?php echo ( ! $is_edit || $center->status === 'active' ) ? 'selected' : ''; ?>>
                                <?php esc_html_e( 'Active', 'u-commerce' ); ?>
                            </option>
                            <option value="inactive" <?php echo ( $is_edit && $center->status === 'inactive' ) ? 'selected' : ''; ?>>
                                <?php esc_html_e( 'Inactive', 'u-commerce' ); ?>
                            </option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Inactive centers will not appear in dropdowns', 'u-commerce' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <input type="submit"
                   name="uc_center_submit"
                   class="button button-primary button-large"
                   value="<?php echo $is_edit ? esc_attr__( 'Update Center', 'u-commerce' ) : esc_attr__( 'Add Center', 'u-commerce' ); ?>">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-centers' ) ); ?>"
               class="button button-large"
               style="margin-left: 10px;">
                <?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
            </a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Show/hide parent center dropdown based on type
    $('#center_type').on('change', function() {
        if ($(this).val() === 'sub') {
            $('#parent_center_row').show();
        } else {
            $('#parent_center_row').hide();
            $('#center_parent').val('0');
        }
    });
});
</script>
