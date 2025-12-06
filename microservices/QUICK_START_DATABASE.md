# 🚀 Quick Start: Database Setup

## ⚡ Fastest Way to Setup Databases

### **Option 1: Using Batch Script (Easiest)**

```cmd
cd "c:\xampp\htdocs\scheduling management system\microservices"
setup-databases.bat
```

Enter your MySQL password when prompted. Done!

---

### **Option 2: Manual MySQL Command**

```bash
cd "c:\xampp\htdocs\scheduling management system\microservices"
mysql -u root -p < setup-legal-databases.sql
```

---

### **Option 3: phpMyAdmin (Visual)**

1. Open: http://localhost/phpmyadmin
2. Click "Import" tab
3. Browse to: `setup-legal-databases.sql`
4. Click "Go"
5. Wait for success message

---

## ✅ Verify Setup

### **Check Databases Created**
```sql
SHOW DATABASES;
```

Should see:
- case_management_db
- client_management_db
- document_management_db
- court_scheduling_db
- billing_finance_db
- compliance_security_db
- legal_analytics_db

### **Check Sample Data**
```sql
USE case_management_db;
SELECT * FROM cases;
```

Should see 5 sample cases.

---

## 🧪 Test Services

### **1. Restart Services**
```powershell
# Stop old services
Get-Process php | Stop-Process

# Start case service
cd case-service
php -S 0.0.0.0:8008 index.php
```

### **2. Test API**
```powershell
# Test case service (now with real database)
curl http://localhost:8008/api/cases
```

### **3. Check Frontend**
Open: http://localhost:3000/legal/cases

You should see real data from the database!

---

## 📊 What You Get

### **Sample Data Included:**

- **5 Cases**
  - CASE-2024-001 to CASE-2024-005
  - Mix of Civil, Family, Corporate, Immigration, Personal Injury

- **5 Clients**
  - John Smith (Active, $5,250 outstanding)
  - Jane Doe (Active, $3,800 outstanding)
  - Acme Corporation (VIP, $15,000 outstanding)
  - Plus 2 more

- **5 Documents**
  - Motion to Dismiss, Client Agreement, Evidence Photo, etc.

- **3 Court Hearings**
  - Scheduled between Oct 26 - Nov 15, 2024

- **4 Invoices**
  - Total: $24,050
  - Mix of Paid and Pending

- **4 Time Entries**
  - From Sarah Johnson, Mike Davis, Lisa Chen

- **4 Audit Logs**
  - Recent activity tracking

- **3 Lawyer Performance Records**
  - Sarah Johnson: 245 hrs, $98K
  - Mike Davis: 198 hrs, $79K
  - Lisa Chen: 167 hrs, $67K

---

## 🎯 Next Steps

### **After Database Setup:**

1. ✅ **Configure Connection**
   - Edit `db-config.php` if needed
   - Update password if not blank

2. ✅ **Restart Services**
   - Stop existing PHP processes
   - Start services again

3. ✅ **Test Endpoints**
   - Try: http://localhost:8008/api/cases
   - Should return database records

4. ✅ **View in Frontend**
   - Go to: http://localhost:3000/legal
   - Data should be from database

5. 🔄 **Update Other Services** (Optional)
   - Client Service (8009)
   - Document Service (8010)
   - Court Service (8011)
   - Billing Service (8012)
   - Compliance Service (8013)
   - Analytics Service (8014)

---

## 💡 Tips

### **Add More Data**
```sql
USE case_management_db;

INSERT INTO cases (case_number, title, client_name, case_type, status, priority, filed_date, assigned_lawyer, description) 
VALUES ('CASE-2024-006', 'New Case Title', 'Client Name', 'Civil', 'Active', 'High', '2024-10-25', 'Sarah Johnson', 'Case description');
```

### **View All Cases**
```sql
SELECT case_number, title, status, priority FROM cases;
```

### **Update Case Status**
```sql
UPDATE cases SET status = 'Completed' WHERE id = 1;
```

### **Delete a Case**
```sql
DELETE FROM cases WHERE id = 1;
```

---

## 🔧 Troubleshooting

### **"Access Denied" Error**
**Problem:** Wrong MySQL password
**Solution:** Check password in setup-databases.bat or MySQL command

### **"Database Exists" Error**
**Problem:** Databases already created
**Solution:** Either:
- Drop existing: `DROP DATABASE case_management_db;`
- Or skip, databases already setup

### **"Table Already Exists"**
**Problem:** Tables already created
**Solution:** Script uses `IF NOT EXISTS`, safe to re-run

### **No Data Returned**
**Problem:** Database not connected or empty
**Solution:**
1. Check `db-config.php` settings
2. Verify tables exist: `SHOW TABLES;`
3. Check data exists: `SELECT * FROM cases;`

---

## 📞 Quick Commands

### **Check MySQL Running**
```cmd
net start | findstr MySQL
```

### **Start MySQL**
```cmd
net start MySQL
```

### **Connect to MySQL**
```cmd
mysql -u root -p
```

### **List Databases**
```sql
SHOW DATABASES;
```

### **Use Database**
```sql
USE case_management_db;
```

### **Show Tables**
```sql
SHOW TABLES;
```

### **Count Records**
```sql
SELECT COUNT(*) FROM cases;
```

---

## 🎉 That's It!

Your databases are now setup and ready to use!

**Total Time:** < 2 minutes

**What's Ready:**
✅ 7 databases created
✅ 15+ tables with schemas
✅ 30+ sample records
✅ Case service using real data
✅ Ready for frontend integration

**Test it now:** http://localhost:3000/legal/cases

**Happy Managing!** 🚀
