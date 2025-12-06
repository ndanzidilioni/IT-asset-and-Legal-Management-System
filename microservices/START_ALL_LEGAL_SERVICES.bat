@echo off
echo ================================================
echo   LEGAL MANAGEMENT MICROSERVICES LAUNCHER
echo ================================================
echo.
echo Starting all legal microservices...
echo.

REM Start Contract Register Service (Port 8015)
start "Contract Service - Port 8015" cmd /k "cd /d %~dp0contract-service && color 0A && echo CONTRACT REGISTER SERVICE - http://localhost:8015 && php -S 0.0.0.0:8015 index.php"

REM Start Case Management Service (Port 8008)
start "Case Service - Port 8008" cmd /k "cd /d %~dp0case-service && color 0B && echo CASE MANAGEMENT SERVICE - http://localhost:8008 && php -S 0.0.0.0:8008 index.php"

REM Start Client Management Service (Port 8009)
start "Client Service - Port 8009" cmd /k "cd /d %~dp0client-service && color 0C && echo CLIENT MANAGEMENT SERVICE - http://localhost:8009 && php -S 0.0.0.0:8009 index.php"

REM Start Document Management Service (Port 8010)
start "Document Service - Port 8010" cmd /k "cd /d %~dp0document-service && color 0D && echo DOCUMENT MANAGEMENT SERVICE - http://localhost:8010 && php -S 0.0.0.0:8010 index.php"

REM Start Court Scheduling Service (Port 8011)
start "Court Service - Port 8011" cmd /k "cd /d %~dp0court-scheduling-service && color 0E && echo COURT SCHEDULING SERVICE - http://localhost:8011 && php -S 0.0.0.0:8011 index.php"

REM Start Billing Service (Port 8012)
start "Billing Service - Port 8012" cmd /k "cd /d %~dp0billing-finance-service && color 09 && echo BILLING SERVICE - http://localhost:8012 && php -S 0.0.0.0:8012 index.php"

REM Start Compliance Service (Port 8013)
start "Compliance Service - Port 8013" cmd /k "cd /d %~dp0compliance-security-service && color 06 && echo COMPLIANCE SERVICE - http://localhost:8013 && php -S 0.0.0.0:8013 index.php"

REM Start Legal Analytics Service (Port 8014)
start "Analytics Service - Port 8014" cmd /k "cd /d %~dp0legal-analytics-service && color 05 && echo ANALYTICS SERVICE - http://localhost:8014 && php -S 0.0.0.0:8014 index.php"

echo.
echo ================================================
echo All legal services have been started!
echo ================================================
echo.
echo Services running:
echo   - Contract Register:  http://localhost:8015
echo   - Case Management:    http://localhost:8008
echo   - Client Management:  http://localhost:8009
echo   - Document Service:   http://localhost:8010
echo   - Court Scheduling:   http://localhost:8011
echo   - Billing Service:    http://localhost:8012
echo   - Compliance Service: http://localhost:8013
echo   - Analytics Service:  http://localhost:8014
echo.
echo Keep the service windows open to maintain services
echo.
pause
