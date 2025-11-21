#!/bin/bash

echo "🔄 Přesun Docker dat na /mnt/data..."

# Zastav Docker
echo "⏸️  Zastavuji Docker..."
sudo snap stop docker

# Vytvoř nový adresář pro Docker data
echo "📁 Vytvářím nový Docker adresář..."
sudo mkdir -p /mnt/data/docker

# Zkopíruj existující data (pokud existují)
if [ -d "/var/snap/docker/common/var-lib-docker" ]; then
    echo "📦 Kopíruji existující Docker data..."
    sudo rsync -aP /var/snap/docker/common/var-lib-docker/ /mnt/data/docker/
fi

# Odstraň stará data
echo "🗑️  Mažu stará data..."
sudo rm -rf /var/snap/docker/common/var-lib-docker

# Vytvoř symlink
echo "🔗 Vytvářím symlink..."
sudo ln -s /mnt/data/docker /var/snap/docker/common/var-lib-docker

# Spusť Docker
echo "▶️  Spouštím Docker..."
sudo snap start docker

echo ""
echo "✅ Docker data byla přesunuta na /mnt/data/docker"
echo "📊 Volné místo na /mnt/data:"
df -h /mnt/data | tail -1
