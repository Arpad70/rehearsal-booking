<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($this->getStats() as $stat)
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $stat->getLabel() }}
                            </p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $stat->getValue() }}
                            </p>
                            @if($stat->getDescription())
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $stat->getDescription() }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-500 py-8">Žádné statistiky k dispozici</p>
            @endforelse
        </div>

        <!-- Recent Access Log -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow">
            <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    🔓 Poslední pokusy o přístup
                </h2>
            </div>

            <div class="overflow-x-auto">
                {{ $this->table }}
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <a href="{{ \App\Filament\Resources\RoomReaderResource::getUrl('index') }}" 
               class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">🚪</span>
                    <div>
                        <p class="font-semibold text-blue-900 dark:text-blue-100">Čtečky místností</p>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Správa</p>
                    </div>
                </div>
            </a>

            <a href="{{ \App\Filament\Resources\GlobalReaderResource::getUrl('index') }}" 
               class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 hover:bg-green-100 dark:hover:bg-green-900/40 transition">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">🌐</span>
                    <div>
                        <p class="font-semibold text-green-900 dark:text-green-100">Globální čtečky</p>
                        <p class="text-sm text-green-700 dark:text-green-300">Správa</p>
                    </div>
                </div>
            </a>

            <a href="{{ \App\Filament\Resources\ServiceAccessResource::getUrl('index') }}" 
               class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">🔧</span>
                    <div>
                        <p class="font-semibold text-purple-900 dark:text-purple-100">Servisní přístupy</p>
                        <p class="text-sm text-purple-700 dark:text-purple-300">Správa</p>
                    </div>
                </div>
            </a>

            <a href="{{ \App\Filament\Resources\ReaderAlertResource::getUrl('index') }}" 
               class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <p class="font-semibold text-red-900 dark:text-red-100">Upozornění</p>
                        <p class="text-sm text-red-700 dark:text-red-300">Řešit</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-filament-panels::page>
