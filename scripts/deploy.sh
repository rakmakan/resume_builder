#!/bin/bash
# Resume Builder Deployment Script

set -e

echo "🚀 Deploying Resume Builder..."

# Load configuration
CONFIG_FILE="config/app.json"
if [ ! -f "$CONFIG_FILE" ]; then
    echo "❌ Configuration file not found: $CONFIG_FILE"
    exit 1
fi

# Get environment from config
ENVIRONMENT=$(python3 -c "import json; print(json.load(open('$CONFIG_FILE'))['app']['environment'])")
echo "📦 Deploying for environment: $ENVIRONMENT"

# Run setup first
echo "🔧 Running setup..."
./scripts/setup.sh

# Create backup before deployment
echo "💾 Creating backup..."
BACKUP_DIR="database/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/resume_backup_$TIMESTAMP.sqlite"

if [ -f "database/resume.sqlite" ]; then
    cp database/resume.sqlite "$BACKUP_FILE"
    echo "✅ Backup created: $BACKUP_FILE"
fi

# Optimize database
echo "⚡ Optimizing database..."
if [ -f "database/resume.sqlite" ]; then
    php -r "
    require_once 'database/db.php';
    try {
        \$db = ResumeDB::getInstance();
        \$db->execute('VACUUM');
        \$db->execute('ANALYZE');
        echo 'Database optimized\n';
    } catch (Exception \$e) {
        echo 'Database optimization failed: ' . \$e->getMessage() . '\n';
    }
    "
fi

# Production optimizations
if [ "$ENVIRONMENT" = "production" ]; then
    echo "🏭 Applying production optimizations..."
    
    # Clear logs
    echo "Clearing logs..."
    find logs/ -name "*.log" -delete 2>/dev/null || true
    
    # Remove development files
    echo "Removing development files..."
    rm -f test_*.* 2>/dev/null || true
    rm -rf .git/hooks/pre-commit* 2>/dev/null || true
    
    # Optimize PHP
    if command -v opcache &> /dev/null; then
        echo "Optimizing PHP opcache..."
        # This would need server configuration
    fi
fi

# Test the installation
echo "🧪 Testing installation..."
php -l database/db.php
php -l shared/config_loader.php

# Test database connection
php -r "
require_once 'database/db.php';
try {
    \$db = ResumeDB::getInstance();
    echo 'Database connection: OK\n';
} catch (Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage() . '\n';
    exit(1);
}
"

# Test Python backend
echo "Testing Python backend..."
cd resume_builder_backend
if python3 -c "from app.config import DATABASE_PATH; print('Backend config: OK')" 2>/dev/null; then
    echo "✅ Backend configuration: OK"
else
    echo "⚠️ Backend configuration may have issues"
fi
cd ..

echo "✅ Deployment complete!"
echo ""
echo "Application is ready to use:"
echo "- Frontend: Start with 'php -S localhost:8000'"
echo "- Backend: Run 'cd resume_builder_backend && python3 main.py'"
echo "- Database: Located at $(python3 -c "import json; print(json.load(open('$CONFIG_FILE'))['paths']['database'])/resume.sqlite")"