<?php
/**
 * Variables admin page.
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

$variables_handler = new UC_Variables();
$categories_handler = new UC_Categories();

// Handle form submission
if ( isset( $_POST['uc_variable_submit'] ) ) {
	check_admin_referer( 'uc_variable_save', 'uc_variable_nonce' );

	$variable_id = isset( $_POST['variable_id'] ) ? absint( $_POST['variable_id'] ) : 0;

	$data = array(
		'name'   => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
		'type'   => isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'text',
		'values' => isset( $_POST['values'] ) ? sanitize_textarea_field( $_POST['values'] ) : '',
	);

	if ( $variable_id ) {
		// Update
		$result = $variables_handler->update( $variable_id, $data );
		$message = __( 'Variable updated successfully.', 'u-commerce' );

		// Update category links
		$existing_categories = $variables_handler->get_linked_categories( $variable_id );
		$new_categories = isset( $_POST['linked_categories'] ) ? array_map( 'absint', $_POST['linked_categories'] ) : array();

		// Remove unlinked categories
		foreach ( $existing_categories as $cat_id ) {
			if ( ! in_array( $cat_id, $new_categories ) ) {
				$variables_handler->unlink_from_category( $cat_id, $variable_id );
			}
		}

		// Add new category links
		foreach ( $new_categories as $cat_id ) {
			if ( ! in_array( $cat_id, $existing_categories ) ) {
				$variables_handler->link_to_category( $cat_id, $variable_id );
			}
		}
	} else {
		// Create
		$result = $variables_handler->create( $data );
		$variable_id = $result;
		$message = __( 'Variable created successfully.', 'u-commerce' );

		// Link to categories
		if ( $result && isset( $_POST['linked_categories'] ) ) {
			foreach ( $_POST['linked_categories'] as $cat_id ) {
				$variables_handler->link_to_category( absint( $cat_id ), $variable_id );
			}
		}
	}

	if ( $result ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to save variable.', 'u-commerce' ) . '</p></div>';
	}
}

// Handle delete
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$variable_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

if ( $action === 'delete' && $variable_id ) {
	check_admin_referer( 'delete_variable_' . $variable_id );

	$deleted = $variables_handler->delete( $variable_id );

	if ( $deleted ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Variable deleted successfully.', 'u-commerce' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete variable.', 'u-commerce' ) . '</p></div>';
	}

	$action = ''; // Reset action
}

// Get all variables and categories
$variables = $variables_handler->get_all();
$all_categories = $categories_handler->get_all();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Product Variables', 'u-commerce' ); ?></h1>
	<button type="button" class="page-title-action" id="add-new-variable">
		<?php esc_html_e( 'Add New Variable', 'u-commerce' ); ?>
	</button>
	<hr class="wp-header-end">

	<p class="description">
		<?php esc_html_e( 'Variables are product attributes like Size, Color, Material, etc. You can link variables to categories so they automatically appear when creating products in those categories.', 'u-commerce' ); ?>
	</p>

	<div class="uc-card" style="margin-top: 20px;">
		<?php if ( $variables ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 50px;"><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Type', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Values', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Linked Categories', 'u-commerce' ); ?></th>
						<th style="width: 180px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $variables as $variable ) : ?>
						<?php
						$linked_categories = $variables_handler->get_linked_categories( $variable->id );
						$category_names = array();
						foreach ( $linked_categories as $cat_id ) {
							$cat = $categories_handler->get( $cat_id );
							if ( $cat ) {
								$category_names[] = $cat->name;
							}
						}
						?>
						<tr>
							<td><?php echo esc_html( $variable->id ); ?></td>
							<td><strong><?php echo esc_html( $variable->name ); ?></strong></td>
							<td>
								<span class="uc-badge uc-badge-info">
									<?php echo esc_html( ucfirst( $variable->type ) ); ?>
								</span>
							</td>
							<td>
								<code style="font-size: 11px;">
									<?php echo esc_html( strlen( $variable->values ) > 50 ? substr( $variable->values, 0, 50 ) . '...' : $variable->values ); ?>
								</code>
							</td>
							<td>
								<?php if ( $category_names ) : ?>
									<?php foreach ( $category_names as $cat_name ) : ?>
										<span class="uc-badge" style="background: #f0f0f0; color: #333; margin-right: 5px;">
											<?php echo esc_html( $cat_name ); ?>
										</span>
									<?php endforeach; ?>
								<?php else : ?>
									<span style="color: #999;">—</span>
								<?php endif; ?>
							</td>
							<td>
								<button type="button"
										class="button button-small edit-variable-btn"
										data-id="<?php echo esc_attr( $variable->id ); ?>"
										data-name="<?php echo esc_attr( $variable->name ); ?>"
										data-type="<?php echo esc_attr( $variable->type ); ?>"
										data-values="<?php echo esc_attr( $variable->values ); ?>"
										data-categories="<?php echo esc_attr( implode( ',', $linked_categories ) ); ?>">
									<?php esc_html_e( 'Edit', 'u-commerce' ); ?>
								</button>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-variables&action=delete&id=' . $variable->id ), 'delete_variable_' . $variable->id ) ); ?>"
								   class="button button-small uc-delete-btn"
								   style="color: #b32d2e;">
									<?php esc_html_e( 'Delete', 'u-commerce' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div style="padding: 40px; text-align: center;">
				<p style="font-size: 16px; color: #666;">
					<?php esc_html_e( 'No variables found. Click "Add New Variable" to create your first variable.', 'u-commerce' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- Variable Modal -->
<div id="variable-modal" class="uc-modal" style="display: none;">
	<div class="uc-modal-content">
		<div class="uc-modal-header">
			<h2 id="modal-title"><?php esc_html_e( 'Add New Variable', 'u-commerce' ); ?></h2>
			<button type="button" class="uc-modal-close">&times;</button>
		</div>
		<div class="uc-modal-body">
			<form method="post" action="" id="variable-form">
				<?php wp_nonce_field( 'uc_variable_save', 'uc_variable_nonce' ); ?>
				<input type="hidden" name="variable_id" id="variable_id" value="">

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="variable_name">
								<?php esc_html_e( 'Variable Name', 'u-commerce' ); ?>
								<span style="color: red;">*</span>
							</label>
						</th>
						<td>
							<input type="text"
								   name="name"
								   id="variable_name"
								   class="regular-text"
								   required>
							<p class="description"><?php esc_html_e( 'e.g., Size, Color, Material, Storage', 'u-commerce' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="variable_type">
								<?php esc_html_e( 'Type', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<select name="type" id="variable_type" class="regular-text">
								<option value="text"><?php esc_html_e( 'Text', 'u-commerce' ); ?></option>
								<option value="color"><?php esc_html_e( 'Color', 'u-commerce' ); ?></option>
								<option value="size"><?php esc_html_e( 'Size', 'u-commerce' ); ?></option>
								<option value="number"><?php esc_html_e( 'Number', 'u-commerce' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Type helps with display and validation', 'u-commerce' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="variable_values">
								<?php esc_html_e( 'Values', 'u-commerce' ); ?>
								<span style="color: red;">*</span>
							</label>
						</th>
						<td>
							<textarea name="values"
									  id="variable_values"
									  class="large-text"
									  rows="4"
									  required></textarea>
							<p class="description"><?php esc_html_e( 'Enter values separated by commas. e.g., Small, Medium, Large, XL', 'u-commerce' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="linked_categories">
								<?php esc_html_e( 'Link to Categories', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
								<?php if ( $all_categories ) : ?>
									<?php foreach ( $all_categories as $category ) : ?>
										<label style="display: block; margin-bottom: 8px;">
											<input type="checkbox"
												   name="linked_categories[]"
												   class="category-checkbox"
												   value="<?php echo esc_attr( $category->id ); ?>">
											<?php echo esc_html( $category->name ); ?>
										</label>
									<?php endforeach; ?>
								<?php else : ?>
									<p style="color: #999;"><?php esc_html_e( 'No categories available. Create categories first.', 'u-commerce' ); ?></p>
								<?php endif; ?>
							</div>
							<p class="description"><?php esc_html_e( 'Select categories that should have this variable', 'u-commerce' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit"
						   name="uc_variable_submit"
						   class="button button-primary button-large"
						   value="<?php esc_attr_e( 'Save Variable', 'u-commerce' ); ?>">
					<button type="button" class="button button-large uc-modal-close" style="margin-left: 10px;">
						<?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
					</button>
				</p>
			</form>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Open modal for add
	$('#add-new-variable').on('click', function() {
		$('#modal-title').text('<?php esc_html_e( 'Add New Variable', 'u-commerce' ); ?>');
		$('#variable-form')[0].reset();
		$('#variable_id').val('');
		$('.category-checkbox').prop('checked', false);
		$('#variable-modal').fadeIn();
	});

	// Open modal for edit
	$('.edit-variable-btn').on('click', function() {
		$('#modal-title').text('<?php esc_html_e( 'Edit Variable', 'u-commerce' ); ?>');
		$('#variable_id').val($(this).data('id'));
		$('#variable_name').val($(this).data('name'));
		$('#variable_type').val($(this).data('type'));
		$('#variable_values').val($(this).data('values'));

		// Check linked categories
		$('.category-checkbox').prop('checked', false);
		var linkedCategories = $(this).data('categories').toString().split(',');
		linkedCategories.forEach(function(catId) {
			if (catId) {
				$('.category-checkbox[value="' + catId + '"]').prop('checked', true);
			}
		});

		$('#variable-modal').fadeIn();
	});

	// Close modal
	$('.uc-modal-close').on('click', function() {
		$('#variable-modal').fadeOut();
	});

	// Close modal on outside click
	$(window).on('click', function(e) {
		if ($(e.target).hasClass('uc-modal')) {
			$('#variable-modal').fadeOut();
		}
	});

	// Confirm delete
	$('.uc-delete-btn').on('click', function(e) {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to delete this variable? This will also remove it from all linked categories and products.', 'u-commerce' ); ?>')) {
			e.preventDefault();
			return false;
		}
	});
});
</script>
