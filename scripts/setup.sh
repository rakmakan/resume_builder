#!/bin/bash
# Resume Builder Setup Script

set -e

echo "🚀 Setting up Resume Builder..."

# Check if we're in the right directory
if [ ! -f "config/app.json" ]; then
    echo "❌ Error: Please run this script from the resume_builder root directory"
    exit 1
fi

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p database/backups
mkdir -p exports
mkdir -p logs

# Set up Python backend
echo "🐍 Setting up Python backend..."
cd resume_builder_backend

# Check if Python is available
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 is required but not installed"
    exit 1
fi

# Install Python dependencies
if [ -f "requirements.txt" ]; then
    echo "Installing Python dependencies..."
    pip3 install -r requirements.txt
elif [ -f "pyproject.toml" ]; then
    echo "Installing Python dependencies with Poetry..."
    if command -v poetry &> /dev/null; then
        poetry install
    else
        echo "⚠️ Poetry not found. Please install poetry or use requirements.txt"
    fi
fi

cd ..

# Set up PHP frontend
echo "🐘 Setting up PHP frontend..."

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is required but not installed"
    exit 1
fi

# Install Composer dependencies if composer.json exists
if [ -f "composer.json" ]; then
    if command -v composer &> /dev/null; then
        echo "Installing PHP dependencies..."
        composer install --no-dev --optimize-autoloader
    else
        echo "⚠️ Composer not found. Please install composer for PHP dependencies"
    fi
fi

# Initialize database if it doesn't exist
echo "🗄️ Setting up database..."
if [ ! -f "database/resume.sqlite" ]; then
    echo "Creating initial database..."
    # The database will be created automatically when first accessed
    php -r "
    require_once 'database/db.php';
    try {
        \$db = ResumeDB::getInstance();
        echo 'Database initialized successfully\n';
    } catch (Exception \$e) {
        echo 'Database initialization failed: ' . \$e->getMessage() . '\n';
        exit(1);
    }
    "
fi

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 database/
chmod -R 755 exports/
chmod -R 755 logs/

echo "✅ Setup complete!"
echo ""
echo "Next steps:"
echo "1. Update config/app.json with your specific paths"
echo "2. Update config/database.json if needed"
echo "3. Start the PHP development server: php -S localhost:8000"
echo "4. Run the Python backend as needed"