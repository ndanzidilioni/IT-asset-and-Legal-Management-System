# Quick Test Instructions for Case Management

## 🚀 Start the Application

### 1. Start Backend (Terminal 1)
```bash
cd Backend
php artisan serve
```
✅ Backend should be running on http://localhost:8000

### 2. Start Frontend (Terminal 2)
```bash
cd frontend
npm start
```
✅ Frontend should open on http://localhost:3000

---

## 🧪 Testing Steps

### Step 1: Login
1. Go to http://localhost:3000/login
2. Login with a lawyer or admin account:
   - **Example credentials:**
     - Email: `lawyer@moi.ac.tz` or `admin@moi.ac.tz`
     - Password: (check your database or use default password)

### Step 2: Navigate to Cases Page
1. After login, click on "Cases" in the navigation menu
2. Or go directly to: http://localhost:3000/legal/cases

### Step 3: Check Buttons Are Visible ✅

**At the top right, you should see:**
- 🟦 **"Create New Case"** button (blue, with ➕ icon)
- 🟧 **"Pending Approvals (X)"** button (orange, if there are pending deletions)
- 🟪 **"Monthly Report"** controls (purple "View Report" button)

**On each case card, you should see:**
- 🟩 **"Edit"** button (green, with ✏️ icon)
- 🟥 **"Delete"** button (red, with 🗑️ icon)
- 🟦 **"View"** button (blue, with 👁️ icon)
- 🟪 **"Proceedings"** button (purple, with ⚖️ icon)

### Step 4: Test Create Button
1. Click **"Create New Case"**
2. Should navigate to `/legal/cases/create`
3. Fill out the form and submit
4. Should redirect back to cases list with new case visible

### Step 5: Test View Button
1. Click **"View"** on any case
2. Should navigate to `/legal/cases/view/{id}`
3. Should see full case details WITHOUT ERRORS
4. Check browser console (F12) - should be no errors

### Step 6: Test Edit Button
1. Click **"Edit"** on any case
2. Should navigate to `/legal/cases/edit/{id}`
3. Form should populate with existing case data
4. Can make changes and save

### Step 7: Test Delete Button
1. Click **"Delete"** on any case
2. Should open a modal asking for deletion reason
3. Enter a reason and submit
4. Should create a deletion request (requires approval)

---

## 🔍 Troubleshooting

### If buttons are NOT visible:

#### Check 1: User Role in Console
Open browser console (F12) and run:
```javascript
console.log(JSON.parse(localStorage.getItem('userInfo')))
```
✅ Should show: `{role: 'lawyer', ...}` or `{role: 'admin', ...}`
❌ If null or role is 'client': Buttons won't show (by design)

#### Check 2: Console Logs
Look for these logs in the console:
```
User from authService: {role: 'lawyer', ...}
Can create case: true
```
❌ If "Can create case: false" → role is not admin/lawyer/developer

#### Check 3: Authentication
Run in console:
```javascript
console.log(localStorage.getItem('token'))
```
✅ Should show a long token string
❌ If null: You're not logged in

#### Fix: Clear and Re-login
1. Open DevTools (F12)
2. Go to **Application** → **Local Storage** → **http://localhost:3000**
3. Click **Clear All**
4. Refresh page
5. Login again

---

## 📊 Expected Console Output

When page loads successfully, you should see:
```
User from authService: {id: 1, role: "lawyer", ...}
User role: lawyer
Can create case: true
Cases API response: {data: [...]}
Processed cases data: [...]
```

---

## ❌ Common Errors and Solutions

### Error: "Unauthenticated" when viewing case
**Solution:** Token expired. Logout and login again.

### Error: "Case not found"
**Solution:** Case might have been deleted. Check database.

### Error: Buttons still not showing
**Solution:** 
1. Make sure you logged in as `lawyer` or `admin` role
2. Clear localStorage and login again
3. Check browser console for "Can create case:" log

### Error: "Failed to load case details"
**Solution:**
1. Check backend is running (http://localhost:8000/api/test)
2. Check network tab in DevTools for the failing request
3. Look at console for detailed error message

---

## 🎯 Success Criteria

✅ All buttons visible for lawyer/admin users
✅ Create button opens create form
✅ View button shows case details without errors
✅ Edit button opens edit form with data
✅ Delete button opens confirmation modal
✅ No console errors when navigating pages

---

## 🔐 User Roles & Permissions

| Role | Create | Edit | Delete | View |
|------|--------|------|--------|------|
| admin | ✅ | ✅ | ✅ | ✅ |
| lawyer | ✅ | ✅ | ✅ | ✅ |
| developer | ✅ | ✅ | ✅ | ✅ |
| client | ❌ | ❌ | ❌ | ✅ |
| ict | ❌ | ❌ | ❌ | ✅ |

---

## 📞 Need Help?

If issues persist after following these steps:
1. Check `CASE_MANAGEMENT_FIX_SUMMARY.md` for technical details
2. Look at browser console for specific error messages
3. Verify database has cases with valid data
4. Ensure backend API is responding (test with: http://localhost:8000/api/test)
