# Microservices Setup Script for Scheduling Management System
# PowerShell version for Windows

Write-Host "Starting Microservices Setup for Scheduling Management System" -ForegroundColor Blue

# Function to print colored output
function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Check if Docker is installed
try {
    docker --version | Out-Null
    Write-Status "Docker is available"
} catch {
    Write-Error "Docker is not installed. Please install Docker Desktop first."
    exit 1
}

# Check if Docker Compose is installed
try {
    docker-compose --version | Out-Null
    Write-Status "Docker Compose is available"
} catch {
    Write-Error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
}

Write-Status "Docker and Docker Compose are available"

# Create necessary directories
Write-Status "Creating microservices directory structure..."

# Create directories for each service
$services = @("user-service", "scheduling-service", "task-service", "asset-service", "inquiry-service", "notification-service", "reporting-service")

foreach ($service in $services) {
    $directories = @(
        "$service/app/Models",
        "$service/app/Http/Controllers",
        "$service/app/Http/Middleware",
        "$service/database/migrations",
        "$service/database/seeders",
        "$service/routes",
        "$service/config",
        "$service/storage/logs",
        "$service/bootstrap/cache"
    )
    
    foreach ($dir in $directories) {
        if (!(Test-Path $dir)) {
            New-Item -ItemType Directory -Path $dir -Force | Out-Null
        }
    }
    Write-Status "Created directory structure for $service"
}

# Create shared libraries directory
if (!(Test-Path "shared-libraries")) {
    New-Item -ItemType Directory -Path "shared-libraries" -Force | Out-Null
}
Write-Status "Created shared libraries directory"

# Create monitoring directory
if (!(Test-Path "monitoring")) {
    New-Item -ItemType Directory -Path "monitoring" -Force | Out-Null
}
Write-Status "Created monitoring directory"

Write-Success "Directory structure created successfully"

# Create monitoring configuration
Write-Status "Setting up monitoring configuration..."

$prometheusConfig = @"
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  # - "first_rules.yml"
  # - "second_rules.yml"

scrape_configs:
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']

  - job_name: 'api-gateway'
    static_configs:
      - targets: ['api-gateway:8000']

  - job_name: 'user-service'
    static_configs:
      - targets: ['user-service:8000']

  - job_name: 'scheduling-service'
    static_configs:
      - targets: ['scheduling-service:8000']

  - job_name: 'task-service'
    static_configs:
      - targets: ['task-service:8000']

  - job_name: 'asset-service'
    static_configs:
      - targets: ['asset-service:8000']

  - job_name: 'inquiry-service'
    static_configs:
      - targets: ['inquiry-service:8000']

  - job_name: 'notification-service'
    static_configs:
      - targets: ['notification-service:8000']

  - job_name: 'reporting-service'
    static_configs:
      - targets: ['reporting-service:8000']
"@

$prometheusConfig | Out-File -FilePath "monitoring/prometheus.yml" -Encoding UTF8
Write-Success "Monitoring configuration created"

# Create development environment file
Write-Status "Creating development environment configuration..."

$envConfig = @"
# Development Environment Configuration
COMPOSE_PROJECT_NAME=scheduling-microservices

# Database Passwords
POSTGRES_PASSWORD=password
REDIS_PASSWORD=

# Service URLs
API_GATEWAY_URL=http://localhost:8000
USER_SERVICE_URL=http://localhost:8001
SCHEDULING_SERVICE_URL=http://localhost:8002
TASK_SERVICE_URL=http://localhost:8003
ASSET_SERVICE_URL=http://localhost:8004
INQUIRY_SERVICE_URL=http://localhost:8005
NOTIFICATION_SERVICE_URL=http://localhost:8006
REPORTING_SERVICE_URL=http://localhost:8007
FRONTEND_URL=http://localhost:3000

# Monitoring
PROMETHEUS_URL=http://localhost:9090
GRAFANA_URL=http://localhost:3001
GRAFANA_ADMIN_PASSWORD=admin
"@

$envConfig | Out-File -FilePath ".env.development" -Encoding UTF8
Write-Success "Development environment configuration created"

# Create startup script
Write-Status "Creating startup script..."

$startScript = @'
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
'@

$startScript | Out-File -FilePath "start-services.ps1" -Encoding UTF8
Write-Success "Startup script created"

# Create stop script
$stopScript = @'
Write-Host "Stopping Scheduling Management System Microservices" -ForegroundColor Blue
docker-compose down
Write-Host "All services stopped" -ForegroundColor Green
'@

$stopScript | Out-File -FilePath "stop-services.ps1" -Encoding UTF8
Write-Success "Stop script created"

Write-Success "Microservices setup completed successfully!"
Write-Status ""
Write-Status "Next steps:"
Write-Status "1. Run: .\start-services.ps1"
Write-Status "2. Wait for services to initialize (30-60 seconds)"
Write-Status "3. Access the application at: http://localhost:3000"
Write-Status "4. Monitor services at: http://localhost:9090 (Prometheus)"
Write-Status "5. View dashboards at: http://localhost:3001 (Grafana)"
Write-Status ""
Write-Status "For detailed information, see: MICROSERVICES_SETUP.md"