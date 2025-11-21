<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platba byla úspěšná</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-lg">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Platba byla úspěšná!
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Děkujeme za vaši platbu. Vaše rezervace byla potvrzena.
                </p>
            </div>

            <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detaily platby</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Číslo platby:</dt>
                        <dd class="text-sm font-medium text-gray-900">#{{ $payment->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Částka:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ number_format($payment->amount, 2, ',', ' ') }} {{ $payment->currency }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Status:</dt>
                        <dd class="text-sm font-medium text-green-600">{{ ucfirst($payment->status) }}</dd>
                    </div>
                    @if($payment->paid_at)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Zaplaceno:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $payment->paid_at->format('d.m.Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            @if($reservation)
            <div class="mt-6 bg-blue-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium text-blue-900 mb-4">Detaily rezervace</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-blue-600">Číslo rezervace:</dt>
                        <dd class="text-sm font-medium text-blue-900">#{{ $reservation->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-blue-600">Místnost:</dt>
                        <dd class="text-sm font-medium text-blue-900">{{ $reservation->room->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-blue-600">Datum:</dt>
                        <dd class="text-sm font-medium text-blue-900">{{ $reservation->start->format('d.m.Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-blue-600">Čas:</dt>
                        <dd class="text-sm font-medium text-blue-900">{{ $reservation->start->format('H:i') }} - {{ $reservation->end->format('H:i') }}</dd>
                    </div>
                </dl>
            </div>
            @endif

            <div class="mt-8 space-y-3">
                <a href="{{ route('reservations.show', $reservation) }}" 
                   class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Zobrazit rezervaci
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Zpět na dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
