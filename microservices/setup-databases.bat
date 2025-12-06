@echo off
echo ========================================
echo Legal Management Database Setup
echo ========================================
echo.
echo This script will:
echo 1. Create 7 legal management databases
echo 2. Create all required tables
echo 3. Insert sample data for testing
echo.
echo Requirements:
echo - MySQL/MariaDB running on localhost
echo - Root access to MySQL
echo.
pause

echo.
echo Starting database setup...
echo.

REM Import SQL file
mysql -u root -p < setup-legal-databases.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS! Databases created successfully
    echo ========================================
    echo.
    echo Created databases:
    echo   - case_management_db
    echo   - client_management_db
    echo   - document_management_db
    echo   - court_scheduling_db
    echo   - billing_finance_db
    echo   - compliance_security_db
    echo   - legal_analytics_db
    echo.
    echo Sample data inserted for testing
    echo.
    echo Next steps:
    echo 1. Verify db-config.php settings
    echo 2. Restart your legal services
    echo 3. Test API endpoints
    echo.
) else (
    echo.
    echo ========================================
    echo ERROR: Database setup failed
    echo ========================================
    echo.
    echo Please check:
    echo 1. MySQL is running
    echo 2. Correct password entered
    echo 3. User has CREATE DATABASE permissions
    echo.
)

pause
