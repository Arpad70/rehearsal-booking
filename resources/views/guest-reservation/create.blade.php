<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rezervace bez registrace - RockSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="{{ route('landing') }}" class="flex items-center space-x-2">
                    <i class="fas fa-guitar text-4xl text-purple-600"></i>
                    <span class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        RockSpace
                    </span>
                </a>
                <a href="{{ route('landing') }}" class="text-gray-700 hover:text-purple-600 transition font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Zpět na hlavní stránku
                </a>
            </div>
        </div>
    </nav>

    <div class="min-h-screen py-12 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-5xl font-black text-gray-900 mb-4">Rezervace bez registrace</h1>
                <p class="text-xl text-gray-600">Zadejte email a telefon pro ověření</p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-12">
                <div class="flex items-center justify-center space-x-4">
                    <div id="step-1" class="flex items-center step-active">
                        <div class="w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold">1</div>
                        <span class="ml-2 font-semibold text-purple-600">Kontaktní údaje</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300" id="line-1"></div>
                    <div id="step-2" class="flex items-center opacity-50">
                        <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold">2</div>
                        <span class="ml-2 font-semibold text-gray-500">Ověření</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300" id="line-2"></div>
                    <div id="step-3" class="flex items-center opacity-50">
                        <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold">3</div>
                        <span class="ml-2 font-semibold text-gray-500">Detaily rezervace</span>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alert-container" class="mb-6"></div>

            <!-- Step 1: Contact Information -->
            <div id="step-1-content" class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-6">Kontaktní údaje</h2>
                <form id="contact-form">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email
                            </label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                   placeholder="vas@email.cz">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-phone mr-2"></i>Telefon
                            </label>
                            <input type="tel" id="phone" name="phone" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                   placeholder="+420 123 456 789">
                            <p class="text-sm text-gray-500 mt-2">Zadejte telefonní číslo ve formátu +420...</p>
                        </div>
                        <button type="submit" id="send-codes-btn"
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Odeslat ověřovací kódy
                        </button>
                    </div>
                </form>
            </div>

            <!-- Step 2: Verification -->
            <div id="step-2-content" class="hidden bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-6">Ověření kontaktů</h2>
                <p class="text-gray-600 mb-6">Na váš email a telefon jsme zaslali 6místné ověřovací kódy. Zadejte je níže:</p>
                
                <div class="space-y-6">
                    <!-- Email Verification -->
                    <div class="border-2 border-gray-200 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-2xl text-purple-600 mr-3"></i>
                                <div>
                                    <h3 class="font-bold">Email ověření</h3>
                                    <p class="text-sm text-gray-600" id="email-display"></p>
                                </div>
                            </div>
                            <div id="email-verified-badge" class="hidden bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-check mr-1"></i>Ověřeno
                            </div>
                        </div>
                        <div id="email-verification-form">
                            <input type="text" id="email-code" maxlength="6" pattern="[0-9]{6}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 text-center text-2xl font-bold tracking-widest mb-3"
                                   placeholder="000000">
                            <button type="button" id="verify-email-btn"
                                    class="w-full bg-purple-600 text-white py-3 rounded-xl font-bold hover:bg-purple-700 transition">
                                <i class="fas fa-check-circle mr-2"></i>Ověřit email
                            </button>
                        </div>
                    </div>

                    <!-- Phone Verification -->
                    <div class="border-2 border-gray-200 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-phone text-2xl text-purple-600 mr-3"></i>
                                <div>
                                    <h3 class="font-bold">Telefonní ověření</h3>
                                    <p class="text-sm text-gray-600" id="phone-display"></p>
                                </div>
                            </div>
                            <div id="phone-verified-badge" class="hidden bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-check mr-1"></i>Ověřeno
                            </div>
                        </div>
                        <div id="phone-verification-form">
                            <input type="text" id="phone-code" maxlength="6" pattern="[0-9]{6}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 text-center text-2xl font-bold tracking-widest mb-3"
                                   placeholder="000000">
                            <button type="button" id="verify-phone-btn"
                                    class="w-full bg-purple-600 text-white py-3 rounded-xl font-bold hover:bg-purple-700 transition">
                                <i class="fas fa-check-circle mr-2"></i>Ověřit telefon
                            </button>
                        </div>
                    </div>

                    <button type="button" id="continue-to-reservation-btn" disabled
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                        <i class="fas fa-arrow-right mr-2"></i>
                        Pokračovat k rezervaci
                    </button>

                    <button type="button" id="resend-codes-btn"
                            class="w-full bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
                        <i class="fas fa-redo mr-2"></i>Znovu odeslat kódy
                    </button>
                </div>
            </div>

            <!-- Step 3: Reservation Details -->
            <div id="step-3-content" class="hidden bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-6">Detaily rezervace</h2>
                <form id="reservation-form">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user mr-2"></i>Jméno (volitelné)
                            </label>
                            <input type="text" id="guest-name" name="guest_name"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600"
                                   placeholder="Vaše jméno nebo název kapely">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-door-open mr-2"></i>Zkušebna
                            </label>
                            <select id="room-id" name="room_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600">
                                <option value="">Vyberte zkušebnu...</option>
                                @if($room)
                                    <option value="{{ $room->id }}" selected>{{ $room->name }} ({{ number_format($room->price_per_hour, 0) }} Kč/hod)</option>
                                @endif
                            </select>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-2"></i>Začátek
                                </label>
                                <input type="datetime-local" id="start-time" name="start_time" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-check mr-2"></i>Konec
                                </label>
                                <input type="datetime-local" id="end-time" name="end_time" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600">
                            </div>
                        </div>

                        <div class="bg-purple-50 rounded-xl p-6">
                            <h3 class="font-bold text-lg mb-2">Souhrn</h3>
                            <div class="space-y-2 text-gray-700">
                                <div class="flex justify-between">
                                    <span>Email:</span>
                                    <span id="summary-email" class="font-semibold"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Telefon:</span>
                                    <span id="summary-phone" class="font-semibold"></span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="create-reservation-btn"
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
                            <i class="fas fa-check-circle mr-2"></i>
                            Vytvořit rezervaci
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let sessionToken = null;
        let emailVerified = false;
        let phoneVerified = false;
        let userEmail = '';
        let userPhone = '';

        // Step 1: Send Verification Codes
        document.getElementById('contact-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const btn = document.getElementById('send-codes-btn');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Odesílání...';
            
            try {
                const response = await fetch('{{ route("guest.reservation.send-codes") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email, phone })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    sessionToken = data.session_token;
                    userEmail = email;
                    userPhone = phone;
                    
                    showAlert('success', data.message);
                    moveToStep(2);
                    
                    document.getElementById('email-display').textContent = email;
                    document.getElementById('phone-display').textContent = phone;
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Chyba při odesílání kódů. Zkuste to znovu.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Odeslat ověřovací kódy';
            }
        });

        // Verify Email Code
        document.getElementById('verify-email-btn').addEventListener('click', async () => {
            const code = document.getElementById('email-code').value;
            
            if (code.length !== 6) {
                showAlert('error', 'Zadejte 6místný kód.');
                return;
            }
            
            try {
                const response = await fetch('{{ route("guest.reservation.verify-email") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ session_token: sessionToken, code })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    emailVerified = true;
                    document.getElementById('email-verification-form').classList.add('hidden');
                    document.getElementById('email-verified-badge').classList.remove('hidden');
                    showAlert('success', 'Email úspěšně ověřen!');
                    checkBothVerified();
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Chyba při ověřování emailu.');
            }
        });

        // Verify Phone Code
        document.getElementById('verify-phone-btn').addEventListener('click', async () => {
            const code = document.getElementById('phone-code').value;
            
            if (code.length !== 6) {
                showAlert('error', 'Zadejte 6místný kód.');
                return;
            }
            
            try {
                const response = await fetch('{{ route("guest.reservation.verify-phone") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ session_token: sessionToken, code })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    phoneVerified = true;
                    document.getElementById('phone-verification-form').classList.add('hidden');
                    document.getElementById('phone-verified-badge').classList.remove('hidden');
                    showAlert('success', 'Telefon úspěšně ověřen!');
                    checkBothVerified();
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Chyba při ověřování telefonu.');
            }
        });

        // Resend Codes
        document.getElementById('resend-codes-btn').addEventListener('click', async () => {
            const btn = document.getElementById('resend-codes-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Odesílání...';
            
            try {
                const response = await fetch('{{ route("guest.reservation.send-codes") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email: userEmail, phone: userPhone })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', 'Kódy byly znovu odeslány.');
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Chyba při odesílání kódů.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo mr-2"></i>Znovu odeslat kódy';
            }
        });

        // Continue to Reservation
        document.getElementById('continue-to-reservation-btn').addEventListener('click', () => {
            moveToStep(3);
            document.getElementById('summary-email').textContent = userEmail;
            document.getElementById('summary-phone').textContent = userPhone;
        });

        // Create Reservation
        document.getElementById('reservation-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('create-reservation-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Vytváření...';
            
            const formData = {
                session_token: sessionToken,
                guest_name: document.getElementById('guest-name').value,
                room_id: document.getElementById('room-id').value,
                start_time: document.getElementById('start-time').value,
                end_time: document.getElementById('end-time').value
            };
            
            try {
                const response = await fetch('{{ route("guest.reservation.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', 'Rezervace vytvořena! Přesměrování na platbu...');
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 2000);
                } else {
                    showAlert('error', data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Vytvořit rezervaci';
                }
            } catch (error) {
                showAlert('error', 'Chyba při vytváření rezervace.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Vytvořit rezervaci';
            }
        });

        function checkBothVerified() {
            if (emailVerified && phoneVerified) {
                document.getElementById('continue-to-reservation-btn').disabled = false;
            }
        }

        function moveToStep(step) {
            // Hide all steps
            document.getElementById('step-1-content').classList.add('hidden');
            document.getElementById('step-2-content').classList.add('hidden');
            document.getElementById('step-3-content').classList.add('hidden');
            
            // Show current step
            document.getElementById(`step-${step}-content`).classList.remove('hidden');
            
            // Update progress indicators
            for (let i = 1; i <= 3; i++) {
                const stepEl = document.getElementById(`step-${i}`);
                if (i < step) {
                    stepEl.classList.remove('opacity-50');
                    stepEl.querySelector('div').classList.remove('bg-gray-300');
                    stepEl.querySelector('div').classList.add('bg-green-600');
                } else if (i === step) {
                    stepEl.classList.remove('opacity-50');
                    stepEl.querySelector('div').classList.remove('bg-gray-300');
                    stepEl.querySelector('div').classList.add('bg-purple-600');
                    stepEl.querySelector('span').classList.remove('text-gray-500');
                    stepEl.querySelector('span').classList.add('text-purple-600');
                } else {
                    stepEl.classList.add('opacity-50');
                }
            }
            
            // Update lines
            for (let i = 1; i <= 2; i++) {
                const lineEl = document.getElementById(`line-${i}`);
                if (i < step - 1) {
                    lineEl.classList.remove('bg-gray-300');
                    lineEl.classList.add('bg-green-600');
                } else if (i === step - 1) {
                    lineEl.classList.remove('bg-gray-300');
                    lineEl.classList.add('bg-purple-600');
                }
            }
        }

        function showAlert(type, message) {
            const container = document.getElementById('alert-container');
            const bgColor = type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            container.innerHTML = `
                <div class="${bgColor} rounded-xl p-4 flex items-center">
                    <i class="fas ${icon} text-xl mr-3"></i>
                    <span class="font-semibold">${message}</span>
                </div>
            `;
            
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        // Auto-format phone input
        document.getElementById('phone').addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (!value.startsWith('420') && value.length > 0) {
                value = '420' + value;
            }
            if (value.length > 0) {
                e.target.value = '+' + value;
            }
        });

        // Set minimum date/time to now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('start-time').min = now.toISOString().slice(0, 16);
        document.getElementById('end-time').min = now.toISOString().slice(0, 16);
    </script>
</body>
</html>
