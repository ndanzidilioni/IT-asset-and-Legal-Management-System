# User Management & Permissions System Guide

## Overview
The system now has a comprehensive user management and permission system that allows administrators to create users, assign roles, and grant granular permissions (view, edit, delete, etc.).

## Features Implemented

### 1. **Complete User Management**
Admins can:
- ✅ Create new users
- ✅ Edit existing users
- ✅ Delete users
- ✅ Assign roles (Admin, Lawyer, Developer, Client, User)
- ✅ Assign granular permissions/privileges
- ✅ Set user status (Active/Inactive)

### 2. **Granular Permissions System**

Available permissions include:

| Permission Key      | Description              | Use Case                           |
|--------------------|--------------------------|------------------------------------|
| `manage_users`     | Manage Users             | Create, edit, delete users         |
| `manage_tasks`     | Manage Tasks             | Create, assign, update tasks       |
| `manage_assets`    | Manage IT Assets         | Create, edit IT asset records      |
| `view_reports`     | View Reports             | Access inquiry and system reports  |
| `manage_schedules` | Manage Schedules         | Create and manage schedules        |
| `developer_access` | Developer Access         | Access developer panel             |
| `client_access`    | Client Access            | Access client panel                |
| `admin_panel`      | Admin Panel Access       | Access admin configuration panel   |
| `export_data`      | Export Data              | Export data to CSV/Excel           |
| `import_data`      | Import Data              | Import data from CSV/Excel         |

### 3. **Role-Based Access Control (RBAC)**

System supports 5 user roles:

#### **Admin Role**
- **Full system access**
- All permissions automatically granted
- Can manage other users
- Access to all modules

#### **Lawyer Role**  
- Access to Legal Management System
- Can be granted specific permissions:
  - View legal reports
  - Manage cases
  - Manage clients
  - Manage contracts

#### **Developer Role**
- Access to Developer Panel
- Can view assigned tasks
- Requires `developer_access` permission

#### **Client Role**
- Access to Client Panel
- Can submit task requests
- Requires `client_access` permission

#### **User Role**
- Basic user access
- Permissions assigned individually by admin

## How to Use

### Creating a New User (Admin)

1. **Login as Admin**
2. **Navigate to Admin Panel** (`/admin`)
3. **Click "Users" tab**
4. **Click "+ Add New User"**
5. **Fill in the form:**
   - First Name, Last Name (required)
   - Middle Name (optional)
   - Email (required, unique)
   - Username (required, unique)
   - Password (required, min 8 characters)
   - **Role:** Select from dropdown (Admin, Lawyer, Developer, Client, User)
   - **Status:** Active or Inactive
   - **Permissions & Privileges:** Check boxes for specific permissions

6. **Click "Create User"**

### Editing User Permissions

1. **Go to Admin Panel > Users tab**
2. **Click "Edit" on the user row**
3. **Update Permissions:**
   - Check/uncheck permissions as needed
   - Selected count shown at bottom
4. **Click "Update User"**

### Permission Examples

#### Example 1: Legal Assistant (Lawyer Role)
```
Role: Lawyer
Permissions:
☑ View Reports
☑ Manage Tasks  
☐ Manage Users (not allowed)
☐ Admin Panel (not allowed)
☐ Export Data
☐ Import Data
```

#### Example 2: IT Manager (User Role)
```
Role: User
Permissions:
☑ Manage IT Assets
☑ View Reports
☑ Export Data
☑ Manage Schedules
☐ Manage Users (not admin)
```

#### Example 3: Developer (Developer Role)
```
Role: Developer
Permissions:
☑ Developer Access (required)
☑ Manage Tasks
☑ View Reports
☐ Manage Users
☐ Admin Panel
```

## API Endpoints

### User Management

```http
GET    /api/users                          # List all users
POST   /api/users                          # Create new user
PUT    /api/users/{id}                     # Update user
DELETE /api/users/{id}                     # Delete user
```

### Privileges Management

```http
GET    /api/users/privileges/available      # Get available privileges
PUT    /api/users/{id}/privileges           # Update user privileges
POST   /api/users/{id}/reset-password       # Reset user password (admin)
POST   /api/users/{id}/unlock               # Unlock locked account
```

### Request Examples

#### Create User with Permissions
```json
POST /api/users
{
  "fname": "John",
  "mname": "M",
  "lname": "Doe",
  "email": "john@example.com",
  "username": "johndoe",
  "password": "SecurePass123",
  "role": "lawyer",
  "status": "active",
  "privileges": ["view_reports", "manage_tasks", "export_data"]
}
```

#### Update User Privileges
```json
PUT /api/users/5/privileges
{
  "privileges": ["view_reports", "manage_tasks", "manage_assets", "export_data"]
}
```

## Security Features

### 1. **Password Policy**
- Minimum 8 characters
- Must contain uppercase letter
- Must contain lowercase letter
- Must contain number
- Must contain special character

### 2. **Account Protection**
- Failed login attempts tracked
- Account auto-locks after 5 failed attempts
- Lock duration: 30 minutes
- Admin can manually unlock accounts

### 3. **Force Password Change**
- New users must change password on first login
- Admin-reset passwords require change on next login

### 4. **Self-Protection**
- Admins cannot deactivate their own account
- Admins cannot delete their own account

## Frontend Components

### AdminPanel.js
**Location:** `frontend/src/components/AdminPanel.js`

**Features:**
- User listing with role and privilege badges
- User creation/edit form with:
  - Role dropdown (all 5 roles)
  - Permission checkboxes (10 permissions)
  - Status toggle
- Real-time privilege count display
- Visual privilege summary in user table

### User Table Columns
| Column     | Description                              |
|-----------|------------------------------------------|
| Name      | Full name (First, Middle, Last)          |
| Email     | User email address                       |
| Username  | Login username                           |
| Role      | Color-coded role badge                   |
| Status    | Active/Inactive badge                    |
| Privileges| Count + first 2 privileges               |
| Created   | Account creation date                    |
| Actions   | Edit and Delete buttons                  |

## Database Schema

### Users Table
```sql
users (
  id                    BIGINT PRIMARY KEY,
  fname                 VARCHAR(255) NOT NULL,
  mname                 VARCHAR(255),
  lname                 VARCHAR(255) NOT NULL,
  email                 VARCHAR(255) UNIQUE NOT NULL,
  username              VARCHAR(255) UNIQUE NOT NULL,
  password              VARCHAR(255) NOT NULL,
  role                  ENUM('admin','developer','client','user','lawyer'),
  status                ENUM('active','inactive') DEFAULT 'active',
  privileges            LONGTEXT,  -- JSON array of permission keys
  password_changed_at   TIMESTAMP,
  must_change_password  TINYINT(1) DEFAULT 0,
  failed_login_attempts INT DEFAULT 0,
  locked_until          TIMESTAMP NULL,
  created_at            TIMESTAMP,
  updated_at            TIMESTAMP
)
```

### Privileges Storage
Stored as JSON array in `privileges` column:
```json
["manage_users", "view_reports", "export_data"]
```

## Testing the System

### Test Scenario 1: Create Lawyer with Limited Permissions
1. Login as admin
2. Create user with role = "Lawyer"
3. Grant only: "View Reports", "Export Data"
4. Login as the new lawyer user
5. Verify: Can access `/legal` but NOT `/admin` or `/it-assets`

### Test Scenario 2: Grant IT Asset Access
1. Create user with role = "User"
2. Grant: "Manage IT Assets", "View Reports"
3. Login as this user
4. Verify: Can access IT Asset module with limited permissions

### Test Scenario 3: Permission Enforcement
1. Create user WITHOUT "Manage Users" permission
2. Try to access user management API
3. Verify: Receives 403 Forbidden error

## Code Examples

### Check Permission in Backend (PHP)
```php
// In any controller
$user = $request->user();

if (!$user->hasPrivilege('manage_assets')) {
    return response()->json([
        'message' => 'You do not have permission to manage assets'
    ], 403);
}

// Continue with asset management...
```

### Check Permission in Frontend (JavaScript)
```javascript
// Get user from localStorage
const userInfo = JSON.parse(localStorage.getItem('userInfo'));

// Check if user has specific privilege
const canManageAssets = userInfo.privileges?.includes('manage_assets');

if (!canManageAssets) {
  alert('You do not have permission to perform this action');
  return;
}
```

## Files Modified

### Backend
1. `app/Models/User.php` - Already had privilege methods
2. `app/Http/Controllers/UserController.php` - Updated to handle privileges
3. `routes/api.php` - Added privilege management routes

### Frontend
1. `components/AdminPanel.js` - Added privilege management UI

## Best Practices

1. **Principle of Least Privilege**
   - Grant only necessary permissions
   - Start with minimal access, add as needed

2. **Regular Audits**
   - Review user permissions quarterly
   - Remove inactive users

3. **Role Assignment**
   - Use appropriate roles for job functions
   - Don't make everyone an admin

4. **Permission Documentation**
   - Document why specific permissions were granted
   - Keep track of permission changes

## Troubleshooting

### Issue: Permissions not saving
**Solution:** Check browser console for API errors. Verify user is admin.

### Issue: User can't access after permission grant
**Solution:** User must logout and login again to refresh permissions.

### Issue: Admin role doesn't show permission checkboxes
**Solution:** Admins have all permissions automatically. Checkboxes are cosmetic for admins.

---
**Last Updated:** November 4, 2025  
**Version:** 1.0
