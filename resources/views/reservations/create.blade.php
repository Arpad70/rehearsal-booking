<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Nová rezervace') }}
            </h2>
            <a href="{{ url()->previous() }}" 
               class="inline-flex items-center px-3 py-2 bg-gray-100 rounded text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                {{ __('Zpět') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-lg">
                            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('reservations.store') }}" class="space-y-6">
                        @csrf
                        <div>
                            <label for="room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Místnost') }}
                            </label>
                            <select id="room_id" name="room_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" 
                                        {{ (old('room_id', request()->input('room')) == $room->id) ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Začátek') }}
                                </label>
                                <input type="datetime-local" name="start_at" id="start_at"
                                    value="{{ old('start_at') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="end_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Konec') }}
                                </label>
                                <input type="datetime-local" name="end_at" id="end_at"
                                    value="{{ old('end_at') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-white border border-indigo-700 rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest shadow-sm hover:bg-indigo-50 hover:border-indigo-800 hover:text-indigo-800 focus:bg-indigo-50 active:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:bg-indigo-600 dark:border-transparent dark:text-white dark:hover:bg-indigo-700 dark:focus:ring-offset-gray-800">
                                {{ __('Vytvořit rezervaci') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>  