#!/bin/bash

# Barvy pro výstup
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   IoT Device Simulators + Rehearsal App Startup${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""

# 1. Spustit simulátory
echo -e "${YELLOW}🚀 Starting IoT Device Simulators...${NC}"
cd /mnt/data/www/Simulace

echo "  Stopping existing containers..."
docker compose down --remove-orphans > /dev/null 2>&1

echo "  Building and starting simulators..."
docker compose up -d --build

# Čekat na start (30s)
echo -e "${YELLOW}⏳ Waiting for simulators to initialize (30s)...${NC}"
for i in {1..30}; do
    echo -n "."
    sleep 1
done
echo ""

# 2. Ověřit dostupnost simulátorů
echo -e "${YELLOW}🔍 Checking simulator health...${NC}"

# Porty k testování
declare -A ports=(
    ["9101"]="QR Reader #1"
    ["9102"]="QR Reader #2"
    ["9201"]="Camera #1"
    ["9202"]="Camera #2"
    ["9301"]="Shelly/Mixer #1"
    ["9302"]="Shelly #2"
    ["9401"]="Keypad #1"
)

online_count=0
offline_count=0

for port in "${!ports[@]}"; do
    if curl -s --max-time 2 http://localhost:$port/ > /dev/null 2>&1; then
        echo -e "  ${GREEN}✅ Port $port - ${ports[$port]} - OK${NC}"
        ((online_count++))
    else
        echo -e "  ${RED}❌ Port $port - ${ports[$port]} - FAILED${NC}"
        ((offline_count++))
    fi
done

echo ""
echo -e "  ${BLUE}Online: $online_count | Offline: $offline_count${NC}"

if [ $offline_count -gt 0 ]; then
    echo -e "${RED}⚠️  Some simulators failed to start!${NC}"
    echo "  Check logs: docker compose logs -f"
    echo ""
fi

# 3. Spustit rehearsal-app
echo -e "${YELLOW}🏗️  Starting Rehearsal App...${NC}"
cd /mnt/data/www/rehearsal-app

echo "  Stopping existing containers..."
docker compose down > /dev/null 2>&1

echo "  Starting application..."
docker compose up -d

# Čekat na start app
echo "  Waiting for app to start (15s)..."
sleep 15

# 4. Test konektivity z aplikace
echo -e "${YELLOW}🧪 Testing connectivity from app...${NC}"

# Test QR Reader
qr_test=$(docker exec rehearsal-app curl -s --max-time 2 http://172.17.0.1:9101/device-info 2>/dev/null | jq -r '.device.model' 2>/dev/null)
if [ -n "$qr_test" ] && [ "$qr_test" != "null" ]; then
    echo -e "  ${GREEN}✅ QR Reader: $qr_test${NC}"
else
    echo -e "  ${RED}❌ QR Reader: Connection failed${NC}"
fi

# Test Shelly
shelly_test=$(docker exec rehearsal-app curl -s --max-time 2 http://172.17.0.1:9301/status 2>/dev/null | jq -r '.switch[0].ison' 2>/dev/null)
if [ -n "$shelly_test" ] && [ "$shelly_test" != "null" ]; then
    echo -e "  ${GREEN}✅ Shelly: Relay status = $shelly_test${NC}"
else
    echo -e "  ${RED}❌ Shelly: Connection failed${NC}"
fi

# Test Keypad
keypad_test=$(docker exec rehearsal-app curl -s --max-time 2 http://172.17.0.1:9401/device-info 2>/dev/null | jq -r '.device.model' 2>/dev/null)
if [ -n "$keypad_test" ] && [ "$keypad_test" != "null" ]; then
    echo -e "  ${GREEN}✅ Keypad: $keypad_test${NC}"
else
    echo -e "  ${RED}❌ Keypad: Connection failed${NC}"
fi

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✅ All systems ready!${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo "📊 Access Points:"
echo "   - Rehearsal App: http://localhost:8090"
echo "   - QR Readers: http://localhost:9101-9106"
echo "   - Cameras: http://localhost:9201-9212"
echo "   - Shelly/Mixer: http://localhost:9301-9306"
echo "   - Keypads: http://localhost:9401-9402"
echo ""
echo "📝 Useful commands:"
echo "   - View app logs: cd /mnt/data/www/rehearsal-app && docker compose logs -f app"
echo "   - View simulator logs: cd /mnt/data/www/Simulace && docker compose logs -f"
echo "   - Stop all: ./scripts/stop-all-devices.sh"
echo ""
