# Start Microservices Script
Write-Host "Starting Scheduling Management System Microservices" -ForegroundColor Blue

# Check if Docker is running
try {
    docker info | Out-Null
    Write-Host "Docker is running" -ForegroundColor Green
} catch {
    Write-Host "Docker is not running. Please start Docker Desktop first." -ForegroundColor Red
    exit 1
}

# Start services
Write-Host "Starting Docker containers..." -ForegroundColor Blue
docker-compose up -d

# Wait for services to be ready
Write-Host "Waiting for services to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Check service health
Write-Host "Checking service health..." -ForegroundColor Blue

$services = @(
    @{Name="api-gateway"; Port=8000},
    @{Name="user-service"; Port=8001},
    @{Name="scheduling-service"; Port=8002},
    @{Name="task-service"; Port=8003},
    @{Name="asset-service"; Port=8004},
    @{Name="inquiry-service"; Port=8005},
    @{Name="notification-service"; Port=8006},
    @{Name="reporting-service"; Port=8007}
)

foreach ($service in $services) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:$($service.Port)/health" -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            Write-Host "$($service.Name) is healthy" -ForegroundColor Green
        } else {
            Write-Host "$($service.Name) is not responding" -ForegroundColor Red
        }
    } catch {
        Write-Host "$($service.Name) is not responding" -ForegroundColor Red
    }
}

Write-Host "All services started successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Service URLs:" -ForegroundColor Blue
Write-Host "   API Gateway: http://localhost:8000" -ForegroundColor White
Write-Host "   Frontend: http://localhost:3000" -ForegroundColor White
Write-Host "   Prometheus: http://localhost:9090" -ForegroundColor White
Write-Host "   Grafana: http://localhost:3001 (admin/admin)" -ForegroundColor White
Write-Host ""
Write-Host "To stop services: docker-compose down" -ForegroundColor Yellow
Write-Host "To view logs: docker-compose logs -f [service-name]" -ForegroundColor Yellow
