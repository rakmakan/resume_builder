#!/bin/bash
# Docker Management Script for Resume Builder

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}=== $1 ===${NC}"
}

# Function to check if Docker is running
check_docker() {
    if ! docker info >/dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
}

# Function to backup current database
backup_database() {
    print_header "Creating Database Backup"
    
    BACKUP_DIR="$PROJECT_DIR/database/backups"
    mkdir -p "$BACKUP_DIR"
    
    if [ -f "$PROJECT_DIR/database/resume.sqlite" ]; then
        TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
        BACKUP_FILE="$BACKUP_DIR/backup_before_docker_$TIMESTAMP.sqlite"
        cp "$PROJECT_DIR/database/resume.sqlite" "$BACKUP_FILE"
        print_status "Database backed up to: $BACKUP_FILE"
    else
        print_warning "No existing database found to backup"
    fi
}

# Function to restore database from backup
restore_database() {
    print_header "Restoring Database from Backup"
    
    BACKUP_DIR="$PROJECT_DIR/database/backups"
    
    if [ -z "$1" ]; then
        echo "Available backups:"
        ls -la "$BACKUP_DIR"/*.sqlite 2>/dev/null || echo "No backups found"
        echo ""
        read -p "Enter backup filename: " BACKUP_FILE
    else
        BACKUP_FILE="$1"
    fi
    
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_FILE"
    
    if [ -f "$BACKUP_PATH" ]; then
        cp "$BACKUP_PATH" "$PROJECT_DIR/database/resume.sqlite"
        print_status "Database restored from: $BACKUP_PATH"
    else
        print_error "Backup file not found: $BACKUP_PATH"
        exit 1
    fi
}

# Function to start services
start_services() {
    print_header "Starting Resume Builder Services"
    check_docker
    
    cd "$PROJECT_DIR"
    
    # Create necessary directories
    mkdir -p database/backups exports logs
    
    # Start services
    docker-compose up -d
    
    print_status "Services started successfully!"
    print_status "Frontend: http://localhost:8000"
    print_status "Backend: http://localhost:8001"
    
    # Wait for services to be ready
    print_status "Waiting for services to be ready..."
    sleep 10
    
    # Check service status
    docker-compose ps
}

# Function to stop services
stop_services() {
    print_header "Stopping Resume Builder Services"
    cd "$PROJECT_DIR"
    docker-compose down
    print_status "Services stopped successfully!"
}

# Function to restart services
restart_services() {
    print_header "Restarting Resume Builder Services"
    stop_services
    start_services
}

# Function to view logs
view_logs() {
    cd "$PROJECT_DIR"
    
    if [ -z "$1" ]; then
        docker-compose logs -f
    else
        docker-compose logs -f "$1"
    fi
}

# Function to backup database from Docker
backup_docker_db() {
    print_header "Backing up Database from Docker"
    cd "$PROJECT_DIR"
    
    TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    BACKUP_FILE="database/backups/docker_backup_$TIMESTAMP.sqlite"
    
    docker-compose exec database cp /app/data/resume.sqlite /app/database/docker_backup_temp.sqlite
    cp database/docker_backup_temp.sqlite "$BACKUP_FILE"
    rm -f database/docker_backup_temp.sqlite
    
    print_status "Docker database backed up to: $BACKUP_FILE"
}

# Function to restore database to Docker
restore_docker_db() {
    print_header "Restoring Database to Docker"
    
    if [ -z "$1" ]; then
        echo "Available backups:"
        ls -la database/backups/*.sqlite 2>/dev/null || echo "No backups found"
        echo ""
        read -p "Enter backup filename: " BACKUP_FILE
    else
        BACKUP_FILE="$1"
    fi
    
    BACKUP_PATH="database/backups/$BACKUP_FILE"
    
    if [ -f "$BACKUP_PATH" ]; then
        cd "$PROJECT_DIR"
        cp "$BACKUP_PATH" database/restore_temp.sqlite
        docker-compose exec database cp /app/database/restore_temp.sqlite /app/data/resume.sqlite
        rm -f database/restore_temp.sqlite
        
        # Restart services to pick up new database
        docker-compose restart frontend backend
        
        print_status "Database restored to Docker from: $BACKUP_PATH"
    else
        print_error "Backup file not found: $BACKUP_PATH"
        exit 1
    fi
}

# Function to show status
show_status() {
    print_header "Resume Builder Status"
    cd "$PROJECT_DIR"
    
    if docker-compose ps | grep -q "Up"; then
        print_status "Services are running:"
        docker-compose ps
        
        echo ""
        print_status "Service URLs:"
        echo "  Frontend: http://localhost:8000"
        echo "  Backend:  http://localhost:8001"
        
        echo ""
        print_status "Database volume info:"
        docker volume inspect resume_builder_db_data --format "{{.Mountpoint}}" 2>/dev/null || echo "Volume not found"
    else
        print_warning "Services are not running"
        echo "Run '$0 start' to start services"
    fi
}

# Function to clean up
cleanup() {
    print_header "Cleaning Up Docker Resources"
    cd "$PROJECT_DIR"
    
    read -p "This will stop services and remove containers. Continue? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        docker-compose down -v
        docker system prune -f
        print_status "Cleanup completed!"
    else
        print_status "Cleanup cancelled"
    fi
}

# Function to show help
show_help() {
    echo "Resume Builder Docker Management Script"
    echo ""
    echo "Usage: $0 [COMMAND] [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  start                     Start all services"
    echo "  stop                      Stop all services"
    echo "  restart                   Restart all services"
    echo "  status                    Show service status"
    echo "  logs [service]            View logs (all services or specific service)"
    echo "  backup                    Backup local database before starting Docker"
    echo "  restore [backup_file]     Restore local database from backup"
    echo "  backup-docker             Backup database from Docker volume"
    echo "  restore-docker [backup]   Restore database to Docker volume"
    echo "  cleanup                   Stop services and clean up Docker resources"
    echo "  help                      Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 start                  # Start all services"
    echo "  $0 logs frontend          # View frontend logs"
    echo "  $0 backup-docker          # Backup current Docker database"
    echo "  $0 restore backup.sqlite  # Restore from backup.sqlite"
}

# Main script logic
case "${1:-help}" in
    start)
        start_services
        ;;
    stop)
        stop_services
        ;;
    restart)
        restart_services
        ;;
    status)
        show_status
        ;;
    logs)
        view_logs "$2"
        ;;
    backup)
        backup_database
        ;;
    restore)
        restore_database "$2"
        ;;
    backup-docker)
        backup_docker_db
        ;;
    restore-docker)
        restore_docker_db "$2"
        ;;
    cleanup)
        cleanup
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        print_error "Unknown command: $1"
        echo ""
        show_help
        exit 1
        ;;
esac