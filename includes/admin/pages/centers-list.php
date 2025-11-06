<?php
/**
 * Centers list view.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get all centers
$all_centers = $centers_handler->get_all( array( 'where' => array() ) );

// Organize into hierarchy
$main_centers = array();
$sub_centers = array();

foreach ( $all_centers as $center ) {
    if ( $center->type === 'main' ) {
        $center->sub_centers = array();
        $main_centers[] = $center;
    } else {
        $sub_centers[] = $center;
    }
}

// Assign sub-centers to their parents
foreach ( $sub_centers as $sub ) {
    foreach ( $main_centers as $main ) {
        if ( $sub->parent_id == $main->id ) {
            $main->sub_centers[] = $sub;
            break;
        }
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Centers', 'u-commerce' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-centers&action=new' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New Center', 'u-commerce' ); ?>
    </a>
    <hr class="wp-header-end">

    <div class="uc-card">
        <?php if ( $all_centers ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Type', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Parent', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Address', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Contact', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
                        <th style="width: 150px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display main centers first
                    foreach ( $main_centers as $center ) :
                    ?>
                        <tr>
                            <td><?php echo esc_html( $center->id ); ?></td>
                            <td><strong><?php echo esc_html( $center->name ); ?></strong></td>
                            <td>
                                <span class="uc-badge uc-badge-info">
                                    <?php esc_html_e( 'Main', 'u-commerce' ); ?>
                                </span>
                            </td>
                            <td>—</td>
                            <td><?php echo esc_html( wp_trim_words( $center->address, 8 ) ); ?></td>
                            <td><?php echo esc_html( wp_trim_words( $center->contact_info, 5 ) ); ?></td>
                            <td>
                                <span class="uc-badge <?php echo $center->status === 'active' ? 'uc-badge-success' : 'uc-badge-warning'; ?>">
                                    <?php echo esc_html( ucfirst( $center->status ) ); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-centers&action=edit&id=' . $center->id ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'Edit', 'u-commerce' ); ?>
                                </a>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-centers&action=delete&id=' . $center->id ), 'delete_center_' . $center->id ) ); ?>"
                                   class="button button-small uc-delete-btn"
                                   style="color: #b32d2e;">
                                    <?php esc_html_e( 'Delete', 'u-commerce' ); ?>
                                </a>
                            </td>
                        </tr>

                        <?php
                        // Display sub-centers under their parent
                        if ( ! empty( $center->sub_centers ) ) :
                            foreach ( $center->sub_centers as $sub ) :
                        ?>
                            <tr style="background: #f9f9f9;">
                                <td><?php echo esc_html( $sub->id ); ?></td>
                                <td>
                                    <span style="margin-left: 20px;">↳ <?php echo esc_html( $sub->name ); ?></span>
                                </td>
                                <td>
                                    <span class="uc-badge" style="background: #ddd; color: #333;">
                                        <?php esc_html_e( 'Sub', 'u-commerce' ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( $center->name ); ?></td>
                                <td><?php echo esc_html( wp_trim_words( $sub->address, 8 ) ); ?></td>
                                <td><?php echo esc_html( wp_trim_words( $sub->contact_info, 5 ) ); ?></td>
                                <td>
                                    <span class="uc-badge <?php echo $sub->status === 'active' ? 'uc-badge-success' : 'uc-badge-warning'; ?>">
                                        <?php echo esc_html( ucfirst( $sub->status ) ); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-centers&action=edit&id=' . $sub->id ) ); ?>" class="button button-small">
                                        <?php esc_html_e( 'Edit', 'u-commerce' ); ?>
                                    </a>
                                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-centers&action=delete&id=' . $sub->id ), 'delete_center_' . $sub->id ) ); ?>"
                                       class="button button-small uc-delete-btn"
                                       style="color: #b32d2e;">
                                        <?php esc_html_e( 'Delete', 'u-commerce' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    <?php endforeach; ?>

                    <?php
                    // Display orphan sub-centers (no parent or parent doesn't exist)
                    foreach ( $sub_centers as $sub ) :
                        $has_parent = false;
                        foreach ( $main_centers as $main ) {
                            if ( $sub->parent_id == $main->id ) {
                                $has_parent = true;
                                break;
                            }
                        }

                        if ( ! $has_parent ) :
                    ?>
                        <tr style="background: #fff8dc;">
                            <td><?php echo esc_html( $sub->id ); ?></td>
                            <td><?php echo esc_html( $sub->name ); ?></td>
                            <td>
                                <span class="uc-badge" style="background: #ddd; color: #333;">
                                    <?php esc_html_e( 'Sub', 'u-commerce' ); ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: #d63638;">
                                    <?php esc_html_e( 'No Parent', 'u-commerce' ); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html( wp_trim_words( $sub->address, 8 ) ); ?></td>
                            <td><?php echo esc_html( wp_trim_words( $sub->contact_info, 5 ) ); ?></td>
                            <td>
                                <span class="uc-badge <?php echo $sub->status === 'active' ? 'uc-badge-success' : 'uc-badge-warning'; ?>">
                                    <?php echo esc_html( ucfirst( $sub->status ) ); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-centers&action=edit&id=' . $sub->id ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'Edit', 'u-commerce' ); ?>
                                </a>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-centers&action=delete&id=' . $sub->id ), 'delete_center_' . $sub->id ) ); ?>"
                                   class="button button-small uc-delete-btn"
                                   style="color: #b32d2e;">
                                    <?php esc_html_e( 'Delete', 'u-commerce' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </tbody>
            </table>
        <?php else : ?>
            <div style="padding: 40px; text-align: center;">
                <p style="font-size: 16px; color: #666;">
                    <?php esc_html_e( 'No centers found. Click "Add New Center" to create your first center.', 'u-commerce' ); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
