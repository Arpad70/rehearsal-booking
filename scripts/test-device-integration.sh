#!/bin/bash

# Device Integration Test Script
# Tests connectivity between Rehearsal App and IoT Simulators

BLUE='\033[0;34m'
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Device Integration Test${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""

# Test 1: QR Reader
echo -e "${YELLOW}📱 Testing QR Reader...${NC}"
qr_result=$(docker exec rehearsal-app curl -s --max-time 3 http://172.17.0.1:9101/device-info 2>/dev/null)
if echo "$qr_result" | jq -e '.device.model' > /dev/null 2>&1; then
    model=$(echo "$qr_result" | jq -r '.device.model')
    firmware=$(echo "$qr_result" | jq -r '.device.firmware')
    echo -e "  ${GREEN}✅ QR Reader: $model (FW: $firmware)${NC}"
else
    echo -e "  ${RED}❌ QR Reader: Connection failed${NC}"
fi

# Test 2: Shelly Pro EM
echo -e "${YELLOW}🔌 Testing Shelly Pro EM...${NC}"
shelly_result=$(docker exec rehearsal-app curl -s --max-time 3 http://172.17.0.1:9301/status 2>/dev/null)
if echo "$shelly_result" | jq -e '.switch' > /dev/null 2>&1; then
    relay_state=$(echo "$shelly_result" | jq -r '.switch[0].ison')
    power=$(echo "$shelly_result" | jq -r '.em1[0].power // 0')
    echo -e "  ${GREEN}✅ Shelly: Relay=$relay_state, Power=${power}W${NC}"
else
    echo -e "  ${RED}❌ Shelly: Connection failed${NC}"
fi

# Test 3: IP Camera
echo -e "${YELLOW}📹 Testing IP Camera...${NC}"
camera_result=$(docker exec rehearsal-app curl -s --max-time 3 http://172.17.0.1:9201/device-info 2>/dev/null)
if echo "$camera_result" | jq -e '.device.model' > /dev/null 2>&1; then
    model=$(echo "$camera_result" | jq -r '.device.model')
    resolution=$(echo "$camera_result" | jq -r '.sensor.resolution')
    echo -e "  ${GREEN}✅ Camera: $model ($resolution)${NC}"
else
    echo -e "  ${RED}❌ Camera: Connection failed${NC}"
fi

# Test 4: RFID Keypad
echo -e "${YELLOW}🔢 Testing RFID Keypad...${NC}"
keypad_result=$(docker exec rehearsal-app curl -s --max-time 3 http://172.17.0.1:9401/device-info 2>/dev/null)
if echo "$keypad_result" | jq -e '.device.model' > /dev/null 2>&1; then
    model=$(echo "$keypad_result" | jq -r '.device.model')
    firmware=$(echo "$keypad_result" | jq -r '.device.firmware')
    echo -e "  ${GREEN}✅ Keypad: $model (FW: $firmware)${NC}"
else
    echo -e "  ${RED}❌ Keypad: Connection failed${NC}"
fi

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"

# Test 5: Laravel Health Check Command
echo -e "${YELLOW}🏥 Running Laravel Device Health Check...${NC}"
docker exec rehearsal-app php artisan devices:health-check

echo ""
echo -e "${GREEN}✅ Test completed${NC}"
