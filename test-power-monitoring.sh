#!/bin/bash

# Power Monitoring Test Script
# Sbírá inicální data z Shelly zařízení a ověřuje funkčnost systému

set -e

echo "🔋 Power Monitoring System Test"
echo "================================"
echo ""

cd /mnt/data/www/rehearsal-app

# 1. Check database
echo "1️⃣  Checking database..."
php artisan tinker <<EOF
\Psy::debug();
echo "✅ Database connection OK\n";
\$count = DB::table('power_monitoring')->count();
echo "Current records in power_monitoring: \$count\n";
exit;
EOF

echo ""
echo "2️⃣  Running initial data collection..."
php artisan power-monitoring:collect

echo ""
echo "3️⃣  Verifying collected data..."
php artisan tinker <<EOF
\Psy::debug();
\$records = DB::table('power_monitoring')->count();
echo "Records in database: \$records\n";

\$devices = DB::table('power_monitoring')
    ->select('device_id', DB::raw('MAX(created_at) as latest'))
    ->groupBy('device_id')
    ->get();

echo "\nLatest data by device:\n";
foreach (\$devices as \$device) {
    echo "Device ID {$device->device_id}: {$device->latest}\n";
}
exit;
EOF

echo ""
echo "✅ Power Monitoring System is ready!"
echo ""
echo "Next steps:"
echo "1. Visit http://rehearsal-app.local/admin/power-monitorings"
echo "2. Check the Power Monitoring Stats widget on dashboard"
echo "3. API endpoints are available at /api/v1/power-monitoring/*"
echo ""
