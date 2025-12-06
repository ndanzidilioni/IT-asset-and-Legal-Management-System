# 🚀 Demand Notes Database Installation

## Choose Your Installation Method

### ✅ Method 1: Direct MySQL Import (Fastest - Recommended)

**Best for:** Quick setup, XAMPP users, production deployment

**Steps:**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select your database: `scheduling_management_system`
3. Click **SQL** tab
4. Copy contents of `demand_notes_system.sql`
5. Paste and click **Go**

**Time:** ~30 seconds

📖 **Full Guide:** See `INSTALL_DEMAND_NOTES.md`

---

### ✅ Method 2: Laravel Migration (Development)

**Best for:** Laravel development workflow, version control

**Steps:**
1. Open terminal in `Backend` folder:
   ```bash
   cd "c:\xampp\htdocs\scheduling management system\Backend"
   ```

2. Run migration:
   ```bash
   php artisan migrate
   ```

**Time:** ~1 minute

---

## 🎯 What Gets Installed

Both methods create:

### Tables
- ✅ `demand_notes` - Main demand notes table
- ✅ `demand_note_payments` - Payment records

### Triggers
- ✅ `trg_after_payment_insert` - Auto-updates totals on payment add
- ✅ `trg_after_payment_delete` - Auto-updates totals on payment delete

### Functions & Procedures
- ✅ `generate_demand_note_number()` - Auto-generates DN-2025-0001
- ✅ `update_overdue_demand_notes()` - Batch updates overdue status

### Views
- ✅ `vw_demand_notes_summary` - Simplified reporting view

### Indexes
- ✅ Multiple indexes for query performance

---

## ✅ Verify Installation

Run this query in phpMyAdmin or MySQL:

```sql
-- Check tables
SHOW TABLES LIKE '%demand%';

-- Expected: demand_notes, demand_note_payments

-- Check function
SELECT generate_demand_note_number();

-- Expected: DN-2025-0001

-- Check view
SELECT * FROM vw_demand_notes_summary LIMIT 1;
```

---

## 🧪 Quick Test

### Create Test Demand Note:
```sql
INSERT INTO demand_notes (
  demand_note_number,
  client_name,
  amount_claimed,
  due_date,
  nature_of_claim,
  issued_date,
  balance_due
) VALUES (
  'DN-2025-0001',
  'Test Client',
  1000000.00,
  DATE_ADD(CURDATE(), INTERVAL 30 DAY),
  'Works',
  CURDATE(),
  1000000.00
);
```

### Add Test Payment:
```sql
INSERT INTO demand_note_payments (
  demand_note_id,
  payment_amount,
  payment_date
) VALUES (
  (SELECT id FROM demand_notes WHERE client_name = 'Test Client'),
  400000.00,
  CURDATE()
);
```

### Verify Auto-Update:
```sql
SELECT 
  demand_note_number,
  amount_claimed,
  amount_paid,
  balance_due,
  current_status
FROM demand_notes 
WHERE client_name = 'Test Client';
```

**Expected Result:**
- amount_paid: 400000.00
- balance_due: 600000.00
- current_status: partially_paid ✅

---

## 🔧 Troubleshooting

### Migration Error: "Table already exists"
**Solution:** Tables already created via SQL import. This is OK!

### Error: "Trigger already exists"
**Solution:** 
```bash
# Drop and recreate
php artisan migrate:fresh
# WARNING: This deletes ALL data!
```

### Foreign Key Error
**Solution:** Ensure `users` table exists:
```sql
SHOW TABLES LIKE 'users';
```

### Laravel Can't Find Tables
**Solution:** Clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📁 Files Overview

```
Backend/database/migrations/
├── demand_notes_system.sql              ← Direct SQL import
├── 2025_11_07_000001_create_demand_notes_tables.php  ← Laravel migration
├── INSTALL_DEMAND_NOTES.md              ← Detailed installation guide
└── README_INSTALLATION.md               ← This file
```

---

## 🎉 Next Steps

After installation:

1. ✅ **Start your servers:**
   - MySQL (XAMPP)
   - Apache (XAMPP)
   - Laravel backend: `php artisan serve`
   - React frontend: `npm start`

2. ✅ **Access the system:**
   - Frontend: `http://localhost:3000/legal/demand-notes`
   - Login as lawyer or admin

3. ✅ **Create your first demand note!**

---

## 📚 Documentation

- **Full API Documentation:** `/DEMAND_NOTES_GUIDE.md`
- **Installation Guide:** `/INSTALL_DEMAND_NOTES.md`
- **Database Schema:** See SQL file comments

---

**Installation Complete! 🎊**

Your Demand Notes Management System database is ready to use.
