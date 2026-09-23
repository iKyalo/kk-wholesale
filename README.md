# KK Wholesalers - Inventory & Sales Management System

A Laravel-based wholesale inventory and sales management system for managing products, stores, inventory, stock transfers, and sales.

## Features

- Product management
- Branch and store management
- Store-level inventory tracking
- Sales processing
- Automatic inventory deductions when a sale is completed
- Stock movement tracking
- Stock transfers between stores
- Transfer status tracking
- Low-stock monitoring
- Sales and inventory summaries
- Server-side validation
- Bootstrap-based interface

---

## Requirements

Make sure the following are installed:

- PHP 8.2+
- Composer
- MySQL 8+
- Node.js and npm
- Laravel 11/12
- Git

---

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/iKyalo/kk-wholesale
cd kk-wholesale
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Create the Environment File

```bash
cp .env.example .env
```

On Windows:

```bash
copy .env.example .env
```

### 5. Generate the Application Key

```bash
php artisan key:generate
```

### 6. Configure the Database

Create a MySQL database, then update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kk_wholesale
DB_USERNAME=root
DB_PASSWORD=
```

Adjust the credentials to match your environment.

### 7. Run Migrations and data seeders

```bash
php artisan migrate --seed
```

### 8. Start the Laravel Development Server

```bash
php artisan serve
```

The application should be available at:

```text
http://127.0.0.1:8000
```

### 8. Login

Login with the following credentials:

```text
email: admin@kkwholesale.co.ke

password: password
```

---

# Assumptions Made

The following assumptions were made during implementation.

### Users and Roles

The system assumes three primary roles:

- **Administrator** — has access to the entire system.
- **Branch Manager** — manages stores and operations belonging to their branch.
- **Store Manager** — manages operations for their assigned store.

### Inventory

Inventory is maintained at the **store level**, rather than only at the branch level.

A product can therefore have different quantities in different stores.

For example:

```text
Product A
    Store 1 → 100 units
    Store 2 → 50 units
    Store 3 → 0 units
```

### Product Pricing

Each product has:

- Cost price
- Selling price

The selling price must always be greater than the cost price.

This is enforced through request validation.

### Sales

A sale contains one or more sale items.

Each sale item records:

- Product
- Quantity
- Unit price
- Sale

Inventory is deducted from the selected store when the sale is processed.

### Stock Movements

Every inventory deduction caused by a sale creates a corresponding `stock_movements` record.

This provides an audit trail of inventory changes.

### Stock Transfers

Stock transfers occur between stores.

A transfer contains:

- Source store
- Destination store
- Transfer number
- Transfer items
- Quantities
- Transfer status
- User who created the transfer

A store cannot transfer stock to itself.

### Stock Availability

Available stock is calculated from the inventory belonging to the selected store and product.

---

# Important Decisions Made

## 1. Store-Level Inventory

Inventory is associated with stores instead of directly with branches.

This allows the system to accurately represent stock physically held at individual locations.

```text
Branch
 ├── Store A
 │    ├── Product A → 100
 │    └── Product B → 50
 │
 └── Store B
      ├── Product A → 30
      └── Product B → 80
```

## 2. Database Transactions for Stock Changes

Sales and inventory deductions should be performed inside a database transaction.

The logical operation is:

```text
Create Sale
    ↓
Create Sale Items
    ↓
Validate Stock
    ↓
Deduct Inventory
    ↓
Create Stock Movement
    ↓
Commit Transaction
```

If one operation fails, the transaction should roll back.

This prevents situations where a sale exists but inventory was not deducted correctly.

## 3. Stock Movement Audit Trail

Inventory changes are not treated as simple quantity updates.

A `stock_movements` record is created for deductions so that the system can track why stock changed.

Typical movement types can include:

```text
SALE
TRANSFER_OUT
TRANSFER_IN
ADJUSTMENT
```

This makes future inventory auditing possible.

## 4. Server-Side Validation

Important business rules are enforced in Laravel controllers/form requests rather than relying exclusively on JavaScript.

Examples include:

```text
Selling price > Cost price
Transfer source != destination
Product exists
Store exists
Quantity > 0
Sufficient stock exists
```

JavaScript is used mainly for improving the user experience.

## 5. Store Selection Determines Available Stock

When creating a sale, the selected store determines which inventory quantity is displayed for the selected product.

## 6. Eloquent Relationships

The application uses Laravel Eloquent relationships to connect:

```text
Branch
Store
Product
Inventory
Sale
SaleItem
StockTransfer
StockTransferItem
StockMovement
User
```

This keeps database access readable and allows related data to be eager-loaded where appropriate.

## 7. Bootstrap for the UI

Bootstrap is used for the interface to reduce custom CSS and keep the application responsive.

The design prioritizes:

- Simple forms
- Responsive tables
- Clear status indicators
- Dashboard cards
- Minimal custom styling

## 8. Blade and JavaScript

Blade is used for server-rendered pages.

JavaScript/jQuery is used where dynamic behaviour is required, such as:

- Loading product stock after selecting a store
- Adding/removing sale items
- Updating totals
- Dynamic form interactions

A full SPA architecture was not considered necessary for this application.

---

# Known Limitations

## 1. No Real-Time Inventory Updates

Inventory is not updated in real time across multiple users.

If two users attempt to sell the last available units simultaneously, database-level locking should be used to guarantee consistent stock.

## 2. Limited Concurrency Protection

The system currently relies primarily on database transactions and application validation.

For high-volume concurrent sales, inventory operations should additionally use row-level locking such as:

```php
->lockForUpdate()
```

This should be applied when retrieving inventory before deducting stock.

## 3. Transfer Receiving Workflow

The transfer workflow assumes a relatively simple lifecycle.

A more advanced implementation could support:

- Partial receiving
- Damaged quantities
- Rejected quantities
- Receiving discrepancies
- Transfer approval
- Transfer cancellation
- Transfer history

## 4. No Barcode Scanning

Products are currently selected through the application's product interface.

Barcode scanning is not yet implemented.

## 5. No Advanced Reporting Module

The dashboard provides basic metrics, but a complete reporting system could additionally provide:

- Daily sales reports
- Monthly sales reports
- Product profitability
- Store profitability
- Inventory valuation
- Stock movement reports
- Best-selling products
- Slow-moving products
- Transfer reports

## 6. No Automated Low-Stock Notifications

Low-stock products can be identified by the system, but automatic notifications through email, SMS, or WhatsApp are not currently implemented.

## 7. No Payment Gateway Integration

Sales are recorded within the application.

External payment integrations such as M-Pesa, card payments, or other payment providers are outside the current scope.

## 8. Basic Authorization

The application defines branch, store, and administrator concepts, but production deployment should ensure that every controller action is protected by proper authorization policies or middleware.

For example:

```text
Administrator
    → All stores

Branch Manager
    → Stores within assigned branch

Store Manager
    → Assigned store only
```

## 9. No Offline Mode

The application requires an active connection to the Laravel server and database.

Offline sales and synchronization are not currently supported.

---

# Recommended Production Considerations

Before deploying the application to production:

1. Configure proper authorization policies.
2. Add database transactions around all stock-changing operations.
3. Use `lockForUpdate()` for concurrent inventory deductions.
4. Add indexes to frequently queried inventory and transaction columns.
5. Configure application and database backups.
6. Disable Laravel debug mode.
7. Configure HTTPS.
8. Configure queue workers if notifications or background jobs are introduced.
9. Add automated tests for sales and stock transfers.
10. Add audit logging for sensitive administrative actions.

---

# Testing

Run the Laravel test suite with:

```bash
php artisan test
```

Important test cases should include:

### Sales

- Sale can be created.
- Multiple sale items can be created.
- Insufficient stock prevents a sale.
- Inventory is deducted correctly.
- A stock movement is created for every deduction.
- Failed sales roll back all database changes.

### Products

- Selling price must be greater than cost price.
- Required product fields are validated.
- Inactive products cannot be sold.

### Transfers

- Source and destination stores must be different.
- Transfer quantities cannot exceed available stock.
- Transfer items are created correctly.
- Transfer status changes correctly.
- Inventory changes correctly when stock is transferred.

---

# Project Principle

The database should remain the source of truth for inventory.

Frontend calculations are for user experience.

Critical business rules must always be validated on the server.

Stock-changing operations should be atomic and auditable.
