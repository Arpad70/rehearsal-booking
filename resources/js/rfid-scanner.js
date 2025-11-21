/**
 * RFID Scanner Integration for Filament
 * 
 * Automaticky načítá RFID tagy z USB čtečky (keyboard emulation mode)
 */

(function() {
    'use strict';

    let rfidBuffer = '';
    let rfidTimeout = null;
    const RFID_TIMEOUT = 100; // ms - čas mezi znaky od čtečky
    const RFID_MIN_LENGTH = 8; // minimální délka RFID tagu
    
    /**
     * Detekuje, zda je focus v RFID input poli
     */
    function isRfidInput(element) {
        if (!element) return false;
        
        const name = element.getAttribute('name') || '';
        const id = element.getAttribute('id') || '';
        const placeholder = element.getAttribute('placeholder') || '';
        
        return name.includes('rfid') || 
               id.includes('rfid') || 
               placeholder.toLowerCase().includes('rfid');
    }

    /**
     * Zpracuje načtený RFID tag
     */
    function processRfidTag(tag) {
        const trimmedTag = tag.trim();
        
        if (trimmedTag.length < RFID_MIN_LENGTH) {
            return;
        }

        console.log('🏷️ RFID tag načten:', trimmedTag);

        // Dispatch custom event pro Alpine.js komponenty
        window.dispatchEvent(new CustomEvent('rfid-scanned', {
            detail: { tag: trimmedTag },
            bubbles: true
        }));

        // Najdi aktivní RFID input pole
        const activeElement = document.activeElement;
        if (isRfidInput(activeElement)) {
            activeElement.value = trimmedTag;
            
            // Trigger input event pro Livewire/Alpine
            activeElement.dispatchEvent(new Event('input', { bubbles: true }));
            activeElement.dispatchEvent(new Event('change', { bubbles: true }));

            // Zobraz notifikaci
            showNotification('✅ RFID tag načten: ' + trimmedTag);
        }
    }

    /**
     * Zobrazí notifikaci (Filament style)
     */
    function showNotification(message) {
        // Pokusí se použít Filament notifikace
        if (window.$wire) {
            window.$wire.dispatchFormEvent('notification', {
                title: 'RFID tag načten',
                body: message,
                status: 'success',
            });
        } else {
            // Fallback: jednoduchý toast
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity';
            toast.textContent = message;
            toast.style.opacity = '0';
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.style.opacity = '1', 10);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }

    /**
     * Keypress listener pro keyboard emulation
     */
    document.addEventListener('keypress', function(e) {
        const activeElement = document.activeElement;
        
        // Ignoruj, pokud je focus v jiném než RFID poli
        if (activeElement && activeElement.tagName === 'INPUT' && !isRfidInput(activeElement)) {
            return;
        }

        // Přidej znak do bufferu
        rfidBuffer += e.key;

        // Reset timeout
        clearTimeout(rfidTimeout);
        rfidTimeout = setTimeout(() => {
            if (rfidBuffer.length >= RFID_MIN_LENGTH) {
                processRfidTag(rfidBuffer);
            }
            rfidBuffer = '';
        }, RFID_TIMEOUT);
    });

    /**
     * Enter key jako konec čtení tagu
     */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && rfidBuffer.length > 0) {
            clearTimeout(rfidTimeout);
            if (rfidBuffer.length >= RFID_MIN_LENGTH) {
                processRfidTag(rfidBuffer);
            }
            rfidBuffer = '';
            e.preventDefault();
        }
    });

    /**
     * Auto-focus na RFID pole při načtení stránky
     */
    window.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const rfidInputs = document.querySelectorAll('input[name*="rfid"], input[id*="rfid"]');
            if (rfidInputs.length > 0) {
                console.log('🔍 Nalezeno ' + rfidInputs.length + ' RFID polí');
                
                // Auto-focus na první viditelné RFID pole
                for (const input of rfidInputs) {
                    if (input.offsetParent !== null) { // je viditelné
                        input.focus();
                        console.log('🎯 Auto-focus na RFID pole');
                        break;
                    }
                }
            }
        }, 500);
    });

    /**
     * Livewire hook - znovu nastav focus po update
     */
    document.addEventListener('livewire:load', function() {
        Livewire.hook('message.processed', (message, component) => {
            const rfidInput = document.querySelector('input[name*="rfid"]:not([readonly])');
            if (rfidInput && rfidInput.offsetParent !== null) {
                setTimeout(() => rfidInput.focus(), 100);
            }
        });
    });

    console.log('✅ RFID Scanner Integration loaded');
})();
