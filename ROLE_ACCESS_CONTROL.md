# Role-Based Access Control (RBAC) Implementation

## Overview
The system now enforces strict role-based access control to prevent users from accessing pages they don't have permission for.

## Problem Solved
Previously, lawyers could manually navigate to admin-only pages like `/it-assets` by typing the URL directly in the browser. This has been fixed with proper route protection.

## Implementation

### 1. Route Protection Components

#### `RoleBasedRoute` Component
**Location:** `frontend/src/App.js`

This component:
- Checks if user is authenticated
- Verifies user role from localStorage
- Redirects unauthorized users to their appropriate dashboard
- Only renders the page if user has required role

```javascript
const RoleBasedRoute = ({children, allowedRoles}) => {
  const token = localStorage.getItem('token');
  const userInfo = localStorage.getItem('userInfo');
  
  if (!token || !userInfo) {
    return <Navigate to="/login" />;
  }
  
  const user = JSON.parse(userInfo);
  
  if (!allowedRoles.includes(user.role)) {
    // Redirect to appropriate dashboard based on role
    switch(user.role) {
      case 'lawyer':
        return <Navigate to="/legal" />;
      case 'admin':
        return <Navigate to="/dashboard" />;
      case 'developer':
        return <Navigate to="/developer" />;
      case 'client':
        return <Navigate to="/client" />;
      default:
        return <Navigate to="/dashboard" />;
    }
  }
  
  return children;
};
```

#### `DefaultRoute` Component
**Location:** `frontend/src/App.js`

Redirects users to their role-appropriate dashboard when accessing the root path `/`.

### 2. Route Access Matrix

| Route                    | Allowed Roles      | Description                |
|--------------------------|-------------------|----------------------------|
| `/login`                 | Everyone          | Login page                 |
| `/register`              | Everyone          | Registration page          |
| `/dashboard`             | `admin`           | Admin/IT Dashboard         |
| `/it-assets`             | `admin`           | IT Asset Management        |
| `/inquiry-report`        | `admin`           | Inquiry Reports            |
| `/admin`                 | `admin`           | Admin Panel                |
| `/create-task`           | `admin`           | Create Task                |
| `/developer`             | `developer`       | Developer Panel            |
| `/client`                | `client`          | Client Panel               |
| `/legal/*`               | `lawyer`, `admin` | Legal Management System    |
| `/legal/cases`           | `lawyer`, `admin` | Case Management            |
| `/legal/contracts`       | `lawyer`, `admin` | Contract Register          |
| `/legal/clients`         | `lawyer`, `admin` | Client Management          |
| `/legal/billing`         | `lawyer`, `admin` | Billing Dashboard          |
| `/legal/court-schedule`  | `lawyer`, `admin` | Court Scheduling           |
| `/legal/analytics`       | `lawyer`, `admin` | Legal Analytics            |

### 3. Navigation Bar (NavBar)

The navigation bar is now role-aware and only shows menu items the user has access to:

**Admin sees:**
- Dashboard
- IT Assets
- Legal Management
- Admin Panel

**Lawyer sees:**
- Legal Management (only)

**Developer sees:**
- Developer Dashboard

**Client sees:**
- Client Dashboard

### 4. Automatic Redirects

When a user tries to access a page they don't have permission for:

| User Role  | Tries to Access | Redirected To |
|-----------|-----------------|---------------|
| lawyer    | `/it-assets`    | `/legal`      |
| lawyer    | `/dashboard`    | `/legal`      |
| lawyer    | `/admin`        | `/legal`      |
| admin     | `/developer`    | `/dashboard`  |
| developer | `/it-assets`    | `/developer`  |
| client    | `/admin`        | `/client`     |

## Testing the Implementation

### Test 1: Lawyer Cannot Access IT Assets
1. **Login as lawyer:**
   - Username: `lawyer`
   - Password: `password`
2. **Try to navigate to:** `http://localhost:3000/it-assets`
3. **Expected result:** Automatically redirected to `/legal`

### Test 2: Admin Can Access Everything
1. **Login as admin:**
   - Username: `admin`
   - Password: `password`
2. **Navigate to:** `http://localhost:3000/it-assets`
3. **Expected result:** IT Assets page loads successfully
4. **Navigate to:** `http://localhost:3000/legal`
5. **Expected result:** Legal Management page loads successfully

### Test 3: Navigation Bar Updates
1. **Login as lawyer**
2. **Check navigation bar**
3. **Expected result:** Only "⚖️ Legal Management" link is visible
4. **Logout and login as admin**
5. **Check navigation bar**
6. **Expected result:** "Dashboard", "IT Assets", "⚖️ Legal Management", and "Admin Panel" links are visible

### Test 4: Default Route Redirect
1. **Login as lawyer**
2. **Navigate to:** `http://localhost:3000/`
3. **Expected result:** Automatically redirected to `/legal`
4. **Logout and login as admin**
5. **Navigate to:** `http://localhost:3000/`
6. **Expected result:** Automatically redirected to `/dashboard`

## Files Modified

1. **`frontend/src/App.js`**
   - Added `RoleBasedRoute` component
   - Added `DefaultRoute` component
   - Updated all protected routes to use role-based protection

2. **`frontend/src/components/NavBar.js`**
   - Made navigation menu role-aware
   - Only shows links user has access to
   - Clears userInfo on logout

3. **`frontend/src/components/Login.js`** (from previous fix)
   - Stores user info including role on login
   - Implements role-based redirect after login

4. **`Backend/app/Http/Controllers/AuthController.php`** (from previous fix)
   - Returns user data including role in login response

## Security Notes

⚠️ **Important:** This is frontend-only protection. For production systems, you should also:

1. **Backend API Authorization:** Ensure all API endpoints verify user permissions
2. **Token Validation:** API should validate tokens and check roles before returning data
3. **Database-level Security:** Implement row-level security where applicable

The current implementation prevents UI access but APIs should still validate permissions independently.

## Role Management

To add a new role or change user roles:

```sql
-- Add new role to enum
ALTER TABLE users 
MODIFY role ENUM('admin','developer','client','user','lawyer','new_role') 
NOT NULL DEFAULT 'user';

-- Assign role to user
UPDATE users SET role='new_role' WHERE username='username';
```

Then update `App.js` and `NavBar.js` to handle the new role.

---
*Last Updated: November 4, 2025*
