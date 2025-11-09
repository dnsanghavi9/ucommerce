# UCommerce - Comprehensive Testing Guide

## Testing Scenario: Urban Fashion - T-Shirt & Shirt Business

### Business Overview

**Business Name**: Urban Fashion
**Business Type**: T-Shirt and Formal/Casual Shirt Retail
**Owner**: Rajesh Kumar
**Number of Locations**: 3 Centers

**Centers**:
1. **Phoenix Mall Store** - Premium location, high footfall, expensive rent
2. **MG Road Street Shop** - Mid-range location, regular customers, moderate rent
3. **Online Warehouse** - Storage and online order fulfillment

### Business Requirements

Rajesh needs to:
- Track inventory separately for each location
- Know which products are available at which center
- Manage multiple vendors who supply different products
- Keep customer records for repeat business
- Track purchases from vendors and sales to customers
- Monitor stock levels to avoid out-of-stock situations
- Calculate profits on sales
- View complete history of transactions

---

## Phase 1: Initial Setup (Day 1)

### Test 1.1: Plugin Installation

**Objective**: Ensure plugin installs correctly and creates all necessary database tables.

**Steps**:
1. Log into WordPress Admin (http://yoursite.com/wp-admin)
2. Navigate to Plugins > Add New > Upload Plugin
3. Upload the `u-commerce` folder
4. Click "Activate Plugin"

**Expected Results**:
- Plugin activates successfully
- New menu item "U-Commerce" appears in WordPress admin sidebar
- No PHP errors displayed
- Database tables created automatically

**Status Check**:
- [ ] Plugin activated without errors
- [ ] U-Commerce menu visible
- [ ] Can access U-Commerce > Dashboard

---

### Test 1.2: Create Categories

**Objective**: Set up product categories for the business.

**Steps**:
1. Navigate to U-Commerce > Categories
2. Add the following categories:

| Category Name | Status | Notes |
|--------------|--------|-------|
| T-Shirts | Active | Casual t-shirts |
| Formal Shirts | Active | Office wear |
| Casual Shirts | Active | Everyday wear |
| Polo Shirts | Active | Semi-formal |

**For each category**:
- Click "Add New"
- Enter name
- Select status: "Active"
- Click "Create"

**Expected Results**:
- All 4 categories created successfully
- Success message displayed after each creation
- Categories appear in the list view
- Can edit and delete categories

**Validation Tests**:
- [ ] Try creating duplicate category name (should allow)
- [ ] Create category with empty name (should show error)
- [ ] Edit a category and change name
- [ ] Set a category to "Inactive" status

---

### Test 1.3: Create Centers (Shops/Locations)

**Objective**: Set up the 3 business locations.

**Steps**:
1. Navigate to U-Commerce > Centers
2. Add the following centers:

**Center 1: Phoenix Mall Store**
- Name: Phoenix Mall Store
- Address: Shop No. 245, Phoenix Mall, Whitefield, Bangalore - 560066
- Phone: 9876543210
- Email: phoenixmall@urbanfashion.com
- Status: Active

**Center 2: MG Road Street Shop**
- Name: MG Road Street Shop
- Address: 123 MG Road, Brigade Road Junction, Bangalore - 560001
- Phone: 9876543211
- Email: mgroad@urbanfashion.com
- Status: Active

**Center 3: Online Warehouse**
- Name: Online Warehouse
- Address: Plot 45, Electronics City Phase 1, Bangalore - 560100
- Phone: 9876543212
- Email: warehouse@urbanfashion.com
- Status: Active

**Expected Results**:
- All 3 centers created successfully
- Phone validation works (Indian format: 10 digits starting with 6-9)
- Email validation works
- Centers appear in dropdown menus throughout the system

**Validation Tests**:
- [ ] Try invalid phone format: "12345" (should show error)
- [ ] Try invalid phone format: "1234567890" (should show error - doesn't start with 6-9)
- [ ] Try invalid email: "notanemail" (should show error)
- [ ] Try valid phone: "9876543210" (should work)
- [ ] Edit a center and update phone number

---

### Test 1.4: Create Variables

**Objective**: Define product attributes for inventory management.

**Steps**:
1. Navigate to U-Commerce > Variables
2. Add the following variables:

| Variable Name | Status | Usage |
|--------------|--------|-------|
| Size | Active | S, M, L, XL, XXL |
| Color | Active | Black, White, Blue, Red, etc. |
| Fit | Active | Slim Fit, Regular Fit, Loose Fit |
| Material | Active | Cotton, Polyester, Blend |

**Expected Results**:
- All variables created successfully
- Variables available when adding product details
- Can be applied differently per center per product

**Validation Tests**:
- [ ] Create variable with empty name (should error)
- [ ] Edit variable name
- [ ] Set variable to inactive status

---

### Test 1.5: Add Vendors

**Objective**: Set up supplier information with multiple contact persons.

**Steps**:
1. Navigate to U-Commerce > Vendors
2. Click "Add New Vendor"

**Vendor 1: Cotton Craft Suppliers**

*Basic Info Tab*:
- Name: Cotton Craft Suppliers
- Phone: 9988776655
- Email: orders@cottoncraft.com
- Address: 456 Industrial Area, Peenya, Bangalore - 560058
- GST Number: 29ABCDE1234F1Z5
- Status: Active
- Click "Save"

*Contact Persons Tab* (Click "Contact Persons" tab):
- Contact 1:
  - Name: Suresh Patel
  - Mobile: 9988776655
- Contact 2:
  - Name: Meena Sharma
  - Mobile: 9988776656
- Click "Save Contacts"

**Vendor 2: Premium Textiles Ltd**

*Basic Info Tab*:
- Name: Premium Textiles Ltd
- Phone: 9876567890
- Email: sales@premiumtextiles.com
- Address: 789 Textile Market, Rajaji Nagar, Bangalore - 560010
- GST Number: 29XYZAB5678G2H6
- Status: Active
- Click "Save"

*Contact Persons Tab*:
- Contact 1:
  - Name: Rajiv Kumar
  - Mobile: 9876567890
- Contact 2:
  - Name: Anita Desai
  - Mobile: 9876567891
- Contact 3:
  - Name: Vikram Singh
  - Mobile: 9876567892
- Click "Save Contacts"

**Vendor 3: Polo Brand Wholesale**

*Basic Info Tab*:
- Name: Polo Brand Wholesale
- Phone: 9123456789
- Email: info@polobrand.com
- Address: 321 Garment District, Gandhinagar, Bangalore - 560009
- GST Number: 29PQRST9012K3L7
- Status: Active
- Click "Save"

*Contact Persons Tab*:
- Contact 1:
  - Name: Amit Verma
  - Mobile: 9123456789
- Click "Save Contacts"

**Expected Results**:
- All 3 vendors created with contact persons
- Phone number is unique (cannot use same number for two vendors)
- Indian phone format validated
- Email format validated
- Can add unlimited contact persons per vendor
- History tab shows "No purchase bills yet"

**Validation Tests**:
- [ ] Try duplicate phone number across vendors (should error: "Phone number already exists")
- [ ] Try invalid phone: "5234567890" (should error - doesn't start with 6-9)
- [ ] Try invalid email format (should error)
- [ ] Try invalid GST format (should error if validation is strict)
- [ ] Add contact person with invalid mobile (should error)
- [ ] Remove a contact person and save
- [ ] Add 5 contact persons to one vendor (should work)

---

### Test 1.6: Add Customers

**Objective**: Set up customer records.

**Steps**:
1. Navigate to U-Commerce > Customers
2. Click "Add New Customer"

**Customer 1: Priya Sharma**
- Name: Priya Sharma
- Phone: 8899776655
- Email: priya.sharma@gmail.com
- Address: 45 Koramangala, Bangalore - 560034
- Status: Active
- Click "Save"

**Customer 2: Rahul Mehta**
- Name: Rahul Mehta
- Phone: 8877665544
- Email: rahul.mehta@yahoo.com
- Address: 12 Indiranagar, Bangalore - 560038
- Status: Active
- Click "Save"

**Customer 3: Kavita Reddy**
- Name: Kavita Reddy
- Phone: 7766554433
- Email: kavita.reddy@outlook.com
- Address: 78 Jayanagar, Bangalore - 560041
- Status: Active
- Click "Save"

**Customer 4: Arjun Nair**
- Name: Arjun Nair
- Phone: 7788990011
- Email: arjun.nair@gmail.com
- Address: 90 HSR Layout, Bangalore - 560102
- Status: Active
- Click "Save"

**Expected Results**:
- All 4 customers created successfully
- Phone numbers are unique across customers
- No GST field displayed (removed for customers)
- History tab shows "No sales bills yet"
- Customer phone format validated (Indian format)

**Validation Tests**:
- [ ] Try duplicate phone number (should error)
- [ ] Try invalid phone format: "1234567890" (should error)
- [ ] Try valid phone: "9876543299" (should work)
- [ ] Try invalid email format (should error)
- [ ] Verify GST field is NOT present in customer form
- [ ] Edit customer and change phone to another valid number

---

## Phase 2: Product Catalog Setup (Day 2)

### Test 2.1: Add Products

**Objective**: Create product catalog with all details.

**Steps**:
1. Navigate to U-Commerce > Products
2. Click "Add New Product"

**Product 1: Classic Cotton T-Shirt**

*Basic Info Tab*:
- Name: Classic Cotton T-Shirt
- SKU: TSH-CCT-001
- Category: T-Shirts
- Description: 100% pure cotton, comfortable fit, breathable fabric
- Base Cost: ₹150
- Status: Active
- Click "Save"

**Product 2: Premium Polo Shirt**

*Basic Info Tab*:
- Name: Premium Polo Shirt
- SKU: PLO-PPS-001
- Category: Polo Shirts
- Description: Premium quality polo with collar, 60% cotton 40% polyester blend
- Base Cost: ₹350
- Status: Active
- Click "Save"

**Product 3: Formal White Shirt**

*Basic Info Tab*:
- Name: Formal White Shirt
- SKU: FS-FWS-001
- Category: Formal Shirts
- Description: Classic white formal shirt for office wear, wrinkle-free fabric
- Base Cost: ₹450
- Status: Active
- Click "Save"

**Product 4: Casual Denim Shirt**

*Basic Info Tab*:
- Name: Casual Denim Shirt
- SKU: CS-CDS-001
- Category: Casual Shirts
- Description: Trendy denim shirt for casual outings, stone washed finish
- Base Cost: ₹550
- Status: Active
- Click "Save"

**Product 5: Graphic Print T-Shirt**

*Basic Info Tab*:
- Name: Graphic Print T-Shirt
- SKU: TSH-GPT-001
- Category: T-Shirts
- Description: Trendy graphic print t-shirt with modern designs
- Base Cost: ₹200
- Status: Active
- Click "Save"

**Product 6: Slim Fit Formal Shirt**

*Basic Info Tab*:
- Name: Slim Fit Formal Shirt
- SKU: FS-SFS-001
- Category: Formal Shirts
- Description: Modern slim fit formal shirt, various colors available
- Base Cost: ₹500
- Status: Active
- Click "Save"

**Expected Results**:
- All 6 products created successfully
- SKU is unique (no duplicates allowed)
- Base cost stored correctly
- Products appear in product list
- Purchase History tab shows "No purchases yet"
- Sales History tab shows "No sales yet"

**Validation Tests**:
- [ ] Try duplicate SKU (should error or warn)
- [ ] Try creating product with empty name (should error)
- [ ] Try negative base cost (should error or warn)
- [ ] Try zero base cost (might be allowed)
- [ ] Edit product and change base cost
- [ ] Edit product and change category

---

### Test 2.2: Add Product Variables (Size and Color)

**Objective**: Add center-specific product variables.

**Steps**:
1. Navigate to U-Commerce > Products
2. Click "Edit" on "Classic Cotton T-Shirt"
3. Click on "Variables" tab

**For Phoenix Mall Store**:
- Select Variable: Size
- Value: S, M, L, XL, XXL
- Click "Add Variable"

- Select Variable: Color
- Value: Black, White, Navy Blue, Grey
- Click "Add Variable"

**For MG Road Street Shop**:
- Select Variable: Size
- Value: M, L, XL
- Click "Add Variable"

- Select Variable: Color
- Value: Black, White, Red
- Click "Add Variable"

**For Online Warehouse**:
- Select Variable: Size
- Value: S, M, L, XL, XXL, XXXL
- Click "Add Variable"

- Select Variable: Color
- Value: Black, White, Navy Blue, Grey, Red, Green
- Click "Add Variable"

**Repeat for other products** with appropriate variables.

**Expected Results**:
- Variables can be different per center
- Variables saved per product-center combination
- Can view all variables in Variables tab
- Variables can be edited or removed

**Validation Tests**:
- [ ] Add variable without selecting center (should error)
- [ ] Add same variable twice for same center (should replace or error)
- [ ] Remove a variable
- [ ] Edit variable value

---

## Phase 3: Purchase Bills - Receiving Stock (Day 3)

### Test 3.1: Create First Purchase Bill

**Objective**: Receive stock from vendor and add to inventory.

**Scenario**: Rajesh orders 100 Classic Cotton T-Shirts from Cotton Craft Suppliers for Phoenix Mall Store.

**Steps**:
1. Navigate to U-Commerce > Purchase Bills
2. Click "Add New Purchase Bill"

**Purchase Bill Details**:
- Bill Number: (Auto-generated - PB-00001)
- Vendor: Cotton Craft Suppliers
- Center: Phoenix Mall Store
- Bill Date: (Select today's date)
- Status: Completed

**Items**:
- Click "Add Item"
  - Product: Classic Cotton T-Shirt (SKU: TSH-CCT-001)
  - Quantity: 100
  - Unit Cost: ₹150 (should auto-fill from base cost)
  - Total: ₹15,000 (auto-calculated)

- Notes: "First stock order for mall store"
- Click "Create Purchase Bill"

**Expected Results**:
- Bill created successfully with bill number PB-00001
- Redirected to bill view page showing all details
- Inventory automatically updated:
  - Classic Cotton T-Shirt at Phoenix Mall Store = 100 units
- Success message displayed
- Can view bill in purchase bills list

**Verification**:
1. Navigate to U-Commerce > Inventory
2. Filter by Center: Phoenix Mall Store
3. Should see: Classic Cotton T-Shirt with 100 units in stock

**Validation Tests**:
- [ ] Bill number auto-generated correctly
- [ ] Cannot create bill without center (should error)
- [ ] Cannot create bill without vendor (should error)
- [ ] Cannot create bill without items (should error)
- [ ] Total amount calculated correctly (100 × ₹150 = ₹15,000)
- [ ] Inventory updated correctly

---

### Test 3.2: Create Multiple Purchase Bills

**Objective**: Add stock for multiple products and centers.

**Purchase Bill 2: Online Warehouse Stock**
- Vendor: Premium Textiles Ltd
- Center: Online Warehouse
- Bill Date: (Today)
- Status: Completed

Items:
1. Classic Cotton T-Shirt - Qty: 200, Cost: ₹150 = ₹30,000
2. Graphic Print T-Shirt - Qty: 150, Cost: ₹200 = ₹30,000
3. Premium Polo Shirt - Qty: 100, Cost: ₹350 = ₹35,000
4. Formal White Shirt - Qty: 80, Cost: ₹450 = ₹36,000
5. Casual Denim Shirt - Qty: 60, Cost: ₹550 = ₹33,000

Total Bill Amount: ₹164,000

**Purchase Bill 3: MG Road Shop Stock**
- Vendor: Cotton Craft Suppliers
- Center: MG Road Street Shop
- Bill Date: (Today)
- Status: Completed

Items:
1. Classic Cotton T-Shirt - Qty: 80, Cost: ₹150 = ₹12,000
2. Premium Polo Shirt - Qty: 50, Cost: ₹350 = ₹17,500
3. Casual Denim Shirt - Qty: 40, Cost: ₹550 = ₹22,000

Total Bill Amount: ₹51,500

**Purchase Bill 4: More Phoenix Mall Stock**
- Vendor: Polo Brand Wholesale
- Center: Phoenix Mall Store
- Bill Date: (Today)
- Status: Completed

Items:
1. Premium Polo Shirt - Qty: 80, Cost: ₹350 = ₹28,000
2. Slim Fit Formal Shirt - Qty: 60, Cost: ₹500 = ₹30,000
3. Formal White Shirt - Qty: 50, Cost: ₹450 = ₹22,500

Total Bill Amount: ₹80,500

**Expected Results**:
- All 4 purchase bills created successfully
- Inventory updated for all products at respective centers
- Bill numbers sequential: PB-00001, PB-00002, PB-00003, PB-00004
- Total calculations correct for each bill
- Can view all bills in purchase bills list

**Verification - Check Inventory**:

Navigate to U-Commerce > Inventory:

| Product | Phoenix Mall | MG Road | Online Warehouse |
|---------|-------------|---------|-----------------|
| Classic Cotton T-Shirt | 100 | 80 | 200 |
| Graphic Print T-Shirt | 0 | 0 | 150 |
| Premium Polo Shirt | 80 | 50 | 100 |
| Formal White Shirt | 50 | 0 | 80 |
| Casual Denim Shirt | 0 | 40 | 60 |
| Slim Fit Formal Shirt | 60 | 0 | 0 |

**Validation Tests**:
- [ ] All inventory quantities match expected values
- [ ] Inventory dashboard shows correct total products count
- [ ] Filter by center works correctly
- [ ] Search by product name/SKU works
- [ ] Stock status badges show correct colors (green for in stock)

---

### Test 3.3: Verify Vendor Purchase History

**Objective**: Check that vendor history tabs show purchase bills.

**Steps**:
1. Navigate to U-Commerce > Vendors
2. Click "Edit" on "Cotton Craft Suppliers"
3. Click "History" tab

**Expected Results**:
- Shows 2 purchase bills (PB-00001 and PB-00003)
- Summary cards show:
  - Total Bills: 2
  - Total Amount: ₹66,500 (₹15,000 + ₹51,500)
- Table shows bill numbers, dates, centers, item counts, amounts
- Can click "View" to see full bill details

**Repeat for other vendors**:
- Premium Textiles Ltd - Should show 1 bill (PB-00002) - ₹164,000
- Polo Brand Wholesale - Should show 1 bill (PB-00004) - ₹80,500

**Validation Tests**:
- [ ] History tab displays all bills correctly
- [ ] Summary totals are accurate
- [ ] Can click through to view bill details
- [ ] Bills sorted by date (newest first)

---

### Test 3.4: Verify Product Purchase History

**Objective**: Check product purchase history tabs.

**Steps**:
1. Navigate to U-Commerce > Products
2. Click "Edit" on "Classic Cotton T-Shirt"
3. Click "Purchase History" tab

**Expected Results**:
- Shows 3 purchase bill entries (from 3 different bills)
- Entry 1: PB-00001, Phoenix Mall, 100 qty, ₹150 cost
- Entry 2: PB-00002, Online Warehouse, 200 qty, ₹150 cost
- Entry 3: PB-00003, MG Road, 80 qty, ₹150 cost
- Footer totals: 380 qty total, ₹57,000 total cost
- Each entry has "View Bill" button

**Repeat for Premium Polo Shirt**:
- Should show 3 entries from different bills
- Total quantity: 230 (80 + 100 + 50)
- Total cost: ₹80,500

**Validation Tests**:
- [ ] All purchase entries displayed correctly
- [ ] Quantities and costs accurate
- [ ] Vendor names shown correctly
- [ ] Center names shown correctly
- [ ] Totals calculated correctly
- [ ] Can click "View Bill" to see full bill

---

## Phase 4: Sales Bills - Making Sales (Day 4)

### Test 4.1: Create First Sales Bill with Stock Checking

**Objective**: Make a sale and verify stock checking works.

**Scenario**: Customer Priya Sharma buys 5 Classic Cotton T-Shirts from Phoenix Mall Store.

**Steps**:
1. Navigate to U-Commerce > Sales Bills
2. Click "Add New Sales Bill"

**Sales Bill Details**:
- Bill Number: (Auto-generated - SB-00001)
- Customer: Priya Sharma
- Center: Phoenix Mall Store (IMPORTANT: Select center first)
- Payment Status: Paid
- Payment Method: Cash

**Items**:
- Click "Add Item"
  - Product: Classic Cotton T-Shirt
  - **Observe**: Stock indicator should appear showing "100" in GREEN
  - Quantity: 5
  - Unit Price: ₹299 (selling price - markup from ₹150 cost)
  - Total: ₹1,495 (auto-calculated)

- Click "Create Sales Bill"

**Expected Results**:
- Bill created successfully (SB-00001)
- Stock checked before creation (100 available, selling 5 - OK)
- Inventory automatically deducted:
  - Classic Cotton T-Shirt at Phoenix Mall = 95 units (was 100, now 95)
- Success message displayed
- Redirected to bill view page

**Verification**:
1. Navigate to U-Commerce > Inventory
2. Filter: Phoenix Mall Store
3. Classic Cotton T-Shirt should show 95 units (not 100)

**Validation Tests**:
- [ ] Stock indicator shows correct quantity
- [ ] Stock indicator is GREEN (good stock level)
- [ ] Cannot create bill without selecting center first
- [ ] Inventory correctly deducted after bill creation
- [ ] Total amount calculated correctly

---

### Test 4.2: Test Stock Checking with Low Stock

**Objective**: Verify visual indicators for low stock.

**Scenario**: Try to sell products and see different stock level indicators.

**Steps**:
1. Navigate to U-Commerce > Sales Bills
2. Click "Add New Sales Bill"
3. Center: MG Road Street Shop
4. Customer: (Leave empty - walk-in customer)

**Test Item 1: Good Stock (GREEN)**
- Product: Classic Cotton T-Shirt
- **Observe**: Stock shows 80 in GREEN color (good stock)
- Quantity: 10
- Unit Price: ₹299
- Should work fine

**Test Item 2: Low Stock (ORANGE)**
- First, we need to create a scenario with low stock
- Product: Casual Denim Shirt (has 40 units at MG Road)
- Sell 35 units in another bill first to bring it down to 5

**New Bill**:
- Center: MG Road Street Shop
- Item: Casual Denim Shirt, Qty: 35, Price: ₹899
- Create bill (should succeed, leaves 5 units)

**Now Create Another Bill**:
- Center: MG Road Street Shop
- Product: Casual Denim Shirt
- **Observe**: Stock shows 5 in ORANGE color (low stock warning)
- Quantity: 3
- Unit Price: ₹899
- Should work fine

**Expected Results**:
- Stock > 10: Shows GREEN indicator
- Stock 1-10: Shows ORANGE indicator (low stock warning)
- Stock 0: Shows RED indicator (out of stock)
- Visual color coding helps identify stock levels

**Validation Tests**:
- [ ] GREEN indicator for stock > 10
- [ ] ORANGE indicator for stock 1-10
- [ ] Color changes are immediate and visible
- [ ] Stock quantities accurate

---

### Test 4.3: Test Overselling Prevention

**Objective**: Verify system prevents selling more than available stock.

**Scenario**: Try to sell more units than available in stock.

**Steps**:
1. Check current stock of Slim Fit Formal Shirt at Phoenix Mall
   - Should be 60 units
2. Navigate to U-Commerce > Sales Bills
3. Click "Add New Sales Bill"

**Sales Bill Details**:
- Center: Phoenix Mall Store
- Customer: Rahul Mehta
- Payment Status: Paid
- Payment Method: Card

**Item**:
- Product: Slim Fit Formal Shirt
- **Observe**: Stock shows 60 units
- Quantity: 70 (MORE than available!)
- Unit Price: ₹999
- Total: ₹69,930

**Try to Create Bill**:
- Click "Create Sales Bill"

**Expected Results**:
- **CLIENT-SIDE VALIDATION**:
  - Row should turn RED background
  - Alert message: "Some items have insufficient stock. Please adjust quantities."
  - Bill NOT created
  - Form submission prevented

**Now Try Valid Quantity**:
- Change Quantity to 50 (less than 60 available)
- Row background returns to normal
- Click "Create Sales Bill"
- Should succeed

**Verification**:
- Inventory for Slim Fit Formal Shirt at Phoenix Mall = 10 units (60 - 50)

**Validation Tests**:
- [ ] Cannot sell more than available stock
- [ ] Client-side validation shows error
- [ ] Visual feedback (red background) shown
- [ ] Valid quantity allows bill creation
- [ ] Inventory correctly updated after valid sale

---

### Test 4.4: Test Out of Stock Prevention

**Objective**: Verify system prevents selling products with zero stock.

**Scenario**: Sell all remaining stock, then try to sell more.

**Steps**:

**First**: Sell all Graphic Print T-Shirts from Online Warehouse (150 units)
1. Create bill: Center = Online Warehouse, Product = Graphic Print T-Shirt, Qty = 150
2. Should succeed, inventory becomes 0

**Second**: Try to sell when stock is 0
1. Create new bill: Center = Online Warehouse
2. Add Product: Graphic Print T-Shirt
3. **Observe**: Stock shows 0 in RED color
4. Try Quantity: 1
5. Try to create bill

**Expected Results**:
- Stock indicator shows 0 in RED (out of stock)
- Client-side validation prevents form submission
- Error message displayed
- Bill NOT created
- System protects against overselling

**Validation Tests**:
- [ ] RED indicator for 0 stock
- [ ] Cannot sell when stock is 0
- [ ] Error message clear and helpful
- [ ] Form submission blocked

---

### Test 4.5: Create Multiple Sales Bills

**Objective**: Process various sales scenarios.

**Sales Bill 2: Walk-in Customer at Phoenix Mall**
- Bill Number: (Auto SB-00002)
- Customer: (None - walk-in)
- Center: Phoenix Mall Store
- Payment Status: Paid
- Payment Method: UPI

Items:
1. Classic Cotton T-Shirt - Qty: 10, Price: ₹299 = ₹2,990
2. Premium Polo Shirt - Qty: 8, Price: ₹699 = ₹5,592

Total: ₹8,582

**Sales Bill 3: Customer Purchase at MG Road**
- Customer: Kavita Reddy
- Center: MG Road Street Shop
- Payment Status: Partial
- Payment Method: Cash

Items:
1. Classic Cotton T-Shirt - Qty: 15, Price: ₹299 = ₹4,485
2. Premium Polo Shirt - Qty: 10, Price: ₹699 = ₹6,990

Total: ₹11,475

**Sales Bill 4: Online Order**
- Customer: Arjun Nair
- Center: Online Warehouse
- Payment Status: Paid
- Payment Method: Bank Transfer

Items:
1. Formal White Shirt - Qty: 20, Price: ₹899 = ₹17,980
2. Casual Denim Shirt - Qty: 15, Price: ₹899 = ₹13,485

Total: ₹31,465

**Sales Bill 5: Large Order at Phoenix Mall**
- Customer: Priya Sharma (repeat customer)
- Center: Phoenix Mall Store
- Payment Status: Unpaid
- Payment Method: Cash

Items:
1. Formal White Shirt - Qty: 25, Price: ₹899 = ₹22,475
2. Premium Polo Shirt - Qty: 20, Price: ₹699 = ₹13,980

Total: ₹36,455

**Expected Results**:
- All bills created successfully
- Bill numbers sequential: SB-00001 through SB-00005
- Stock checked and deducted for each bill
- Walk-in sales work without customer selection
- Different payment statuses and methods recorded
- All calculations correct

**Validation Tests**:
- [ ] Customer field is optional (walk-in sales)
- [ ] All payment statuses work (paid/partial/unpaid)
- [ ] All payment methods work (cash/card/upi/bank_transfer)
- [ ] Multiple items per bill calculated correctly
- [ ] Sequential bill numbering maintained

---

### Test 4.6: Verify Customer Sales History

**Objective**: Check customer history tabs show sales bills.

**Steps**:
1. Navigate to U-Commerce > Customers
2. Click "Edit" on "Priya Sharma"
3. Click "History" tab

**Expected Results**:
- Shows 2 sales bills (SB-00001 and SB-00005)
- Summary cards show:
  - Total Bills: 2
  - Total Amount: ₹37,950 (₹1,495 + ₹36,455)
  - Paid: ₹1,495
  - Unpaid: ₹36,455
- Table shows bill details with payment status
- Can click "View" to see full bill

**Repeat for Kavita Reddy**:
- Should show 1 bill (SB-00003)
- Total Amount: ₹11,475
- Payment Status: Partial

**Repeat for Arjun Nair**:
- Should show 1 bill (SB-00004)
- Total Amount: ₹31,465
- Payment Status: Paid

**Validation Tests**:
- [ ] History shows all customer bills
- [ ] Summary cards calculate correctly
- [ ] Payment status displayed correctly
- [ ] Paid/Unpaid amounts calculated correctly

---

### Test 4.7: Verify Product Sales History

**Objective**: Check product sales history tabs show profit.

**Steps**:
1. Navigate to U-Commerce > Products
2. Click "Edit" on "Classic Cotton T-Shirt"
3. Click "Sales History" tab

**Expected Results**:
- Shows all sales bills containing this product
- For each entry:
  - Bill number, date, customer, center
  - Quantity sold, unit price, total price
  - **Profit** calculated: (Unit Price - Base Cost) × Quantity
  - Example: Sold at ₹299, Cost ₹150 = ₹149 profit per unit
- Footer shows:
  - Total quantity sold across all bills
  - Total sales amount
  - **Total profit** in GREEN if positive
- Color-coded profit (green for positive, red for loss)

**Expected Sales for Classic Cotton T-Shirt**:
- SB-00001: 5 units @ ₹299 = ₹1,495 (Profit: ₹745)
- SB-00002: 10 units @ ₹299 = ₹2,990 (Profit: ₹1,490)
- SB-00003: 15 units @ ₹299 = ₹4,485 (Profit: ₹2,235)
- **Total**: 30 units, ₹8,970 revenue, ₹4,470 profit

**Repeat for Premium Polo Shirt**:
- Multiple entries from different bills
- Profit calculation: (₹699 - ₹350) × quantity
- Should show accumulated profit

**Validation Tests**:
- [ ] All sales entries displayed
- [ ] Profit calculated correctly per entry
- [ ] Total profit calculated correctly
- [ ] Profit shown in green color
- [ ] Can view individual bills

---

## Phase 5: Inventory Management (Day 5)

### Test 5.1: Inventory Dashboard Overview

**Objective**: Use inventory dashboard to monitor all stock.

**Steps**:
1. Navigate to U-Commerce > Inventory
2. View default dashboard

**Expected Results - Summary Cards**:

**Total Products**: 6 unique products

**Out of Stock**: Count of products with 0 stock (e.g., Graphic Print T-Shirt at some centers)

**Low Stock**: Count of products with 1-10 stock (e.g., Slim Fit Formal Shirt at Phoenix Mall after sales)

**Inventory Value**: Total value calculated as (quantity × base_cost) for all products across all centers

Example calculation:
- Classic Cotton T-Shirt: (85 + 55 + 200) × ₹150 = ₹51,000
- Plus all other products
- **Total Inventory Value**: Should be in lakhs

**Expected Results - Inventory Table**:
- Shows all products at all centers
- Columns: Product Name, SKU, Center, In Stock, Reserved, Available, Status
- Status badges:
  - GREEN: Available > 10
  - ORANGE: Available 1-10
  - RED: Available = 0
- Sorted by product name, then center name

**Validation Tests**:
- [ ] Summary cards show correct counts
- [ ] Inventory value calculated correctly
- [ ] All product-center combinations listed
- [ ] Status badges color-coded correctly
- [ ] Table sorted properly

---

### Test 5.2: Filter by Center

**Objective**: View stock for specific location.

**Steps**:
1. On Inventory page, select "Phoenix Mall Store" from Center filter
2. Click "Filter"

**Expected Results**:
- Shows ONLY products at Phoenix Mall Store
- Summary cards recalculate for this center only
- Table filtered to Phoenix Mall inventory
- Other centers not displayed

**Test for Each Center**:
- Phoenix Mall Store
- MG Road Street Shop
- Online Warehouse

**Validation Tests**:
- [ ] Filter works correctly
- [ ] Only selected center displayed
- [ ] Summary cards update for filtered data
- [ ] Can reset filter to "All Centers"

---

### Test 5.3: Filter by Stock Level

**Objective**: Find products needing reorder.

**Steps**:

**Test 1: Out of Stock**
1. Select Stock Level: "Out of Stock"
2. Click "Filter"
3. Should show ONLY products with 0 quantity
4. Use this to identify products needing immediate reorder

**Test 2: Low Stock**
1. Select Stock Level: "Low Stock"
2. Click "Filter"
3. Should show products with 1-10 quantity
4. These need reorder soon (warning level)

**Test 3: In Stock**
1. Select Stock Level: "In Stock"
2. Click "Filter"
3. Should show products with >10 quantity
4. These are adequately stocked

**Expected Results**:
- Each filter shows correct subset
- Helps identify reorder needs quickly
- Summary cards update for filtered results
- Can combine with center filter

**Validation Tests**:
- [ ] Out of Stock filter accurate
- [ ] Low Stock filter accurate (1-10 units)
- [ ] In Stock filter accurate (>10 units)
- [ ] Filters help inventory management

---

### Test 5.4: Search by Product/SKU

**Objective**: Quickly find specific products.

**Steps**:
1. In search box, type: "Cotton"
2. Should show: Classic Cotton T-Shirt across all centers

3. Clear search, type: "TSH-CCT-001" (SKU)
4. Should show: Classic Cotton T-Shirt (same results)

5. Clear search, type: "Formal"
6. Should show: Formal White Shirt, Slim Fit Formal Shirt

**Expected Results**:
- Search works for product name
- Search works for SKU
- Search is case-insensitive
- Partial matches work
- Results update immediately

**Validation Tests**:
- [ ] Search by product name works
- [ ] Search by SKU works
- [ ] Partial search works
- [ ] Case-insensitive search
- [ ] Can combine search with filters

---

### Test 5.5: Combined Filters

**Objective**: Use multiple filters together.

**Scenario**: Find all low-stock products at Phoenix Mall Store.

**Steps**:
1. Center: Phoenix Mall Store
2. Stock Level: Low Stock
3. Click "Filter"

**Expected Results**:
- Shows ONLY Phoenix Mall products
- AND ONLY those with 1-10 units
- Helps prioritize reorders for specific location

**Test More Combinations**:
- MG Road + Out of Stock = Products needing immediate reorder at MG Road
- Online Warehouse + In Stock = Well-stocked products at warehouse
- All Centers + Low Stock + Search "Shirt" = All low-stock shirts

**Validation Tests**:
- [ ] Multiple filters work together
- [ ] AND logic applied correctly
- [ ] Results accurate for combinations
- [ ] Can reset all filters

---

## Phase 6: Advanced Testing & Edge Cases (Day 6)

### Test 6.1: Validation Edge Cases

**Test 6.1.1: Phone Number Validation**

Try these phone numbers and verify behavior:

| Phone Number | Expected Result | Test For |
|-------------|----------------|----------|
| 9876543210 | ✓ Accept | Valid format |
| 6123456789 | ✓ Accept | Starts with 6 |
| 7234567890 | ✓ Accept | Starts with 7 |
| 8345678901 | ✓ Accept | Starts with 8 |
| 5234567890 | ✗ Reject | Starts with 5 (invalid) |
| 1234567890 | ✗ Reject | Starts with 1 (invalid) |
| 98765432 | ✗ Reject | Only 8 digits |
| 987654321012 | ✗ Reject | More than 10 digits |
| 98765abcde | ✗ Reject | Contains letters |
| (987)654-3210 | ✗ Reject | Special characters |

**Test in**:
- [ ] Vendor creation
- [ ] Customer creation
- [ ] Center creation
- [ ] Vendor contact persons

---

**Test 6.1.2: Email Validation**

Try these emails:

| Email | Expected Result | Test For |
|-------|----------------|----------|
| test@example.com | ✓ Accept | Valid standard email |
| user.name@domain.co.in | ✓ Accept | Valid with dots and subdomain |
| test+tag@email.com | ✓ Accept | Valid with plus sign |
| test@domain | ✗ Reject | Missing TLD |
| testdomain.com | ✗ Reject | Missing @ |
| @domain.com | ✗ Reject | Missing username |
| test@.com | ✗ Reject | Missing domain |

---

**Test 6.1.3: Bill Creation Validation**

Try creating bills with invalid data:

- [ ] Purchase bill without vendor (should error)
- [ ] Purchase bill without center (should error)
- [ ] Purchase bill without items (should error)
- [ ] Purchase bill with 0 quantity (should error or warn)
- [ ] Purchase bill with negative quantity (should error)
- [ ] Purchase bill with negative unit cost (should error)
- [ ] Sales bill without center (should error)
- [ ] Sales bill without items (should error)
- [ ] Sales bill with quantity > stock (should error)

---

### Test 6.2: Unique Constraint Testing

**Test 6.2.1: Duplicate Phone Numbers**

**Vendors**:
1. Create Vendor A with phone: 9876543210
2. Try to create Vendor B with same phone: 9876543210
3. **Expected**: Error message "Phone number already exists"
4. Edit Vendor B to use phone: 9876543211
5. **Expected**: Should succeed

**Customers**:
1. Create Customer A with phone: 8876543210
2. Try to create Customer B with same phone: 8876543210
3. **Expected**: Error message "Phone number already exists"

**Cross-Check**:
- Can vendor and customer have same phone? (Should be allowed - different tables)

---

**Test 6.2.2: Duplicate SKU**

1. Create Product A with SKU: TEST-001
2. Try to create Product B with same SKU: TEST-001
3. **Expected**: Error or warning about duplicate SKU

---

### Test 6.3: History Tab Accuracy

**Test 6.3.1: Product History Accuracy**

For "Classic Cotton T-Shirt":
1. Check Purchase History tab
2. Manually count all purchase bills containing this product
3. Verify quantities and totals match
4. Check Sales History tab
5. Manually count all sales bills containing this product
6. Verify quantities, totals, and profit match
7. Profit should be: (Sale Price - Base Cost) × Quantity for each entry

---

**Test 6.3.2: Vendor History Accuracy**

For "Cotton Craft Suppliers":
1. Check History tab
2. Should show all purchase bills from this vendor
3. Verify:
   - Bill count matches actual bills created
   - Total amount matches sum of all bills
   - Each bill clickable to view details

---

**Test 6.3.3: Customer History Accuracy**

For "Priya Sharma":
1. Check History tab
2. Should show all sales bills to this customer
3. Verify:
   - Bill count matches actual bills
   - Total amount matches sum
   - Paid amount = sum of bills with status "paid"
   - Unpaid amount = sum of bills with status "unpaid" or "partial"

---

### Test 6.4: Inventory Accuracy After Multiple Operations

**Objective**: Verify inventory remains accurate after complex operations.

**Steps**:

1. **Choose One Product**: Premium Polo Shirt
2. **Track All Operations**:

   **Initial State**: 0 units everywhere

   **Purchase Operations**:
   - PB-00002: +100 units at Online Warehouse
   - PB-00003: +50 units at MG Road
   - PB-00004: +80 units at Phoenix Mall

   **Sales Operations**:
   - SB-00002: -8 units from Phoenix Mall
   - SB-00003: -10 units from MG Road
   - SB-00005: -20 units from Phoenix Mall

   **Expected Final Inventory**:
   - Phoenix Mall: 80 - 8 - 20 = 52 units
   - MG Road: 50 - 10 = 40 units
   - Online Warehouse: 100 - 0 = 100 units

3. **Verify**: Navigate to Inventory, check Premium Polo Shirt quantities match expected

**Validation Tests**:
- [ ] All purchase additions reflected
- [ ] All sales deductions reflected
- [ ] Math adds up correctly
- [ ] No phantom inventory created
- [ ] No negative inventory allowed

---

### Test 6.5: Concurrent Sales Prevention

**Objective**: Test if system prevents double-selling same stock.

**Scenario**: Two sales persons try to sell the last 10 units simultaneously.

**Setup**:
1. Ensure Formal White Shirt at Phoenix Mall has exactly 10 units
2. Open two browser windows/tabs (simulating two users)

**Window 1**:
- Create sales bill for 10 units of Formal White Shirt
- DO NOT submit yet

**Window 2**:
- Create sales bill for 10 units of same product at same center
- DO NOT submit yet

**Test**:
- Submit Window 1 first → Should succeed, inventory becomes 0
- Submit Window 2 → Should fail (stock now 0, cannot sell 10)

**Expected Results**:
- First submission succeeds
- Second submission fails with stock error
- Final inventory: 0 units (not -10)
- System prevents overselling in concurrent scenario

---

## Phase 7: Business Insights & Reports (Day 7)

### Test 7.1: Business Performance Analysis

**Using the system to answer business questions:**

**Question 1**: Which center has the highest inventory value?

**Steps**:
1. Go to Inventory
2. Filter each center separately
3. Note Inventory Value in summary card for each

**Expected Insights**:
- Online Warehouse likely has highest (more stock)
- Phoenix Mall has premium products
- MG Road has moderate stock

---

**Question 2**: Which vendor have we purchased most from?

**Steps**:
1. Go to Vendors
2. Check History tab for each vendor
3. Compare Total Amount in summary cards

**Expected**:
- Premium Textiles Ltd: ₹164,000 (largest single bill)
- Cotton Craft Suppliers: ₹66,500 (two bills)
- Polo Brand Wholesale: ₹80,500 (one bill)

**Winner**: Premium Textiles Ltd

---

**Question 3**: Which customer has purchased the most?

**Steps**:
1. Go to Customers
2. Check History tab for each customer
3. Compare Total Amount

**Expected**:
- Priya Sharma: ₹37,950
- Arjun Nair: ₹31,465
- Kavita Reddy: ₹11,475

**Winner**: Priya Sharma (repeat customer, high value)

---

**Question 4**: Which products are most profitable?

**Steps**:
1. Go to each product
2. Check Sales History tab
3. Note Total Profit in footer

**Compare profit per product sold**

---

**Question 5**: Which products need reordering urgently?

**Steps**:
1. Go to Inventory
2. Filter: Stock Level = "Out of Stock"
3. These need immediate attention
4. Filter: Stock Level = "Low Stock"
5. These need reorder soon

---

### Test 7.2: End of Day Reconciliation

**Scenario**: End of business day at Phoenix Mall Store.

**Steps**:

1. **Check Sales for the Day**:
   - Go to Sales Bills
   - Filter by date if available, or manually check bills with today's date
   - Count bills for Phoenix Mall Store
   - Sum total amounts

2. **Check Cash Collections**:
   - From sales bills list, note payment method
   - Calculate: Cash sales total
   - Calculate: Card/UPI sales total
   - Calculate: Unpaid bills total

3. **Check Inventory Status**:
   - Go to Inventory
   - Filter: Phoenix Mall Store
   - Check for low stock items
   - Note items for reorder

4. **Generate Reorder List**:
   - Products with low stock at Phoenix Mall
   - Decide quantities based on sales velocity
   - Prepare purchase order for next day

---

## Phase 8: Stress Testing (Day 8)

### Test 8.1: Large Volume Data

**Create**:
- 50+ products
- 100+ purchase bills
- 500+ sales bills
- Check system performance

**Expected**:
- Pages load within acceptable time
- Searches remain fast
- Filters work smoothly
- Database queries optimized

---

### Test 8.2: Long-Running Session

**Test**:
- Keep WordPress admin session open for extended period
- Perform various operations
- Check for session timeouts
- Verify nonces don't expire unexpectedly

---

### Test 8.3: Browser Compatibility

**Test on**:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

**Verify**:
- All forms work
- AJAX stock checking works
- Dynamic rows add/remove correctly
- Styling appears correctly

---

## Phase 9: Final Checklist

### Feature Completeness

- [ ] ✓ All categories created and manageable
- [ ] ✓ All centers created and manageable
- [ ] ✓ All variables created and manageable
- [ ] ✓ All vendors created with multiple contacts
- [ ] ✓ All customers created
- [ ] ✓ All products created
- [ ] ✓ Product variables assigned per center
- [ ] ✓ Purchase bills create and update inventory
- [ ] ✓ Sales bills create and deduct inventory
- [ ] ✓ Stock checking works in real-time
- [ ] ✓ Overselling prevented
- [ ] ✓ Vendor history shows purchase bills
- [ ] ✓ Customer history shows sales bills
- [ ] ✓ Product purchase history accurate
- [ ] ✓ Product sales history shows profit
- [ ] ✓ Inventory dashboard functional
- [ ] ✓ Center filter works
- [ ] ✓ Stock level filter works
- [ ] ✓ Product search works
- [ ] ✓ Summary cards accurate
- [ ] ✓ All validations working
- [ ] ✓ Phone validation (Indian format)
- [ ] ✓ Email validation
- [ ] ✓ GST validation for vendors
- [ ] ✓ Unique phone constraint
- [ ] ✓ Tab-based architecture for vendors
- [ ] ✓ Tab-based architecture for customers
- [ ] ✓ Tab-based architecture for products

### Security Checks

- [ ] ✓ WordPress nonces present on all forms
- [ ] ✓ Input sanitization working
- [ ] ✓ Output escaping working
- [ ] ✓ SQL injection prevented (prepared statements)
- [ ] ✓ XSS protection in place
- [ ] ✓ CSRF protection via nonces

### User Experience

- [ ] ✓ Forms easy to use
- [ ] ✓ Error messages clear and helpful
- [ ] ✓ Success messages displayed
- [ ] ✓ Navigation intuitive
- [ ] ✓ Table sorting makes sense
- [ ] ✓ Filters accessible and useful
- [ ] ✓ Real-time feedback (stock indicators)
- [ ] ✓ Visual indicators (colors) helpful
- [ ] ✓ Tab navigation smooth
- [ ] ✓ No broken links
- [ ] ✓ Consistent styling

---

## Conclusion

After completing all test phases, Rajesh (Urban Fashion owner) should be able to:

1. **Manage 3 centers independently** with separate inventory
2. **Track all products** with SKUs and categories
3. **Maintain vendor relationships** with multiple contacts
4. **Build customer database** with transaction history
5. **Receive stock** via purchase bills with automatic inventory updates
6. **Make sales** with real-time stock checking to prevent overselling
7. **Monitor inventory** across all centers with powerful filters
8. **View complete history** for products, vendors, and customers
9. **Calculate profitability** per product from sales history
10. **Make data-driven decisions** using inventory insights

The system should handle real-world business operations smoothly, with validations preventing errors and user-friendly interfaces making daily tasks efficient.

---

## Bug Reporting Template

If you find any issues during testing, report them as:

**Bug Title**: Brief description

**Steps to Reproduce**:
1. Step one
2. Step two
3. Step three

**Expected Behavior**: What should happen

**Actual Behavior**: What actually happened

**Screenshots**: If applicable

**Environment**:
- WordPress Version:
- PHP Version:
- Browser:

**Severity**: Critical / High / Medium / Low

---

## Testing Sign-Off

**Tester Name**: ___________________
**Date**: ___________________
**Overall Result**: Pass / Fail / Pass with Issues
**Notes**: ___________________

---

**End of Testing Guide**
