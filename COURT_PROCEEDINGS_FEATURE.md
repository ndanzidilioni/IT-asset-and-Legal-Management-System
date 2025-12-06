# Court Proceedings Tracking Feature

## Overview
A comprehensive court proceedings tracking system that records all hearing activities for each legal case. This feature allows lawyers to maintain detailed records of all court appearances, proceedings, orders, and next hearing dates.

## Features

### 📋 Track Court Activities
- Record multiple court proceedings for each case
- Automatic case information pre-filling
- Chronological ordering of proceedings
- Full audit trail with timestamps

### 📝 Detailed Form Fields
1. **Hearing Date** (Required) - Date of the court hearing
2. **Name of Court** - Which court the hearing took place
3. **Parties** - All parties involved (auto-filled from case)
4. **Case No** - Case reference number (auto-filled from case)
5. **Court/Judge** - Name of the presiding judge
6. **Clerk/Karani** - Court clerk's name
7. **Advocate for Opponent** - Opponent's legal representative
8. **Advocate for MOI** - MOI's legal representative
9. **Proceedings** - Detailed description of what happened in court
10. **Order** - Court's order or ruling
11. **Next Date** - Next hearing/appearance date (auto-updates case hearing date)
12. **Remarks** - Additional notes or observations

### 🔄 Automatic Updates
- When you set a "Next Date", the main case's hearing date automatically updates
- Maintains full history of all past proceedings

## Database Schema

### Table: `court_proceedings`
```sql
CREATE TABLE IF NOT EXISTS `court_proceedings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` BIGINT UNSIGNED NOT NULL,
  `hearing_date` DATE NOT NULL,
  `name_of_court` VARCHAR(255) NULL,
  `parties` VARCHAR(500) NULL,
  `case_number` VARCHAR(100) NULL,
  `court_judge` VARCHAR(255) NULL,
  `clerk_karani` VARCHAR(255) NULL,
  `advocate_for_opponent` VARCHAR(255) NULL,
  `advocate_for_moi` VARCHAR(255) NULL,
  `proceedings` TEXT NULL,
  `court_order` TEXT NULL,
  `next_date` DATE NULL,
  `remarks` TEXT NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

## Installation Steps

### 1. Run Database Migration
Execute the SQL migration file:
```bash
mysql -u root -p scheduling_management_system < "Backend/database/migrations/create_court_proceedings_table.sql"
```

Or manually in phpMyAdmin/MySQL Workbench:
- Open `Backend/database/migrations/create_court_proceedings_table.sql`
- Execute the SQL commands

### 2. Backend Files Created
- ✅ `Backend/app/Models/CourtProceeding.php` - Model
- ✅ `Backend/app/Http/Controllers/CourtProceedingController.php` - Controller
- ✅ Updated `Backend/routes/api.php` - API routes
- ✅ Updated `Backend/app/Models/LegalCase.php` - Added relationship

### 3. Frontend Files Created
- ✅ `frontend/src/components/Legal/CourtProceedingsForm.js` - Main component
- ✅ Updated `frontend/src/App.js` - Added route
- ✅ Updated `frontend/src/components/Legal/ViewCase.js` - Added button
- ✅ Updated `frontend/src/components/Legal/CaseManagement.js` - Added button

## API Endpoints

### Get All Proceedings
```
GET /api/court-proceedings
GET /api/court-proceedings?case_id=1
```

### Get Proceedings by Case ID
```
GET /api/court-proceedings/case/{caseId}
```

### Create New Proceeding
```
POST /api/court-proceedings
Content-Type: application/json

{
  "case_id": 1,
  "hearing_date": "2024-11-09",
  "name_of_court": "High Court of Tanzania",
  "parties": "John Doe vs ABC Corp",
  "case_number": "HC/123/2024",
  "court_judge": "Hon. Justice Mwakasege",
  "clerk_karani": "Mr. Hassan",
  "advocate_for_opponent": "Advocate Juma",
  "advocate_for_moi": "Advocate Bakari",
  "proceedings": "The case was called...",
  "court_order": "Matter adjourned to...",
  "next_date": "2024-12-15",
  "remarks": "Additional notes"
}
```

### Update Proceeding
```
PUT /api/court-proceedings/{id}
```

### Delete Proceeding
```
DELETE /api/court-proceedings/{id}
```

## How to Use

### Access Court Proceedings

**Option 1: From Case View Page**
1. Go to Legal Management → Cases
2. Click "View" on any case
3. Click the "⚖️ Court Proceedings" button

**Option 2: From Case List**
1. Go to Legal Management → Cases
2. Click "⚖️ Proceedings" button on any case card

### Add New Court Proceeding

1. Click "➕ Add New Court Proceeding" button
2. Fill in the form:
   - **Required:** Hearing Date
   - **Optional:** All other fields
3. The case information (parties, case number, court name) is pre-filled
4. Enter proceedings, orders, and next hearing date
5. Click "✅ Save Proceeding"

### View Proceedings History

- All proceedings are displayed in chronological order (newest first)
- Each proceeding shows:
  - Date of hearing
  - Court and judge information
  - Advocates for both parties
  - Full proceedings text
  - Court orders
  - Next hearing date
  - Remarks
  - Timestamp when recorded

### Delete a Proceeding

- Click the "🗑️ Delete" button on any proceeding
- Confirm the deletion
- The proceeding will be permanently removed

## User Interface

### Main Features
- 📅 Chronological timeline view
- 🔍 Easy-to-read card layout
- ✏️ Quick add/edit functionality
- 🗑️ Delete with confirmation
- 📊 Full case context displayed at top
- ⏱️ Audit timestamps on all records

### Navigation
- **Back to Cases** button to return to case list
- Breadcrumb navigation showing case context
- Direct links between case details and proceedings

## Use Cases

1. **During Court Hearings**
   - Record real-time proceedings
   - Note judge's orders
   - Capture next hearing dates

2. **Post-Hearing Documentation**
   - Complete detailed proceedings
   - Add remarks and observations
   - Update case status

3. **Case Review**
   - Review full hearing history
   - Track case progression
   - Prepare for upcoming hearings

4. **Legal Reporting**
   - Generate hearing reports
   - Track advocate performance
   - Monitor case timelines

## Security

- All endpoints require authentication (`auth:sanctum` middleware)
- Only lawyers and admins can access
- Full audit logging for all actions
- User tracking (created_by field)

## Future Enhancements

- [ ] Export proceedings to PDF
- [ ] Email notifications for next hearing dates
- [ ] Calendar integration
- [ ] Attach documents to specific proceedings
- [ ] Advanced search and filtering
- [ ] Bulk operations

## Support

For issues or questions:
1. Check the database migration is complete
2. Verify API routes are registered
3. Ensure user has lawyer/admin role
4. Check browser console for errors

---

**Version:** 1.0  
**Last Updated:** November 9, 2025  
**Created By:** Development Team
