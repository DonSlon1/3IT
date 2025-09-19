#!/bin/bash

# 3IT Test Application Stop Script
# This script stops the Docker containers and cleans up

set -e  # Exit on any error

echo "🛑 Stopping 3IT Test Application..."
echo "==================================="

# Stop and remove containers
echo "🐳 Stopping Docker containers..."
docker-compose down

# Show status
echo "📊 Final container status:"
docker-compose ps

echo ""
echo "✅ Application stopped successfully!"
echo "💡 To start again, run: ./start.sh"