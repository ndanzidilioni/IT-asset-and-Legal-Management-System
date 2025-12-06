# Contract Deletion Approval System - Setup Instructions

## Overview
The system now requires approval from another lawyer before a contract can be deleted. This implements a two-step approval workflow for contract deletions.

## Backend Setup

### 1. Run the Database Migration
Run this command in your Backend directory to create the contract_deletion_requests table:

```bash
cd "c:\xampp\htdocs\scheduling management system\Backend"
php artisan migrate --path=database/migrations/create_contract_deletion_requests_table.php
```

### 2. Database Table Structure
The migration creates a `contract_deletion_requests` table with:
- `id` - Primary key
- `contract_id` - ID of the contract to be deleted
- `requested_by` - User ID who requested deletion
- `requester_name` - Name of the requester
- `reason` - Reason for deletion request
- `status` - pending/approved/rejected
- `reviewed_by` - User ID who approved/rejected
- `reviewer_name` - Name of the reviewer
- `review_comment` - Optional comment from reviewer
- `reviewed_at` - Timestamp of review
- `created_at` / `updated_at` - Timestamps

### 3. API Endpoints Created
- `GET /api/contract-deletion-requests` - Get all deletion requests
- `GET /api/contract-deletion-requests/pending` - Get pending approvals (excludes own requests)
- `POST /api/contract-deletion-requests` - Create a deletion request
- `POST /api/contract-deletion-requests/{id}/approve` - Approve and delete contract
- `POST /api/contract-deletion-requests/{id}/reject` - Reject deletion request
- `DELETE /api/contract-deletion-requests/{id}` - Cancel own request

## How It Works

### For Lawyers Requesting Deletion:
1. Click the delete button (🗑️) on a contract
2. Modal opens asking for deletion reason
3. Enter reason and click "📤 Submit Request"
4. Request is sent for approval
5. Cannot approve own deletion requests

### For Lawyers Reviewing Deletions:
1. "⏳ Pending Approvals (N)" button appears in header when requests exist
2. Click to view all pending deletion requests
3. See requester name, date, reason, and contract details
4. Choose to:
   - **✅ Approve** - Optionally add comment, contract gets deleted
   - **❌ Reject** - Must provide reason, request is rejected

### Security Features:
- ✅ Cannot approve own deletion requests
- ✅ Reason required for deletion requests
- ✅ Comment required for rejection
- ✅ Only pending requests can be reviewed
- ✅ Contract deleted only after approval
- ✅ Full audit trail maintained

## Frontend Updates

### Files Modified:
1. **ContractRegister.js** - Main component with approval workflow
2. **legalApi.js** - API service methods added
3. **Backend Routes** - New API endpoints

### UI Components:
1. **Delete Request Modal** - For submitting deletion requests with reason
2. **Pending Approvals Modal** - Shows all requests waiting for approval
3. **Review Modal** - For approving/rejecting with comments
4. **Notification Badge** - Shows count of pending approvals in header

## Testing the Feature

### Test Scenario 1: Request Deletion
1. Login as Lawyer A
2. Go to Contract Register
3. Click delete on a contract
4. Enter reason
5. Submit request
6. Verify message: "Deletion request submitted successfully..."

### Test Scenario 2: Approve Deletion
1. Login as Lawyer B (different from requester)
2. Go to Contract Register
3. Click "⏳ Pending Approvals" button
4. See the request from Lawyer A
5. Click "✅ Approve"
6. Optionally add comment
7. Click "✅ Approve & Delete"
8. Contract is deleted

### Test Scenario 3: Reject Deletion
1. Login as Lawyer B
2. View pending approvals
3. Click "❌ Reject"
4. Enter rejection reason (required)
5. Click "❌ Reject"
6. Request is rejected, contract remains

### Test Scenario 4: Cannot Approve Own Request
1. Login as Lawyer A
2. Try to view pending approvals
3. Your own requests won't appear
4. Only requests from other lawyers are shown

## Benefits

1. **Prevents Accidental Deletions** - Two-person verification
2. **Audit Trail** - All deletion attempts are logged
3. **Accountability** - Know who requested and who approved
4. **Transparency** - Reasons documented for all actions
5. **Security** - No single point of failure

## Database Query Examples

### Check Pending Requests:
```sql
SELECT * FROM contract_deletion_requests WHERE status = 'pending';
```

### See All Deletion History:
```sql
SELECT cdr.*, c.contract_title 
FROM contract_deletion_requests cdr
LEFT JOIN contracts c ON cdr.contract_id = c.id
ORDER BY cdr.created_at DESC;
```

### Approved Deletions:
```sql
SELECT * FROM contract_deletion_requests 
WHERE status = 'approved' 
ORDER BY reviewed_at DESC;
```

## Support

If you encounter any issues:
1. Check browser console for errors
2. Verify database migration ran successfully
3. Ensure user is logged in with lawyer role
4. Check API responses in Network tab
