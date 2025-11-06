<?php
/**
 * Dashboard admin page.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_view_dashboard' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

// Get dashboard stats
$reports = new UC_Reports();
$user_center_id = UC_Capabilities::get_user_center_id();
$stats = $reports->get_dashboard_stats( $user_center_id );

// Check if setup is needed
$setup_needed = get_option( 'u_commerce_newly_installed', false );
?>

<div class="wrap">
    <h1><?php esc_html_e( 'U-Commerce Dashboard', 'u-commerce' ); ?></h1>

    <?php if ( $setup_needed ) : ?>
        <div class="notice notice-info">
            <p>
                <?php esc_html_e( 'Welcome to U-Commerce! Click the button below to complete the initial setup.', 'u-commerce' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-setup-wizard' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Start Setup Wizard', 'u-commerce' ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>

    <div class="uc-dashboard-stats">
        <div class="uc-stats-row">
            <!-- Today's Sales -->
            <div class="uc-stat-card">
                <div class="uc-stat-icon dashicons dashicons-money-alt"></div>
                <div class="uc-stat-content">
                    <h3><?php esc_html_e( "Today's Sales", 'u-commerce' ); ?></h3>
                    <p class="uc-stat-value">
                        <?php
                        echo esc_html(
                            UC_Utilities::format_price(
                                $stats['today_sales']['total_amount'] ?? 0
                            )
                        );
                        ?>
                    </p>
                    <p class="uc-stat-meta">
                        <?php
                        printf(
                            /* translators: %d: number of bills */
                            esc_html__( '%d bills', 'u-commerce' ),
                            absint( $stats['today_sales']['total_bills'] ?? 0 )
                        );
                        ?>
                    </p>
                </div>
            </div>

            <!-- This Month's Sales -->
            <div class="uc-stat-card">
                <div class="uc-stat-icon dashicons dashicons-chart-line"></div>
                <div class="uc-stat-content">
                    <h3><?php esc_html_e( "This Month's Sales", 'u-commerce' ); ?></h3>
                    <p class="uc-stat-value">
                        <?php
                        echo esc_html(
                            UC_Utilities::format_price(
                                $stats['month_sales']['total_amount'] ?? 0
                            )
                        );
                        ?>
                    </p>
                    <p class="uc-stat-meta">
                        <?php
                        printf(
                            /* translators: %d: number of bills */
                            esc_html__( '%d bills', 'u-commerce' ),
                            absint( $stats['month_sales']['total_bills'] ?? 0 )
                        );
                        ?>
                    </p>
                </div>
            </div>

            <!-- Total Products -->
            <div class="uc-stat-card">
                <div class="uc-stat-icon dashicons dashicons-products"></div>
                <div class="uc-stat-content">
                    <h3><?php esc_html_e( 'Total Products', 'u-commerce' ); ?></h3>
                    <p class="uc-stat-value">
                        <?php echo esc_html( number_format( $stats['product_count'] ?? 0 ) ); ?>
                    </p>
                    <p class="uc-stat-meta">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products' ) ); ?>">
                            <?php esc_html_e( 'View all', 'u-commerce' ); ?>
                        </a>
                    </p>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="uc-stat-card uc-stat-warning">
                <div class="uc-stat-icon dashicons dashicons-warning"></div>
                <div class="uc-stat-content">
                    <h3><?php esc_html_e( 'Low Stock Items', 'u-commerce' ); ?></h3>
                    <p class="uc-stat-value">
                        <?php echo esc_html( number_format( $stats['low_stock_count'] ?? 0 ) ); ?>
                    </p>
                    <p class="uc-stat-meta">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-inventory' ) ); ?>">
                            <?php esc_html_e( 'View inventory', 'u-commerce' ); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="uc-dashboard-content">
        <div class="uc-dashboard-col-left">
            <div class="uc-dashboard-widget">
                <h2><?php esc_html_e( 'Quick Actions', 'u-commerce' ); ?></h2>
                <div class="uc-quick-actions">
                    <?php if ( current_user_can( 'u_commerce_create_sales' ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills&action=new' ) ); ?>" class="button button-primary button-large">
                            <?php esc_html_e( 'New Sale', 'u-commerce' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( current_user_can( 'u_commerce_add_purchase_bills' ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-purchase-bills&action=new' ) ); ?>" class="button button-large">
                            <?php esc_html_e( 'New Purchase', 'u-commerce' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( current_user_can( 'u_commerce_manage_products' ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=new' ) ); ?>" class="button button-large">
                            <?php esc_html_e( 'Add Product', 'u-commerce' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( current_user_can( 'u_commerce_view_reports' ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-reports' ) ); ?>" class="button button-large">
                            <?php esc_html_e( 'View Reports', 'u-commerce' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="uc-dashboard-widget">
                <h2><?php esc_html_e( 'Recent Sales', 'u-commerce' ); ?></h2>
                <?php
                $sales = new UC_Sales_Bills();
                $recent_sales = $sales->get_all( array( 'limit' => 5 ) );

                if ( $recent_sales ) :
                    ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Bill #', 'u-commerce' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'u-commerce' ); ?></th>
                                <th><?php esc_html_e( 'Amount', 'u-commerce' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_sales as $sale ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $sale->bill_number ); ?></td>
                                    <td><?php echo esc_html( UC_Utilities::format_date( $sale->created_at ) ); ?></td>
                                    <td><?php echo esc_html( UC_Utilities::format_price( $sale->total_amount ) ); ?></td>
                                    <td><?php echo esc_html( ucfirst( $sale->payment_status ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php esc_html_e( 'No recent sales found.', 'u-commerce' ); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="uc-dashboard-col-right">
            <div class="uc-dashboard-widget">
                <h2><?php esc_html_e( 'System Info', 'u-commerce' ); ?></h2>
                <ul class="uc-system-info">
                    <li>
                        <strong><?php esc_html_e( 'Version:', 'u-commerce' ); ?></strong>
                        <?php echo esc_html( UC_VERSION ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Database Version:', 'u-commerce' ); ?></strong>
                        <?php echo esc_html( get_option( 'u_commerce_db_version', '1.0.0' ) ); ?>
                    </li>
                    <?php
                    $user_center = UC_Capabilities::get_user_center_id();
                    if ( $user_center ) :
                        $centers = new UC_Centers();
                        $center = $centers->get( $user_center );
                        ?>
                        <li>
                            <strong><?php esc_html_e( 'Your Center:', 'u-commerce' ); ?></strong>
                            <?php echo esc_html( $center ? $center->name : __( 'Not assigned', 'u-commerce' ) ); ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="uc-dashboard-widget">
                <h2><?php esc_html_e( 'Need Help?', 'u-commerce' ); ?></h2>
                <p><?php esc_html_e( 'Get started with these resources:', 'u-commerce' ); ?></p>
                <ul>
                    <li><a href="#" target="_blank"><?php esc_html_e( 'Documentation', 'u-commerce' ); ?></a></li>
                    <li><a href="#" target="_blank"><?php esc_html_e( 'Video Tutorials', 'u-commerce' ); ?></a></li>
                    <li><a href="#" target="_blank"><?php esc_html_e( 'Support Forum', 'u-commerce' ); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.uc-dashboard-stats {
    margin: 20px 0;
}

.uc-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.uc-stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.uc-stat-card.uc-stat-warning {
    border-left: 4px solid #f0b849;
}

.uc-stat-icon {
    font-size: 40px;
    width: 50px;
    height: 50px;
    color: #2271b1;
}

.uc-stat-content h3 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #666;
}

.uc-stat-value {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
}

.uc-stat-meta {
    margin: 5px 0 0;
    font-size: 12px;
    color: #666;
}

.uc-dashboard-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.uc-dashboard-widget {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.uc-dashboard-widget h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #ccd0d4;
}

.uc-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.uc-system-info {
    list-style: none;
    margin: 0;
    padding: 0;
}

.uc-system-info li {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f1;
}

.uc-system-info li:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .uc-dashboard-content {
        grid-template-columns: 1fr;
    }

    .uc-stats-row {
        grid-template-columns: 1fr;
    }
}
</style>
