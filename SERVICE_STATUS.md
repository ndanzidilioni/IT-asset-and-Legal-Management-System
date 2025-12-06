# Service Status and Configuration

## Services Running

### Microservices (Legal Management)
All services started via `start-legal-services.ps1 -Minimized`

- ✅ **Case Management** - http://localhost:8008
- ✅ **Client Management** - http://localhost:8009
- ✅ **Document Management** - http://localhost:8010
- ✅ **Court Scheduling** - http://localhost:8011
- ✅ **Billing & Finance** - http://localhost:8012
- ✅ **Compliance & Security** - http://localhost:8013
- ✅ **Legal Analytics** - http://localhost:8014
- ✅ **Contract Management** - http://localhost:8015 (NEW - Added to startup script)

### Laravel Backend (IT Asset Management)
Started via `php artisan serve --host=0.0.0.0 --port=8000`

- ✅ **IT Asset API** - http://localhost:8000/api
  - Database: MySQL (scheduling)
  - Authentication: Laravel Sanctum (token-based)

## Recent Fixes

### 1. Case Management Statistics Endpoint (Fixed ✅)
**Issue:** `GET http://localhost:8008/api/cases/statistics` returned 500 error

**Root Cause:** SQL queries used incorrect column names
- Used `status` instead of `current_status`
- Used `case_type` instead of `nature_of_case`
- Incorrect status enum values

**Fix Applied:**
- Updated `microservices/case-service/index.php` line 334-341
- Changed column names to match database schema
- Updated status values to match enum: 'Active', 'Pending Appeal', 'Pending Hearing'

**Verified:** Returns proper statistics:
```json
{
  "success": true,
  "data": {
    "total_cases": 6,
    "active_cases": 4,
    "pending_cases": 2,
    "urgent_cases": 0,
    "cases_by_type": {"Civil": 3, "Criminal": 3}
  }
}
```

### 2. Contract Service Missing (Fixed ✅)
**Issue:** `POST http://localhost:8015/api/contracts` - Connection refused

**Root Cause:** Contract service was not included in the startup script

**Fix Applied:**
- Added Contract Management service to `start-legal-services.ps1`
- Added health check URL for port 8015
- Service now starts automatically with other microservices

**Verified:** Contract creation works successfully

### 3. IT Asset Statistics Endpoint (Fixed ✅)
**Issue:** `GET http://localhost:8000/api/it-assets/statistics` - 500 Internal Server Error

**Root Cause:** 
- Route was protected by `auth:sanctum` middleware
- Unauthenticated requests tried to redirect to non-existent 'login' route
- Frontend dashboard needed public access to statistics

**Fix Applied:**
- Moved statistics endpoint outside authentication middleware in `Backend/routes/api.php`
- Added exception handler for authentication errors in `Backend/bootstrap/app.php`
- Statistics now accessible without authentication for dashboard display

**Verified:** Returns proper statistics:
```json
{
  "success": true,
  "data": {
    "total": 127,
    "active": 64,
    "maintenance": 21,
    "disposed": 20,
    "conditions": {"excellent": 30, "good": 60, "fair": 12, "poor": 8, "damaged": 17}
  }
}
```

## Starting Services

### Legal Microservices
```powershell
cd "c:\xampp\htdocs\scheduling management system\microservices"
.\start-legal-services.ps1 -Minimized
```

### Laravel Backend
```powershell
cd "c:\xampp\htdocs\scheduling management system\Backend"
php artisan serve --host=0.0.0.0 --port=8000
```

### Stop All Services
```powershell
Stop-Process -Name php -Force
```

## Notes

- **IT Asset Statistics:** Currently public for dashboard access. Consider adding token-based auth when frontend authentication is properly configured.
- **Database:** MySQL running on localhost:3306 via XAMPP
- **Frontend:** Should be accessible on port 3000 (React dev server)

## Health Check URLs

```
http://localhost:8008/health - Case Management
http://localhost:8009/health - Client Management
http://localhost:8010/health - Document Management
http://localhost:8011/health - Court Scheduling
http://localhost:8012/health - Billing & Finance
http://localhost:8013/health - Compliance & Security
http://localhost:8014/health - Legal Analytics
http://localhost:8015/health - Contract Management
http://localhost:8000/up - Laravel Backend
```

---
*Last Updated: November 4, 2025*
