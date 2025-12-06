@echo off
echo Starting all Legal Management Services...
start "Case Management (8008)" /min cmd /c "cd case-service && start.bat"
start "Client Management (8009)" /min cmd /c "cd client-service && start.bat"
start "Document Management (8010)" /min cmd /c "cd document-service && start.bat"
start "Court Scheduling (8011)" /min cmd /c "cd court-scheduling-service && start.bat"
start "Billing Finance (8012)" /min cmd /c "cd billing-finance-service && start.bat"
start "Compliance Security (8013)" /min cmd /c "cd compliance-security-service && start.bat"
start "Legal Analytics (8014)" /min cmd /c "cd legal-analytics-service && start.bat"

echo.
echo ✅ All services starting...
echo.
echo Test with:
echo   http://localhost:8008/api/cases
echo   http://localhost:8009/api/clients
echo   http://localhost:8012/api/invoices
echo   http://localhost:8014/api/legal-analytics
echo.
pause
