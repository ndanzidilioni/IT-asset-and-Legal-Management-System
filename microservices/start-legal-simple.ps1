# Simple Legal Services Starter
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
    $path = Join-Path $PSScriptRoot $svc.Dir
    Push-Location $path
    Start-Job -ScriptBlock {
        param($port, $dir)
        Set-Location $dir
        php -S "0.0.0.0:$port" index.php
    } -ArgumentList $svc.Port, $path -Name $svc.Name | Out-Null
    Pop-Location
    Write-Host "✅ Started $($svc.Name) on port $($svc.Port)" -ForegroundColor Green
}

Write-Host "`nAll services started as background jobs!" -ForegroundColor Green
Write-Host "`nTest URLs:" -ForegroundColor Yellow
Write-Host "  http://localhost:8008/api/cases"
Write-Host "  http://localhost:8009/api/clients"
Write-Host "  http://localhost:8012/api/invoices"
Write-Host "  http://localhost:8014/api/legal-analytics"
Write-Host "`nTo stop: Get-Job | Stop-Job; Get-Job | Remove-Job" -ForegroundColor Yellow
