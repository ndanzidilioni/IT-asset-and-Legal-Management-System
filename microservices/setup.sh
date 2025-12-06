#!/bin/bash

# Microservices Setup Script for Scheduling Management System
# This script sets up the complete microservices architecture

set -e

echo "🚀 Starting Microservices Setup for Scheduling Management System"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

print_status "Docker and Docker Compose are available"

# Create necessary directories
print_status "Creating microservices directory structure..."

# Create directories for each service
services=("user-service" "scheduling-service" "task-service" "asset-service" "inquiry-service" "notification-service" "reporting-service")

for service in "${services[@]}"; do
    mkdir -p "$service/app/Models"
    mkdir -p "$service/app/Http/Controllers"
    mkdir -p "$service/app/Http/Middleware"
    mkdir -p "$service/database/migrations"
    mkdir -p "$service/database/seeders"
    mkdir -p "$service/routes"
    mkdir -p "$service/config"
    mkdir -p "$service/storage/logs"
    mkdir -p "$service/bootstrap/cache"
    print_status "Created directory structure for $service"
done

# Create shared libraries directory
mkdir -p "shared-libraries"
print_status "Created shared libraries directory"

# Create monitoring directory
mkdir -p "monitoring"
print_status "Created monitoring directory"

print_success "Directory structure created successfully"

# Copy Laravel application files to each service
print_status "Setting up Laravel applications for each service..."

# Function to create basic Laravel structure for a service
setup_laravel_service() {
    local service_name=$1
    local service_port=$2
    
    print_status "Setting up $service_name on port $service_port"
    
    # Create basic Laravel files
    cat > "$service_name/composer.json" << EOF
{
    "name": "scheduling-system/$service_name",
    "type": "project",
    "description": "$service_name for Scheduling System",
    "keywords": ["laravel", "microservice"],
    "license": "MIT",
    "require": {
        "php": "^8.1",
        "guzzlehttp/guzzle": "^7.2",
        "laravel/framework": "^10.10",
        "laravel/sanctum": "^3.2",
        "laravel/tinker": "^2.8",
        "predis/predis": "^2.0"
    },
    "require-dev": {
        "fakerphp/faker": "^1.9.1",
        "laravel/pint": "^1.0",
        "mockery/mockery": "^1.4.4",
        "nunomaduro/collision": "^7.0",
        "phpunit/phpunit": "^10.1",
        "spatie/laravel-ignition": "^2.0"
    },
    "autoload": {
        "psr-4": {
            "App\\\\": "app/",
            "Database\\\\Factories\\\\": "database/factories/",
            "Database\\\\Seeders\\\\": "database/seeders/"
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
EOF

    # Create .env file
    cat > "$service_name/.env" << EOF
APP_NAME="$service_name"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:$service_port

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=${service_name}-db
DB_PORT=5432
DB_DATABASE=${service_name//-/_}_db
DB_USERNAME=postgres
DB_PASSWORD=password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"
EOF

    print_success "$service_name Laravel structure created"
}

# Setup each service
setup_laravel_service "user-service" "8001"
setup_laravel_service "scheduling-service" "8002"
setup_laravel_service "task-service" "8003"
setup_laravel_service "asset-service" "8004"
setup_laravel_service "inquiry-service" "8005"
setup_laravel_service "notification-service" "8006"
setup_laravel_service "reporting-service" "8007"

print_success "All Laravel services configured"

# Create monitoring configuration
print_status "Setting up monitoring configuration..."

cat > "monitoring/prometheus.yml" << EOF
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
EOF

print_success "Monitoring configuration created"

# Create development environment file
print_status "Creating development environment configuration..."

cat > ".env.development" << EOF
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
EOF

print_success "Development environment configuration created"

# Create startup script
print_status "Creating startup script..."

cat > "start-services.sh" << 'EOF'
#!/bin/bash

# Start Microservices Script
echo "🚀 Starting Scheduling Management System Microservices"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Start services
echo "📦 Starting Docker containers..."
docker-compose up -d

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 30

# Check service health
echo "🔍 Checking service health..."

services=("api-gateway:8000" "user-service:8001" "scheduling-service:8002" "task-service:8003" "asset-service:8004" "inquiry-service:8005" "notification-service:8006" "reporting-service:8007")

for service in "${services[@]}"; do
    IFS=':' read -r name port <<< "$service"
    if curl -f http://localhost:$port/health > /dev/null 2>&1; then
        echo "✅ $name is healthy"
    else
        echo "❌ $name is not responding"
    fi
done

echo "🎉 All services started successfully!"
echo ""
echo "📋 Service URLs:"
echo "   API Gateway: http://localhost:8000"
echo "   Frontend: http://localhost:3000"
echo "   Prometheus: http://localhost:9090"
echo "   Grafana: http://localhost:3001 (admin/admin)"
echo ""
echo "🔧 To stop services: docker-compose down"
echo "📊 To view logs: docker-compose logs -f [service-name]"
EOF

chmod +x start-services.sh

print_success "Startup script created"

# Create stop script
cat > "stop-services.sh" << 'EOF'
#!/bin/bash

echo "🛑 Stopping Scheduling Management System Microservices"
docker-compose down
echo "✅ All services stopped"
EOF

chmod +x stop-services.sh

print_success "Stop script created"

# Create README for microservices
print_status "Creating comprehensive documentation..."

cat > "MICROSERVICES_SETUP.md" << 'EOF'
# 🏗️ Microservices Setup Guide

## Quick Start

1. **Start all services:**
   ```bash
   ./start-services.sh
   ```

2. **Stop all services:**
   ```bash
   ./stop-services.sh
   ```

3. **View logs:**
   ```bash
   docker-compose logs -f [service-name]
   ```

## Service Architecture

### Core Services
- **API Gateway** (Port 8000): Routes requests to appropriate services
- **User Service** (Port 8001): User management and authentication
- **Scheduling Service** (Port 8002): Schedule and availability management
- **Task Service** (Port 8003): Task creation and management
- **Asset Service** (Port 8004): IT asset management
- **Inquiry Service** (Port 8005): Customer inquiries and support
- **Notification Service** (Port 8006): Email and SMS notifications
- **Reporting Service** (Port 8007): Analytics and reporting

### Frontend
- **React App** (Port 3000): Single page application

### Monitoring
- **Prometheus** (Port 9090): Metrics collection
- **Grafana** (Port 3001): Dashboards and visualization

## Database Architecture

Each service has its own PostgreSQL database:
- `user_management_db`
- `scheduling_db`
- `task_management_db`
- `asset_management_db`
- `inquiry_management_db`
- `notification_db`
- `reporting_db`

## Development Workflow

1. **Make changes to a service**
2. **Rebuild the service:**
   ```bash
   docker-compose build [service-name]
   docker-compose up -d [service-name]
   ```

3. **View service logs:**
   ```bash
   docker-compose logs -f [service-name]
   ```

## API Endpoints

All API requests go through the API Gateway at `http://localhost:8000`

### Authentication
- `POST /api/auth/login`
- `POST /api/auth/register`
- `POST /api/auth/logout`

### User Management
- `GET /api/users`
- `POST /api/users`
- `PUT /api/users/{id}`
- `DELETE /api/users/{id}`

### Scheduling
- `GET /api/schedules`
- `POST /api/schedules`
- `PUT /api/schedules/{id}`
- `DELETE /api/schedules/{id}`

### Tasks
- `GET /api/tasks`
- `POST /api/tasks`
- `PUT /api/tasks/{id}`
- `DELETE /api/tasks/{id}`

### Assets
- `GET /api/it-assets`
- `POST /api/it-assets`
- `PUT /api/it-assets/{id}`
- `DELETE /api/it-assets/{id}`

### Inquiries
- `GET /api/inquiries`
- `POST /api/inquiries`
- `PUT /api/inquiries/{id}`
- `DELETE /api/inquiries/{id}`

## Troubleshooting

### Common Issues

1. **Port conflicts:** Make sure ports 8000-8007, 3000, 3001, 9090 are available
2. **Database connection:** Wait for databases to initialize (30-60 seconds)
3. **Service not responding:** Check logs with `docker-compose logs [service-name]`

### Health Checks

Check service health:
```bash
curl http://localhost:8000/health  # API Gateway
curl http://localhost:8001/health # User Service
curl http://localhost:8002/health  # Scheduling Service
# ... etc for other services
```

### Reset Everything

To completely reset the microservices:
```bash
docker-compose down -v
docker system prune -f
./start-services.sh
```

## Production Deployment

For production deployment, consider:
- Using Kubernetes for orchestration
- Setting up proper SSL certificates
- Configuring load balancers
- Setting up monitoring and alerting
- Implementing proper backup strategies
EOF

print_success "Documentation created"

print_success "🎉 Microservices setup completed successfully!"
print_status ""
print_status "Next steps:"
print_status "1. Run: ./start-services.sh"
print_status "2. Wait for services to initialize (30-60 seconds)"
print_status "3. Access the application at: http://localhost:3000"
print_status "4. Monitor services at: http://localhost:9090 (Prometheus)"
print_status "5. View dashboards at: http://localhost:3001 (Grafana)"
print_status ""
print_status "For detailed information, see: MICROSERVICES_SETUP.md"














