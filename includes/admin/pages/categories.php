<?php
/**
 * Categories admin page.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_manage_categories' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

// Handle actions
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$category_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$categories_handler = new UC_Categories();

// Handle delete
if ( $action === 'delete' && $category_id ) {
    check_admin_referer( 'delete_category_' . $category_id );

    $deleted = $categories_handler->delete( $category_id );

    if ( $deleted ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Category deleted successfully.', 'u-commerce' ) . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete category.', 'u-commerce' ) . '</p></div>';
    }
}

// Get all categories with product counts
$categories = $categories_handler->get_all_with_counts();

// Organize into parent-child structure
$category_tree = array();
$category_map = array();

foreach ( $categories as $category ) {
    $category_map[$category->id] = $category;
    $category->children = array();
}

foreach ( $categories as $category ) {
    if ( $category->parent_id && isset( $category_map[$category->parent_id] ) ) {
        $category_map[$category->parent_id]->children[] = $category;
    } else {
        $category_tree[] = $category;
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Product Categories', 'u-commerce' ); ?></h1>
    <button type="button" class="page-title-action" id="uc-add-category-btn">
        <?php esc_html_e( 'Add New Category', 'u-commerce' ); ?>
    </button>
    <hr class="wp-header-end">

    <div class="uc-card">
        <?php if ( $categories ) : ?>
            <!-- Search Controls -->
            <div class="uc-table-controls" style="padding: 15px 15px 0; border-bottom: 1px solid #ddd;">
                <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                    <input type="text" class="uc-table-search" placeholder="<?php esc_attr_e( 'Search categories by name, slug, or description...', 'u-commerce' ); ?>" style="flex: 1; min-width: 250px; padding: 6px 10px;">
                    <button type="button" class="button uc-clear-search"><?php esc_html_e( 'Clear', 'u-commerce' ); ?></button>
                </div>
                <div class="uc-results-count" style="color: #666; font-size: 13px; margin-bottom: 10px;"></div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Slug', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'u-commerce' ); ?></th>
                        <th style="width: 100px; text-align: center;"><?php esc_html_e( 'Products', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Parent', 'u-commerce' ); ?></th>
                        <th style="width: 150px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Function to display category rows recursively
                    function display_category_row( $category, $level = 0, $category_map ) {
                        $indent = str_repeat( '—', $level );
                        ?>
                        <tr data-category-id="<?php echo esc_attr( $category->id ); ?>">
                            <td><?php echo esc_html( $category->id ); ?></td>
                            <td>
                                <strong><?php echo esc_html( $indent . ' ' . $category->name ); ?></strong>
                                <?php if ( ! empty( $category->is_default ) ) : ?>
                                    <span class="uc-badge uc-badge-primary" style="margin-left: 5px;">
                                        <?php esc_html_e( 'Default', 'u-commerce' ); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $category->slug ); ?></td>
                            <td><?php echo esc_html( wp_trim_words( $category->description, 10 ) ); ?></td>
                            <td style="text-align: center;">
                                <strong><?php echo esc_html( isset( $category->product_count ) ? $category->product_count : 0 ); ?></strong>
                            </td>
                            <td>
                                <?php
                                if ( $category->parent_id && isset( $category_map[$category->parent_id] ) ) {
                                    echo esc_html( $category_map[$category->parent_id]->name );
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <button type="button" class="button button-small uc-edit-category-btn"
                                        data-id="<?php echo esc_attr( $category->id ); ?>">
                                    <?php esc_html_e( 'Edit', 'u-commerce' ); ?>
                                </button>
                                <?php if ( empty( $category->is_default ) ) : ?>
                                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-categories&action=delete&id=' . $category->id ), 'delete_category_' . $category->id ) ); ?>"
                                       class="button button-small uc-delete-btn"
                                       style="color: #b32d2e;"
                                       onclick="return confirm('<?php esc_attr_e( 'Are you sure? Products in this category will be moved to the default category.', 'u-commerce' ); ?>');">
                                        <?php esc_html_e( 'Delete', 'u-commerce' ); ?>
                                    </a>
                                <?php else : ?>
                                    <span style="color: #999;"><?php esc_html_e( 'Cannot delete default', 'u-commerce' ); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                        // Display children
                        if ( ! empty( $category->children ) ) {
                            foreach ( $category->children as $child ) {
                                display_category_row( $child, $level + 1, $category_map );
                            }
                        }
                    }

                    // Display all categories
                    foreach ( $category_tree as $category ) {
                        display_category_row( $category, 0, $category_map );
                    }
                    ?>
                </tbody>
            </table>
        <?php else : ?>
            <div style="padding: 40px; text-align: center;">
                <p style="font-size: 16px; color: #666;">
                    <?php esc_html_e( 'No categories found. Click "Add New Category" to create your first category.', 'u-commerce' ); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="uc-category-modal" class="uc-modal">
    <div class="uc-modal-content">
        <div class="uc-modal-header">
            <h2 id="uc-category-modal-title"><?php esc_html_e( 'Add Category', 'u-commerce' ); ?></h2>
            <button type="button" class="uc-modal-close">&times;</button>
        </div>
        <form id="uc-category-form">
            <input type="hidden" id="category_id" name="category_id" value="">
            <?php wp_nonce_field( 'uc_category_save', 'uc_category_nonce' ); ?>

            <div class="uc-form-group">
                <label for="category_name" class="uc-form-label">
                    <?php esc_html_e( 'Category Name', 'u-commerce' ); ?> <span style="color: red;">*</span>
                </label>
                <input type="text" id="category_name" name="name" class="uc-form-input" required>
            </div>

            <div class="uc-form-group">
                <label for="category_slug" class="uc-form-label">
                    <?php esc_html_e( 'Slug', 'u-commerce' ); ?>
                </label>
                <input type="text" id="category_slug" name="slug" class="uc-form-input">
                <p class="uc-form-help"><?php esc_html_e( 'Leave empty to auto-generate from name', 'u-commerce' ); ?></p>
            </div>

            <div class="uc-form-group">
                <label for="category_parent" class="uc-form-label">
                    <?php esc_html_e( 'Parent Category', 'u-commerce' ); ?>
                </label>
                <select id="category_parent" name="parent_id" class="uc-form-input">
                    <option value="0"><?php esc_html_e( 'None (Top Level)', 'u-commerce' ); ?></option>
                    <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->id ); ?>">
                            <?php echo esc_html( $cat->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="uc-form-group">
                <label for="category_description" class="uc-form-label">
                    <?php esc_html_e( 'Description', 'u-commerce' ); ?>
                </label>
                <textarea id="category_description" name="description" class="uc-form-input" rows="3"></textarea>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <button type="button" class="button uc-modal-close" style="margin-right: 10px;">
                    <?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
                </button>
                <button type="submit" class="button button-primary" id="uc-category-submit-btn">
                    <?php esc_html_e( 'Save Category', 'u-commerce' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Open add category modal
    $('#uc-add-category-btn').on('click', function() {
        $('#uc-category-modal-title').text('<?php esc_html_e( 'Add Category', 'u-commerce' ); ?>');
        $('#uc-category-form')[0].reset();
        $('#category_id').val('');
        $('#uc-category-modal').addClass('active');
    });

    // Open edit category modal
    $('.uc-edit-category-btn').on('click', function() {
        var categoryId = $(this).data('id');

        // Show loading
        $('#uc-category-modal-title').text('<?php esc_html_e( 'Loading...', 'u-commerce' ); ?>');
        $('#uc-category-modal').addClass('active');

        // Fetch category data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'uc_get_category',
                category_id: categoryId,
                nonce: '<?php echo wp_create_nonce( 'uc_admin_nonce' ); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var cat = response.data;
                    $('#uc-category-modal-title').text('<?php esc_html_e( 'Edit Category', 'u-commerce' ); ?>');
                    $('#category_id').val(cat.id);
                    $('#category_name').val(cat.name);
                    $('#category_slug').val(cat.slug);
                    $('#category_parent').val(cat.parent_id || 0);
                    $('#category_description').val(cat.description);
                } else {
                    alert(response.data.message || 'Failed to load category');
                    $('#uc-category-modal').removeClass('active');
                }
            },
            error: function() {
                alert('Error loading category');
                $('#uc-category-modal').removeClass('active');
            }
        });
    });

    // Close modal
    $('.uc-modal-close').on('click', function() {
        $('#uc-category-modal').removeClass('active');
    });

    // Close on backdrop click
    $('#uc-category-modal').on('click', function(e) {
        if ($(e.target).is('#uc-category-modal')) {
            $(this).removeClass('active');
        }
    });

    // Handle form submission
    $('#uc-category-form').on('submit', function(e) {
        e.preventDefault();

        var $submitBtn = $('#uc-category-submit-btn');
        var originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('<?php esc_html_e( 'Saving...', 'u-commerce' ); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $(this).serialize() + '&action=uc_save_category',
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Category saved successfully');
                    window.location.reload();
                } else {
                    alert(response.data.message || 'Failed to save category');
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('Error saving category');
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>

<style>
.uc-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 100000;
    align-items: center;
    justify-content: center;
}

.uc-modal.active {
    display: flex;
}

.uc-modal-content {
    background: #fff;
    border-radius: 4px;
    padding: 30px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.uc-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ccd0d4;
}

.uc-modal-header h2 {
    margin: 0;
}

.uc-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #646970;
    line-height: 1;
    padding: 0;
}

.uc-modal-close:hover {
    color: #000;
}
</style>
