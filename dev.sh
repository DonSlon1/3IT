#!/bin/bash

# 3IT Test Application Development Script
# Quick development commands and utilities

set -e  # Exit on any error

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

show_help() {
    echo -e "${BLUE}3IT Test Application - Development Tools${NC}"
    echo "======================================"
    echo ""
    echo "Usage: ./dev.sh [command]"
    echo ""
    echo "Commands:"
    echo "  start     - Start the application"
    echo "  stop      - Stop the application"
    echo "  restart   - Restart the application"
    echo "  logs      - Show application logs"
    echo "  status    - Show container status"
    echo "  db        - Connect to database shell"
    echo "  import    - Import sample data"
    echo "  test      - Run quick functionality test"
    echo "  clean     - Clean cache and logs"
    echo "  help      - Show this help"
    echo ""
}

start_app() {
    echo -e "${GREEN}🚀 Starting application...${NC}"
    ./start.sh
}

stop_app() {
    echo -e "${YELLOW}🛑 Stopping application...${NC}"
    ./stop.sh
}

restart_app() {
    echo -e "${YELLOW}🔄 Restarting application...${NC}"
    docker-compose restart
    echo -e "${GREEN}✅ Application restarted${NC}"
}

show_logs() {
    echo -e "${BLUE}📋 Showing logs...${NC}"
    docker-compose logs -f
}

show_status() {
    echo -e "${BLUE}📊 Container Status:${NC}"
    docker-compose ps
    echo ""

    if docker-compose ps | grep -q "Up"; then
        WEB_PORT=$(docker-compose port webserver 80 2>/dev/null | cut -d: -f2)
        if [ ! -z "$WEB_PORT" ]; then
            echo -e "${GREEN}🌐 Application running at: http://localhost:${WEB_PORT}${NC}"
        fi
    else
        echo -e "${RED}❌ Application is not running${NC}"
    fi
}

connect_db() {
    echo -e "${BLUE}🗄️  Connecting to database...${NC}"
    docker exec -it 3it_test_database mariadb -uroot -ptoor 3it-test
}

import_data() {
    echo -e "${BLUE}📥 Importing sample data...${NC}"
    WEB_PORT=$(docker-compose port webserver 80 2>/dev/null | cut -d: -f2)
    if [ ! -z "$WEB_PORT" ]; then
        curl -s "http://localhost:${WEB_PORT}/download" > /dev/null
        echo -e "${GREEN}✅ Data imported successfully${NC}"
    else
        echo -e "${RED}❌ Application is not running${NC}"
    fi
}

test_app() {
    echo -e "${BLUE}🧪 Running functionality test...${NC}"
    WEB_PORT=$(docker-compose port webserver 80 2>/dev/null | cut -d: -f2)

    if [ -z "$WEB_PORT" ]; then
        echo -e "${RED}❌ Application is not running${NC}"
        exit 1
    fi

    echo "Testing endpoints..."

    # Test home page
    if curl -s "http://localhost:${WEB_PORT}/" > /dev/null; then
        echo -e "${GREEN}✅ Home page${NC}"
    else
        echo -e "${RED}❌ Home page${NC}"
    fi

    # Test table page
    if curl -s "http://localhost:${WEB_PORT}/tabulka" > /dev/null; then
        echo -e "${GREEN}✅ Table page${NC}"
    else
        echo -e "${RED}❌ Table page${NC}"
    fi

    # Test API
    if curl -s "http://localhost:${WEB_PORT}/api/stats" | grep -q "success"; then
        echo -e "${GREEN}✅ API endpoint${NC}"
    else
        echo -e "${RED}❌ API endpoint${NC}"
    fi

    echo -e "${GREEN}🎉 Test completed${NC}"
}

clean_cache() {
    echo -e "${YELLOW}🧹 Cleaning cache and logs...${NC}"
    rm -rf zeta/cache/*
    rm -rf zeta/logs/*
    rm -rf zeta/latte/*
    echo -e "${GREEN}✅ Cache and logs cleaned${NC}"
}

# Main script logic
case "$1" in
    "start")
        start_app
        ;;
    "stop")
        stop_app
        ;;
    "restart")
        restart_app
        ;;
    "logs")
        show_logs
        ;;
    "status")
        show_status
        ;;
    "db")
        connect_db
        ;;
    "import")
        import_data
        ;;
    "test")
        test_app
        ;;
    "clean")
        clean_cache
        ;;
    "help"|"")
        show_help
        ;;
    *)
        echo -e "${RED}❌ Unknown command: $1${NC}"
        echo ""
        show_help
        exit 1
        ;;
esac