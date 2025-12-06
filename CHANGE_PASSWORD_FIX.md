# Change Password API 401 Error - Fix Summary

## Problem
When trying to change password, the following error occurred:
```
user_id, current_password, and new_password are required
```

This was a 401 Unauthorized error because the request wasn't reaching the Laravel API properly.

## Root Causes

### 1. Wrong Endpoint
**Line 290-291 in `api.js`:**
```javascript
// BEFORE:
return axios.post('http://localhost/change-password.php', data, config);
```
The code was trying to call `http://localhost/change-password.php` which doesn't exist.

### 2. Missing Authentication
The request wasn't including the Bearer token needed to authenticate the user.

### 3. Missing Required Field
The frontend was sending:
```javascript
{
  user_id: userData.id,           // Not needed (user comes from token)
  current_password: "...",
  new_password: "..."             // Missing new_password_confirmation
}
```

But the backend expects:
```javascript
{
  current_password: "...",
  new_password: "...",
  new_password_confirmation: "..."  // Required by Laravel
}
```

## Solution Applied

### 1. Fixed API Routing in `frontend/src/services/api.js`

**Changed:**
```javascript
// BEFORE:
} else if (url === '/change-password') {
  console.log('💬 Change Password POST: → REAL PASSWORD API');
  return axios.post('http://localhost/change-password.php', data, config);
}

// AFTER:
} else if (url === '/change-password') {
  console.log('🔐 Change Password POST: → Laravel API');
  const token = localStorage.getItem('token');
  const headers = {
    ...(config?.headers || {}),
    'Authorization': token ? `Bearer ${token}` : undefined,
    'Content-Type': 'application/json'
  };
  return originalPost.call(this, '/change-password', data, { ...config, headers });
}
```

### 2. Fixed Request Data in `frontend/src/components/ChangePassword.js`

**Changed:**
```javascript
// BEFORE:
const userData = JSON.parse(localStorage.getItem('user') || '{}');
const requestData = {
  user_id: userData.id,
  current_password: formData.current_password,
  new_password: formData.new_password
};

// AFTER:
const requestData = {
  current_password: formData.current_password,
  new_password: formData.new_password,
  new_password_confirmation: formData.new_password_confirmation
};
```

Added debug logging to help diagnose future issues.

## Backend Requirements

The Laravel backend (`AuthController::changePassword`) requires:

### Authentication
- **Bearer Token**: User must be authenticated via `auth:sanctum` middleware
- The user is obtained from `$request->user()` - no need to send `user_id`

### Request Fields
```php
[
  'current_password' => 'required|string',
  'new_password' => 'required|string|min:8|confirmed',
  // Laravel's 'confirmed' rule automatically looks for 'new_password_confirmation'
]
```

### Password Policy
The new password must meet these requirements:
- ✅ At least 8 characters long
- ✅ Contains at least one uppercase letter
- ✅ Contains at least one lowercase letter
- ✅ Contains at least one number
- ✅ Contains at least one special character

### Response Format
**Success (200):**
```json
{
  "success": true,
  "message": "Password changed successfully."
}
```

**Error (422):**
```json
{
  "success": false,
  "message": "Current password is incorrect."
}
```

Or:
```json
{
  "success": false,
  "message": "Password does not meet requirements.",
  "errors": [
    "Password must contain at least one uppercase letter",
    "Password must contain at least one special character"
  ]
}
```

## Testing

### Backend Verification
Tested the API endpoint directly:
```
✅ Login successful (Bearer token obtained)
✅ Password change endpoint accessible with token
✅ Validation works correctly (requires all 3 fields)
✅ Current password verification works
✅ Password policy enforcement works
✅ API returns proper error messages
```

### How to Test in Browser

1. **Start Backend:**
   ```bash
   cd Backend
   php artisan serve
   ```

2. **Start Frontend:**
   ```bash
   cd frontend
   npm start
   ```

3. **Login:**
   - Username: `admin`
   - Password: `admin123`

4. **Change Password:**
   - If prompted with "must change password" modal, fill in:
     - Current Password: `admin123`
     - New Password: `NewPass123!` (must meet policy)
     - Confirm Password: `NewPass123!`
   - Click "Change Password"

5. **Expected Result:**
   - ✅ Success message: "Password changed successfully!"
   - ✅ Modal closes
   - ✅ Can continue using the application

### Browser Console Output
```
🔐 Sending password change request: {
  current_password: '***',
  new_password: '***',
  new_password_confirmation: '***'
}
🔐 Change Password POST: → Laravel API
```

## Valid Password Examples
These passwords meet the policy requirements:
- ✅ `Admin123!`
- ✅ `Password123!`
- ✅ `Test1234!`
- ✅ `Secure@Pass1`
- ✅ `MyP@ssw0rd`

These do NOT meet requirements:
- ❌ `admin123` (missing uppercase + special char)
- ❌ `Admin123` (missing special char)
- ❌ `admin123!` (missing uppercase)
- ❌ `ADMIN123!` (missing lowercase)
- ❌ `Admin!` (too short - less than 8 chars)

**Note:** See PASSWORD_POLICY_GUIDE.md for comprehensive password examples and tips.

## Files Modified

1. **frontend/src/services/api.js**
   - Fixed `/change-password` endpoint routing
   - Added Bearer token authentication
   - Added debug logging

2. **frontend/src/components/ChangePassword.js**
   - Removed `user_id` from request (not needed)
   - Added `new_password_confirmation` field
   - Added debug logging for password change requests

## Related Issues Fixed

This fix ensures:
- ✅ Password change works from the forced password change modal
- ✅ Password change works from user settings
- ✅ Proper error messages are displayed
- ✅ Password policy is enforced
- ✅ Bearer token authentication is used consistently

## Status
✅ **RESOLVED** - Change password API now correctly routes to Laravel backend with proper authentication and validation
