<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $room->name }}
            </h2>
            <a href="{{ route('rooms.index') }}" 
               class="inline-flex items-center px-3 py-2 bg-gray-100 rounded text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                {{ __('Zpět na seznam') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($room->image)
                        <div class="mb-6 relative">
                            <div class="image-container w-full h-96">
                                <img src="{{ Storage::url($room->image) }}" 
                                     alt="{{ $room->name }}" 
                                     class="w-full h-full object-cover rounded-lg shadow-xl"
                                     loading="eager"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full bg-gradient-to-br from-purple-100 to-pink-100 dark:from-gray-700 dark:to-gray-600 rounded-lg shadow-xl flex items-center justify-center" style="display:none;">
                                    <div class="text-center">
                                        <i class="fas fa-guitar text-8xl text-purple-300 dark:text-gray-500 mb-4"></i>
                                        <p class="text-purple-400 dark:text-gray-400 font-semibold text-xl">{{ $room->name }}</p>
                                    </div>
                                </div>
                            </div>
                            @if($room->address)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($room->address) }}" 
                               target="_blank"
                               class="absolute bottom-4 right-4 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-bold shadow-xl transition transform hover:scale-105 flex items-center">
                                <i class="fas fa-directions mr-2"></i>
                                Navigovat ke zkušebně
                            </a>
                            @endif
                        </div>
                    @else
                        <div class="mb-6 relative w-full h-96 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-gray-700 dark:to-gray-600 rounded-lg shadow-xl flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-guitar text-8xl text-purple-300 dark:text-gray-500 mb-4"></i>
                                <p class="text-purple-400 dark:text-gray-400 font-semibold text-xl">{{ $room->name }}</p>
                            </div>
                            @if($room->address)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($room->address) }}" 
                               target="_blank"
                               class="absolute bottom-4 right-4 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-bold shadow-xl transition transform hover:scale-105 flex items-center">
                                <i class="fas fa-directions mr-2"></i>
                                Navigovat ke zkušebně
                            </a>
                            @endif
                        </div>
                    @endif

                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            @if($room->description)
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium mb-2">{{ __('Popis') }}</h3>
                                    <p class="text-gray-600 dark:text-gray-300">{{ $room->description }}</p>
                                </div>
                            @endif
                            
                            <div class="space-y-3">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-users w-6 mr-3"></i>
                                    <span>Kapacita: <strong>{{ $room->capacity }} osob</strong></span>
                                </div>
                                @if($room->size)
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-ruler-combined w-6 mr-3"></i>
                                    <span>Rozměr: <strong>{{ $room->size }}</strong></span>
                                </div>
                                @endif
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-money-bill-wave w-6 mr-3"></i>
                                    <span>Cena: <strong class="text-indigo-600 dark:text-indigo-400 text-xl">{{ number_format($room->price_per_hour, 0, ',', ' ') }} Kč/hod</strong></span>
                                </div>
                                @if($room->location)
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-map-marker-alt w-6 mr-3"></i>
                                    <span>{{ $room->location }}</span>
                                </div>
                                @endif
                                @if($room->address)
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-home w-6 mr-3"></i>
                                    <span>{{ $room->address }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <a href="{{ route('reservations.create', ['room' => $room->id]) }}" 
                               class="block w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white text-center py-3 px-6 rounded-lg font-bold hover:shadow-xl transition transform hover:scale-105">
                                <i class="fas fa-calendar-check mr-2"></i>
                                {{ __('Vytvořit rezervaci') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>