# Audit Trail System - Complete Guide

## Overview
The Audit Trail system provides comprehensive tracking of all user activities in the system, including logins, logouts, and any actions performed. This ensures accountability, security compliance, and helps with troubleshooting.

---

## Features

### ✅ What's Tracked

1. **User Authentication**
   - Login attempts (successful)
   - Logout events
   - IP addresses
   - User agents (browser/device info)

2. **User Activities**
   - Create operations
   - Update/Edit operations
   - Delete operations
   - View/Read operations
   - Export operations
   - Import operations

3. **System Information**
   - Timestamp (date and time)
   - Username
   - Action type
   - Description
   - Request method (GET, POST, PUT, DELETE)
   - Request URL
   - Request data (sanitized - passwords hidden)
   - Response status
   - Additional metadata

---

## Database Structure

### Audit Logs Table

```sql
CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,          -- User who performed the action
  username VARCHAR(255) NULL,             -- Username for quick reference
  action VARCHAR(255) NOT NULL,           -- Action type (login, logout, create, etc.)
  description TEXT NULL,                  -- Human-readable description
  ip_address VARCHAR(45) NULL,            -- IPv4 or IPv6 address
  user_agent TEXT NULL,                   -- Browser/device information
  request_method VARCHAR(10) NULL,        -- GET, POST, PUT, DELETE
  request_url TEXT NULL,                  -- Full URL of the request
  request_data LONGTEXT NULL,             -- JSON of request data (sanitized)
  response_status INT NULL,               -- HTTP response code
  metadata JSON NULL,                     -- Additional custom data
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_user_id (user_id),
  INDEX idx_action (action),
  INDEX idx_created_at (created_at),
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Points:**
- `user_id` is nullable and sets to NULL if user is deleted (preserves history)
- `username` stored for quick display even if user account is deleted
- All sensitive data (passwords, tokens) is automatically redacted
- Indexes optimize queries by user, action type, and date

---

## API Endpoints

### 1. Get Audit Logs (Paginated)
```http
GET /api/audit-logs
```

**Authentication:** Required (Admin only)

**Query Parameters:**
- `user_id` (optional) - Filter by specific user
- `action` (optional) - Filter by action type (login, create, update, delete, etc.)
- `start_date` (optional) - Format: YYYY-MM-DD
- `end_date` (optional) - Format: YYYY-MM-DD
- `ip_address` (optional) - Filter by IP address
- `search` (optional) - Search in username, action, description
- `per_page` (optional) - Results per page (default: 50)
- `page` (optional) - Page number (default: 1)

**Example Request:**
```javascript
GET /api/audit-logs?action=login&start_date=2025-11-01&per_page=25&page=1
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "user_id": 5,
      "username": "john.doe",
      "action": "login",
      "description": "User logged in successfully",
      "ip_address": "192.168.1.100",
      "user_agent": "Mozilla/5.0...",
      "request_method": "POST",
      "request_url": "http://localhost:8000/api/login",
      "created_at": "2025-11-04 12:30:45"
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 25,
    "current_page": 1,
    "last_page": 6,
    "from": 1,
    "to": 25
  }
}
```

### 2. Get Audit Statistics
```http
GET /api/audit-logs/statistics
```

**Authentication:** Required (Admin only)

**Query Parameters:**
- `start_date` (optional) - Default: 30 days ago
- `end_date` (optional) - Default: today

**Response:**
```json
{
  "success": true,
  "data": {
    "total_activities": 1250,
    "unique_users": 45,
    "total_logins": 320,
    "activities_by_action": [
      { "action": "login", "count": 320 },
      { "action": "create_asset", "count": 145 },
      { "action": "update_asset", "count": 98 }
    ],
    "activities_by_user": [
      { "username": "admin", "count": 450 },
      { "username": "john.doe", "count": 230 }
    ],
    "recent_logins": [
      {
        "username": "john.doe",
        "ip_address": "192.168.1.100",
        "created_at": "2025-11-04 12:30:45"
      }
    ]
  }
}
```

### 3. Get Single Audit Log
```http
GET /api/audit-logs/{id}
```

**Authentication:** Required (Admin only)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "user_id": 5,
    "username": "john.doe",
    "action": "login",
    "description": "User logged in successfully",
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
    "request_method": "POST",
    "request_url": "http://localhost:8000/api/login",
    "request_data": "{\"login\":\"john.doe\",\"password\":\"***REDACTED***\"}",
    "metadata": "{\"role\":\"ict\"}",
    "created_at": "2025-11-04 12:30:45",
    "user": {
      "id": 5,
      "username": "john.doe",
      "email": "john.doe@example.com",
      "fname": "John",
      "lname": "Doe"
    }
  }
}
```

### 4. Export Audit Logs to CSV
```http
GET /api/audit-logs/export/csv
```

**Authentication:** Required (Admin only)

**Query Parameters:** Same as GET /api/audit-logs

**Response:** CSV file download

**CSV Format:**
```csv
ID,Username,Action,Description,IP Address,Timestamp
123,john.doe,login,User logged in successfully,192.168.1.100,2025-11-04 12:30:45
124,jane.smith,create_asset,Created IT asset: Laptop,192.168.1.101,2025-11-04 12:35:20
```

---

## How to Use

### Frontend Interface

#### 1. Access Audit Logs
1. Login as **Admin**
2. Click **"🔍 Audit Logs"** in the navigation menu
3. You'll see two tabs:
   - **📋 Activity Logs** - Detailed log entries
   - **📊 Statistics** - Summary and analytics

#### 2. Activity Logs Tab

**Features:**
- **Search Box** - Search by username, action, or description
- **Action Filter** - Filter by action type
- **Date Range** - Filter by start and end date
- **Search Button** - Apply filters
- **Clear Button** - Reset all filters
- **Export CSV Button** - Download filtered results

**Table Columns:**
- ID - Unique audit log ID
- Timestamp - When the action occurred
- Username - Who performed the action
- Action - Type of action (color-coded badge)
- Description - Details about the action
- IP Address - User's IP address
- User Agent - Browser/device information

**Pagination:**
- Shows 50 records per page (configurable)
- Navigate with Previous/Next buttons
- Shows current page and total records

#### 3. Statistics Tab

**Overview Cards:**
- **Total Activities** - Count of all logged activities
- **Unique Users** - Number of different users active
- **Total Logins** - Number of login events

**Top Activities Table:**
- Shows most frequent actions
- Displays count for each action type

**Most Active Users Table:**
- Lists users by activity count
- Shows which users are most active

**Recent Logins Table:**
- Latest login events
- Shows username, IP, and timestamp

---

## Backend Implementation

### How to Log Activities

#### Automatic Logging (Already Implemented)

**Login & Logout:**
```php
// In AuthController.php
AuditLog::create([
    'user_id' => $user->id,
    'username' => $user->username,
    'action' => 'login',
    'description' => 'User logged in successfully',
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'request_method' => 'POST',
    'request_url' => $request->fullUrl(),
    'metadata' => json_encode(['role' => $user->role]),
]);
```

#### Manual Logging (For Custom Actions)

**Simple Method:**
```php
use App\Models\AuditLog;

AuditLog::log('create_asset', 'Created IT asset: Laptop #12345');
```

**Detailed Method:**
```php
AuditLog::log(
    'update_user',
    'Updated user profile for ' . $targetUser->username,
    [
        'target_user_id' => $targetUser->id,
        'changes' => ['email', 'role'],
        'old_role' => 'user',
        'new_role' => 'ict'
    ]
);
```

**Full Control:**
```php
AuditLog::create([
    'user_id' => auth()->id(),
    'username' => auth()->user()->username,
    'action' => 'export_data',
    'description' => 'Exported 500 IT asset records to CSV',
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'request_method' => 'GET',
    'request_url' => request()->fullUrl(),
    'metadata' => json_encode([
        'record_count' => 500,
        'format' => 'csv',
        'filters_applied' => ['status' => 'active']
    ]),
]);
```

---

## Security Features

### 1. Automatic Data Sanitization

Sensitive fields are automatically redacted:
- `password`
- `password_confirmation`
- `current_password`
- `new_password`
- `token`
- `access_token`

**Before Logging:**
```json
{
  "username": "john.doe",
  "password": "SecurePass123!",
  "email": "john@example.com"
}
```

**After Logging:**
```json
{
  "username": "john.doe",
  "password": "***REDACTED***",
  "email": "john@example.com"
}
```

### 2. Admin-Only Access

- Only users with `role='admin'` can view audit logs
- All audit log endpoints check admin permission
- Returns 403 Forbidden for non-admin users

### 3. Immutable Records

- Audit logs cannot be edited once created
- No update or delete endpoints provided
- `created_at` timestamp cannot be modified
- Ensures integrity of audit trail

### 4. User Deletion Handling

When a user is deleted:
- Their audit logs remain intact
- `user_id` is set to NULL
- `username` is preserved for reference
- Full history is maintained

---

## Use Cases

### 1. Security Investigation

**Scenario:** Suspicious activity detected

**Steps:**
1. Go to Audit Logs
2. Filter by IP address or username
3. Review timeline of actions
4. Check for unusual patterns

**Example Findings:**
- Multiple failed login attempts
- Access from unusual IP addresses
- Actions outside normal working hours
- Bulk data exports

### 2. Compliance & Reporting

**Scenario:** Generate activity report for auditors

**Steps:**
1. Go to Statistics tab
2. Set date range (e.g., last quarter)
3. Review activity summary
4. Export full logs to CSV
5. Provide to compliance team

**Report Includes:**
- Total user activities
- Breakdown by action type
- Most active users
- Login history

### 3. Troubleshooting

**Scenario:** User reports data loss

**Steps:**
1. Search audit logs for the user
2. Filter by "delete" action
3. Review timeline of deletions
4. Identify when and what was deleted
5. Check who performed the action

### 4. User Activity Monitoring

**Scenario:** Monitor new employee activity

**Steps:**
1. Filter logs by username
2. Review all actions
3. Verify appropriate access patterns
4. Ensure no policy violations

---

## Action Types Reference

| Action Pattern | Description | Badge Color |
|---------------|-------------|-------------|
| `login` | User logged in | Green (Success) |
| `logout` | User logged out | Blue (Info) |
| `create_*` | Created a record | Purple (Primary) |
| `update_*` | Updated a record | Yellow (Warning) |
| `delete_*` | Deleted a record | Red (Danger) |
| `view_*` | Viewed a record | Gray (Default) |
| `export_*` | Exported data | Green (Success) |
| `import_*` | Imported data | Purple (Primary) |

---

## Performance Considerations

### Indexes
The `audit_logs` table has indexes on:
- `user_id` - Fast filtering by user
- `action` - Fast filtering by action type
- `created_at` - Fast date range queries

### Pagination
- Default: 50 records per page
- Prevents loading thousands of records at once
- Improves page load times

### Data Retention
Consider implementing retention policy:
- Keep last 12 months in active table
- Archive older records to separate table
- Regular cleanup of very old logs

**Example Cleanup (Optional):**
```sql
-- Archive logs older than 2 years
INSERT INTO audit_logs_archive SELECT * FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);

-- Delete archived records
DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);
```

---

## Files Created/Modified

### Backend
1. ✅ `app/Models/AuditLog.php` - Model with logging methods
2. ✅ `app/Http/Controllers/AuditLogController.php` - API endpoints
3. ✅ `app/Http/Controllers/AuthController.php` - Added login/logout logging
4. ✅ `routes/api.php` - Registered audit log routes
5. ✅ Database: `audit_logs` table created

### Frontend
1. ✅ `components/AuditLog.js` - Audit logs interface
2. ✅ `components/AuditLog.css` - Styling
3. ✅ `App.js` - Added route
4. ✅ `NavBar.js` - Added navigation link

---

## Testing

### Test Login Tracking
1. Logout completely
2. Login with different users
3. Go to Audit Logs
4. Verify each login is tracked with:
   - Correct username
   - Correct timestamp
   - Your IP address
   - Your browser info

### Test Filtering
1. Create several test activities
2. Use action filter (select "login")
3. Verify only login events shown
4. Try date range filter
5. Try search box

### Test Export
1. Apply some filters
2. Click "Export CSV"
3. Open downloaded file
4. Verify it contains filtered data

### Test Statistics
1. Switch to Statistics tab
2. Verify numbers are accurate
3. Check Recent Logins shows your login
4. Verify activity counts

---

## Troubleshooting

### Issue: No logs appearing
**Solution:**
- Check you're logged in as admin
- Verify database table was created
- Check browser console for errors
- Verify API endpoints are accessible

### Issue: Export not working
**Solution:**
- Check browser allows downloads
- Verify CSV export route is registered
- Check server error logs

### Issue: Statistics not loading
**Solution:**
- Check date range parameters
- Verify statistics endpoint is accessible
- Check for database query errors

---

## Future Enhancements

Potential improvements:
1. **Real-time Monitoring** - WebSocket updates for live feed
2. **Advanced Analytics** - Charts and graphs
3. **Alert System** - Notify admins of suspicious activity
4. **Custom Reports** - Scheduled report generation
5. **Activity Replay** - Recreate user session
6. **Retention Policy UI** - Configure data retention
7. **Bulk Actions** - Archive or delete multiple logs
8. **API Rate Limiting** - Track API usage per user

---

## Best Practices

1. **Regular Review**
   - Check audit logs weekly
   - Review recent logins daily
   - Monitor unusual patterns

2. **Access Control**
   - Keep audit log access admin-only
   - Log who accesses audit logs
   - Implement two-factor auth for admins

3. **Data Retention**
   - Archive logs periodically
   - Maintain at least 12 months
   - Backup before archiving

4. **Performance**
   - Use date filters for large datasets
   - Export for offline analysis
   - Monitor database size

5. **Compliance**
   - Document audit procedures
   - Train admins on audit log use
   - Include in security policy

---

**Created:** November 4, 2025  
**Version:** 1.0  
**Status:** ✅ Fully Implemented
