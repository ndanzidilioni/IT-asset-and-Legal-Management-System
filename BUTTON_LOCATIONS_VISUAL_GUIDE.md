# Visual Guide: Button Locations in Case Management

## 📍 Page Layout Overview

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                          CASE MANAGEMENT                                    ┃
┃                                                                             ┃
┃  ┌─────────────────────────────────────────────────────────────────────┐  ┃
┃  │  Case Management                                    [Monthly Report] │  ┃
┃  │                                        [⏳ Pending Approvals (X)]    │  ┃
┃  │                                        [➕ Create New Case]          │  ┃
┃  └─────────────────────────────────────────────────────────────────────┘  ┃
┃                                                                             ┃
┃  ┌────────────── Statistics Cards ──────────────┐                          ┃
┃  │  Total: 10  │  Active: 8  │  Urgent: 2  │...│                          ┃
┃  └──────────────────────────────────────────────┘                          ┃
┃                                                                             ┃
┃  ┌────────── Filter Buttons ──────────┐                                    ┃
┃  │ [All Cases] [Active] [Urgent] [Pending] │                              ┃
┃  └────────────────────────────────────┘                                    ┃
┃                                                                             ┃
┃  ┌───────────────────── CASE CARD #1 ─────────────────────┐               ┃
┃  │  Case Number: CIVIL/2024/001                    [Active]│               ┃
┃  │  Type: Civil                                            │               ┃
┃  │  Amount: TZS 5,000,000                                  │               ┃
┃  │  Filed: 2024-01-15                                      │               ┃
┃  │                                                         │               ┃
┃  │  [✏️ Edit]  [🗑️ Delete]                                 │               ┃
┃  │  ─────────────────────────────────────────────         │               ┃
┃  │  [👁️ View]                [⚖️ Proceedings]              │               ┃
┃  └─────────────────────────────────────────────────────────┘               ┃
┃                                                                             ┃
┃  ┌───────────────────── CASE CARD #2 ─────────────────────┐               ┃
┃  │  Case Number: CRIMINAL/2024/005              [Urgent]  │               ┃
┃  │  Type: Criminal                                         │               ┃
┃  │  Amount: TZS 2,000,000                                  │               ┃
┃  │  Filed: 2024-02-20                                      │               ┃
┃  │                                                         │               ┃
┃  │  [✏️ Edit]  [🗑️ Delete]                                 │               ┃
┃  │  ─────────────────────────────────────────────         │               ┃
┃  │  [👁️ View]                [⚖️ Proceedings]              │               ┃
┃  └─────────────────────────────────────────────────────────┘               ┃
┃                                                                             ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
```

---

## 🔵 Top Section Buttons

### 1. ➕ Create New Case Button
**Location:** Top-right corner of the page header  
**Color:** Blue (#3b82f6)  
**Size:** Large (12px padding, 16px font)  
**Visibility:** Only shown if `canCreateCase === true`  
**Action:** Navigate to `/legal/cases/create`

**Code Location:** Lines 367-386 in CaseManagement.js
```javascript
{canCreateCase && (
  <button 
    className="btn btn-primary" 
    onClick={() => navigate('/legal/cases/create')}
  >
    ➕ Create New Case
  </button>
)}
```

### 2. ⏳ Pending Approvals Button
**Location:** Top-right, left of Create button  
**Color:** Orange gradient (#f59e0b to #d97706)  
**Visibility:** Only shown if `pendingDeletions.length > 0`  
**Action:** Opens modal showing pending deletion requests

**Code Location:** Lines 346-365 in CaseManagement.js

### 3. 📊 Monthly Report Controls
**Location:** Top-right, in a gray container  
**Components:** 
- Month picker input
- "View Report" button (purple #8b5cf6)

**Code Location:** Lines 314-344 in CaseManagement.js

---

## 🟢 Case Card Buttons (Per Card)

Each case card has **4 buttons** arranged in two rows:

### Row 1 (Top Buttons on Card)
Located in the card header next to the status badge.

#### 3. ✏️ Edit Button
**Color:** Green (#10b981)  
**Size:** Small (6px padding, 14px font)  
**Visibility:** Only if `canCreateCase === true`  
**Action:** Navigate to `/legal/cases/edit/${caseId}`

**Code Location:** Lines 487-500 in CaseManagement.js
```javascript
<button 
  onClick={() => navigate(`/legal/cases/edit/${c.id}`)}
  style={{
    padding: '6px 12px',
    background: '#10b981',
    color: 'white',
    border: 'none',
    borderRadius: '6px',
    cursor: 'pointer',
    fontSize: '14px'
  }}
>
  ✏️ Edit
</button>
```

#### 4. 🗑️ Delete Button
**Color:** Red (#ef4444)  
**Size:** Small (6px padding, 14px font)  
**Visibility:** Only if `canCreateCase === true`  
**Action:** Opens deletion confirmation modal

**Code Location:** Lines 501-515 in CaseManagement.js
```javascript
<button 
  onClick={() => setDeleteConfirm(c)}
  style={{
    padding: '6px 12px',
    background: '#ef4444',
    color: 'white',
    border: 'none',
    borderRadius: '6px',
    cursor: 'pointer',
    fontSize: '14px'
  }}
>
  🗑️ Delete
</button>
```

### Row 2 (Bottom Buttons on Card)
Located at the bottom of the card in a bordered section.

#### 5. 👁️ View Button
**Color:** Blue (#6366f1)  
**Size:** Medium (8px padding, 14px font)  
**Visibility:** Always visible (all authenticated users)  
**Action:** Navigate to `/legal/cases/view/${caseId}`

**Code Location:** Lines 533-547 in CaseManagement.js
```javascript
<button 
  onClick={() => navigate(`/legal/cases/view/${c.id}`)}
  style={{
    flex: 1,
    padding: '8px',
    background: '#6366f1',
    color: 'white',
    border: 'none',
    borderRadius: '6px',
    cursor: 'pointer',
    fontSize: '14px'
  }}
>
  👁️ View
</button>
```

#### 6. ⚖️ Proceedings Button
**Color:** Purple (#8b5cf6)  
**Size:** Medium (8px padding, 14px font)  
**Visibility:** Always visible (all authenticated users)  
**Action:** Navigate to `/legal/cases/${caseId}/proceedings`

**Code Location:** Lines 548-562 in CaseManagement.js
```javascript
<button 
  onClick={() => navigate(`/legal/cases/${c.id}/proceedings`)}
  style={{
    flex: 1,
    padding: '8px',
    background: '#8b5cf6',
    color: 'white',
    border: 'none',
    borderRadius: '6px',
    cursor: 'pointer',
    fontSize: '14px'
  }}
>
  ⚖️ Proceedings
</button>
```

---

## 🎨 Button Styling Summary

| Button | Icon | Color | Hex Code | Visibility Rule |
|--------|------|-------|----------|----------------|
| Create New Case | ➕ | Blue | #3b82f6 | `canCreateCase` |
| Pending Approvals | ⏳ | Orange | #f59e0b | `pendingDeletions > 0` |
| View Report | 📄 | Purple | #8b5cf6 | Always |
| Edit | ✏️ | Green | #10b981 | `canCreateCase` |
| Delete | 🗑️ | Red | #ef4444 | `canCreateCase` |
| View | 👁️ | Blue | #6366f1 | Always |
| Proceedings | ⚖️ | Purple | #8b5cf6 | Always |

---

## 🔐 Visibility Logic

```javascript
// From CaseManagement.js lines 186-193
const canCreateCase = userRole && (
  userRole === 'admin' || 
  userRole === 'lawyer' || 
  userRole === 'developer'
);
```

**Buttons visible ONLY when `canCreateCase === true`:**
- ✅ Create New Case
- ✅ Edit
- ✅ Delete

**Buttons visible for ALL authenticated users:**
- ✅ View
- ✅ Proceedings
- ✅ View Report
- ✅ Pending Approvals (if any exist)

---

## 🐛 Why Buttons Might Not Show

### Issue: `userRole` is `null`
**Causes:**
1. User data not stored in localStorage after login
2. Wrong localStorage key being checked
3. Token expired/invalid

**Fix Applied:**
- Updated authService to check both `'user'` and `'userInfo'` keys
- Login now stores in both keys for compatibility

### Issue: `userRole` exists but `canCreateCase` is false
**Causes:**
1. User role is 'client' or 'ict' (don't have permission)
2. User role stored incorrectly in database

**Solution:**
- Verify user role in database
- Ensure user has role: 'admin', 'lawyer', or 'developer'

---

## ✅ Verification Checklist

After logging in as lawyer/admin, you should see:

- [ ] "Create New Case" button in top-right
- [ ] Monthly report controls in top-right
- [ ] Each case card shows:
  - [ ] "Edit" button (green)
  - [ ] "Delete" button (red)
  - [ ] "View" button (blue)
  - [ ] "Proceedings" button (purple)
- [ ] No console errors in browser DevTools
- [ ] Console shows: "Can create case: true"

---

## 📸 Expected Screenshot

```
╔═══════════════════════════════════════════════════════╗
║  Case Management        [Report] [Approvals] [➕Create] ║
╠═══════════════════════════════════════════════════════╣
║  📊 Total: 10  |  Active: 8  |  Urgent: 2           ║
╠═══════════════════════════════════════════════════════╣
║  [All Cases] [Active] [Urgent] [Pending]             ║
╠═══════════════════════════════════════════════════════╣
║  ┌─────────────────────────────────────────┐         ║
║  │ CIVIL/2024/001              [Active]    │         ║
║  │ Type: Civil                              │         ║
║  │ Amount: TZS 5,000,000                    │         ║
║  │                                          │         ║
║  │ [✏️ Edit] [🗑️ Delete]                    │         ║
║  │ ──────────────────────────────────────   │         ║
║  │ [👁️ View]        [⚖️ Proceedings]        │         ║
║  └─────────────────────────────────────────┘         ║
╚═══════════════════════════════════════════════════════╝
```

If your screen looks like this, everything is working! ✅
