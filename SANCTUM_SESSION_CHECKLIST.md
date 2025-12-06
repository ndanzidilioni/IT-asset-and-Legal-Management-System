# Sanctum Session Authentication Checklist

## Current Issue
- Login works (session created)
- Password change modal appears
- API calls return 401 (Unauthenticated)
- Session not recognized by `/api/*` routes

## Root Cause
Domain/origin mismatch between frontend and backend causing Sanctum to not recognize the session as stateful.

## Required Configuration

### 1. Backend `.env` (CRITICAL - must match frontend origin)

**If React runs on `localhost:3000`:**
```env
APP_URL=http://localhost:8000
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

**If React runs on `127.0.0.1:3000`:**
```env
APP_URL=http://127.0.0.1:8000
SESSION_DOMAIN=127.0.0.1
SANCTUM_STATEFUL_DOMAINS=127.0.0.1:3000,127.0.0.1
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### 2. Backend CORS (`config/cors.php`)
```php
'paths' => [
    'sanctum/csrf-cookie',
    'login-web',
    'logout-web',
    'api/*',
],
'allowed_methods' => ['*'],
'allowed_origins' => [
    'http://localhost:3000',  // or http://127.0.0.1:3000
],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

### 3. Frontend API client (`frontend/src/services/api.js`)
```javascript
const API = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',  // or localhost:8000
  withCredentials: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
});

const AuthAPI = axios.create({
  baseURL: 'http://127.0.0.1:8000',  // or localhost:8000
  withCredentials: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
});
```

### 4. Login flow
```javascript
// 1. Get CSRF cookie
await AuthAPI.get('/sanctum/csrf-cookie');
// 2. Login
await AuthAPI.post('/login-web', { login, password });
// 3. API calls now work with session
await API.get('/user');
await API.post('/change-password', {...});
```

## Verification Steps

### 1. Check domain consistency
- Open React app in browser
- Note the URL (localhost:3000 or 127.0.0.1:3000)
- Ensure `.env` matches this domain

### 2. Check cookies after login
- Open DevTools → Application → Cookies
- Should see for your backend domain:
  - `XSRF-TOKEN`
  - `laravel_session`
  - Domain should match `SESSION_DOMAIN` in `.env`

### 3. Check API requests
- Open DevTools → Network
- POST `/api/change-password` should include:
  - Request header: `X-XSRF-TOKEN`
  - Request header: `Cookie` with both tokens
  - If missing, cookies aren't being sent (domain mismatch)

### 4. Check Laravel logs
```bash
tail -50 storage/logs/laravel.log
```
Look for:
- "Change password attempt" log
- `has_user` should be true
- `auth_check` should be true
- If false, session isn't recognized

## Common Issues

### Issue: Cookies not being sent with API requests
**Cause:** Domain mismatch (localhost vs 127.0.0.1)
**Fix:** Use same domain everywhere (all localhost or all 127.0.0.1)

### Issue: auth()->check() returns false
**Cause:** Frontend origin not in SANCTUM_STATEFUL_DOMAINS
**Fix:** Add exact origin (with port) to SANCTUM_STATEFUL_DOMAINS

### Issue: CSRF token mismatch (419)
**Cause:** Routes not excluded from CSRF or wrong CSRF config
**Fix:** Already fixed in `bootstrap/app.php`

### Issue: CORS errors
**Cause:** Frontend origin not in allowed_origins or credentials not supported
**Fix:** Update `config/cors.php` with correct origin and `supports_credentials: true`

## After Configuration Changes

Always run:
```bash
php artisan config:clear
php artisan optimize:clear
# Restart Laravel server
# Clear browser cookies
# Refresh React app
```

## Debug Commands

### Check current config:
```bash
php artisan config:show sanctum
php artisan config:show session
```

### Check routes:
```bash
php artisan route:list | grep login
php artisan route:list | grep change-password
```

### Test session manually:
Visit: `http://127.0.0.1:8000/debug-session`
Should show session_id and csrf_token

### Test auth manually:
Visit: `http://127.0.0.1:8000/api/debug-auth` (after login)
Should show authenticated: true

## Success Criteria

✅ Login returns `must_change_password: true`
✅ Password change modal appears
✅ Console shows "Session verified, user: {...}"
✅ Console shows "Auth debug: { authenticated: true, ... }"
✅ POST `/api/change-password` returns 200 (not 401)
✅ After password change, can access protected routes
