# 🚀 Microservices Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the Scheduling Management System microservices architecture.

## 📋 Prerequisites

### System Requirements
- **OS**: Windows 10/11, macOS, or Linux
- **RAM**: Minimum 8GB (16GB recommended)
- **Storage**: 20GB free space
- **CPU**: 4 cores minimum

### Software Requirements
- **Docker Desktop**: Latest version
- **Docker Compose**: v2.0+
- **Git**: For version control
- **PowerShell**: For Windows users

## 🛠️ Installation Steps

### Step 1: Clone and Setup

```bash
# Navigate to your project directory
cd "C:\xampp\htdocs\scheduling management system"

# The microservices are already created in the microservices/ directory
cd microservices
```

### Step 2: Run Setup Script

#### For Windows (PowerShell):
```powershell
# Run the PowerShell setup script
.\setup.ps1
```

#### For Linux/macOS:
```bash
# Run the bash setup script
chmod +x setup.sh
./setup.sh
```

### Step 3: Start Services

#### For Windows:
```powershell
# Start all microservices
.\start-services.ps1
```

#### For Linux/macOS:
```bash
# Start all microservices
./start-services.sh
```

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    API Gateway (Kong)                        │
│                        Port: 8000                           │
└─────────────────┬───────────────────────────────────────────┘
                  │
    ┌─────────────┼─────────────┐
    │             │             │
┌───▼───┐    ┌───▼───┐    ┌───▼───┐
│ User  │    │Sched- │    │ Task  │
│ Mgmt  │    │uling  │    │ Mgmt  │
│ 8001  │    │ 8002  │    │ 8003  │
└───────┘    └───────┘    └───────┘
    │             │             │
┌───▼───┐    ┌───▼───┐    ┌───▼───┐
│Asset  │    │Inquiry│    │Notify │
│ Mgmt  │    │ Mgmt  │    │cation │
│ 8004  │    │ 8005  │    │ 8006  │
└───────┘    └───────┘    └───────┘
    │
┌───▼───┐
│Report │
│ Mgmt  │
│ 8007  │
└───────┘
```

## 🔧 Service Configuration

### API Gateway (Kong)
- **Port**: 8000
- **Function**: Routes requests to appropriate services
- **Features**: Rate limiting, CORS, authentication

### Core Services

#### 1. User Management Service
- **Port**: 8001
- **Database**: `user_management_db`
- **Endpoints**: `/api/auth/*`, `/api/users/*`
- **Features**: Authentication, user management, role-based access

#### 2. Scheduling Service
- **Port**: 8002
- **Database**: `scheduling_db`
- **Endpoints**: `/api/schedules/*`
- **Features**: Schedule management, availability tracking, conflict detection

#### 3. Task Management Service
- **Port**: 8003
- **Database**: `task_management_db`
- **Endpoints**: `/api/tasks/*`
- **Features**: Task creation, assignment, status tracking

#### 4. IT Asset Management Service
- **Port**: 8004
- **Database**: `asset_management_db`
- **Endpoints**: `/api/it-assets/*`, `/api/dropdown-options/*`
- **Features**: Asset registration, categorization, reporting

#### 5. Inquiry Management Service
- **Port**: 8005
- **Database**: `inquiry_management_db`
- **Endpoints**: `/api/inquiries/*`
- **Features**: Customer inquiries, support tickets

#### 6. Notification Service
- **Port**: 8006
- **Database**: `notification_db`
- **Endpoints**: `/api/notifications/*`
- **Features**: Email, SMS, in-app notifications

#### 7. Reporting Service
- **Port**: 8007
- **Database**: `reporting_db`
- **Endpoints**: `/api/reports/*`
- **Features**: Analytics, dashboards, data aggregation

### Frontend Application
- **Port**: 3000
- **Technology**: React
- **Features**: Single page application, responsive design

### Monitoring Stack
- **Prometheus**: Port 9090 (Metrics collection)
- **Grafana**: Port 3001 (Dashboards and visualization)

## 🗄️ Database Architecture

Each service has its own PostgreSQL database:

```sql
-- User Management Database
user_management_db
├── users
├── personal_access_tokens

-- Scheduling Database
scheduling_db
├── schedules

-- Task Management Database
task_management_db
├── tasks

-- Asset Management Database
asset_management_db
├── it_assets
├── dropdown_options
├── location_hierarchy

-- Inquiry Management Database
inquiry_management_db
├── inquiries

-- Notification Database
notification_db
├── notifications
├── notification_templates

-- Reporting Database
reporting_db
├── reports
├── analytics_data
```

## 🔍 Health Checks

### Service Health Endpoints
```bash
# Check API Gateway
curl http://localhost:8000/health

# Check User Service
curl http://localhost:8001/health

# Check Scheduling Service
curl http://localhost:8002/health

# Check Task Service
curl http://localhost:8003/health

# Check Asset Service
curl http://localhost:8004/health

# Check Inquiry Service
curl http://localhost:8005/health

# Check Notification Service
curl http://localhost:8006/health

# Check Reporting Service
curl http://localhost:8007/health
```

### Database Health Checks
```bash
# Check database connections
docker-compose exec user-db pg_isready
docker-compose exec scheduling-db pg_isready
docker-compose exec task-db pg_isready
docker-compose exec asset-db pg_isready
docker-compose exec inquiry-db pg_isready
docker-compose exec notification-db pg_isready
docker-compose exec reporting-db pg_isready
```

## 📊 Monitoring and Logs

### View Service Logs
```bash
# View all logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f user-service
docker-compose logs -f scheduling-service
docker-compose logs -f task-service
docker-compose logs -f asset-service
docker-compose logs -f inquiry-service
docker-compose logs -f notification-service
docker-compose logs -f reporting-service
```

### Access Monitoring Dashboards
- **Prometheus**: http://localhost:9090
- **Grafana**: http://localhost:3001 (admin/admin)

### Key Metrics to Monitor
- Service response times
- Error rates
- Database connection status
- Memory and CPU usage
- Queue processing times

## 🔧 Troubleshooting

### Common Issues

#### 1. Port Conflicts
```bash
# Check if ports are in use
netstat -an | findstr :8000
netstat -an | findstr :8001
# ... check all ports 8000-8007, 3000, 3001, 9090
```

**Solution**: Stop conflicting services or change ports in docker-compose.yml

#### 2. Database Connection Issues
```bash
# Check database containers
docker-compose ps

# Check database logs
docker-compose logs user-db
docker-compose logs scheduling-db
# ... check all database logs
```

**Solution**: Wait for databases to initialize (30-60 seconds) or restart containers

#### 3. Service Not Responding
```bash
# Check service status
docker-compose ps

# Restart specific service
docker-compose restart user-service
docker-compose restart scheduling-service
# ... restart problematic services
```

#### 4. Memory Issues
```bash
# Check Docker resource usage
docker stats

# Increase Docker memory limit in Docker Desktop settings
```

### Reset Everything
```bash
# Stop all services
docker-compose down

# Remove all containers and volumes
docker-compose down -v
docker system prune -f

# Restart services
.\start-services.ps1  # Windows
./start-services.sh   # Linux/macOS
```

## 🚀 Production Deployment

### Environment Variables
Create `.env.production` file:
```env
# Production Environment Configuration
COMPOSE_PROJECT_NAME=scheduling-microservices-prod

# Database Configuration
POSTGRES_PASSWORD=your_secure_password
REDIS_PASSWORD=your_redis_password

# Service URLs (Update with your domain)
API_GATEWAY_URL=https://api.yourdomain.com
FRONTEND_URL=https://yourdomain.com

# Monitoring
PROMETHEUS_URL=https://monitoring.yourdomain.com
GRAFANA_URL=https://grafana.yourdomain.com
```

### Production Docker Compose
```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  # Add production-specific configurations
  api-gateway:
    # ... existing config
    environment:
      - KONG_DATABASE=postgres
      - KONG_PG_HOST=postgres
      - KONG_PG_DATABASE=kong
    restart: unless-stopped

  # Add all other services with production configs
  # ... rest of services
```

### SSL/HTTPS Configuration
```yaml
# Add SSL configuration to API Gateway
api-gateway:
  # ... existing config
  volumes:
    - ./ssl/cert.pem:/kong/ssl/cert.pem
    - ./ssl/key.pem:/kong/ssl/key.pem
```

### Load Balancing
```yaml
# Add load balancer configuration
nginx:
  image: nginx:alpine
  ports:
    - "80:80"
    - "443:443"
  volumes:
    - ./nginx/nginx.conf:/etc/nginx/nginx.conf
    - ./ssl:/etc/nginx/ssl
  depends_on:
    - api-gateway
```

## 📈 Scaling

### Horizontal Scaling
```bash
# Scale specific services
docker-compose up -d --scale user-service=3
docker-compose up -d --scale scheduling-service=2
docker-compose up -d --scale task-service=2
```

### Database Scaling
```yaml
# Add read replicas
user-db-read:
  image: postgres:14
  environment:
    - POSTGRES_DB=user_management_db
    - POSTGRES_USER=postgres
    - POSTGRES_PASSWORD=password
  volumes:
    - user_db_data:/var/lib/postgresql/data
```

## 🔒 Security Considerations

### Authentication
- JWT tokens for service-to-service communication
- API Gateway handles authentication
- Role-based access control

### Network Security
- Services communicate through internal Docker network
- External access only through API Gateway
- Database access restricted to services

### Data Security
- Encrypted database connections
- Secure password storage
- Regular security updates

## 📚 Maintenance

### Regular Tasks
- Monitor service health
- Check database performance
- Update dependencies
- Review logs for errors
- Backup databases

### Backup Strategy
```bash
# Backup all databases
docker-compose exec user-db pg_dump -U postgres user_management_db > backup_user_db.sql
docker-compose exec scheduling-db pg_dump -U postgres scheduling_db > backup_scheduling_db.sql
# ... backup all databases
```

### Updates
```bash
# Update services
docker-compose pull
docker-compose up -d

# Update specific service
docker-compose pull user-service
docker-compose up -d user-service
```

## 🎯 Performance Optimization

### Caching
- Redis for session storage
- Application-level caching
- Database query optimization

### Database Optimization
- Proper indexing
- Query optimization
- Connection pooling

### Service Optimization
- Async processing for heavy operations
- Queue-based task processing
- Efficient API design

---

*This deployment guide provides comprehensive instructions for deploying and managing your microservices architecture.*
