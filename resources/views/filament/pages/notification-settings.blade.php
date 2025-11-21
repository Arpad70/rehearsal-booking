<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                O notifikacích
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <p>
                    Zde můžete spravovat své notifikace a upravit preference pro příjem upozornění.
                </p>
                <ul>
                    <li><strong>Emailové notifikace:</strong> Odesílány při změně stavu kritického vybavení</li>
                    <li><strong>In-app notifikace:</strong> Zobrazují se přímo v administračním panelu</li>
                    <li><strong>Nastavení preferencí:</strong> V sekci "Uživatelé" může administrátor upravit, kdo bude dostávat notifikace</li>
                </ul>
            </div>
        </div>

        <div class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                        Tip pro administrátory
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                        <p>
                            Pro aktivaci notifikací pro konkrétního uživatele:
                        </p>
                        <ol class="list-decimal list-inside mt-2 space-y-1">
                            <li>Přejděte do sekce "Uživatelé"</li>
                            <li>Upravte požadovaného uživatele</li>
                            <li>Zapněte přepínač "Přijímat notifikace o kritickém vybavení"</li>
                            <li>Uložte změny</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white dark:bg-gray-800 shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
