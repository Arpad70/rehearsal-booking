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
                    @if($room->description)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-2">{{ __('Popis') }}</h3>
                            <p class="text-gray-600 dark:text-gray-300">{{ $room->description }}</p>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-2">{{ __('Rezervace místnosti') }}</h3>
                        <a href="{{ route('reservations.create', ['room' => $room->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:focus:ring-offset-gray-800">
                            {{ __('Vytvořit rezervaci') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>