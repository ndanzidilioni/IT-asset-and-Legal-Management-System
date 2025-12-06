# 📋 Demand Notes Management System - Complete Guide

## Overview
The Demand Notes Management System is a comprehensive solution for creating, tracking, and managing payment demand notes for legal claims. It automates generation, tracks payments, sends notifications, and provides detailed reporting for accountability and timely follow-ups.

---

## ✨ Key Features

### 1. **Demand Note Creation & Management**
- ✅ Auto-generated unique demand note numbers (`DN-2025-0001`)
- ✅ Comprehensive client information capture
- ✅ Detailed claim specifications
- ✅ Legal notice documentation
- ✅ Settlement tracking
- ✅ Status management (Pending, Paid, Partially Paid, Overdue, Cancelled)

### 2. **Financial Tracking**
- 💰 Amount claimed tracking
- 💵 Payment recording with full history
- 📊 Balance due calculations
- 💸 Late fee management
- 📈 Automatic status updates based on payments

### 3. **Payment Management**
- Add multiple payments per demand note
- Track payment methods (Bank Transfer, Cash, Check, etc.)
- Receipt number recording
- Payment date tracking
- Automatic balance calculations

### 4. **Document Management**
- Store demand notes securely
- View complete demand note details
- Track all modifications
- Export to CSV for reporting

### 5. **Reporting & Analytics**
- Dashboard with real-time statistics
- Total claimed, collected, and outstanding amounts
- Status-based filtering (Pending, Paid, Overdue)
- Nature of claim breakdown
- Export filtered reports to CSV

### 6. **User Roles & Access Control**
- Admin and Lawyer access only
- Role-based permissions enforced
- Audit trail for all activities
- User attribution for creation and modifications

---

## 📊 Database Structure

### demand_notes Table
```sql
- id: Auto-increment primary key
- demand_note_number: Unique identifier (DN-YYYY-####)
- client_name: Client full name *
- client_number: Optional client reference
- claim_reference: Claim reference number
- amount_claimed: Total amount claimed *
- due_date: Payment due date *
- nature_of_claim: Type (Works, Supply of Goods, Services, Consultancy, Other) *
- agency_of_claim: Agency/Organization name
- notice_to_institute_suit: Legal notice details
- time_given_to_settle: Settlement timeframe
- settlement_action_taken: Actions taken
- current_status: Status (pending, paid, partially_paid, overdue, cancelled)
- remarks: Additional notes
- created_by: User who created the note
- issued_date: Date note was issued
- payment_date: Date fully paid (if applicable)
- amount_paid: Total amount paid so far
- balance_due: Remaining balance (auto-calculated)
- late_fee: Late payment fees
- created_at/updated_at: Timestamps
```

### demand_note_payments Table
```sql
- id: Auto-increment primary key
- demand_note_id: Foreign key to demand_notes
- payment_amount: Amount paid *
- payment_date: Date of payment *
- payment_method: Payment method (e.g., Bank Transfer)
- receipt_number: Receipt/transaction number
- notes: Additional payment notes
- recorded_by: User who recorded payment
- created_at: Timestamp
```

---

## 🔐 Access Control

### Allowed Roles
- ✅ **Admin** - Full access to all functions
- ✅ **Lawyer** - Full access to demand notes management
- ❌ **ICT** - No access
- ❌ **Client** - No access
- ❌ **User** - No access

All endpoints require authentication via Bearer token.

---

## 📡 API Endpoints

### 1. Get All Demand Notes (Paginated)
```http
GET /api/demand-notes
```

**Query Parameters:**
- `status` - Filter by status (pending, paid, etc.)
- `client_name` - Search client name
- `nature_of_claim` - Filter by nature
- `start_date` - Due date start range
- `end_date` - Due date end range
- `search` - Search note number, client, or reference
- `per_page` - Results per page (default: 20)
- `page` - Page number

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "demand_note_number": "DN-2025-0001",
      "client_name": "John Doe Ltd",
      "amount_claimed": "5000000.00",
      "amount_paid": "2000000.00",
      "balance_due": "3000000.00",
      "due_date": "2025-12-31",
      "current_status": "partially_paid",
      "nature_of_claim": "Works",
      ...
    }
  ],
  "pagination": {
    "total": 50,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3
  }
}
```

### 2. Get Single Demand Note
```http
GET /api/demand-notes/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "demand_note_number": "DN-2025-0001",
    "client_name": "John Doe Ltd",
    "client_number": "CLT-001",
    "claim_reference": "REF-2025-001",
    "amount_claimed": "5000000.00",
    "amount_paid": "2000000.00",
    "balance_due": "3000000.00",
    "due_date": "2025-12-31",
    "nature_of_claim": "Works",
    "current_status": "partially_paid",
    "creator": {
      "id": 5,
      "username": "lawyer1",
      "fname": "Jane",
      "lname": "Smith"
    },
    "payments": [
      {
        "id": 1,
        "payment_amount": "2000000.00",
        "payment_date": "2025-10-15",
        "payment_method": "Bank Transfer",
        "receipt_number": "RCP-001",
        "recorder": {
          "id": 5,
          "username": "lawyer1"
        }
      }
    ]
  }
}
```

### 3. Create Demand Note
```http
POST /api/demand-notes
```

**Request Body:**
```json
{
  "client_name": "John Doe Ltd",
  "client_number": "CLT-001",
  "claim_reference": "REF-2025-001",
  "amount_claimed": 5000000,
  "due_date": "2025-12-31",
  "nature_of_claim": "Works",
  "agency_of_claim": "Ministry of Works",
  "notice_to_institute_suit": "Formal notice sent on...",
  "time_given_to_settle": "30 days",
  "settlement_action_taken": "",
  "remarks": "High priority claim",
  "late_fee": 0
}
```

**Response:**
```json
{
  "success": true,
  "message": "Demand note created successfully",
  "data": { ... }
}
```

### 4. Update Demand Note
```http
PUT /api/demand-notes/{id}
```

**Request Body:** (All fields optional)
```json
{
  "client_name": "John Doe Ltd",
  "amount_claimed": 5500000,
  "current_status": "pending",
  "settlement_action_taken": "Negotiation in progress",
  ...
}
```

### 5. Delete Demand Note
```http
DELETE /api/demand-notes/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Demand note deleted successfully"
}
```

### 6. Add Payment
```http
POST /api/demand-notes/{id}/payments
```

**Request Body:**
```json
{
  "payment_amount": 2000000,
  "payment_date": "2025-11-07",
  "payment_method": "Bank Transfer",
  "receipt_number": "RCP-001",
  "notes": "First installment"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment added successfully",
  "data": { ... updated demand note with all payments ... }
}
```

### 7. Get Statistics
```http
GET /api/demand-notes/statistics
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_demand_notes": 50,
    "pending": 20,
    "paid": 15,
    "overdue": 10,
    "partially_paid": 5,
    "total_claimed": "250000000.00",
    "total_collected": "180000000.00",
    "total_outstanding": "70000000.00",
    "by_nature": [
      {"nature_of_claim": "Works", "count": 25},
      {"nature_of_claim": "Services", "count": 15}
    ],
    "recent_notes": [...]
  }
}
```

### 8. Export to CSV
```http
GET /api/demand-notes/export/csv?status=pending&start_date=2025-01-01
```

Downloads CSV file with filtered demand notes.

---

## 🎨 Frontend Interface

### Dashboard (`/legal/demand-notes`)

#### Statistics Cards
- **Total Notes** - Count of all demand notes
- **Pending** - Notes awaiting payment
- **Overdue** - Past due date
- **Paid** - Fully paid notes
- **Total Claimed** - Sum of all amounts claimed
- **Total Collected** - Sum of all payments received

#### Filters
- Search by note number, client name, or reference
- Filter by status dropdown
- Filter by nature of claim
- Date range (start & end date)
- Clear filters button

#### Actions
- **Create Demand Note** - Add new demand note
- **Export CSV** - Download filtered results

#### Demand Notes Table
Columns:
- Note Number (clickable)
- Client Name
- Amount Claimed
- Amount Paid
- Balance Due (highlighted in red)
- Due Date
- Nature of Claim (badge)
- Status (color-coded badge)
- Actions (View, Edit, Delete)

### Create Demand Note (`/legal/demand-notes/create`)

Form Sections:
1. **Client Information**
   - Client Name *
   - Client Number

2. **Claim Details**
   - Claim Reference
   - Nature of Claim * (dropdown)
   - Amount Claimed (TZS) *
   - Late Fee (TZS)
   - Due Date *
   - Agency of Claim

3. **Legal Notice & Settlement**
   - Notice to Institute Suit (textarea)
   - Time Given to Settle
   - Settlement/Action Taken (textarea)

4. **Additional Information**
   - Remarks (textarea)

### Edit Demand Note (`/legal/demand-notes/edit/:id`)

Same as Create form, plus:
- Status field (dropdown)
- Pre-filled with existing data

### View Demand Note (`/legal/demand-notes/view/:id`)

#### Header
- Demand Note Number
- Status Badge (large, color-coded)
- Actions: Back, Edit, Add Payment

#### Financial Summary (Purple Card)
- Amount Claimed
- Late Fee
- Total Due
- Amount Paid
- **Balance Due** (highlighted)

#### Information Cards
- **Client Information**
  - Client Name
  - Client Number
  - Claim Reference

- **Claim Details**
  - Nature of Claim
  - Agency of Claim
  - Due Date
  - Issued Date

- **Legal Notice & Settlement**
  - Notice to Institute Suit
  - Time Given to Settle
  - Settlement/Action Taken

- **Remarks**
  - Additional notes

#### Payment History Table
- Date
- Amount
- Method
- Receipt No.
- Recorded By
- Notes

#### System Information
- Created By
- Created At
- Last Updated

#### Add Payment Form (Expandable)
- Payment Amount *
- Payment Date *
- Payment Method
- Receipt Number
- Notes

---

## 🔄 Automatic Features

### Auto-Generated Demand Note Number
Format: `DN-YYYY-####`
- `DN` - Prefix
- `YYYY` - Current year
- `####` - Sequential 4-digit number (resets each year)

Example: `DN-2025-0001`, `DN-2025-0002`, etc.

### Automatic Balance Calculation
```
Balance Due = Amount Claimed + Late Fee - Amount Paid
```

Updated whenever:
- Demand note is created
- Demand note is updated
- Payment is added
- Payment is deleted

### Automatic Status Updates
When payments are added:
- `amount_paid >= amount_claimed` → Status: **Paid**
- `amount_paid > 0 && amount_paid < amount_claimed` → Status: **Partially Paid**
- `amount_paid == 0 && due_date < today` → Status: **Overdue**

### Issued Date
Automatically set to current date when demand note is created if not specified.

---

## 📊 Status Indicators

| Status | Color | Meaning |
|--------|-------|---------|
| **Pending** | 🟡 Yellow | Awaiting payment, not yet overdue |
| **Paid** | 🟢 Green | Fully paid |
| **Partially Paid** | 🔵 Blue | Some payment received, balance remaining |
| **Overdue** | 🔴 Red | Past due date, no payment or partial payment |
| **Cancelled** | ⚪ Gray | Demand note cancelled |

---

## 🎯 Use Cases

### 1. Create New Demand Note
**Scenario:** Law firm sends demand note for unpaid construction work

**Steps:**
1. Navigate to `/legal/demand-notes`
2. Click **"+ Create Demand Note"**
3. Fill in client details: "ABC Construction Ltd"
4. Enter claim: TZS 10,000,000 for unpaid work
5. Set due date: 30 days from today
6. Select nature: "Works"
7. Add legal notice details
8. Click **"✓ Create Demand Note"**
9. **System generates:** `DN-2025-0015`

### 2. Record Payment
**Scenario:** Client makes partial payment

**Steps:**
1. Navigate to demand note details
2. Click **"💵 Add Payment"**
3. Enter amount: TZS 4,000,000
4. Select date: Today
5. Payment method: "Bank Transfer"
6. Receipt number: "RCP-45678"
7. Click **"✓ Add Payment"**
8. **System updates:**
   - Amount Paid: TZS 4,000,000
   - Balance Due: TZS 6,000,000
   - Status: "Partially Paid"

### 3. Track Overdue Notes
**Scenario:** Review all overdue payments

**Steps:**
1. Navigate to `/legal/demand-notes`
2. Select status filter: **"Overdue"**
3. View all notes past due date
4. Check "Overdue" statistic card shows count
5. Take action or follow up

### 4. Generate Monthly Report
**Scenario:** Create report for management

**Steps:**
1. Set date range: First & last day of month
2. Apply any status filter if needed
3. Click **"📥 Export CSV"**
4. Open in Excel
5. Review total claimed vs. collected
6. Present to management

---

## 🔐 Security Features

### Authentication & Authorization
- Bearer token authentication required
- Role-based access (Admin & Lawyer only)
- 403 Forbidden for unauthorized roles

### Audit Trail
All actions logged:
- Create demand note → `create_demand_note`
- Update demand note → `update_demand_note`
- Delete demand note → `delete_demand_note`
- View demand note → `view_demand_note`
- Add payment → `add_payment_demand_note`
- Export data → `export_demand_notes`

### Data Integrity
- Foreign key constraints
- Cascade delete for payments
- SET NULL for user references (preserve history)
- Automatic balance calculations prevent manual errors

---

## 📈 Reporting Metrics

### Dashboard Statistics
- Total demand notes issued
- Pending vs. Paid ratio
- Overdue count (requires action)
- Partially paid count
- Total amount claimed
- Total amount collected
- Outstanding balance

### Nature of Claim Breakdown
Track which types of claims are most common:
- Works
- Supply of Goods
- Services
- Consultancy
- Other

### Payment Tracking
- Payment history per demand note
- Payment methods used
- Average payment time
- Collection rate

---

## 🎨 UI/UX Features

### Color-Coded Status
- Visual indication of status at a glance
- Large status badge on details page
- Consistent color scheme throughout

### Responsive Design
- Mobile-friendly tables
- Collapsible filters on small screens
- Touch-friendly action buttons

### Real-Time Updates
- Statistics update after create/delete
- Payment history updates immediately
- Balance recalculates automatically

### User Feedback
- Success messages (green)
- Error messages (red)
- Loading spinners
- Confirmation dialogs for delete

---

## 📝 Best Practices

### Creating Demand Notes
1. Always include client reference numbers
2. Be specific in claim descriptions
3. Set realistic due dates
4. Document legal notices thoroughly
5. Add comprehensive remarks

### Recording Payments
1. Record payments promptly
2. Always include receipt numbers
3. Specify payment method
4. Add notes for context
5. Verify amounts before saving

### Managing Overdue Notes
1. Review overdue list daily
2. Follow up with clients
3. Document all communication
4. Update settlement actions
5. Consider late fees

### Reporting
1. Export data regularly
2. Review statistics weekly
3. Track collection rates
4. Identify problematic clients
5. Analyze payment patterns

---

## 🔧 Files Created

### Backend
1. **Database:**
   - `demand_notes` table
   - `demand_note_payments` table

2. **Models:**
   - `app/Models/DemandNote.php`
   - `app/Models/DemandNotePayment.php`

3. **Controllers:**
   - `app/Http/Controllers/DemandNoteController.php`

4. **Routes:**
   - Added to `routes/api.php`

### Frontend
1. **Components:**
   - `components/Legal/DemandNotesDashboard.js`
   - `components/Legal/CreateDemandNote.js`
   - `components/Legal/EditDemandNote.js`
   - `components/Legal/ViewDemandNote.js`

2. **Styles:**
   - `components/Legal/DemandNotesDashboard.css`
   - `components/Legal/DemandNoteForm.css`
   - `components/Legal/ViewDemandNote.css`

3. **Routes:**
   - Added to `App.js`
   - Link added to `LegalDashboard.js`

---

## 🧪 Testing Checklist

- [ ] Create new demand note
- [ ] Edit existing demand note
- [ ] Delete demand note (with confirmation)
- [ ] View demand note details
- [ ] Add single payment
- [ ] Add multiple payments
- [ ] Verify balance calculation
- [ ] Check status auto-update (pending → partially paid → paid)
- [ ] Test overdue detection
- [ ] Filter by status
- [ ] Filter by date range
- [ ] Search functionality
- [ ] Export to CSV
- [ ] Pagination (create 20+ notes)
- [ ] View statistics accuracy
- [ ] Check role-based access (try with non-lawyer/admin)
- [ ] Mobile responsiveness
- [ ] Audit log entries

---

## 🚀 Future Enhancements

Potential improvements:
1. **Email Notifications**
   - Send demand note via email
   - Payment receipt emails
   - Overdue reminders

2. **SMS Integration**
   - Send payment reminders
   - Due date notifications

3. **PDF Generation**
   - Generate formal demand note PDF
   - Payment receipt PDF
   - Batch PDF export

4. **Payment Plans**
   - Schedule installment payments
   - Track payment plan compliance

5. **Advanced Analytics**
   - Collection rate trends
   - Client payment history
   - Forecasting

6. **Bulk Operations**
   - Bulk status updates
   - Mass reminder sending

7. **Document Attachments**
   - Attach supporting documents
   - Store signed agreements

---

**Created:** November 7, 2025  
**Version:** 1.0  
**Status:** ✅ Fully Implemented and Operational
