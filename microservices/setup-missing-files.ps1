# Setup Missing Files for Microservices
Write-Host "Setting up missing files for microservices..." -ForegroundColor Blue

$services = @(
    "task-service", "asset-service", "inquiry-service", "notification-service",
    "reporting-service", "client-service", "document-service", "court-scheduling-service",
    "billing-finance-service", "compliance-security-service", "legal-analytics-service"
)

foreach ($service in $services) {
    Write-Host "Setting up $service..." -ForegroundColor Yellow
    
    # Create empty composer.lock
    $composerLockPath = Join-Path $service "composer.lock"
    if (!(Test-Path $composerLockPath)) {
        "{}" | Out-File -FilePath $composerLockPath -Encoding UTF8
    }
    
    Write-Host "Done with $service" -ForegroundColor Green
}

Write-Host "All services have been setup!" -ForegroundColor Green
