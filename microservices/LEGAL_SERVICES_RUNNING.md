# ⚖️ Legal Management Services - NOW RUNNING! ✅

## 🚀 All Services Active

All 7 Legal Management services are now running and responding on ports 8008-8014.

## 📡 Service Endpoints

### 1. Case Management Service - Port 8008
**Base URL:** `http://localhost:8008`

#### Available Endpoints:
- `GET /health` - Service health check
- `GET /api/cases` - List all cases
- `GET /api/cases/{id}` - Get specific case
- `POST /api/cases` - Create new case
- `GET /api/cases/statistics` - Case statistics
- `GET /api/cases/dashboard` - Dashboard data

#### Example Data:
```json
{
  "id": 1,
  "case_number": "CASE-2024-001",
  "title": "Smith vs. Johnson",
  "client_name": "John Smith",
  "case_type": "Civil",
  "status": "Active",
  "priority": "High",
  "filed_date": "2024-09-15",
  "assigned_lawyer": "Sarah Johnson",
  "next_hearing": "2024-11-03"
}
```

---

### 2. Client Management Service - Port 8009  
**Base URL:** `http://localhost:8009`

#### Available Endpoints:
- `GET /health` - Service health check
- `GET /api/clients` - List all clients
- `GET /api/clients/{id}` - Get specific client
- `POST /api/clients` - Create new client
- `GET /api/clients/statistics` - Client statistics

#### Example Data:
```json
{
  "id": 1,
  "name": "John Smith",
  "email": "john.smith@email.com",
  "phone": "+1 (555) 123-4567",
  "address": "123 Main St, City, State",
  "client_since": "2024-01-15",
  "active_cases": 2,
  "total_cases": 3,
  "outstanding_balance": 5250.00,
  "status": "Active"
}
```

---

### 3. Document Management Service - Port 8010
**Base URL:** `http://localhost:8010`

#### Available Endpoints:
- `GET /health` - Service health check
- `GET /api/documents` - List all documents
- `GET /api/documents/{id}` - Get specific document
- `POST /api/documents` - Upload document
- `POST /api/documents/{id}/sign` - E-sign document
- `GET /api/documents/statistics` - Document statistics

#### Example Data:
```json
{
  "id": 1,
  "name": "Motion to Dismiss",
  "case_number": "CASE-2024-001",
  "type": "PDF",
  "size": "2.4 MB",
  "created_by": "Sarah Johnson",
  "created_at": "2024-10-24",
  "status": "Signed",
  "version": "2.1",
  "category": "Pleadings"
}
```

---

### 4. Court Scheduling Service - Port 8011
**Base URL:** `http://localhost:8011`

#### Available Endpoints:
- `GET /health` - Service health check
- `GET /api/hearings` - List all hearings
- `POST /api/hearings` - Schedule hearing
- `GET /api/deadlines` - List deadlines
- `GET /api/court-schedules/today` - Today's schedule

#### Example Data:
```json
{
  "id": 1,
  "case_number": "CASE-2024-001",
  "type": "Court Hearing",
  "date": "2024-11-03",
  "time": "10:00 AM",
  "court": "Downtown District Court",
  "judge": "Hon. Maria Rodriguez",
  "room": "Courtroom 3A",
  "status": "Scheduled",
  "assigned_lawyer": "Sarah Johnson"
}
```

---

### 5. Billing & Finance Service - Port 8012
**Base URL:** `http://localhost:8012`

#### Available Endpoints:
- `GET /health` - Service health check
- `GET /api/invoices` - List all invoices
- `POST /api/invoices` - Generate invoice
- `GET /api/billing/summary` - Financial summary
- `GET /api/billing/time-entries` - Time tracking entries
- `POST /api/expenses` - Add expense

#### Example Data:
```json
{
  "monthly_revenue": 125000.00,
  "outstanding_balance": 45000.00,
  "collected_this_month": 80000.00,
  "total_billable_hours": 320.5,
  "average_hourly_rate": 250.00,
  "invoices_sent": 48,
  "invoices_paid": 35,
  "invoices_overdue": 8
}
```

---

### 6. Compliance & Security Service - Port 8013
**Base URL:** `http://localhost:8013`

#### Available Endpoints:
- `GET /health` - Service health check
- `GET /api/audit-logs` - List audit logs
- `GET /api/compliance/status` - Compliance status
- `POST /api/compliance/check` - Run compliance check
- `GET /api/security/alerts` - Security alerts

#### Example Data:
```json
{
  "gdpr_compliant": true,
  "data_encryption_active": true,
  "backup_status": "Completed 2 hours ago",
  "security_incidents": 0,
  "unauthorized_access_attempts": 0,
  "last_security_audit": "2024-10-20"
}
```

---

### 7. Legal Analytics Service - Port 8014
**Base URL:** `http://localhost:8014`

#### Available Endpoints:
- `GET /health` - Service health check
- `GET /api/legal-analytics` - Analytics dashboard
- `GET /api/legal-analytics/dashboard` - Dashboard data
- `POST /api/legal-analytics/report` - Generate custom report
- `GET /api/legal-analytics/reports` - Monthly reports

#### Example Data:
```json
{
  "cases_by_status": {
    "Active": 45,
    "Pending": 18,
    "Completed": 12,
    "On Hold": 5,
    "Closed": 87
  },
  "revenue_trends": {
    "January": 98000,
    "February": 105000,
    "March": 112000,
    "April": 108000,
    "May": 125000,
    "June": 118000
  },
  "lawyer_performance": [
    {"name": "Sarah Johnson", "hours": 245, "cases": 15, "revenue": 98000},
    {"name": "Mike Davis", "hours": 198, "cases": 12, "revenue": 79000}
  ]
}
```

---

## 🧪 Quick Test Commands

### PowerShell:
```powershell
# Test all services
curl http://localhost:8008/api/cases | ConvertFrom-Json
curl http://localhost:8009/api/clients | ConvertFrom-Json
curl http://localhost:8010/api/documents | ConvertFrom-Json
curl http://localhost:8011/api/hearings | ConvertFrom-Json
curl http://localhost:8012/api/invoices | ConvertFrom-Json
curl http://localhost:8013/api/audit-logs | ConvertFrom-Json
curl http://localhost:8014/api/legal-analytics | ConvertFrom-Json
```

### Browser:
Open these URLs in your browser:
- http://localhost:8008/api/cases/dashboard
- http://localhost:8009/api/clients/statistics
- http://localhost:8012/api/billing/summary
- http://localhost:8013/api/compliance/status
- http://localhost:8014/api/legal-analytics

---

## 🎯 Sample Use Cases

### View All Cases
```
GET http://localhost:8008/api/cases
```

### Check Billing Summary
```
GET http://localhost:8012/api/billing/summary
```

### View Today's Court Schedule
```
GET http://localhost:8011/api/court-schedules/today
```

### Check System Compliance
```
GET http://localhost:8013/api/compliance/status
```

### View Analytics Dashboard
```
GET http://localhost:8014/api/legal-analytics
```

---

## 🛑 Stop Services

To stop all services:
```powershell
Get-Process php | Stop-Process
```

---

## 🔄 Restart Services

To restart all services, run:
```powershell
cd "c:\xampp\htdocs\scheduling management system\microservices"

# Stop any running PHP processes
Get-Process php -ErrorAction SilentlyContinue | Stop-Process

# Start each service in background
cd case-service; php -S 0.0.0.0:8008 index.php &
cd ../client-service; php -S 0.0.0.0:8009 index.php &
cd ../document-service; php -S 0.0.0.0:8010 index.php &
cd ../court-scheduling-service; php -S 0.0.0.0:8011 index.php &
cd ../billing-finance-service; php -S 0.0.0.0:8012 index.php &
cd ../compliance-security-service; php -S 0.0.0.0:8013 index.php &
cd ../legal-analytics-service; php -S 0.0.0.0:8014 index.php &
```

---

## 📊 Service Status

All services are **RUNNING** and responding with sample data:

| Service | Port | Status | Endpoints |
|---------|------|--------|-----------|
| Case Management | 8008 | ✅ Running | 6 endpoints |
| Client Management | 8009 | ✅ Running | 5 endpoints |
| Document Management | 8010 | ✅ Running | 6 endpoints |
| Court Scheduling | 8011 | ✅ Running | 5 endpoints |
| Billing & Finance | 8012 | ✅ Running | 6 endpoints |
| Compliance & Security | 8013 | ✅ Running | 5 endpoints |
| Legal Analytics | 8014 | ✅ Running | 4 endpoints |

---

## 🎉 You Can Now Test!

The Legal Management System is fully operational. All endpoints are returning sample data that demonstrates how each service works.

**Next Steps:**
1. Test endpoints in your browser or Postman
2. Build frontend components to display this data
3. Replace sample data with real database connections
4. Add authentication and authorization
5. Deploy to production environment

**Happy Testing!** 🚀
