<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('reservations.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        {{ __('Všechny rezervace') }}
                        <span class="ml-2 px-2.5 py-0.5 text-xs font-medium bg-gray-200 text-gray-800 rounded-full dark:bg-gray-600 dark:text-gray-200">
                            {{ $reservations->count() }}
                        </span>
                    </a>

                    @if(Route::has('rooms.index'))
                        <a href="{{ route('rooms.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ __('Místnosti') }}
                        </a>
                    @endif
                </div>

                <a href="{{ route('reservations.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-semibold text-white uppercase tracking-wider hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:focus:ring-offset-gray-800 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    {{ __('Nová rezervace') }}
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">{{ __('Vaše nadcházející rezervace') }}</h3>

                    @if($reservations->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">{{ __('Zatím nemáte žádné rezervace.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b dark:border-gray-700">
                                        <th class="py-3 px-6 text-left">{{ __('Místnost') }}</th>
                                        <th class="py-3 px-6 text-left">{{ __('Od') }}</th>
                                        <th class="py-3 px-6 text-left">{{ __('Do') }}</th>
                                        <th class="py-3 px-6 text-left">{{ __('Stav') }}</th>
                                        <th class="py-3 px-6 text-right">{{ __('Akce') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reservations as $reservation)
                                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="py-4 px-6">
                                                <div class="font-medium">{{ $reservation->room->name }}</div>
                                                @if($reservation->room->description)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ Str::limit($reservation->room->description, 50) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6">
                                                <div>{{ $reservation->start_at->format('d.m.Y') }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $reservation->start_at->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div>{{ $reservation->end_at->format('d.m.Y') }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $reservation->end_at->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                @if($reservation->status === 'pending')
                                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                                        {{ __('Čeká') }}
                                                    </span>
                                                @elseif($reservation->status === 'active')
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                        {{ __('Aktivní') }}
                                                    </span>
                                                @elseif($reservation->status === 'completed')
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                                        {{ __('Dokončeno') }}
                                                    </span>
                                                @endif
                                                @if($reservation->start_at->isToday())
                                                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                        {{ __('Dnes') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <div class="flex items-center justify-end space-x-3">
                                                    <a href="{{ route('reservations.show', $reservation) }}"
                                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        {{ __('Detail') }}
                                                    </a>
                                                    @if($reservation->status === 'pending' || $reservation->status === 'active')
                                                        <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" onclick="return confirm('Opravdu chcete zrušit tuto rezervaci?')"
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                                {{ __('Zrušit') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>