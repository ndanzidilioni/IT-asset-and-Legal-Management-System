# 📝 Create Case Feature - Complete Guide

## ✅ What Was Created

I've built a comprehensive case creation system with all your specified requirements including document upload capability.

---

## 🎯 Features Implemented

### **1. Updated Database Schema**
✅ **New Fields Added:**
- Case Number (unique, required)
- Parties (text field for all parties)
- Nature of Case (Civil, Criminal, Bankruptcy)
- Amount in Claim (TZS Shillings, nullable)
- Date Filed (nullable)
- Current Status (Pending Appeal, Pending Hearing, Active, Closed, Completed)
- Next Hearing Date (nullable)
- Any Appeal (Yes, No, Pending)
- Remarks (text field)
- Document upload support

### **2. Backend API Updated**
✅ **New Endpoints:**
- `POST /api/cases` - Create new case with validation
- `POST /api/cases/documents` - Upload case documents
- `GET /api/cases/documents?case_id={id}` - Get case documents

### **3. Beautiful Frontend Form**
✅ **Features:**
- Modern, responsive design
- Field validation
- Auto-generate case numbers
- File upload with preview
- Amount formatting for TZS
- Date pickers
- Dropdown selections
- Real-time form validation
- Success/Error messages

---

## 🚀 Setup Instructions

### **Step 1: Update Database Schema**

Run the SQL update script:

```bash
mysql -u root -p < update-case-schema.sql
```

**Or using phpMyAdmin:**
1. Open http://localhost/phpmyadmin
2. Select `case_management_db`
3. Click "Import"
4. Choose `update-case-schema.sql`
5. Click "Go"

**What this does:**
- Drops old cases table
- Creates new table with all required fields
- Adds case_documents table
- Inserts sample data

### **Step 2: Restart Case Service**

```powershell
# Stop existing service
Get-Process php | Where-Object {$_.CommandLine -like '*8008*'} | Stop-Process

# Start updated service
cd "c:\xampp\htdocs\scheduling management system\microservices\case-service"
php -S 0.0.0.0:8008 index.php
```

### **Step 3: Restart Frontend**

```cmd
cd "c:\xampp\htdocs\scheduling management system\frontend"
npm start
```

The app should auto-reload. If not, press Ctrl+F5 to hard refresh.

---

## 📱 How to Use

### **Access Create Case Form:**

**Option 1: From Case Management**
1. Go to http://localhost:3000/legal/cases
2. Click **"➕ Create New Case"** button (top right)

**Option 2: Direct URL**
```
http://localhost:3000/legal/cases/create
```

**Note:** Only users with **lawyer** or **admin** role can create cases.

---

## 📝 Form Fields Explained

### **Required Fields (marked with *):**

| Field | Type | Description |
|-------|------|-------------|
| **Case Number*** | Text | Unique identifier (can auto-generate) |
| **Parties*** | Textarea | All parties involved |
| **Nature of Case*** | Dropdown | Civil, Criminal, or Bankruptcy |

### **Optional Fields:**

| Field | Type | Description |
|-------|------|-------------|
| **Amount in Claim** | Number | Value in TZS Shillings |
| **Date Filed** | Date | When case was filed |
| **Current Status** | Dropdown | Default: Pending Hearing |
| **Next Hearing Date** | Date | Scheduled hearing |
| **Any Appeal** | Dropdown | Yes, No, or Pending |
| **Assigned Lawyer** | Text | Lawyer handling the case |
| **Remarks** | Textarea | Additional notes |
| **Upload Documents** | Files | Multiple files supported |

---

## 💡 Form Features

### **1. Auto-Generate Case Number**
Click **"Generate"** button next to Case Number field:
- Format: `CASE-YYYY-XXXX`
- Example: `CASE-2024-1234`
- Ensures uniqueness

### **2. Amount in Claim**
- Enter amount in TZS (Tanzania Shillings)
- Example: `50000000` for 50 million TZS
- Can be left empty for criminal cases
- Automatically formatted with decimals

### **3. Date Pickers**
- Modern calendar interface
- Optional fields
- Date Filed: When case was registered
- Next Hearing: Future court date

### **4. Status Dropdown**
Options:
- **Pending Hearing** (default)
- **Pending Appeal**
- **Active**
- **Closed**
- **Completed**

### **5. Appeal Status**
- **No** (default) - No appeal filed
- **Yes** - Appeal in progress
- **Pending** - Appeal decision pending

### **6. Document Upload**
- **Supported formats:** PDF, DOC, DOCX, JPG, PNG, XLSX
- **Multiple files:** Upload several at once
- **File preview:** See selected files before submission
- **Size display:** Shows file sizes

---

## 🎨 Example: Creating a Case

### **Civil Case Example:**

```
Case Number: CASE-2024-1056
Parties: John Smith (Plaintiff) vs. Acme Corporation (Defendant)
Nature of Case: Civil
Amount in Claim: 50000000
Date Filed: 2024-10-25
Current Status: Pending Hearing
Next Hearing Date: 2024-11-15
Any Appeal: No
Assigned Lawyer: Sarah Johnson
Remarks: Property dispute regarding land ownership in Kinondoni, Dar es Salaam
Documents: [complaint.pdf, evidence-photos.zip, property-deed.pdf]
```

### **Criminal Case Example:**

```
Case Number: CASE-2024-1057
Parties: The Republic vs. Michael Doe
Nature of Case: Criminal
Amount in Claim: [leave empty]
Date Filed: 2024-10-25
Current Status: Pending Hearing
Next Hearing Date: 2024-11-05
Any Appeal: No
Assigned Lawyer: Mike Davis
Remarks: Criminal assault case, trial scheduled
Documents: [police-report.pdf, witness-statements.pdf]
```

### **Bankruptcy Case Example:**

```
Case Number: CASE-2024-1058
Parties: ABC Bank (Creditor) vs. XYZ Limited (Debtor)
Nature of Case: Bankruptcy
Amount in Claim: 150000000
Date Filed: 2024-10-20
Current Status: Pending Hearing
Next Hearing Date: 2024-12-01
Any Appeal: No
Assigned Lawyer: Lisa Chen
Remarks: Corporate bankruptcy proceedings, creditor petition
Documents: [financial-statements.xlsx, petition.pdf, creditor-list.pdf]
```

---

## 🔒 Security & Validation

### **Backend Validation:**
- ✅ Case number must be unique
- ✅ Required fields validated
- ✅ Amount must be numeric (if provided)
- ✅ Dates must be valid format
- ✅ Only lawyers/admins can create cases
- ✅ Activity logged in audit trail

### **Frontend Validation:**
- ✅ Required field indicators (*)
- ✅ Real-time validation
- ✅ Helpful error messages
- ✅ Success confirmation
- ✅ File type restrictions
- ✅ Prevents double submission

---

## 📊 After Creating a Case

### **What Happens:**
1. ✅ Case saved to database
2. ✅ Activity logged: "Case Created"
3. ✅ Audit trail updated
4. ✅ Documents uploaded (if any)
5. ✅ Success message displayed
6. ✅ Auto-redirect to case list after 2 seconds

### **View Created Case:**
- Go to `/legal/cases`
- Find your case in the list
- Click to view full details

---

## 🧪 Testing

### **Test Case Creation:**

**Step 1: Login**
```
URL: http://localhost:3000/login
Email: admin@test.com
Password: 123456
```

**Step 2: Navigate to Create Case**
```
http://localhost:3000/legal/cases/create
```

**Step 3: Fill Form**
- Use "Generate" for case number
- Fill required fields (*)
- Add optional fields as needed
- Upload test documents

**Step 4: Submit**
- Click "✅ Create Case"
- Wait for success message
- Verify redirect to case list

**Step 5: Verify**
- Check case appears in list
- Verify all fields saved correctly
- Check documents uploaded

---

## 🔧 API Reference

### **Create Case Endpoint:**

```http
POST http://localhost:8008/api/cases
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "case_number": "CASE-2024-1056",
  "parties": "John Smith (Plaintiff) vs. Acme Corp (Defendant)",
  "nature_of_case": "Civil",
  "amount_in_claim": 50000000.00,
  "date_filed": "2024-10-25",
  "current_status": "Pending Hearing",
  "next_hearing_date": "2024-11-15",
  "any_appeal": "No",
  "remarks": "Property dispute case",
  "assigned_lawyer": "Sarah Johnson"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Case created successfully",
  "data": {
    "id": 6,
    "case_number": "CASE-2024-1056"
  }
}
```

### **Upload Documents Endpoint:**

```http
POST http://localhost:8008/api/cases/documents
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: multipart/form-data

case_id: 6
document: [file]
```

---

## 📱 Mobile Responsive

The form is fully responsive:
- ✅ Desktop: 2-column layout
- ✅ Tablet: Adjusted spacing
- ✅ Mobile: Single column, full-width buttons
- ✅ Touch-friendly controls
- ✅ Optimized file upload

---

## 🎯 Field Tips

### **Parties Field:**
Be specific and clear:
```
✅ Good: "John Smith (Plaintiff) vs. Acme Corporation (Defendant)"
✅ Good: "The Republic vs. Michael Doe (Accused)"
❌ Avoid: "Smith vs Corp"
```

### **Amount in Claim:**
Use full numbers:
```
✅ Good: 50000000 (50 million TZS)
✅ Good: 2500000 (2.5 million TZS)
❌ Avoid: "50M" or "2.5mil"
```

### **Remarks:**
Include important context:
```
✅ Good: "Property dispute regarding Plot 123, Kinondoni. 
         Previous mediation failed on 2024-09-15."
❌ Avoid: "land case"
```

---

## 🚨 Troubleshooting

### **"Case number already exists"**
**Solution:** Use "Generate" button for unique number or check existing cases

### **"Required fields missing"**
**Solution:** Fill all fields marked with red asterisk (*)

### **"Upload failed"**
**Solution:** Check file format (PDF, DOC, JPG, PNG, XLSX only)

### **"Unauthorized"**
**Solution:** Login with lawyer or admin account

### **Form not appearing**
**Solution:** Check you're logged in and have correct role

---

## 📊 Database Schema

### **Cases Table Structure:**

```sql
CREATE TABLE cases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_number VARCHAR(50) UNIQUE NOT NULL,
    parties TEXT NOT NULL,
    nature_of_case ENUM('Civil', 'Criminal', 'Bankruptcy') NOT NULL,
    amount_in_claim DECIMAL(15, 2) NULL,
    date_filed DATE NULL,
    current_status ENUM('Pending Appeal', 'Pending Hearing', 'Active', 'Closed', 'Completed'),
    next_hearing_date DATE NULL,
    any_appeal ENUM('Yes', 'No', 'Pending') DEFAULT 'No',
    remarks TEXT NULL,
    assigned_lawyer VARCHAR(255) NULL,
    created_by VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 🎉 Summary

### **Created:**
✅ Updated database schema with all fields
✅ Backend API with validation
✅ Beautiful responsive form
✅ Document upload capability
✅ Auto-generate case numbers
✅ Role-based access control
✅ Audit trail logging
✅ Complete documentation

### **Features:**
✅ 10 input fields (3 required, 7 optional)
✅ Multiple file upload
✅ TZS amount formatting
✅ Date pickers
✅ Dropdown selections
✅ Real-time validation
✅ Success/Error handling
✅ Mobile responsive

### **Ready For:**
✅ Production use
✅ Create civil cases
✅ Create criminal cases
✅ Create bankruptcy cases
✅ Upload case documents
✅ Track case status
✅ Monitor appeals

---

**Your case creation system is ready to use!** 🎊

**Access it now:** http://localhost:3000/legal/cases/create

**Need help?** All fields have helpful tooltips and the form includes a complete field guide at the bottom!
