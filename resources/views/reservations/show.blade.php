<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detail rezervace') }}
            </h2>
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center px-3 py-2 bg-gray-100 rounded text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                {{ __('Zpět na přehled') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium mb-4">{{ __('Informace o rezervaci') }}</h3>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-4">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Místnost') }}</span>
                                    <p class="font-medium">{{ $reservation->room->name }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Začátek') }}</span>
                                    <p class="font-medium">{{ $reservation->start_at->format('d.m.Y H:i') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Konec') }}</span>
                                    <p class="font-medium">{{ $reservation->end_at->format('d.m.Y H:i') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Stav') }}</span>
                                    <p>
                                        @if($reservation->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                                {{ __('Čeká') }}
                                            </span>
                                        @elseif($reservation->status === 'active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                {{ __('Aktivní') }}
                                            </span>
                                        @elseif($reservation->status === 'completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                                {{ __('Dokončeno') }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium mb-4">{{ __('Přístupový QR kód') }}</h3>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <div class="aspect-square w-full max-w-[300px] mx-auto bg-white p-4 rounded-lg shadow-sm">
                                    <img src="{{ route('reservations.qr', $reservation) }}" 
                                         alt="{{ __('QR kód pro přístup') }}"
                                         class="w-full h-full"
                                         id="qr-code">
                                </div>
                                <div class="mt-4 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('QR kód je platný od') }} {{ $reservation->token_valid_from->format('H:i') }}
                                        {{ __('do') }} {{ $reservation->token_expires_at->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($reservation->status !== 'completed')
                        <div class="mt-6 flex justify-end">
                            <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    onclick="return confirm('Opravdu chcete zrušit tuto rezervaci?')"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:focus:ring-offset-gray-800">
                                    {{ __('Zrušit rezervaci') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>  