# Start minimal Legal Management services (PHP built-in servers)
param(
    [switch]$Minimized
)

function Ensure-PHP {
    try { php -v | Out-Null } catch {
        Write-Host "PHP not found in PATH. Please install PHP or use XAMPP's php.exe and add it to PATH." -ForegroundColor Red
        exit 1
    }
}

function Start-ServiceServer($Name, $Dir, $Port) {
    $wd = Join-Path -Path $PSScriptRoot -ChildPath $Dir
    if (!(Test-Path (Join-Path $wd 'index.php'))) {
        Write-Host "[$Name] index.php not found in $wd" -ForegroundColor Yellow
        return
    }
    $ws = if ($Minimized) { 'Minimized' } else { 'Normal' }
    Start-Process -FilePath powershell -ArgumentList "-NoLogo -NoProfile -Command cd '$wd'; php -S 0.0.0.0:$Port" -WindowStyle $ws | Out-Null
    Write-Host "Started $Name on http://localhost:$Port" -ForegroundColor Green
}

Write-Host "Starting Legal Management services..." -ForegroundColor Blue
Ensure-PHP

Start-ServiceServer -Name 'Case Management'        -Dir 'case-service'                 -Port 8008
Start-ServiceServer -Name 'Client Management'      -Dir 'client-service'               -Port 8009
Start-ServiceServer -Name 'Document Management'    -Dir 'document-service'             -Port 8010
Start-ServiceServer -Name 'Court Scheduling'       -Dir 'court-scheduling-service'     -Port 8011
Start-ServiceServer -Name 'Billing & Finance'      -Dir 'billing-finance-service'      -Port 8012
Start-ServiceServer -Name 'Compliance & Security'  -Dir 'compliance-security-service'  -Port 8013
Start-ServiceServer -Name 'Legal Analytics'        -Dir 'legal-analytics-service'      -Port 8014
Start-ServiceServer -Name 'Contract Management'    -Dir 'contract-service'             -Port 8015

Write-Host "All launch commands issued. Use below health URLs to verify:" -ForegroundColor Blue
@(
  'http://localhost:8008/health',
  'http://localhost:8009/health',
  'http://localhost:8010/health',
  'http://localhost:8011/health',
  'http://localhost:8012/health',
  'http://localhost:8013/health',
  'http://localhost:8014/health',
  'http://localhost:8015/health'
) | ForEach-Object { Write-Host $_ }

Write-Host "To stop services: Stop-Process -Name php -Force" -ForegroundColor Yellow
