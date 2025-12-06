# ⚡ Quick Setup: Create Case Feature

## 🚀 3-Step Setup (2 Minutes)

### **Step 1: Update Database** (30 seconds)
```bash
cd "c:\xampp\htdocs\scheduling management system\microservices"
mysql -u root -p case_management_db < update-case-schema.sql
```
*Press Enter if no MySQL password*

### **Step 2: Restart Services** (30 seconds)
```powershell
# Stop case service
Get-Process php | Where-Object {$_.CommandLine -like '*8008*'} | Stop-Process

# Start case service
cd "c:\xampp\htdocs\scheduling management system\microservices\case-service"
php -S 0.0.0.0:8008 index.php
```

### **Step 3: Refresh Frontend** (10 seconds)
- React should auto-reload
- Or press **Ctrl+F5** in browser

---

## ✅ Test It Now!

### **Access Create Case Form:**
```
http://localhost:3000/legal/cases/create
```

### **Or from Case Management:**
1. Go to http://localhost:3000/legal/cases
2. Click **"➕ Create New Case"** button

---

## 📝 Quick Test Case

**Copy this example:**
```
Case Number: [Click "Generate"]
Parties: John Smith (Plaintiff) vs. ABC Company (Defendant)
Nature of Case: Civil
Amount in Claim: 50000000
Date Filed: 2024-10-25
Current Status: Pending Hearing
Next Hearing Date: 2024-11-15
Any Appeal: No
Assigned Lawyer: Sarah Johnson
Remarks: Test case for property dispute
Documents: [Upload any PDF]
```

Click **"✅ Create Case"** → Success!

---

## 🎯 What You Get

### **Form Fields:**
- ✅ Case Number (auto-generate)
- ✅ Parties (all involved)
- ✅ Nature (Civil/Criminal/Bankruptcy)
- ✅ Amount in TZS Shillings
- ✅ Date Filed
- ✅ Current Status
- ✅ Next Hearing Date
- ✅ Any Appeal status
- ✅ Remarks
- ✅ Document Upload

### **Features:**
- ✅ Beautiful modern design
- ✅ Real-time validation
- ✅ Auto-generate case numbers
- ✅ Multiple file upload
- ✅ Success/Error messages
- ✅ Mobile responsive
- ✅ Role-based access (lawyers only)

---

## 🔐 Access Requirements

**Only these roles can create cases:**
- ✅ Admin
- ✅ Lawyer
- ✅ Developer

**Test Users:**
```
Email: admin@test.com
Password: 123456
Role: admin (full access)
```

---

## 📊 New Database Fields

```sql
case_number          VARCHAR(50)    UNIQUE, REQUIRED
parties              TEXT           REQUIRED
nature_of_case       ENUM           Civil, Criminal, Bankruptcy
amount_in_claim      DECIMAL(15,2)  TZS Shillings (nullable)
date_filed           DATE           Nullable
current_status       ENUM           Multiple options
next_hearing_date    DATE           Nullable
any_appeal           ENUM           Yes, No, Pending
remarks              TEXT           Nullable
```

---

## 🧪 Verify Setup Worked

### **Check Database:**
```sql
mysql -u root -p
USE case_management_db;
DESCRIBE cases;
```

**You should see:** All new columns (parties, nature_of_case, amount_in_claim, etc.)

### **Check API:**
```powershell
curl http://localhost:8008/api/cases
```

**You should see:** Sample cases with new fields

### **Check Frontend:**
```
http://localhost:3000/legal/cases/create
```

**You should see:** Beautiful form with all fields

---

## 💡 Tips

### **Case Number Format:**
- Auto-generated: `CASE-2024-XXXX`
- Manual: Any unique format
- Must be unique!

### **Amount in Claim:**
- Enter in TZS (Tanzania Shillings)
- Example: `50000000` = 50 million TZS
- Leave empty for criminal cases

### **Document Upload:**
- Formats: PDF, DOC, DOCX, JPG, PNG, XLSX
- Multiple files allowed
- Files shown before upload

---

## 🚨 Troubleshooting

**"Table doesn't exist"**
```bash
# Run update script again
mysql -u root -p case_management_db < update-case-schema.sql
```

**"Create button not showing"**
- Check you're logged in as lawyer/admin
- Refresh page (Ctrl+F5)

**"Unauthorized"**
- Login with lawyer account
- Test user: admin@test.com / 123456

**Form fields missing**
- Clear browser cache
- Hard refresh (Ctrl+Shift+R)
- Check console for errors

---

## 📞 Quick Commands

### **View Cases in Database:**
```sql
mysql -u root -p
USE case_management_db;
SELECT case_number, parties, nature_of_case, amount_in_claim FROM cases;
```

### **Check Service Running:**
```powershell
Get-Process php | Where-Object {$_.CommandLine -like '*8008*'}
```

### **Test API Create:**
```powershell
$token = "your-token-here"
$body = @{
    case_number = "CASE-TEST-001"
    parties = "Test vs Test"
    nature_of_case = "Civil"
} | ConvertTo-Json

Invoke-WebRequest -Uri http://localhost:8008/api/cases `
  -Method POST `
  -Headers @{"Authorization"="Bearer $token"; "Content-Type"="application/json"} `
  -Body $body
```

---

## ✅ Done!

**Your case creation system is ready!**

**Create your first case:**
http://localhost:3000/legal/cases/create

**Need detailed docs?** See `CREATE_CASE_GUIDE.md`

🎉 **Happy Case Management!**
