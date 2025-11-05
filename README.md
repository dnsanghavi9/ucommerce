# U-Commerce WordPress Plugin

A comprehensive multi-center retail business management system with inventory, billing, and reporting capabilities for WordPress.

## Features

### Core Functionality
- **Multi-Center Management**: Manage multiple retail centers/warehouses with hierarchy support
- **Product Management**: Complete product catalog with categories and variables (size, color, etc.)
- **Inventory Tracking**: Real-time inventory management across all centers
- **Purchase Bills**: Track vendor purchases and auto-update inventory
- **Sales Bills**: Process sales with automatic inventory deduction
- **Barcode Generation**: Generate and print EAN-13 barcodes for products
- **Vendor & Customer Management**: Maintain vendor and customer databases
- **Comprehensive Reporting**: Sales, inventory, profit reports with dashboard analytics
- **User Roles**: Custom role-based access control system

### Technical Features
- REST API endpoints for external integrations
- Role-based permissions system
- Real-time inventory updates
- Low stock alerts
- Multi-currency support
- Responsive admin interface
- Security-first architecture with WordPress standards

## Installation

1. Upload the `u-commerce` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Run the setup wizard to configure your business settings
4. Start managing your inventory!

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## User Roles

### U-Commerce Super Admin
Full system access with all capabilities including:
- Manage categories, centers, users, and settings
- Create purchase and sales bills
- Set pricing and generate barcodes
- View all reports

### U-Commerce Center Manager
Center-specific operations:
- Manage inventory for their center
- Set pricing for their center
- Generate barcodes
- Create sales
- View center-specific reports

### U-Commerce Sales Person
Limited to sales operations:
- Create sales bills
- Manage customers
- View limited reports

### U-Commerce Inventory Manager
Inventory focused:
- Add purchase bills
- Manage inventory
- Manage products and vendors
- View inventory reports

## Database Structure

The plugin creates the following custom tables:

- `wp_ucommerce_product_categories` - Product categories
- `wp_ucommerce_product_variables` - Product variables (size, color, etc.)
- `wp_ucommerce_centers` - Center/warehouse information
- `wp_ucommerce_products` - Product catalog
- `wp_ucommerce_inventory` - Inventory tracking per center
- `wp_ucommerce_pricing` - Center-specific pricing
- `wp_ucommerce_purchase_bills` - Purchase bill records
- `wp_ucommerce_purchase_items` - Purchase bill line items
- `wp_ucommerce_sales_bills` - Sales bill records
- `wp_ucommerce_sales_items` - Sales bill line items
- `wp_ucommerce_vendors` - Vendor information
- `wp_ucommerce_customers` - Customer information
- `wp_ucommerce_barcodes` - Barcode records

## REST API Endpoints

The plugin provides REST API endpoints under the `/wp-json/u-commerce/v1/` namespace:

- `GET /products` - Get all products
- `GET /products/{id}` - Get single product
- `GET /inventory/{product_id}/{center_id}` - Get inventory data
- `POST /sales` - Create a new sale
- `GET /barcode/{barcode}` - Lookup product by barcode
- `GET /reports/dashboard` - Get dashboard statistics

## Hooks and Filters

### Action Hooks

```php
do_action( 'u_commerce_plugin_activated' );
do_action( 'u_commerce_after_purchase_bill_created', $bill_id, $bill_data, $items );
do_action( 'u_commerce_after_sales_bill_created', $bill_id, $bill_data, $items );
do_action( 'u_commerce_inventory_updated', $product_id, $center_id, $quantity );
do_action( 'u_commerce_product_created', $product_id, $product_data );
do_action( 'u_commerce_center_created', $center_id, $center_data );
do_action( 'u_commerce_barcode_generated', $barcode, $product_id, $center_id );
do_action( 'u_commerce_low_stock_alert', $product_id, $center_id, $available );
```

### Filter Hooks

```php
apply_filters( 'u_commerce_product_data', $product, $product_id );
apply_filters( 'u_commerce_inventory_calculation', $inventory );
apply_filters( 'u_commerce_bill_total', $total, $items );
apply_filters( 'u_commerce_barcode_format', $format );
apply_filters( 'u_commerce_pricing_rules', $rules );
apply_filters( 'u_commerce_report_data', $data, $report_type, $args );
```

## Settings

Access plugin settings at **U-Commerce > Settings**

### General Settings
- Company Name
- Company Address
- Currency
- Decimal Places

### Inventory Settings
- Low Stock Threshold
- Auto Barcode Generation
- Stock Management Method (FIFO/LIFO)

### Billing Settings
- Bill Number Format
- Auto Bill Numbering
- Default Payment Terms

### Notifications
- Email Notifications
- Low Stock Alerts
- New Sale Notifications

## Development

### File Structure

```
u-commerce/
├── u-commerce.php (Main plugin file)
├── includes/
│   ├── class-uc-plugin.php (Main plugin class)
│   ├── admin/
│   │   ├── class-uc-admin-menu.php
│   │   ├── class-uc-setup-wizard.php
│   │   └── pages/ (Admin page templates)
│   ├── core/
│   │   ├── class-uc-database.php
│   │   ├── class-uc-roles.php
│   │   ├── class-uc-capabilities.php
│   │   └── class-uc-activator.php
│   ├── modules/
│   │   ├── products/ (Product management)
│   │   ├── inventory/ (Inventory management)
│   │   ├── billing/ (Purchase & sales bills)
│   │   ├── centers/ (Center management)
│   │   ├── users/ (User management)
│   │   └── reports/ (Reporting)
│   ├── api/
│   │   └── class-uc-rest-api.php
│   └── helpers/
│       ├── class-uc-barcode.php
│       └── class-uc-utilities.php
├── assets/
│   ├── css/ (Stylesheets)
│   ├── js/ (JavaScript)
│   └── images/
└── languages/ (Translation files)
```

### Coding Standards

This plugin follows WordPress coding standards:
- [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- Security best practices (sanitization, escaping, nonces)

## Security

- All user inputs are sanitized
- All outputs are escaped
- CSRF protection with WordPress nonces
- SQL injection prevention with prepared statements
- Capability checks on all operations
- Role-based access control

## Support

For support, feature requests, or bug reports, please visit:
- Documentation: [Link to docs]
- Support Forum: [Link to forum]
- GitHub Issues: [Link to repo]

## Changelog

### 1.0.0 (Initial Release)
- Complete multi-center retail management system
- Product and category management
- Inventory tracking across centers
- Purchase and sales billing
- Barcode generation
- Vendor and customer management
- Comprehensive reporting
- User role management
- REST API
- Setup wizard

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by [Your Name/Company]

## Roadmap

Future enhancements planned:
- Mobile app integration
- Advanced reporting with charts
- Multi-currency transactions
- Payment gateway integration
- Email/SMS notifications
- Import/Export functionality
- Multi-language support
- Advanced inventory forecasting
