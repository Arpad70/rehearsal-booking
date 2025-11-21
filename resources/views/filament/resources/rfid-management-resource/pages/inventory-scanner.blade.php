<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Formulář pro výběr místnosti -->
        <x-filament::section>
            <x-slot name="heading">
                🏢 Nastavení inventury
            </x-slot>

            <form wire:submit="processInventory">
                {{ $this->form }}
            </form>
        </x-filament::section>

        <!-- Kontrolní panel -->
        <x-filament::section>
            <x-slot name="heading">
                📱 Skenování
            </x-slot>

            <div class="space-y-4">
                <div class="flex gap-3">
                    <button type="button" 
                            wire:click="startScanning"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        🚀 Spustit skenování
                    </button>
                    
                    @if(count($scannedTags) > 0)
                        <button type="button" 
                                wire:click="processInventory"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            ✅ Ukončit a vyhodnotit ({{ count($scannedTags) }} tagů)
                        </button>
                        
                        <button type="button" 
                                wire:click="clearResults"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                            🗑️ Vymazat
                        </button>
                    @endif
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        💡 <strong>Návod:</strong> Skenujte tagy pomocí čtečky nebo NFC scanneru. Každý tag se automaticky přidá do seznamu.
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ count($scannedTags) }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Naskenováno tagů</div>
                        </div>
                        
                        @if($results)
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">{{ $results['found'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Vybavení nalezeno</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Seznam naskenovaných tagů -->
                @if(count($scannedTags) > 0)
                    <div class="mt-4">
                        <h4 class="font-semibold mb-2">Naskenované tagy:</h4>
                        <div class="max-h-40 overflow-y-auto space-y-1">
                            @foreach($scannedTags as $tag)
                                <div class="px-3 py-1 bg-blue-50 dark:bg-blue-900/20 rounded text-sm">
                                    📌 {{ $tag }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <!-- Výsledky -->
        @if($results)
            <x-filament::section>
                <x-slot name="heading">
                    📊 Výsledky inventury
                </x-slot>

                <div class="space-y-4">
                    <!-- Statistiky -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $results['scanned'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Celkem naskenováno</div>
                        </div>
                        
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $results['found'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Vybavení nalezeno</div>
                        </div>
                        
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-red-600">{{ $results['not_found'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Neznámé tagy</div>
                        </div>
                    </div>

                    <!-- Nalezené vybavení -->
                    @if(count($results['equipment']) > 0)
                        <div>
                            <h4 class="font-semibold mb-2 text-green-600">✅ Nalezené vybavení</h4>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @foreach($results['equipment'] as $item)
                                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        <div class="font-semibold">{{ $item['name'] }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            Tag: {{ $item['tag_id'] }} | 
                                            Kategorie: {{ $item['category'] ?? 'N/A' }} |
                                            Umístění: {{ $item['location'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Chybějící vybavení -->
                    @if(isset($results['missing_equipment']) && count($results['missing_equipment']) > 0)
                        <div>
                            <h4 class="font-semibold mb-2 text-red-600">❌ Chybějící vybavení (mělo být v místnosti)</h4>
                            <div class="space-y-2">
                                @foreach($results['missing_equipment'] as $item)
                                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                        <div class="font-semibold">{{ $item['name'] }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            Tag: {{ $item['tag_id'] }} | Umístění: {{ $item['location'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Neznámé tagy -->
                    @if(count($results['missing_tags']) > 0)
                        <div>
                            <h4 class="font-semibold mb-2 text-yellow-600">⚠️ Neznámé tagy</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($results['missing_tags'] as $tag)
                                    <span class="px-3 py-1 bg-yellow-50 dark:bg-yellow-900/20 rounded text-sm">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endif
    </div>

    <script>
        window.addEventListener('rfid-scanned', (event) => {
            const tag = event.detail.tag;
            @this.scannedTags.push(tag);
        });

        window.addEventListener('nfc-scanned', (event) => {
            const tag = event.detail.tag;
            @this.scannedTags.push(tag);
        });
    </script>
</x-filament-panels::page>
