# 🚀 Legal System Consolidated Database - Installation Guide

## Overview
This converts the Legal Management System from multiple separate databases (on different ports) into a single unified database with multiple tables.

---

## 📊 What This Does

### Before (Microservices):
```
Port 8008 → case_management_db
Port 8009 → client_management_db  
Port 8010 → document_management_db
Port 8015 → contract_register_db
Port 8011 → court_scheduling_db
Port 8012 → billing_finance_db
Port 8013 → compliance_security_db
Port 8014 → legal_analytics_db
```

### After (Consolidated):
```
Port 8000 → scheduling_management_system (single database)
  ├── legal_clients
  ├── legal_cases
  ├── legal_contracts
  ├── court_schedules
  ├── legal_invoices
  ├── legal_time_entries
  ├── legal_documents
  └── demand_notes (already created)
```

---

## ⚡ Quick Installation

### Step 1: Run SQL Script

**Option A: Using phpMyAdmin**
1. Open: `http://localhost/phpmyadmin`
2. Select database: `scheduling_management_system`
3. Click **SQL** tab
4. Copy contents of `legal_system_consolidated.sql`
5. Paste and click **Go**

**Option B: MySQL Command Line**
```bash
mysql -u root -p scheduling_management_system < legal_system_consolidated.sql
```

### Step 2: Verify Installation

Run this query:
```sql
SHOW TABLES LIKE 'legal%';
```

**Expected Output:**
```
legal_cases
legal_clients
legal_contracts
legal_documents
legal_invoices
legal_time_entries
court_schedules
```

---

## 📋 Tables Created

### 1. **legal_clients** (Client Management)
- Client information (Individual, Corporate, Government, NGO)
- Contact details
- Billing information
- Status tracking

**Key Fields:**
- `client_number` - Unique identifier
- `full_name` - Client name
- `client_type` - Individual/Corporate/etc
- `status` - Active/Inactive
- `assigned_lawyer_id` - FK to users

### 2. **legal_cases** (Case Management)
- Case details and tracking
- Court information
- Financial tracking
- Status management

**Key Fields:**
- `case_number` - Unique identifier
- `case_title` - Case name
- `case_type` - Civil/Criminal/Corporate/etc
- `client_id` - FK to legal_clients
- `status` - Active/Pending/Completed/etc
- `assigned_lawyer_id` - FK to users

### 3. **legal_contracts** (Contract Register)
- Contract documentation
- Tender management
- Expiry tracking
- Party details

**Key Fields:**
- `contract_number` - Unique identifier
- `contract_title` - Contract name
- `contract_type` - Tender/Service Agreement/etc
- `contract_value` - Amount in TZS
- `expiry_date` - Renewal tracking
- `status` - Draft/Active/Expired/etc

### 4. **court_schedules** (Court Scheduling)
- Hearings and deadlines
- Court appearances
- Meeting schedules
- Event tracking

**Key Fields:**
- `event_type` - Hearing/Deadline/Meeting
- `event_date` - Date of event
- `case_id` - FK to legal_cases
- `status` - Scheduled/Completed/etc
- `assigned_lawyer_id` - FK to users

### 5. **legal_invoices** (Billing)
- Invoice management
- Payment tracking
- Financial records

**Key Fields:**
- `invoice_number` - Unique identifier
- `client_id` - FK to legal_clients
- `total_amount` - Invoice total
- `balance_due` - Outstanding amount
- `status` - Draft/Sent/Paid/etc

### 6. **legal_time_entries** (Time Tracking)
- Billable hours
- Time tracking per case
- Rate management

**Key Fields:**
- `case_id` - FK to legal_cases
- `hours` - Hours worked
- `hourly_rate` - Rate charged
- `amount` - Total amount
- `lawyer_id` - FK to users

### 7. **legal_documents** (Document Management)
- Document repository
- File tracking
- Version control

**Key Fields:**
- `document_name` - File name
- `file_path` - Storage location
- `document_type` - Contract/Filing/etc
- `case_id` - FK to legal_cases
- `uploaded_by` - FK to users

---

## 🔗 Foreign Key Relationships

```
legal_clients
  └── legal_cases (client_id)
  └── legal_contracts (client_id)
  └── legal_invoices (client_id)
  └── legal_documents (client_id)

legal_cases
  └── court_schedules (case_id)
  └── legal_documents (case_id)
  └── legal_invoices (case_id)
  └── legal_time_entries (case_id)

users (existing table)
  └── All tables (lawyer assignments, created_by)
```

---

## 🔄 Next Steps - Update Backend API

After installing the database, you need to:

### 1. Update API Routes
All legal endpoints should now use port 8000 (main Laravel app) instead of separate ports.

**Old:**
```
http://localhost:8008/api/cases
http://localhost:8009/api/clients
http://localhost:8015/api/contracts
```

**New:**
```
http://localhost:8000/api/cases
http://localhost:8000/api/clients
http://localhost:8000/api/contracts
```

### 2. Create Laravel Controllers

You'll need to create:
- `LegalCaseController.php`
- `LegalClientController.php`
- `LegalContractController.php`
- `CourtScheduleController.php`
- `LegalInvoiceController.php`
- `LegalDocumentController.php`

### 3. Create Laravel Models

You'll need to create:
- `LegalCase.php`
- `LegalClient.php`
- `LegalContract.php`
- `CourtSchedule.php`
- `LegalInvoice.php`
- `LegalTimeEntry.php`
- `LegalDocument.php`

### 4. Update Frontend API Service

Update `frontend/src/services/legalApi.js`:

**Replace:**
```javascript
const LEGAL_BASE_URL = 'http://localhost';
// Port 8008, 8009, etc.
```

**With:**
```javascript
const LEGAL_BASE_URL = 'http://localhost:8000/api';
// Single port!
```

---

## ✅ Test Queries

### Insert Test Client
```sql
INSERT INTO legal_clients (
  client_number, full_name, client_type, email, phone, status
) VALUES (
  'CLT-2025-001', 'ABC Corporation Ltd', 'Corporate', 
  'contact@abc.co.tz', '0712345678', 'Active'
);
```

### Insert Test Case
```sql
INSERT INTO legal_cases (
  case_number, case_title, case_type, client_id, status
) VALUES (
  'CASE-2025-001', 'Contract Dispute - ABC Corp', 'Civil',
  (SELECT id FROM legal_clients WHERE client_number = 'CLT-2025-001'),
  'Active'
);
```

### Insert Test Contract
```sql
INSERT INTO legal_contracts (
  contract_number, contract_title, contract_type, party_a, party_b,
  contract_value, status
) VALUES (
  'CNT-2025-001', 'Service Agreement - IT Support', 'Service Agreement',
  'ABC Corporation', 'XYZ Tech Ltd', 5000000.00, 'Active'
);
```

### Check Foreign Keys
```sql
SELECT 
  lc.case_number,
  lc.case_title,
  lcl.full_name AS client_name,
  u.username AS lawyer_username
FROM legal_cases lc
LEFT JOIN legal_clients lcl ON lc.client_id = lcl.id
LEFT JOIN users u ON lc.assigned_lawyer_id = u.id;
```

---

## 📊 Statistics Queries

### Cases by Status
```sql
SELECT status, COUNT(*) as count 
FROM legal_cases 
GROUP BY status;
```

### Clients by Type
```sql
SELECT client_type, COUNT(*) as count 
FROM legal_clients 
WHERE status = 'Active'
GROUP BY client_type;
```

### Active Contracts Value
```sql
SELECT 
  SUM(contract_value) as total_value,
  COUNT(*) as contract_count
FROM legal_contracts
WHERE status = 'Active';
```

### Unbilled Time Entries
```sql
SELECT 
  SUM(amount) as unbilled_amount,
  SUM(hours) as unbilled_hours
FROM legal_time_entries
WHERE billable = TRUE AND billed = FALSE;
```

---

## 🎯 Benefits of Consolidation

✅ **Single Database** - Easier to manage  
✅ **No Port Conflicts** - One Laravel app  
✅ **Better Performance** - No cross-database queries  
✅ **Easier Deployment** - One app to deploy  
✅ **Simpler Backups** - Single database backup  
✅ **Referential Integrity** - Foreign keys work  
✅ **Transaction Support** - ACID compliance  
✅ **Unified Authentication** - Single auth system  

---

## 🔧 Troubleshooting

### Foreign Key Error
**Problem:** Can't create tables due to missing `legal_clients` table  
**Solution:** Tables are created in order. Run full script, not partial.

### Duplicate Key Error
**Problem:** Tables already exist  
**Solution:**
```sql
DROP TABLE IF EXISTS legal_time_entries;
DROP TABLE IF EXISTS legal_invoices;
DROP TABLE IF EXISTS legal_documents;
DROP TABLE IF EXISTS court_schedules;
DROP TABLE IF EXISTS legal_contracts;
DROP TABLE IF EXISTS legal_cases;
DROP TABLE IF EXISTS legal_clients;
-- Then re-run creation script
```

### Users Table Not Found
**Problem:** Foreign key constraint fails on users table  
**Solution:** Ensure your main users table exists:
```sql
SELECT COUNT(*) FROM users;
```

---

## 📝 Migration Checklist

- [ ] Run SQL script (`legal_system_consolidated.sql`)
- [ ] Verify all tables created
- [ ] Test foreign key relationships
- [ ] Insert test data
- [ ] Create Laravel models
- [ ] Create Laravel controllers
- [ ] Add API routes
- [ ] Update frontend `legalApi.js`
- [ ] Test API endpoints
- [ ] Update documentation

---

**Installation Complete! 🎉**

Your legal system is now consolidated into a single database.

Next: Create Laravel controllers and update the frontend API calls.
