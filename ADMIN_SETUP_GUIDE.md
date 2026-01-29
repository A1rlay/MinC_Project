# Admin Dashboard Setup Guide - MinC Project

## Overview
Your MinC project has a fully functional admin dashboard with multiple features. Here's what exists and how to set it up.

---

## User Levels & Roles

The system has 4 user levels:

| ID | Role | Access Level | Features |
|---|---|---|---|
| **1** | **IT Personnel** | Full Access | All admin features, system settings |
| **2** | **Owner** | Full Access | Dashboard, Products, Orders, Customers, Reports |
| **3** | **Manager** | Management | Dashboard, Orders, Customers, Reports (no user management) |
| **4** | **Consumer** | Customer | Front-end only (shopping, orders, profile) |

---

## How to Set Up an Admin Account

### Option 1: Use Existing Root Admin Account

**Email:** `root@gmail.com`  
**Password:** `root` (default - CHANGE THIS in production!)  
**Role:** IT Personnel (Level 1)

You can login directly with this account.

### Option 2: Create a New Admin Account via Database

Run this SQL query in your database:

```sql
INSERT INTO `users` (`fname`, `mname`, `lname`, `email`, `password`, `contact_num`, `user_status`, `user_level_id`) 
VALUES (
    'Your First Name',
    'Your Middle Name', 
    'Your Last Name',
    'your.email@example.com',
    '$2y$10$NEjqatumWgO1tu9DSqlC3.PZXjcDJPc6WX8UYwcti8xy/ZWFK2rdC', 
    NULL,
    'active',
    1
);
```

**Note:** The password hash above is `root`. To generate a new password hash, use this PHP code:
```php
echo password_hash('your_password_here', PASSWORD_BCRYPT);
```

### Option 3: Create Admin via Backend API

Create a simple PHP script or use the backend registration endpoints if available.

---

## Accessing the Dashboard

After login with an admin account:

1. You should be redirected to the admin dashboard
2. Dashboard URL: `http://localhost/xampp/htdocs/pages/MinC_Project/app/frontend/dashboard.php`

---

## Dashboard Features

### ✅ Features That Exist:

#### 1. **Dashboard (Main Hub)**
- **Path:** `app/frontend/dashboard.php`
- **Features:**
  - Total Sales (Today)
  - Total Orders (Today)
  - Pending Orders Count
  - Low Stock Products (<10 units)
  - Total Revenue (All Time)
  - Total Customers
  - New Customers (Today)
  - Top Selling Categories (Last 30 Days)
  - Monthly Sales Trend (Last 6 Months)
  - Recent Orders
  - Order Status Distribution

#### 2. **Audit Trail** ✅ 
- **Path:** `app/frontend/audit-trail.php`
- **What it tracks:**
  - All user actions (create, update, delete)
  - User who made the action
  - What was changed (old value → new value)
  - Timestamp of action
  - IP address & User Agent
  - Reason for change
  - Entity type (user, product, category, etc.)
  - Transaction ID
- **Data shown:**
  - Comprehensive log of all system changes
  - Filterable by action type, date range, user
  - Shows before/after values in JSON format

#### 3. **User Management** ✅
- **Path:** `app/frontend/user-management.php`
- **Features:**
  - Add/Edit/Delete users
  - Assign user levels (IT Personnel, Owner, Manager, Consumer)
  - Activate/Deactivate users
  - All actions logged in audit trail

#### 4. **Products Management** ✅
- **Path:** `app/frontend/products.php`
- **Features:**
  - Add/Edit/Delete products
  - Manage stock quantity
  - Set prices
  - Upload product images
  - Activate/Deactivate products
  - All actions logged in audit trail

#### 5. **Categories Management** ✅
- **Path:** `app/frontend/categories.php`
- **Features:**
  - Add/Edit/Delete categories
  - Category images
  - Display order
  - Activate/Deactivate
  - All actions logged

#### 6. **Product Lines Management** ✅
- **Path:** `app/frontend/product-lines.php`
- **Features:**
  - Create product lines within categories
  - Manage product line images
  - All actions logged

#### 7. **Orders Management** ✅
- **Path:** `app/frontend/orders.php`
- **Features:**
  - View all orders
  - Update order status
  - View customer information
  - Track order history

#### 8. **Customers Management** ✅
- **Path:** `app/frontend/customers.php`
- **Features:**
  - View all customers
  - Customer details
  - Export customer data
  - Track purchase history

#### 9. **Reports** ✅
- **Path:** `app/frontend/generate_report.php`
- **Features:**
  - Generate sales reports
  - Export data to CSV/Excel
  - Date range filtering

#### 10. **Audit Trail / Activity Log** ✅
- **Database Table:** `audit_trail`
- **What's logged:**
  - Login/Logout events
  - Data modifications
  - User account changes
  - All create/update/delete operations
  - Shows: User, Action, Entity, Old Value, New Value, Timestamp, IP Address

---

## Database Tables Structure

### Key Tables:

1. **`users`** - User accounts
   - `user_id` (Primary Key)
   - `fname`, `mname`, `lname`
   - `email`, `password`
   - `user_level_id` (Foreign Key to user_levels)
   - `user_status` (active/inactive)
   - Timestamps for created_at, updated_at

2. **`user_levels`** - User roles
   - `user_level_id` (1, 2, 3, 4)
   - `user_type_name` (IT Personnel, Owner, Manager, Consumer)

3. **`audit_trail`** - Complete activity log
   - `audit_trail_id`
   - `user_id`, `action`, `entity_type`, `entity_id`
   - `old_value`, `new_value` (JSON format)
   - `timestamp`, `ip_address`, `user_agent`
   - `change_reason`

4. **`orders`** - Customer orders
5. **`customers`** - Customer information
6. **`products`** - Product inventory
7. **`categories`** - Product categories
8. **`product_lines`** - Product line groupings

---

## Accessing Admin Features

### URL Structure:
```
Base: http://localhost/xampp/htdocs/pages/MinC_Project/app/frontend/

Dashboard: /dashboard.php
Audit Trail: /audit-trail.php
Users: /user-management.php
Products: /products.php
Categories: /categories.php
Product Lines: /product-lines.php
Orders: /orders.php
Customers: /customers.php
Reports: /generate_report.php
Notifications: /notification_system.php
```

### Authentication:
- All pages require authentication
- Only Management Levels (1, 2, 3) can access
- Consumers (Level 4) are redirected to index.php

---

## Creating Additional Admin Accounts

After logging in as root:

1. Go to **User Management** (`user-management.php`)
2. Click **Add New User**
3. Fill in details:
   - First Name, Last Name
   - Email (for login)
   - Assign User Level: Select "IT Personnel" or "Owner" for admin access
   - Set Password
4. Click **Save**
5. New user can now login with their email

---

## Current Data in System

### Existing Users:
```
1. Root Admin - root@gmail.com (IT Personnel) ← USE THIS FOR TESTING
2. Test User - test@gmail.com (Owner)
3. Student Test - student@test.com (Consumer)
4. Parent Test - parent@gmail.com (Manager)
5. Teacher Test - teacher@gmail.com (Owner)
```

### Existing Audit Trail Entries:
The system has logged 65 activities, including:
- User logins/logouts
- Product, Category, and Product Line updates
- User account modifications

---

## Features Status

| Feature | Status | Location |
|---------|--------|----------|
| Dashboard | ✅ Working | `/app/frontend/dashboard.php` |
| Audit Trail | ✅ Working | `/app/frontend/audit-trail.php` |
| Create/Edit/Delete Actions | ✅ Working | All management pages |
| Total Records Tracking | ✅ Working | Audit Trail table has all logs |
| Store Open/Close | ❌ Not Implemented | - |
| Customer Records | ✅ Working | `/app/frontend/customers.php` |
| Customer Login Records | ✅ Working | Audit Trail tracks all logins |
| User Login History | ✅ Working | Check `audit_trail` table for login events |

---

## Recommended Next Steps

1. **Change Root Password** (Important for security!)
   - Update the password hash in database or via User Management

2. **Create Your Admin Account**
   - Go to User Management
   - Add new user with IT Personnel or Owner level

3. **Explore Dashboard**
   - Check all statistics
   - Review audit trail for activity logs

4. **Test Features**
   - Try adding a product
   - Create a new category
   - Check audit trail for logged actions

5. **Implement Store Open/Close** (if needed)
   - Add a `store_status` table
   - Add toggle in dashboard
   - Log store status changes in audit trail

---

## Quick Start Commands

### To view all users:
```sql
SELECT * FROM users;
```

### To view audit trail:
```sql
SELECT * FROM audit_trail ORDER BY timestamp DESC LIMIT 50;
```

### To view all logins:
```sql
SELECT * FROM audit_trail WHERE action = 'login' ORDER BY timestamp DESC;
```

### To create a new admin:
```sql
INSERT INTO `users` (`fname`, `lname`, `email`, `password`, `user_level_id`, `user_status`) 
VALUES ('John', 'Admin', 'john@example.com', PASSWORD('secure_password'), 1, 'active');
```

---

## Support URLs

- **Main Index:** `index.php`
- **Admin Dashboard:** `app/frontend/dashboard.php`
- **Login Page:** `backend/login.php`
- **Database Connection:** `database/connect_database.php`

---

## Default Credentials (Change Immediately!)

**Email:** `root@gmail.com`  
**Password:** `root`  
**Level:** IT Personnel (Full Access)

⚠️ **SECURITY WARNING:** Change this password before production deployment!

---

## Questions?

Check these files for more details:
- `app/frontend/app.php` - Main app configuration
- `backend/auth.php` - Authentication logic
- `database/MinC.sql` - Database schema
- `app/frontend/dashboard.php` - Dashboard logic
