<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platba - RockSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-guitar text-4xl text-purple-600"></i>
                    <span class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        RockSpace
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <div class="min-h-screen py-12 px-4">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                    <i class="fas fa-check text-4xl text-green-600"></i>
                </div>
                <h1 class="text-4xl font-black text-gray-900 mb-2">Rezervace vytvořena!</h1>
                <p class="text-xl text-gray-600">Nyní prosím dokončete platbu</p>
            </div>

            <!-- Reservation Details -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
                <h2 class="text-2xl font-bold mb-6">Detaily rezervace</h2>
                <div class="space-y-4">
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-600">Číslo rezervace:</span>
                        <span class="font-bold">#{{ $reservation->id }}</span>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-600">Zkušebna:</span>
                        <span class="font-bold">{{ $reservation->room->name }}</span>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-600">Začátek:</span>
                        <span class="font-bold">{{ $reservation->start_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-600">Konec:</span>
                        <span class="font-bold">{{ $reservation->end_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-600">Email:</span>
                        <span class="font-bold">{{ $reservation->guest_email }}</span>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-600">Telefon:</span>
                        <span class="font-bold">{{ $reservation->guest_phone }}</span>
                    </div>
                    <div class="flex justify-between py-4 bg-purple-50 rounded-xl px-4 mt-4">
                        <span class="text-lg font-bold">Celková cena:</span>
                        <span class="text-2xl font-black text-purple-600">{{ number_format($reservation->price ?? 0, 0, ',', ' ') }} Kč</span>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-6">Vyberte způsob platby</h2>
                <div class="space-y-4">
                    <form action="{{ route('payment.create', $reservation->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="gateway" value="stripe">
                        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105 flex items-center justify-center">
                            <i class="fab fa-cc-stripe text-2xl mr-3"></i>
                            Platba kartou (Stripe)
                        </button>
                    </form>

                    <form action="{{ route('payment.create', $reservation->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="gateway" value="gopay">
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-credit-card text-2xl mr-3"></i>
                            GoPay
                        </button>
                    </form>

                    <form action="{{ route('payment.create', $reservation->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="gateway" value="comgate">
                        <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-university text-2xl mr-3"></i>
                            ComGate
                        </button>
                    </form>
                </div>

                <div class="mt-8 p-4 bg-blue-50 rounded-xl">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                        <div class="text-sm text-blue-900">
                            <p class="font-semibold mb-1">Bezpečná platba</p>
                            <p>Všechny platby jsou šifrovány a zabezpečeny. Po dokončení platby obdržíte potvrzení a QR kód pro vstup na váš email.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
