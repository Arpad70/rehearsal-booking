#!/usr/bin/env python3
"""
RFID Scanner - Python Client for USB RFID Readers
Automaticky čte RFID tagy a komunikuje s Laravel API
"""

import serial
import requests
import time
import json
import os
from datetime import datetime
from typing import Optional

# Konfigurace
API_BASE_URL = os.getenv("API_BASE_URL", "http://localhost:8090/api/v1/rfid")
SERIAL_PORT = "/dev/ttyUSB0"  # Linux: /dev/ttyUSB0, Windows: COM3, Mac: /dev/cu.usbserial
BAUD_RATE = 9600
TIMEOUT = 1

# Autentizace (pro checkout/checkin)
AUTH_TOKEN = None  # Nastavte token pro autentizované operace
DEFAULT_USER_ID = 2


class RFIDScanner:
    def __init__(self, port: str, baudrate: int):
        """Inicializace RFID čtečky"""
        try:
            self.serial = serial.Serial(port, baudrate, timeout=TIMEOUT)
            print(f"✅ RFID čtečka připojena na {port}")
            time.sleep(2)  # Počkat na inicializaci
        except serial.SerialException as e:
            print(f"❌ Chyba připojení k {port}: {e}")
            print("💡 Zkontrolujte:")
            print("   - Je čtečka připojená k USB?")
            print("   - Linux: ls /dev/ttyUSB*")
            print("   - Windows: Správce zařízení → Porty (COM & LPT)")
            raise

    def read_tag(self) -> Optional[str]:
        """Přečte RFID tag z čtečky"""
        if self.serial.in_waiting > 0:
            try:
                tag = self.serial.readline().decode('utf-8').strip()
                if tag:
                    return tag
            except UnicodeDecodeError:
                print("⚠️ Chyba dekódování tagu")
        return None

    def close(self):
        """Zavře sériový port"""
        if self.serial.is_open:
            self.serial.close()
            print("🔌 RFID čtečka odpojená")


class APIClient:
    def __init__(self, base_url: str, token: Optional[str] = None):
        """Inicializace API klienta"""
        self.base_url = base_url
        self.token = token
        self.session = requests.Session()
        self.session.headers.update({
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        })
        if token:
            self.session.headers.update({
                'Authorization': f'Bearer {token}'
            })

    def read_equipment(self, rfid_tag: str) -> dict:
        """Najde vybavení podle RFID tagu"""
        try:
            response = self.session.post(
                f"{self.base_url}/read",
                json={'rfid_tag': rfid_tag}
            )
            return response.json()
        except requests.RequestException as e:
            return {'success': False, 'error': f'API chyba: {e}'}

    def check_availability(self, rfid_tag: str) -> dict:
        """Zkontroluje dostupnost RFID tagu"""
        try:
            response = self.session.post(
                f"{self.base_url}/check-availability",
                json={'rfid_tag': rfid_tag}
            )
            return response.json()
        except requests.RequestException as e:
            return {'available': False, 'error': str(e)}

    def checkout(self, rfid_tag: str, user_id: int, room_id: Optional[int] = None) -> dict:
        """Zapůjčí vybavení"""
        try:
            payload = {
                'rfid_tag': rfid_tag,
                'user_id': user_id
            }
            if room_id:
                payload['room_id'] = room_id
            
            response = self.session.post(
                f"{self.base_url}/checkout",
                json=payload
            )
            return response.json()
        except requests.RequestException as e:
            return {'success': False, 'error': f'API chyba: {e}'}

    def checkin(self, rfid_tag: str, user_id: int) -> dict:
        """Vrátí vybavení"""
        try:
            response = self.session.post(
                f"{self.base_url}/checkin",
                json={
                    'rfid_tag': rfid_tag,
                    'user_id': user_id
                }
            )
            return response.json()
        except requests.RequestException as e:
            return {'success': False, 'error': f'API chyba: {e}'}


def print_equipment_info(equipment: dict):
    """Zobrazí informace o vybavení"""
    print("\n" + "="*60)
    print(f"📦 {equipment['name']}")
    print("="*60)
    
    if equipment.get('category'):
        cat = equipment['category']
        print(f"Kategorie:     {cat['icon']} {cat['name']}")
    
    print(f"Status:        {equipment['status']}")
    print(f"Umístění:      {equipment.get('location', '-')}")
    print(f"Model:         {equipment.get('model', '-')}")
    print(f"Sériové číslo: {equipment.get('serial_number', '-')}")
    print(f"Dostupnost:    {equipment['quantity_available']} ks")
    
    if equipment['is_critical']:
        print("⚠️  KRITICKÉ VYBAVENÍ")
    
    if equipment.get('description'):
        print(f"\nPopis: {equipment['description']}")
    
    print("="*60 + "\n")


def main():
    """Hlavní smyčka aplikace"""
    print("""
    ╔════════════════════════════════════════════════════════╗
    ║         RFID Scanner - Zkušebny Management           ║
    ╚════════════════════════════════════════════════════════╝
    """)

    # Inicializace
    try:
        scanner = RFIDScanner(SERIAL_PORT, BAUD_RATE)
    except:
        print("\n💡 Pro testování bez USB čtečky zadejte tagy ručně:")
        keyboard_mode()
        return

    api = APIClient(API_BASE_URL, AUTH_TOKEN)
    
    print("🎯 Režim: Automatické skenování")
    print("📡 Přiložte RFID tag ke čtečce...")
    print("⌨️  Stiskněte Ctrl+C pro ukončení\n")

    last_tag = None
    last_scan_time = 0
    
    try:
        while True:
            tag = scanner.read_tag()
            
            if tag and tag != last_tag:
                current_time = time.time()
                
                # Prevence duplicitních skenů (3 sekundy)
                if current_time - last_scan_time < 3:
                    continue
                
                last_tag = tag
                last_scan_time = current_time
                
                timestamp = datetime.now().strftime("%H:%M:%S")
                print(f"[{timestamp}] 🔖 Tag: {tag}")
                
                # Vyhledat vybavení
                result = api.read_equipment(tag)
                
                if result.get('success'):
                    print_equipment_info(result['equipment'])
                    
                    # Volitelně: Automatická výpůjčka
                    # if AUTH_TOKEN:
                    #     checkout_result = api.checkout(tag, DEFAULT_USER_ID)
                    #     if checkout_result.get('success'):
                    #         print("✅ Automaticky zapůjčeno")
                else:
                    print(f"❌ {result.get('error', 'Neznámá chyba')}")
                    if result.get('suggestion'):
                        print(f"💡 {result['suggestion']}")
                    print()
            
            time.sleep(0.1)  # Krátká pauza
            
    except KeyboardInterrupt:
        print("\n\n👋 Ukončuji scanner...")
        scanner.close()


def keyboard_mode():
    """Režim ručního zadávání tagů (bez USB čtečky)"""
    api = APIClient(API_BASE_URL, AUTH_TOKEN)
    
    print("\n📝 Režim: Ruční zadávání")
    print("Zadejte RFID tag (nebo 'q' pro ukončení):\n")
    
    while True:
        try:
            tag = input("RFID Tag: ").strip()
            
            if tag.lower() == 'q':
                break
            
            if not tag:
                continue
            
            result = api.read_equipment(tag)
            
            if result.get('success'):
                print_equipment_info(result['equipment'])
            else:
                print(f"❌ {result.get('error', 'Neznámá chyba')}\n")
        
        except KeyboardInterrupt:
            print("\n\n👋 Ukončuji...")
            break


if __name__ == "__main__":
    main()
