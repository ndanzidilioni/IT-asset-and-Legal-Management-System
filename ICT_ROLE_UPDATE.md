# ICT Role Update - Developer to ICT

## Overview
The system has been updated to change the "Developer" role to "ICT" (Information and Communication Technology) with access restricted to IT Asset management only.

## Changes Made

### 1. **Database Changes**

#### Role Enum Update
```sql
-- Updated users table role enum
ALTER TABLE users 
MODIFY role ENUM('admin','ict','client','user','lawyer') 
NOT NULL DEFAULT 'user';

-- Updated existing developer users to ICT
UPDATE users SET role='ict' WHERE role='developer';
UPDATE users SET role='ict' WHERE username='developer';
```

### 2. **Backend Changes**

#### UserController.php
**Location:** `Backend/app/Http/Controllers/UserController.php`

- Updated validation rules to accept 'ict' instead of 'developer'
- Changed from: `in:admin,user,developer,client,lawyer`
- Changed to: `in:admin,user,ict,client,lawyer`

#### User.php Model
**Location:** `Backend/app/Models/User.php`

Updated available privileges to include granular IT Asset permissions:
- ✅ `view_assets` - View IT Assets
- ✅ `edit_assets` - Edit IT Assets  
- ✅ `delete_assets` - Delete IT Assets
- ❌ Removed `developer_access`

### 3. **Frontend Changes**

#### Login.js
**Location:** `frontend/src/components/Login.js`

Updated role-based routing:
```javascript
case 'ict':
  window.location.href = '/it-assets';  // Redirect to IT Assets
  break;
```

#### App.js
**Location:** `frontend/src/App.js`

- Updated `RoleBasedRoute` redirects for ICT role
- Changed IT Assets route access:
  ```javascript
  <Route path="/it-assets" 
    element={<RoleBasedRoute allowedRoles={['admin', 'ict']}>
      <ITAssetReport />
    </RoleBasedRoute>} 
  />
  ```
- Removed `/developer` route

#### NavBar.js
**Location:** `frontend/src/components/NavBar.js`

Updated navigation to show:
- **Admin users:** Dashboard + IT Assets + Legal Management + Admin Panel
- **ICT users:** IT Assets only
- **Lawyer users:** Legal Management only
- **Client users:** Client Dashboard only

#### AdminPanel.js
**Location:** `frontend/src/components/AdminPanel.js`

Updated role dropdown in user creation form:
```html
<option value="ict">ICT</option>
```

## Role Access Matrix

| Role   | Can Access                        | Primary Dashboard |
|--------|-----------------------------------|-------------------|
| Admin  | Everything (all modules)          | `/dashboard`      |
| ICT    | IT Assets only                    | `/it-assets`      |
| Lawyer | Legal Management only             | `/legal`          |
| Client | Client Portal only                | `/client`         |
| User   | Based on assigned privileges      | `/dashboard`      |

## ICT Role Permissions

### Default Access
- ✅ **IT Asset Register** - Full access to view, add, edit IT assets

### Optional Permissions (Assignable by Admin)
Admins can grant ICT users additional permissions:

| Permission      | Description                  |
|----------------|------------------------------|
| `view_assets`  | View IT Assets (default)     |
| `edit_assets`  | Create/Edit IT Assets        |
| `delete_assets`| Delete IT Assets             |
| `export_data`  | Export IT Assets to CSV      |
| `import_data`  | Import IT Assets from CSV    |
| `view_reports` | View IT Asset Reports        |

### Restricted Access
ICT users **cannot** access:
- ❌ Admin Panel
- ❌ User Management
- ❌ Legal Management System
- ❌ Task Management
- ❌ Schedule Management
- ❌ Main Dashboard (Admin only)

## How to Create an ICT User

1. **Login as Admin**
2. **Go to Admin Panel** → Users tab
3. **Click "+ Add New User"**
4. **Fill in details:**
   ```
   First Name: John
   Last Name: Smith
   Email: john.smith@company.com
   Username: jsmith
   Password: SecurePass123!
   Role: ICT ← Select this
   Status: Active
   ```
5. **Select Permissions (Optional):**
   - ☑ View IT Assets
   - ☑ Edit IT Assets
   - ☑ Delete IT Assets
   - ☑ Export Data
6. **Click "Create User"**

## Example ICT User Configuration

### Basic ICT User (View Only)
```json
{
  "role": "ict",
  "privileges": [
    "view_assets"
  ]
}
```
**Can:** View IT assets, browse asset register  
**Cannot:** Add, edit, or delete assets

### Standard ICT User (Full Asset Management)
```json
{
  "role": "ict",
  "privileges": [
    "view_assets",
    "edit_assets",
    "delete_assets",
    "export_data"
  ]
}
```
**Can:** Complete IT asset management, export reports  
**Cannot:** Access other system modules

### Senior ICT User (Advanced)
```json
{
  "role": "ict",
  "privileges": [
    "view_assets",
    "edit_assets",
    "delete_assets",
    "export_data",
    "import_data",
    "view_reports"
  ]
}
```
**Can:** Full IT asset operations, bulk import, reporting  
**Cannot:** Manage users, access admin functions

## Testing the Changes

### Test 1: ICT User Login Redirect
1. Create or update a user with role = "ICT"
2. Login with ICT credentials
3. **Expected:** Automatically redirected to `/it-assets`

### Test 2: ICT User Access Restrictions
1. Login as ICT user
2. Try to navigate to `/admin`
3. **Expected:** Redirected back to `/it-assets`
4. Try to navigate to `/legal`
5. **Expected:** Redirected back to `/it-assets`
6. Try to navigate to `/dashboard`
7. **Expected:** Redirected back to `/it-assets`

### Test 3: Navigation Bar
1. Login as ICT user
2. Check navigation bar
3. **Expected:** Only "IT Assets" link visible (no Dashboard, Legal, or Admin links)

### Test 4: Admin Creating ICT User
1. Login as admin
2. Go to Admin Panel → Users
3. Click "Add New User"
4. Check Role dropdown
5. **Expected:** "ICT" option available instead of "Developer"

## Migration Notes

### Existing Developer Users
All existing users with role = "developer" have been automatically updated to role = "ict".

If you had developer users in your system:
- Their login credentials remain the same
- Their username/email remains the same
- Their role has changed from "developer" → "ict"
- They now redirect to `/it-assets` instead of `/developer`

### Developer Panel
The Developer Panel (`/developer` route) has been removed as it's no longer needed for the ICT role.

## Security & Access Control

### Route Protection
All routes are protected with `RoleBasedRoute` component:
```javascript
// Only admin and ICT can access IT Assets
<Route path="/it-assets" 
  element={<RoleBasedRoute allowedRoles={['admin', 'ict']}>
```

### Automatic Redirects
Users attempting to access unauthorized routes are automatically redirected to their role-appropriate dashboard without error messages.

## Database Verification

### Check ICT Users
```sql
SELECT id, username, email, role, status 
FROM users 
WHERE role='ict';
```

### Check User Privileges
```sql
SELECT id, username, role, privileges 
FROM users 
WHERE role='ict';
```

## Files Modified

### Backend (PHP/Laravel)
1. ✅ `app/Http/Controllers/UserController.php` - Updated validation
2. ✅ `app/Models/User.php` - Updated privileges list
3. ✅ `scheduling` database - Updated role enum

### Frontend (React)
1. ✅ `components/Login.js` - Updated login redirect
2. ✅ `components/App.js` - Updated routes and role access
3. ✅ `components/NavBar.js` - Updated navigation menu
4. ✅ `components/AdminPanel.js` - Updated role dropdown

## Troubleshooting

### Issue: User still sees "Developer" in dropdown
**Solution:** Clear browser cache and hard refresh (Ctrl+Shift+R)

### Issue: ICT user can't login
**Solution:** Check that user's role in database is exactly 'ict' (lowercase)

### Issue: ICT user redirects to wrong page
**Solution:** User must logout and login again after role change

---
**Migration Date:** November 4, 2025  
**Updated By:** System Administrator  
**Status:** ✅ Complete
