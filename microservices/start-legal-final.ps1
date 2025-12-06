# Final Legal Services Starter - Uses Start-Process with WorkingDirectory
Write-Host "Starting Legal Management Services..." -ForegroundColor Cyan

$services = @(
    @{Name="Case Management"; Dir="case-service"; Port=8008},
    @{Name="Client Management"; Dir="client-service"; Port=8009},
    @{Name="Document Management"; Dir="document-service"; Port=8010},
    @{Name="Court Scheduling"; Dir="court-scheduling-service"; Port=8011},
    @{Name="Billing & Finance"; Dir="billing-finance-service"; Port=8012},
    @{Name="Compliance & Security"; Dir="compliance-security-service"; Port=8013},
    @{Name="Legal Analytics"; Dir="legal-analytics-service"; Port=8014}
)

foreach ($svc in $services) {
    $fullPath = Join-Path $PSScriptRoot $svc.Dir
    
    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = "php"
    $psi.Arguments = "-S 0.0.0.0:$($svc.Port) index.php"
    $psi.WorkingDirectory = $fullPath
    $psi.WindowStyle = [System.Diagnostics.ProcessWindowStyle]::Minimized
    
    [System.Diagnostics.Process]::Start($psi) | Out-Null
    
    Write-Host "✅ Started $($svc.Name) on http://localhost:$($svc.Port)" -ForegroundColor Green
}

Write-Host "`nAll services started!" -ForegroundColor Green
Write-Host "`nTest URLs:" -ForegroundColor Yellow
Write-Host "  http://localhost:8008/api/cases - List all cases"
Write-Host "  http://localhost:8008/api/cases/statistics - Case statistics"  
Write-Host "  http://localhost:8009/api/clients - List all clients"
Write-Host "  http://localhost:8010/api/documents - List all documents"
Write-Host "  http://localhost:8011/api/hearings - Court hearings"
Write-Host "  http://localhost:8012/api/invoices - Invoices list"
Write-Host "  http://localhost:8012/api/billing/summary - Billing summary"
Write-Host "  http://localhost:8013/api/audit-logs - Audit logs"
Write-Host "  http://localhost:8013/api/compliance/status - Compliance status"
Write-Host "  http://localhost:8014/api/legal-analytics - Analytics dashboard"
Write-Host "`nTo stop all: Get-Process php | Stop-Process" -ForegroundColor Yellow
