# 📊 Asset Import Template Guide

## 📋 Excel/CSV Template Format

Use this template to import multiple assets at once. The system supports CSV format which can be opened in Excel.

### 📁 Template File
- **File**: `Backend/asset_import_template.csv`
- **Format**: CSV (Comma Separated Values)
- **Encoding**: UTF-8

## 📝 Required Fields

### **Basic Information (Required)**
| Field | Description | Example | Notes |
|-------|-------------|---------|-------|
| `asset_number` | Unique asset identifier | IT2025010001 | **Required** - Must be unique |
| `asset_description` | Asset description | Dell OptiPlex Desktop | **Required** |
| `asset_category` | Asset category | compute, printer, scanner, switch | **Required** - Use predefined categories |
| `building` | Building name | Main Building | **Required** |
| `floor` | Floor location | 1st Floor, 2nd Floor, Ground Floor | **Required** |
| `department` | Department name | IT Department, HR Department | **Required** |
| `room` | Room identifier | Room 101, Conference Room A | **Required** |
| `condition` | Asset condition | excellent, good, fair, poor, damaged | **Required** |
| `status` | Asset status | active, inactive, maintenance, disposed | **Required** |

### **Optional Fields**
| Field | Description | Example | Notes |
|-------|-------------|---------|-------|
| `notes` | General notes | Primary workstation for IT support | Optional |
| `brand` | Asset brand | Dell, HP, Lenovo, Cisco | Optional |
| `model` | Asset model | OptiPlex 7090, LaserJet Pro 400 | Optional |
| `serial_number` | Serial number | ABC123456 | Optional |
| `processor` | Processor details | Intel i7-11700 | Optional |
| `memory` | Memory specification | 16GB, 8GB | Optional |
| `storage` | Storage details | 512GB SSD, 256GB SSD | Optional |
| `operating_system` | OS information | Windows 11 Pro, Linux | Optional |
| `ip_address` | IP address | 192.168.1.100 | Optional |
| `mac_address` | MAC address | 00:1B:44:11:3A:B7 | Optional |
| `deployment_date` | Deployment date | 2025-01-15 | Format: YYYY-MM-DD |
| `last_maintenance_date` | Last maintenance | 2025-01-05 | Format: YYYY-MM-DD |
| `next_maintenance_date` | Next maintenance | 2025-07-15 | Format: YYYY-MM-DD |
| `assigned_to` | Assigned person | John Smith, Sarah Johnson | Optional |
| `location_details` | Additional location info | IT Support Desk, HR Office | Optional |
| `network_zone` | Network zone | Internal, DMZ, External | Optional |
| `is_critical` | Critical asset flag | true, false | Optional |
| `backup_status` | Backup status | Enabled, Disabled, Not Applicable | Optional |
| `technical_notes` | Technical notes | Latest security patches applied | Optional |


## 📊 Sample Data

### **Row 1 - Desktop Computer:**
```csv
IT2025010001,Dell OptiPlex Desktop,Main Building,1st Floor,IT Department,Room 101,excellent,active,Primary workstation for IT support,Dell,OptiPlex 7090,ABC123456,Intel i7-11700,16GB,512GB SSD,Windows 11 Pro,192.168.1.100,00:1B:44:11:3A:B7,2025-01-15,,2025-07-15,John Smith,IT Support Desk,Internal,true,Enabled,Latest security patches applied
```

### **Row 2 - Network Printer:**
```csv
IT2025010002,HP LaserJet Printer,Main Building,1st Floor,HR Department,Room 102,good,active,Network printer for HR documents,HP,LaserJet Pro 400,XYZ789012,,,,,192.168.1.101,00:1B:44:11:3A:B8,2025-01-10,2025-01-05,2025-04-05,Sarah Johnson,HR Office,Internal,false,Not Applicable,Monthly maintenance scheduled
```

### **Row 3 - Document Scanner:**
```csv
IT2025010003,Canon Document Scanner,Main Building,2nd Floor,Finance Department,Room 201,excellent,active,High-speed document scanner,Canon,DR-G2140,DEF345678,,,,,192.168.1.102,00:1B:44:11:3A:B9,2025-01-12,,2025-07-12,Mike Wilson,Finance Office,Internal,true,Enabled,Regular cleaning required
```

### **Row 4 - Network Switch:**
```csv
IT2025010004,Cisco Managed Switch,Main Building,Basement,IT Department,Server Room,excellent,active,Core network switch,Cisco,Catalyst 2960,GHI456789,,,,,192.168.1.1,00:1B:44:11:3A:BA,2025-01-08,2025-01-01,2025-04-01,Network Admin,Data Center,DMZ,true,Enabled,Critical network infrastructure
```

## 🚀 How to Import

### **Step 1: Prepare Your Data**
1. Download the template: `Backend/asset_import_template.csv`
2. Open in Excel or any spreadsheet application
3. Fill in your asset data following the format
4. Save as CSV format

### **Step 2: Import via Web Interface**
1. Go to the IT Assets page
2. Click "Import CSV" button
3. Select your CSV file
4. Click "Import" to upload

### **Step 3: Verify Import**
1. Check the import results
2. Review any errors or warnings
3. Verify assets appear in the asset list

## ⚠️ Important Notes

### **Data Validation:**
- **Asset Numbers**: Must be unique across all assets
- **Required Fields**: Cannot be empty
- **Date Format**: Use YYYY-MM-DD format
- **Boolean Values**: Use `true` or `false` for is_critical
- **Categories**: Must use predefined category values

### **Common Errors to Avoid:**
- ❌ Duplicate asset numbers
- ❌ Empty required fields
- ❌ Invalid date formats
- ❌ Invalid category names
- ❌ Missing commas in CSV

### **Tips for Success:**
- ✅ Use the provided template as a starting point
- ✅ Test with a few rows first
- ✅ Double-check asset numbers for uniqueness
- ✅ Use consistent naming for departments and rooms
- ✅ Save as UTF-8 encoded CSV

## 📞 Support

If you encounter issues with the import:
1. Check the error messages in the import results
2. Verify your CSV format matches the template
3. Ensure all required fields are filled
4. Check that asset numbers are unique

The system will show detailed error messages for any validation failures.
