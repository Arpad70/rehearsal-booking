<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Průvodce nastavením RFID čtečky
            </x-slot>

            <div class="space-y-4">
                <div class="bg-blue-50 dark:bg-blue-950 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">📡 Podporované čtečky</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li><strong>ACR122U</strong> - USB NFC čtečka (~500 Kč)</li>
                        <li><strong>PN532</strong> - NFC/RFID modul</li>
                        <li><strong>RC522</strong> - Levný RFID modul</li>
                        <li><strong>Mobilní NFC</strong> - Android telefon s NFC</li>
                    </ul>
                </div>

                <div class="bg-green-50 dark:bg-green-950 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">🛒 Kde koupit</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li><strong>CZ.NIC</strong> - ACR122U (~500 Kč)</li>
                        <li><strong>Aliexpress</strong> - Levnější alternativy (~200 Kč)</li>
                        <li><strong>RFID tagy</strong> - NTAG215 (~5-20 Kč/ks)</li>
                        <li><strong>Starter kit</strong> - Čtečka + 10 tagů (~600 Kč)</li>
                    </ul>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-950 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">🔧 Instalace ovladačů</h3>
                    <div class="space-y-2 text-sm">
                        <p><strong>Linux:</strong></p>
                        <pre class="bg-gray-900 text-green-400 p-3 rounded overflow-x-auto">sudo apt-get install libpcsclite1 pcscd</pre>
                        
                        <p class="mt-2"><strong>Windows:</strong></p>
                        <pre class="bg-gray-900 text-green-400 p-3 rounded overflow-x-auto">Stáhněte ovladač z webu výrobce čtečky</pre>
                    </div>
                </div>

                <div class="bg-purple-50 dark:bg-purple-950 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">🐍 Python skript (pro sériový port)</h3>
                    <div class="space-y-2 text-sm">
                        <p>Pro čtečky v módu Serial Communication:</p>
                        <pre class="bg-gray-900 text-green-400 p-3 rounded overflow-x-auto">cd python_gateway
pip install pyserial requests
python rfid_scanner.py</pre>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">
                Testování systému
            </x-slot>

            <div class="space-y-4">
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <h4 class="font-semibold mb-2">Krok 1: Otevřete web rozhraní</h4>
                    <a href="http://localhost:8090/rfid-manager.html" 
                       target="_blank"
                       class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        <x-heroicon-o-arrow-top-right-on-square class="w-5 h-5" />
                        Otevřít RFID Manager
                    </a>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <h4 class="font-semibold mb-2">Krok 2: Testovací curl příkazy</h4>
                    <div class="space-y-2">
                        <p class="text-sm"><strong>Test čtení:</strong></p>
                        <pre class="bg-gray-900 text-green-400 p-3 rounded overflow-x-auto text-xs">curl -X POST http://localhost:8090/api/v1/rfid/read \
  -H "Content-Type: application/json" \
  -d '{"rfid_tag":"RFID-SM58-001"}'</pre>

                        <p class="text-sm mt-3"><strong>Test dostupnosti:</strong></p>
                        <pre class="bg-gray-900 text-green-400 p-3 rounded overflow-x-auto text-xs">curl -X POST http://localhost:8090/api/v1/rfid/check-availability \
  -H "Content-Type: application/json" \
  -d '{"rfid_tag":"RFID-NEW-001"}'</pre>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <h4 class="font-semibold mb-2">Krok 3: Přiložte RFID tag</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        V keyboard emulation módu: Klikněte do pole "RFID Tag" v jakémkoli formuláři a přiložte tag ke čtečce.
                        Tag ID se automaticky vyplní.
                    </p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                📚 Dokumentace
            </x-slot>

            <div class="space-y-3">
                <p class="text-sm text-gray-600 dark:text-gray-400">Kompletní dokumentace je dostupná v souboru:</p>
                <div class="flex items-center gap-3">
                    <code class="bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded text-sm">docs/RFID_DOCUMENTATION.md</code>
                    <a href="{{ asset('docs/RFID_DOCUMENTATION.md') }}" 
                       download="RFID_DOCUMENTATION.md"
                       class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Stáhnout</span>
                    </a>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
