<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platba byla zrušena</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-lg">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Platba byla zrušena
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Platba nebyla dokončena. Můžete to zkusit znovu nebo kontaktovat podporu.
                </p>
            </div>

            <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Co se stalo?</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Platba byla zrušena před dokončením. Vaše rezervace stále čeká na platbu.
                </p>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-2">
                    <li>Můžete zkusit platbu znovu</li>
                    <li>Zkontrolujte údaje platební karty</li>
                    <li>Zkuste jiný způsob platby</li>
                    <li>Kontaktujte naši podporu</li>
                </ul>
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
                        <dt class="text-sm text-blue-600">Cena:</dt>
                        <dd class="text-sm font-medium text-blue-900">{{ number_format($reservation->price, 2, ',', ' ') }} CZK</dd>
                    </div>
                </dl>
            </div>
            @endif

            <div class="mt-8 space-y-3">
                @if($reservation)
                <button onclick="retryPayment()" 
                   class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Zkusit platbu znovu
                </button>
                @endif
                <a href="{{ route('dashboard') }}" 
                   class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Zpět na dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        function retryPayment() {
            // Redirect back to reservation to retry payment
            window.location.href = '{{ route("reservations.show", $reservation) }}';
        }
    </script>
</body>
</html>
