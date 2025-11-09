# UCommerce - Multi-Center Inventory & Billing System

A comprehensive WordPress plugin for managing products, inventory, billing, and multi-location operations. Perfect for businesses with multiple centers/shops that need centralized management with location-specific inventory tracking.

## Overview

UCommerce is a powerful inventory and billing management system built as a WordPress plugin. It provides complete control over products, vendors, customers, purchase bills, sales bills, and real-time inventory tracking across multiple centers.

## Key Features

### Multi-Center Management
- Create and manage multiple centers (shops/warehouses/locations)
- Location-specific inventory tracking
- Center-wise product variables and pricing
- Independent stock management per center

### Product Management
- Comprehensive product catalog with SKU tracking
- Base cost and pricing management
- Custom product variables per center
- Categorization and status management
- Complete purchase and sales history tracking
- Product profitability analysis

### Inventory Management
- Real-time inventory tracking per product per center
- Low stock alerts and out-of-stock indicators
- Reserved quantity management
- Inventory value calculations
- Multi-filter dashboard (by center, stock level, product/SKU search)
- Automatic inventory updates via bills

### Vendor Management
- Tab-based vendor profiles with complete information
- Multiple contact persons per vendor (unlimited)
- Phone number validation (Indian mobile format)
- Email and GST validation
- Complete purchase history per vendor
- Total purchase amount tracking

### Customer Management
- Tab-based customer profiles
- Unique phone number enforcement
- Email validation (no GST required for customers)
- Complete sales history per customer
- Payment tracking (paid/unpaid amounts)
- Customer-wise transaction history

### Purchase Bills Module
- Create purchase bills from vendors
- Dynamic product selection with multiple items
- Auto-fill base cost from product data
- Real-time total calculation
- Automatic inventory addition on bill creation
- Center-specific purchases
- Bill status management (pending/completed)
- Comprehensive bill viewing and tracking

### Sales Bills Module
- Create sales bills for customers (optional customer info)
- Real-time stock checking via AJAX
- Visual stock indicators (green/orange/red)
- Prevents overselling with client and server validation
- Automatic inventory deduction on bill creation
- Payment status tracking (paid/partial/unpaid)
- Multiple payment methods (cash/card/upi/bank_transfer)
- Center-specific sales
- Comprehensive bill viewing and tracking

### Category & Variable Management
- Product categorization system
- Custom variable definitions (Size, Color, etc.)
- Center-specific variable values
- Status management for all entities

## System Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation

1. Download the UCommerce plugin folder
2. Upload to your WordPress `wp-content/plugins/` directory
3. Activate the plugin from the WordPress Admin Plugins page
4. Navigate to "U-Commerce" in the admin menu
5. Database tables will be created automatically on activation

## Plugin Structure

```
u-commerce/
├── u-commerce.php                  # Main plugin file
├── README.md                       # This file
├── testing.md                      # Comprehensive testing guide
├── includes/
│   ├── class-uc-activator.php     # Database setup & activation
│   ├── class-uc-database.php       # Database table management
│   ├── class-uc-plugin.php         # Core plugin class
│   ├── class-uc-utilities.php      # Helper functions
│   ├── admin/
│   │   ├── class-uc-admin-menu.php # Admin menu structure
│   │   └── pages/                  # All admin pages
│   │       ├── dashboard.php       # Main dashboard
│   │       ├── categories.php      # Category management
│   │       ├── centers.php         # Center management
│   │       ├── products.php        # Product controller
│   │       ├── products-list.php   # Product listing
│   │       ├── products-form.php   # Product add/edit form
│   │       ├── products-tab-*.php  # Product tab views
│   │       ├── vendors.php         # Vendor controller
│   │       ├── vendors-list.php    # Vendor listing
│   │       ├── vendors-form.php    # Vendor add/edit form
│   │       ├── customers.php       # Customer controller
│   │       ├── customers-list.php  # Customer listing
│   │       ├── customers-form.php  # Customer add/edit form
│   │       ├── purchase-bills.php  # Purchase bills controller
│   │       ├── purchase-bills-*.php # Purchase bill views
│   │       ├── sales-bills.php     # Sales bills controller
│   │       ├── sales-bills-*.php   # Sales bill views
│   │       ├── inventory.php       # Inventory dashboard
│   │       └── variables.php       # Variable management
│   └── core/
│       ├── class-uc-categories.php     # Category handler
│       ├── class-uc-centers.php        # Center handler
│       ├── class-uc-products.php       # Product handler
│       ├── class-uc-product-variables.php # Product variables handler
│       ├── class-uc-vendors.php        # Vendor handler
│       ├── class-uc-customers.php      # Customer handler
│       ├── class-uc-purchase-bills.php # Purchase bill handler
│       ├── class-uc-sales-bills.php    # Sales bill handler
│       ├── class-uc-inventory.php      # Inventory handler
│       └── class-uc-variables.php      # Variable handler
└── assets/
    ├── css/
    │   └── admin.css               # Admin styling
    └── js/
        └── admin.js                # Admin JavaScript
```

## Database Schema

### Core Tables

**ucommerce_categories**
- Product categorization
- Status management
- Timestamps

**ucommerce_centers**
- Multi-location management
- Address and contact info
- Status tracking

**ucommerce_products**
- Product catalog
- SKU, name, description
- Base cost and pricing
- Category association
- Status management

**ucommerce_vendors**
- Vendor information
- Phone (unique, Indian format)
- Email and GST validation
- Address and status

**ucommerce_vendor_contacts**
- Multiple contacts per vendor
- Contact name and mobile
- One-to-many relationship

**ucommerce_customers**
- Customer information
- Phone (unique, Indian format)
- Email validation
- No GST required
- Address and status

**ucommerce_variables**
- Custom variable definitions
- Name (Size, Color, etc.)
- Status management

**ucommerce_product_variables**
- Center-specific product variables
- Product-center-variable relationship
- Value storage

### Inventory Tables

**ucommerce_inventory**
- Product-center inventory tracking
- Quantity in stock
- Reserved quantity
- Available quantity calculation

### Bills Tables

**ucommerce_purchase_bills**
- Purchase bill header
- Vendor and center association
- Bill number, date, total amount
- Status tracking

**ucommerce_purchase_items**
- Purchase bill line items
- Product, quantity, unit cost
- Total cost calculation

**ucommerce_sales_bills**
- Sales bill header
- Customer (optional) and center
- Bill number, payment status/method
- Total amount

**ucommerce_sales_items**
- Sales bill line items
- Product, quantity, unit price
- Total price calculation

## Usage Guide

### Initial Setup

1. **Create Centers**: Start by adding your shops/warehouses/locations
   - Navigate to U-Commerce > Centers
   - Add each location with name, address, phone, email

2. **Create Categories**: Organize your products
   - Navigate to U-Commerce > Categories
   - Add product categories (e.g., T-Shirts, Shirts, Pants)

3. **Create Variables**: Define product attributes
   - Navigate to U-Commerce > Variables
   - Add variables like Size, Color, Fit, etc.

4. **Add Vendors**: Set up your suppliers
   - Navigate to U-Commerce > Vendors
   - Add vendor details with multiple contacts
   - Tabs: Basic Info, Contact Persons, History

5. **Add Customers**: Set up your customer base
   - Navigate to U-Commerce > Customers
   - Add customer details
   - Tabs: Basic Info, History

6. **Add Products**: Build your product catalog
   - Navigate to U-Commerce > Products
   - Add product details with SKU, pricing
   - Tabs: Basic Info, Variables, Purchase History, Sales History

### Daily Operations

#### Receiving Stock (Purchase Bills)

1. Navigate to U-Commerce > Purchase Bills
2. Click "Add New Purchase Bill"
3. Select vendor and center
4. Add products with quantities and unit costs
5. System automatically adds inventory on save
6. View purchase history in vendor's History tab

#### Making Sales (Sales Bills)

1. Navigate to U-Commerce > Sales Bills
2. Click "Add New Sales Bill"
3. Select center (customer optional)
4. Add products - system shows real-time stock levels
5. Visual indicators prevent overselling
6. Select payment status and method
7. System automatically deducts inventory on save
8. View sales history in customer's History tab

#### Monitoring Inventory

1. Navigate to U-Commerce > Inventory
2. Use filters:
   - By Center: View specific location stock
   - By Stock Level: Find out-of-stock or low-stock items
   - By Product/SKU: Search specific products
3. View summary cards for quick insights
4. Monitor inventory value in real-time

### Form Validations

**Phone Numbers**
- Indian mobile format: 10 digits starting with 6, 7, 8, or 9
- Pattern: `[6-9][0-9]{9}`
- Unique across vendors and unique across customers
- Required field

**Email**
- Standard email format validation
- Optional field

**GST Number**
- Required for vendors (optional)
- Standard GST format validation
- Not required for customers

**Stock Validation**
- Sales bills check stock availability
- Cannot create sale if insufficient stock
- Real-time AJAX validation
- Visual color indicators (green/orange/red)

## Features in Detail

### Tab-Based Architecture

All major modules (Products, Vendors, Customers) use a tab-based interface for better organization:

- **Products**: Basic Info, Variables, Purchase History, Sales History
- **Vendors**: Basic Info, Contact Persons, Purchase History
- **Customers**: Basic Info, Sales History

### History Tracking

Complete transaction history for all entities:

- **Products**: See every purchase and sale with quantities, prices, and profit
- **Vendors**: View all purchase bills with amounts and items
- **Customers**: Track all sales with payment status

### Real-Time Calculations

- Purchase bills calculate totals as you add items
- Sales bills show live stock levels
- Inventory value calculated on-the-fly
- Profit calculations in sales history

### Security Features

- WordPress nonces for all forms
- Input sanitization and validation
- SQL injection prevention via prepared statements
- XSS protection with output escaping
- Unique constraints on critical fields

## Admin Menu Structure

```
U-Commerce
├── Dashboard           # Overview and quick stats
├── Categories          # Product categories
├── Centers            # Shop/warehouse locations
├── Products           # Product catalog
├── Variables          # Product attributes
├── Vendors            # Supplier management
├── Customers          # Customer management
├── Purchase Bills     # Receiving stock
├── Sales Bills        # Making sales
└── Inventory          # Stock monitoring
```

## Development Notes

### Architecture Pattern

The plugin follows a modular MVC-like architecture:

- **Controllers**: Page files handle routing and form processing
- **Models**: Handler classes in `includes/core/`
- **Views**: Separate list and form files

### Action Routing

Controllers use action-based routing:
```php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
- 'list' → Show listing page
- 'new' → Show add form
- 'edit' → Show edit form
- 'view' → Show read-only view
- 'delete' → Delete record
```

### AJAX Handlers

AJAX actions registered in `class-uc-plugin.php`:
- `uc_get_stock`: Real-time stock checking for sales bills

### Utility Functions

Common functions in `class-uc-utilities.php`:
- `format_price()`: Currency formatting
- `format_date()`: Date formatting
- Additional helper methods as needed

## Best Practices

1. **Always select a center** when creating bills
2. **Check inventory** before creating large sales bills
3. **Keep vendor contacts updated** for smooth ordering
4. **Monitor low stock alerts** regularly
5. **Use SKUs consistently** for better tracking
6. **Complete purchase bills** before making sales
7. **Track payment status** for better cash flow

## Testing

For comprehensive testing scenarios and real-world use cases, see `testing.md`.

The testing guide includes:
- Complete walkthrough for a T-shirt and shirt shop owner
- 3 center setup (Mall Shop, Street Shop, Online Warehouse)
- Step-by-step testing of all features
- Real product examples and scenarios
- Edge cases and validation testing

## Version History

### v1.0.0 - Initial Release
- Multi-center support with location-specific inventory
- Complete product management with categories and variables
- Vendor management with multiple contacts per vendor
- Customer management with transaction history
- Purchase bills with automatic inventory addition
- Sales bills with real-time stock checking
- Comprehensive inventory dashboard with filters
- History tabs for Products, Vendors, and Customers
- Indian phone number validation
- Form validations (Phone, Email, GST)
- Real-time calculations and AJAX features
- Tab-based architecture for complex forms

## Roadmap

Future enhancements planned:
- Advanced reporting and analytics
- Payment tracking and receipts
- Email notifications for low stock
- Export functionality (PDF, Excel)
- Barcode/QR code support
- Multi-currency support
- Mobile-responsive design improvements
- Backup and restore functionality

## Support & Documentation

- **Testing Guide**: See `testing.md` for detailed testing scenarios
- **Code Documentation**: Inline comments throughout the codebase
- **WordPress Codex**: Follows WordPress coding standards

## License

This plugin is proprietary software developed for business use.

## Credits

Developed with WordPress best practices and modern PHP standards.
Built using:
- WordPress Plugin API
- jQuery for dynamic interactions
- WordPress database abstraction layer
- WordPress nonces for security
