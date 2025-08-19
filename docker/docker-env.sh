#!/bin/bash
# Environment setup for Docker services

# Create a .env file for Docker Compose if it doesn't exist
ENV_FILE="$(dirname "$(dirname "${BASH_SOURCE[0]}")")/.env"

if [ ! -f "$ENV_FILE" ]; then
    cat > "$ENV_FILE" << 'EOF'
# Resume Builder Docker Environment

# Application Settings
APP_ENV=development
APP_NAME=resume_builder

# Database Settings
DATABASE_PATH=/app/database/resume.sqlite
DATABASE_BACKUP_INTERVAL=3600

# Frontend Settings
FRONTEND_PORT=8000
APACHE_DOCUMENT_ROOT=/var/www/html

# Backend Settings
BACKEND_PORT=8001
PYTHONPATH=/app
PYTHONUNBUFFERED=1

# Backup Settings
BACKUP_RETENTION_DAYS=30
BACKUP_MAX_COUNT=10

# Security (change these in production)
SECRET_KEY=your_secret_key_here
ALLOWED_HOSTS=localhost,127.0.0.1

# Volume Settings
DB_VOLUME_NAME=resume_builder_db_data
EOF

    echo "Created .env file at: $ENV_FILE"
    echo "Please review and update the settings as needed."
else
    echo ".env file already exists at: $ENV_FILE"
fi