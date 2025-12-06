# 🚀 Demand Notes System - MySQL Installation Guide

## Quick Installation

### Option 1: Using phpMyAdmin (Recommended for XAMPP)

1. **Open phpMyAdmin**
   - Navigate to: `http://localhost/phpmyadmin`
   - Login with your credentials

2. **Select Your Database**
   - Click on `scheduling_management_system` database (or your database name)

3. **Import SQL File**
   - Click on the **SQL** tab at the top
   - Copy the contents of `demand_notes_system.sql`
   - Paste into the SQL query box
   - Click **Go** button

4. **Verify Installation**
   - Check the left sidebar for new tables:
     - ✅ `demand_notes`
     - ✅ `demand_note_payments`
   - Check triggers:
     - ✅ `trg_after_payment_insert`
     - ✅ `trg_after_payment_delete`
   - Check functions/procedures:
     - ✅ `generate_demand_note_number()`
     - ✅ `update_overdue_demand_notes()`

### Option 2: Using MySQL Command Line

1. **Open Command Prompt/Terminal**

2. **Navigate to the SQL file directory**
   ```bash
   cd "c:\xampp\htdocs\scheduling management system\Backend\database\migrations"
   ```

3. **Execute the SQL file**
   ```bash
   mysql -u root -p scheduling_management_system < demand_notes_system.sql
   ```

4. **Enter your MySQL password when prompted**

### Option 3: Using MySQL Workbench

1. **Open MySQL Workbench**
2. **Connect to your database**
3. **Open SQL file**: File → Open SQL Script
4. **Select**: `demand_notes_system.sql`
5. **Execute**: Click the lightning bolt icon or press `Ctrl+Shift+Enter`

---

## 📋 What Gets Created

### Tables

#### 1. `demand_notes`
- Stores all demand note records
- Auto-generates unique note numbers
- Tracks financial data and status
- Links to users table for attribution

**Key Fields:**
- `demand_note_number` - Unique identifier (DN-2025-0001)
- `client_name` - Client information
- `amount_claimed` - Total claim amount
- `amount_paid` - Total payments received
- `balance_due` - Remaining balance
- `current_status` - Payment status
- `due_date` - Payment deadline
- `nature_of_claim` - Type of claim

#### 2. `demand_note_payments`
- Records all payment transactions
- Links to demand_notes table
- Tracks payment methods and receipts
- Auto-updates parent demand note totals

**Key Fields:**
- `demand_note_id` - Foreign key
- `payment_amount` - Payment amount
- `payment_date` - Date of payment
- `payment_method` - How paid
- `receipt_number` - Receipt reference

### Triggers

#### 1. `trg_after_payment_insert`
**Purpose:** Automatically updates demand note totals when payment is added

**What it does:**
- Calculates total amount paid
- Updates `amount_paid` in demand_notes
- Recalculates `balance_due`
- Updates `current_status` (pending → partially_paid → paid)
- Sets `payment_date` when fully paid

#### 2. `trg_after_payment_delete`
**Purpose:** Recalculates totals when payment is deleted

**What it does:**
- Recalculates total paid amount
- Updates balance due
- Reverts status if needed
- Clears payment_date if not fully paid

### Functions & Procedures

#### 1. `generate_demand_note_number()`
**Purpose:** Generates next sequential demand note number

**Returns:** String like "DN-2025-0001"

**Example:**
```sql
SELECT generate_demand_note_number();
-- Returns: DN-2025-0001
```

#### 2. `update_overdue_demand_notes()`
**Purpose:** Batch updates all overdue demand notes

**What it does:**
- Finds notes past due date
- Updates status to 'overdue'
- Returns count of updated notes

**Example:**
```sql
CALL update_overdue_demand_notes();
-- Returns: overdue_notes_updated: 5
```

### Views

#### 1. `vw_demand_notes_summary`
**Purpose:** Simplified view with common joins

**Includes:**
- Demand note details
- Creator information
- Payment count
- Days overdue calculation

**Example:**
```sql
SELECT * FROM vw_demand_notes_summary WHERE current_status = 'overdue';
```

### Indexes

**Performance optimization indexes:**
- `idx_demand_note_number` - Fast lookup by note number
- `idx_current_status` - Filter by status
- `idx_due_date` - Date range queries
- `idx_status_due_date` - Combined queries
- `idx_nature_status` - Reporting queries

---

## ✅ Verification Steps

### 1. Check Tables Created
```sql
SHOW TABLES LIKE '%demand%';
```
**Expected Output:**
```
demand_notes
demand_note_payments
```

### 2. Check Table Structure
```sql
DESCRIBE demand_notes;
DESCRIBE demand_note_payments;
```

### 3. Check Triggers
```sql
SHOW TRIGGERS WHERE `Table` IN ('demand_notes', 'demand_note_payments');
```
**Expected Output:**
```
trg_after_payment_insert
trg_after_payment_delete
```

### 4. Check Functions & Procedures
```sql
SHOW FUNCTION STATUS WHERE Db = 'scheduling_management_system' AND Name = 'generate_demand_note_number';
SHOW PROCEDURE STATUS WHERE Db = 'scheduling_management_system' AND Name = 'update_overdue_demand_notes';
```

### 5. Test Function
```sql
SELECT generate_demand_note_number() AS next_number;
```
**Expected Output:** `DN-2025-0001`

### 6. Check View
```sql
SELECT * FROM vw_demand_notes_summary LIMIT 1;
```

---

## 🧪 Test the System

### 1. Insert Test Demand Note
```sql
INSERT INTO demand_notes (
  demand_note_number,
  client_name,
  amount_claimed,
  due_date,
  nature_of_claim,
  issued_date,
  balance_due,
  created_by
) VALUES (
  generate_demand_note_number(),
  'Test Client Ltd',
  5000000.00,
  DATE_ADD(CURDATE(), INTERVAL 30 DAY),
  'Works',
  CURDATE(),
  5000000.00,
  (SELECT id FROM users WHERE role IN ('admin', 'lawyer') LIMIT 1)
);
```

### 2. Check the Note
```sql
SELECT * FROM demand_notes WHERE client_name = 'Test Client Ltd';
```

### 3. Add Test Payment
```sql
INSERT INTO demand_note_payments (
  demand_note_id,
  payment_amount,
  payment_date,
  payment_method,
  recorded_by
) VALUES (
  (SELECT id FROM demand_notes WHERE client_name = 'Test Client Ltd' LIMIT 1),
  2000000.00,
  CURDATE(),
  'Bank Transfer',
  (SELECT id FROM users WHERE role IN ('admin', 'lawyer') LIMIT 1)
);
```

### 4. Verify Auto-Update
```sql
SELECT 
  demand_note_number,
  amount_claimed,
  amount_paid,
  balance_due,
  current_status
FROM demand_notes 
WHERE client_name = 'Test Client Ltd';
```

**Expected Result:**
- `amount_paid`: 2000000.00
- `balance_due`: 3000000.00
- `current_status`: partially_paid

### 5. View Payment History
```sql
SELECT * FROM vw_demand_notes_summary WHERE client_name = 'Test Client Ltd';
```

### 6. Clean Up Test Data (Optional)
```sql
DELETE FROM demand_notes WHERE client_name = 'Test Client Ltd';
-- Payments will be auto-deleted via CASCADE
```

---

## 🔧 Troubleshooting

### Error: Table already exists
**Solution:** Tables already created. You can either:
- Skip this error
- Drop tables first: `DROP TABLE IF EXISTS demand_note_payments, demand_notes;`
- Use the ALTER TABLE commands instead

### Error: Trigger already exists
**Solution:**
```sql
DROP TRIGGER IF EXISTS trg_after_payment_insert;
DROP TRIGGER IF EXISTS trg_after_payment_delete;
-- Then re-run the trigger creation
```

### Error: Function already exists
**Solution:**
```sql
DROP FUNCTION IF EXISTS generate_demand_note_number;
DROP PROCEDURE IF EXISTS update_overdue_demand_notes;
-- Then re-run the function/procedure creation
```

### Error: Foreign key constraint fails
**Solution:** Ensure the `users` table exists and has data
```sql
-- Check users table
SELECT COUNT(*) FROM users;
```

### Laravel Connection Issue
**Solution:** Clear Laravel config cache
```bash
cd Backend
php artisan config:clear
php artisan cache:clear
```

---

## 🔄 Update Existing Installation

If you already have the tables and want to update:

### Add Missing Columns
```sql
-- Check if columns exist first
ALTER TABLE demand_notes ADD COLUMN IF NOT EXISTS late_fee DECIMAL(15, 2) DEFAULT 0.00;
```

### Recreate Triggers
```sql
DROP TRIGGER IF EXISTS trg_after_payment_insert;
DROP TRIGGER IF EXISTS trg_after_payment_delete;
-- Then run the trigger creation code
```

---

## 📊 Useful Queries

### Get Statistics
```sql
SELECT 
  COUNT(*) AS total_notes,
  SUM(CASE WHEN current_status = 'pending' THEN 1 ELSE 0 END) AS pending,
  SUM(CASE WHEN current_status = 'paid' THEN 1 ELSE 0 END) AS paid,
  SUM(CASE WHEN current_status = 'overdue' THEN 1 ELSE 0 END) AS overdue,
  SUM(CASE WHEN current_status = 'partially_paid' THEN 1 ELSE 0 END) AS partially_paid,
  SUM(amount_claimed) AS total_claimed,
  SUM(amount_paid) AS total_collected,
  SUM(balance_due) AS total_outstanding
FROM demand_notes;
```

### Find Overdue Notes
```sql
SELECT * FROM vw_demand_notes_summary 
WHERE current_status = 'overdue' 
ORDER BY days_overdue DESC;
```

### Payment History
```sql
SELECT 
  dn.demand_note_number,
  dn.client_name,
  dnp.payment_amount,
  dnp.payment_date,
  dnp.payment_method,
  u.username AS recorded_by
FROM demand_note_payments dnp
JOIN demand_notes dn ON dnp.demand_note_id = dn.id
LEFT JOIN users u ON dnp.recorded_by = u.id
ORDER BY dnp.payment_date DESC
LIMIT 20;
```

### Top Clients by Amount
```sql
SELECT 
  client_name,
  COUNT(*) AS note_count,
  SUM(amount_claimed) AS total_claimed,
  SUM(amount_paid) AS total_paid,
  SUM(balance_due) AS outstanding
FROM demand_notes
GROUP BY client_name
ORDER BY total_claimed DESC
LIMIT 10;
```

---

## 🎯 Next Steps

After installation:

1. ✅ **Verify** all tables, triggers, and functions are created
2. ✅ **Test** by creating a sample demand note
3. ✅ **Test** by adding a payment
4. ✅ **Verify** automatic calculations work
5. ✅ **Access** the frontend at: `http://localhost:3000/legal/demand-notes`
6. ✅ **Create** your first real demand note!

---

## 📞 Need Help?

Common issues:
- **Connection refused**: Ensure MySQL is running in XAMPP
- **Database not found**: Create database first or update name in SQL file
- **Permission denied**: Run MySQL as admin or check user privileges
- **Trigger fails**: Check MySQL version supports triggers (5.0+)

---

**Installation Complete! 🎉**

Your Demand Notes Management System database is ready to use.
