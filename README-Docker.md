# Resume Builder - Docker Setup

This guide explains how to run the Resume Builder application using Docker with persistent data storage.

## Architecture

The application consists of 4 Docker services:

1. **Database Service** - SQLite with persistent volume storage
2. **Backend Service** - Python AI backend for job fetching and resume generation
3. **Frontend Service** - PHP web interface
4. **Backup Service** - Automated database backups

## Data Persistence Strategy

### Your Question About Database Preservation
✅ **Yes, your database data will be preserved!** Here's how:

1. **Docker Volume**: Uses named volume `db_data` for persistence
2. **Automatic Copy**: Copies existing local database on first run
3. **Backup System**: Automated hourly backups + manual backup tools
4. **Easy Restore**: Simple commands to restore from any backup

## Quick Start

### 1. Initial Setup
```bash
# Create environment file
./docker/docker-env.sh

# Start all services
./docker/docker-manage.sh start
```

### 2. Access Application
- Frontend: http://localhost:8000
- Backend: http://localhost:8001

### 3. Managing Your Data

#### Backup Before Starting (Recommended)
```bash
# Backup your current database
./docker/docker-manage.sh backup
```

#### Start Services
```bash
# This will automatically copy your existing database to Docker volume
./docker/docker-manage.sh start
```

#### Backup from Docker
```bash
# Create backup of current Docker database
./docker/docker-manage.sh backup-docker
```

#### Restore from Backup
```bash
# Restore to Docker from backup
./docker/docker-manage.sh restore-docker backup_filename.sqlite
```

## Available Commands

```bash
# Service Management
./docker/docker-manage.sh start         # Start all services
./docker/docker-manage.sh stop          # Stop all services  
./docker/docker-manage.sh restart       # Restart all services
./docker/docker-manage.sh status        # Show service status

# Database Management
./docker/docker-manage.sh backup              # Backup local database
./docker/docker-manage.sh restore [file]      # Restore local database
./docker/docker-manage.sh backup-docker       # Backup from Docker
./docker/docker-manage.sh restore-docker [file] # Restore to Docker

# Monitoring
./docker/docker-manage.sh logs           # View all logs
./docker/docker-manage.sh logs frontend  # View specific service logs

# Cleanup
./docker/docker-manage.sh cleanup        # Stop and clean up resources
```

## Data Flow

```
Local Database (database/resume.sqlite)
    ↓ (copied on first run)
Docker Volume (db_data)
    ↓ (mounted to)
All Services (/app/database/resume.sqlite)
    ↓ (backed up to)
Backup Directory (database/backups/)
```

## File Structure

```
resume_builder/
├── docker-compose.yml              # Multi-service configuration
├── docker/
│   ├── Dockerfile.backend          # Python backend image
│   ├── Dockerfile.frontend         # PHP frontend image
│   ├── apache.conf                 # Apache configuration
│   ├── docker-manage.sh            # Management script
│   └── docker-env.sh               # Environment setup
├── database/
│   ├── resume.sqlite               # Your original database
│   └── backups/                    # Backup storage
└── .dockerignore                   # Docker ignore rules
```

## Services Details

### Database Service
- **Image**: Alpine Linux with SQLite
- **Volume**: `db_data` (persistent)
- **Function**: Manages database file and ensures persistence

### Backend Service  
- **Image**: Python 3.11 with dependencies
- **Port**: 8001
- **Function**: AI resume builder, job scraping, LinkedIn integration

### Frontend Service
- **Image**: PHP 8.2 with Apache
- **Port**: 8000 
- **Function**: Web interface for resume management

### Backup Service
- **Image**: Alpine Linux 
- **Schedule**: Hourly automatic backups
- **Retention**: Keeps last 10 backups

## Troubleshooting

### Database Not Found
```bash
# Check if volume exists
docker volume ls | grep db_data

# Check volume contents
docker run --rm -v resume_builder_db_data:/data alpine ls -la /data
```

### Services Won't Start
```bash
# Check Docker status
docker info

# View service logs
./docker/docker-manage.sh logs

# Check port conflicts
lsof -i :8000
lsof -i :8001
```

### Data Recovery
```bash
# List available backups
ls -la database/backups/

# Restore from specific backup
./docker/docker-manage.sh restore-docker backup_20240101_120000.sqlite
```

## Production Deployment

1. Update environment variables in `.env`
2. Set `APP_ENV=production`
3. Configure proper security settings
4. Set up external backup strategy
5. Configure reverse proxy (nginx)

## Security Notes

- Database is only accessible within Docker network
- Automatic backups prevent data loss
- Volume permissions properly configured
- Apache security headers enabled