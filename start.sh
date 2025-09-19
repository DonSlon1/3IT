#!/bin/bash

# 3IT Test Application Startup Script
# This script starts the Docker containers and sets up the development environment

set -e  # Exit on any error

echo "🚀 Starting 3IT Test Application..."
echo "=================================="

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running. Please start Docker first."
    exit 1
fi

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Error: docker-compose is not installed."
    exit 1
fi

# Create necessary directories
echo "📁 Creating required directories..."
mkdir -p zeta/logs zeta/cache zeta/latte
chmod 755 zeta/logs zeta/cache zeta/latte

# Start Docker containers
echo "🐳 Starting Docker containers..."
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Check if containers are running
if ! docker-compose ps | grep -q "Up"; then
    echo "❌ Error: Failed to start containers"
    docker-compose logs
    exit 1
fi

# Display container status
echo "📊 Container Status:"
docker-compose ps

# Get container URLs
WEB_PORT=$(docker-compose port webserver 80 | cut -d: -f2)
DB_PORT=$(docker-compose port database 3306 | cut -d: -f2)

echo ""
echo "✅ Application started successfully!"
echo "=================================="
echo "🌐 Web Application: http://localhost:${WEB_PORT}"
echo "🗄️  Database: localhost:${DB_PORT}"
echo ""
echo "📋 Available URLs:"
echo "   • Home:     http://localhost:${WEB_PORT}/"
echo "   • Records:  http://localhost:${WEB_PORT}/tabulka"
echo "   • Import:   http://localhost:${WEB_PORT}/download"
echo "   • Export:   http://localhost:${WEB_PORT}/export"
echo "   • API:      http://localhost:${WEB_PORT}/api/stats"
echo ""
echo "🛠️  Useful commands:"
echo "   • Stop:     docker-compose down"
echo "   • Logs:     docker-compose logs -f"
echo "   • Restart:  docker-compose restart"
echo ""
echo "Ready for development! 🎉"