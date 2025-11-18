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
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                                    <h3 class="text-lg font-semibold mb-2">{{ $room->name }}</h3>
                                    @if($room->description)
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $room->description }}</p>
                                    @endif
                                    <div class="mt-4">
                                        <a href="{{ route('rooms.show', $room) }}" 
                                           class="inline-flex items-center text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            {{ __('Zobrazit detail') }}
                                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
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