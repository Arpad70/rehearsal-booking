#!/bin/bash

# Barvy pro výstup
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🛑 Stopping all devices and services...${NC}"
echo ""

# Stop rehearsal-app
echo "Stopping Rehearsal App..."
cd /mnt/data/www/rehearsal-app
docker compose down --remove-orphans

# Stop simulators
echo "Stopping IoT Simulators..."
cd /mnt/data/www/Simulace
docker compose down --remove-orphans

echo ""
echo -e "${GREEN}✅ All services stopped${NC}"
