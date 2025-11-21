<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Místnosti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($rooms->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">{{ __('Žádné místnosti nejsou k dispozici.') }}</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($rooms as $room)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                                    <div class="relative">
                                        <div class="image-container w-full h-48">
                                            @if($room->image)
                                                <img src="{{ Storage::url($room->image) }}" 
                                                     alt="{{ $room->name }}" 
                                                     class="w-full h-full object-cover"
                                                     loading="lazy"
                                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-gray-600 dark:to-gray-500 flex items-center justify-center" style="display:none;">
                                                    <i class="fas fa-music text-5xl text-indigo-300 dark:text-gray-400"></i>
                                                </div>
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-gray-600 dark:to-gray-500 flex items-center justify-center">
                                                    <i class="fas fa-music text-5xl text-indigo-300 dark:text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        @if($room->address)
                                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($room->address) }}" 
                                           target="_blank"
                                           class="absolute bottom-3 right-3 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-medium shadow-lg transition transform hover:scale-105 flex items-center text-sm">
                                            <i class="fas fa-directions mr-1"></i>
                                            Navigovat
                                        </a>
                                        @endif
                                    </div>
                                    <div class="p-6">
                                        <h3 class="text-lg font-semibold mb-2">{{ $room->name }}</h3>
                                        @if($room->description)
                                            <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $room->description }}</p>
                                        @endif
                                        
                                        <div class="space-y-2 mb-4">
                                            <div class="flex items-center text-gray-600 dark:text-gray-400 text-sm">
                                                <i class="fas fa-users w-5 mr-2"></i>
                                                <span>Kapacita: {{ $room->capacity }} osob</span>
                                            </div>
                                            @if($room->size)
                                            <div class="flex items-center text-gray-600 dark:text-gray-400 text-sm">
                                                <i class="fas fa-ruler-combined w-5 mr-2"></i>
                                                <span>Rozměr: {{ $room->size }}</span>
                                            </div>
                                            @endif
                                            <div class="flex items-center text-gray-600 dark:text-gray-400 text-sm">
                                                <i class="fas fa-money-bill-wave w-5 mr-2"></i>
                                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ number_format($room->price_per_hour, 0, ',', ' ') }} Kč/hod</span>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <a href="{{ route('rooms.show', $room) }}" 
                                               class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-4 rounded-lg font-medium transition text-sm">
                                                {{ __('Zobrazit detail') }}
                                                <i class="fas fa-arrow-right ml-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>