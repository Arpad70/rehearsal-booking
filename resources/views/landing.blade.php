<!DOCTYPE html>
<html lang="cs" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RockSpace - Profesionální zkušebny pro kapely</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        img {
            max-width: 100%;
            height: auto;
        }
        
        .room-image {
            will-change: auto;
        }
        
        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="fixed w-full bg-white/95 backdrop-blur-sm shadow-lg z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-guitar text-4xl text-purple-600"></i>
                    <span class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        RockSpace
                    </span>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-purple-600 transition font-medium">Funkce</a>
                    <a href="#rooms" class="text-gray-700 hover:text-purple-600 transition font-medium">Zkušebny</a>
                    <a href="#pricing" class="text-gray-700 hover:text-purple-600 transition font-medium">Ceník</a>
                    <a href="#faq" class="text-gray-700 hover:text-purple-600 transition font-medium">FAQ</a>
                    <a href="#contact" class="text-gray-700 hover:text-purple-600 transition font-medium">Kontakt</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    @guest
                        <button onclick="openLoginModal()" class="text-gray-700 hover:text-purple-600 transition font-medium hidden sm:inline-block">
                            Přihlásit se
                        </button>
                        <button onclick="openRegisterModal()" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2.5 rounded-full hover:shadow-xl transition transform hover:scale-105 font-semibold">
                            Registrace
                        </button>
                    @else
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-purple-600 transition font-medium">
                                <i class="fas fa-user-circle text-2xl"></i>
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <a href="{{ route('dashboard') }}" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-t-xl transition">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>
                                <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 transition">
                                    <i class="fas fa-calendar-alt mr-2"></i>Moje rezervace
                                </a>
                                <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 transition">
                                    <i class="fas fa-user-cog mr-2"></i>Můj účet
                                </a>
                                <div class="border-t border-gray-200"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-3 text-red-600 hover:bg-red-50 rounded-b-xl transition">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Odhlásit se
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest
                    
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-btn" class="md:hidden text-gray-700 hover:text-purple-600">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden py-4 border-t">
                <div class="flex flex-col space-y-4">
                    <a href="#features" class="text-gray-700 hover:text-purple-600 transition font-medium">Funkce</a>
                    <a href="#rooms" class="text-gray-700 hover:text-purple-600 transition font-medium">Zkušebny</a>
                    <a href="#pricing" class="text-gray-700 hover:text-purple-600 transition font-medium">Ceník</a>
                    <a href="#faq" class="text-gray-700 hover:text-purple-600 transition font-medium">FAQ</a>
                    <a href="#contact" class="text-gray-700 hover:text-purple-600 transition font-medium">Kontakt</a>
                    @guest
                        <button onclick="openLoginModal()" class="text-gray-700 hover:text-purple-600 transition font-medium">Přihlásit se</button>
                    @else
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-purple-600 transition font-medium">Dashboard</a>
                        <a href="#" class="text-gray-700 hover:text-purple-600 transition font-medium">Moje rezervace</a>
                        <a href="#" class="text-gray-700 hover:text-purple-600 transition font-medium">Můj účet</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-purple-600 transition font-medium text-left w-full">
                                Odhlásit se
                            </button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient pt-32 pb-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="text-white space-y-8">
                    <h1 class="text-5xl md:text-7xl font-black leading-tight">
                        Profesionální zkušebny
                        <span class="block text-pink-300">pro vaši kapelu</span>
                    </h1>
                    <p class="text-xl text-purple-100 leading-relaxed">
                        Rezervujte si kvalitní zkušebnu online, plaťte bezpečně a hrajte bez starostí. 
                        Moderní vybavení, skvělá akustika a flexibilní rezervace.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#rooms" class="bg-white text-purple-600 px-8 py-4 rounded-full font-bold text-lg hover:shadow-2xl transition transform hover:scale-105 text-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Rezervovat nyní
                        </a>
                        <a href="#features" class="border-2 border-white text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white hover:text-purple-600 transition text-center">
                            Zjistit více
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 pt-8">
                        <div class="text-center">
                            <div class="text-4xl font-black">15+</div>
                            <div class="text-purple-200">Zkušeben</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-black">500+</div>
                            <div class="text-purple-200">Kapel</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-black">24/7</div>
                            <div class="text-purple-200">Dostupnost</div>
                        </div>
                    </div>
                </div>
                
                <div class="relative float-animation">
                    <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=800&h=600&fit=crop" 
                             alt="Zkušebna" 
                             class="rounded-2xl shadow-xl w-full h-96 object-cover">
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <img src="https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=400&h=300&fit=crop" 
                                 alt="Bicí" 
                                 class="rounded-xl h-40 w-full object-cover">
                            <img src="https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=400&h=300&fit=crop" 
                                 alt="Kytary" 
                                 class="rounded-xl h-40 w-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-5xl font-black text-gray-900 mb-4">Proč RockSpace?</h2>
                <p class="text-xl text-gray-600">Vše, co potřebujete pro perfektní zkoušku</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-hover bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 text-center">
                    <div class="bg-purple-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bolt text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Okamžitá rezervace</h3>
                    <p class="text-gray-600">Rezervujte si zkušebnu během několika sekund. Žádné čekání, žádné telefonáty.</p>
                </div>
                
                <div class="card-hover bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-8 text-center">
                    <div class="bg-blue-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shield-alt text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Bezpečné platby</h3>
                    <p class="text-gray-600">Platba kartou, GoPay nebo bankovním převodem. Vše 100% zabezpečené.</p>
                </div>
                
                <div class="card-hover bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 text-center">
                    <div class="bg-green-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-music text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Profesionální vybavení</h3>
                    <p class="text-gray-600">Bicí soupravy, zesilovače, mikrofony a vše potřebné v perfektním stavu.</p>
                </div>
                
                <div class="card-hover bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl p-8 text-center">
                    <div class="bg-orange-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-clock text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Flexibilní časy</h3>
                    <p class="text-gray-600">Od 1 hodiny až po celý den. Vyberte si čas, který vám vyhovuje.</p>
                </div>
                
                <div class="card-hover bg-gradient-to-br from-pink-50 to-rose-50 rounded-2xl p-8 text-center">
                    <div class="bg-pink-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-headphones text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Skvělá akustika</h3>
                    <p class="text-gray-600">Profesionálně odhlučněné místnosti pro dokonalý zvuk bez rušení okolí.</p>
                </div>
                
                <div class="card-hover bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8 text-center">
                    <div class="bg-indigo-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-qrcode text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">QR kód vstup</h3>
                    <p class="text-gray-600">Automatický vstup pomocí QR kódu. Žádné klíče, žádné čekání u recepce.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id="rooms" class="py-20 px-4 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-5xl font-black text-gray-900 mb-4">Naše zkušebny</h2>
                <p class="text-xl text-gray-600">Vyberte si zkušebnu podle velikosti vaší kapely</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                @forelse($rooms as $room)
                <div class="card-hover bg-white rounded-2xl overflow-hidden shadow-lg">
                    <div class="relative h-64 bg-gradient-to-br from-purple-100 to-pink-100">
                        <div class="image-container w-full h-full">
                            @if($room->image)
                                <img src="{{ Storage::url($room->image) }}" 
                                     alt="{{ $room->name }}" 
                                     class="room-image w-full h-full object-cover"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full flex items-center justify-center" style="display:none;">
                                    <div class="text-center">
                                        <i class="fas fa-guitar text-6xl text-purple-300 mb-4"></i>
                                        <p class="text-purple-400 font-semibold">{{ $room->name }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="fas fa-guitar text-6xl text-purple-300 mb-4"></i>
                                        <p class="text-purple-400 font-semibold">{{ $room->name }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="absolute top-4 right-4 bg-purple-600 text-white px-4 py-2 rounded-full font-bold shadow-lg">
                            {{ number_format($room->price_per_hour, 0, ',', ' ') }} Kč/hod
                        </div>
                        @if($room->address)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($room->address) }}" 
                           target="_blank"
                           class="absolute bottom-4 right-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition transform hover:scale-105 flex items-center">
                            <i class="fas fa-directions mr-2"></i>
                            Navigovat
                        </a>
                        @endif
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold mb-2">{{ $room->name }}</h3>
                        <p class="text-gray-600 mb-4">{{ $room->description ?? 'Profesionální zkušebna s kompletním vybavením' }}</p>
                        
                        <div class="space-y-2 mb-6">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-users w-6"></i>
                                <span>Kapacita: {{ $room->capacity }} osob</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-ruler-combined w-6"></i>
                                <span>Velikost: {{ $room->size ?? 'Střední' }}</span>
                            </div>
                            @if($room->devices && $room->devices->count() > 0)
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-plug w-6"></i>
                                <span>{{ $room->devices->count() }} zařízení</span>
                            </div>
                            @endif
                            @if($room->location)
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt w-6"></i>
                                <span>{{ $room->location }}</span>
                            </div>
                            @endif
                        </div>
                        
                        @auth
                            <a href="{{ route('reservations.create', ['room_id' => $room->id]) }}" 
                               class="block w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white text-center py-3 rounded-xl font-bold hover:shadow-xl transition transform hover:scale-105">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Rezervovat
                            </a>
                        @else
                            <a href="{{ route('guest.reservation.create', ['room_id' => $room->id]) }}" 
                               class="block w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white text-center py-3 rounded-xl font-bold hover:shadow-xl transition transform hover:scale-105">
                                Rezervovat bez registrace
                            </a>
                        @endauth
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-xl text-gray-600">Momentálně nejsou k dispozici žádné zkušebny.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 px-4 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-5xl font-black text-gray-900 mb-4">Ceník</h2>
                <p class="text-xl text-gray-600">Transparentní ceny bez skrytých poplatků</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-hover border-2 border-gray-200 rounded-2xl p-8">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold mb-2">Basic</h3>
                        <div class="text-5xl font-black text-purple-600 mb-2">200 Kč</div>
                        <div class="text-gray-600">za hodinu</div>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Malá zkušebna</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Základní vybavení</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>2-3 osoby</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>QR vstup</span>
                        </li>
                    </ul>
                    <button class="w-full bg-gray-200 text-gray-800 py-3 rounded-xl font-bold hover:bg-gray-300 transition">
                        Vybrat
                    </button>
                </div>
                
                <div class="card-hover border-4 border-purple-600 rounded-2xl p-8 relative shadow-2xl scale-105">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2 rounded-full font-bold">
                        Nejoblíbenější
                    </div>
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold mb-2">Pro</h3>
                        <div class="text-5xl font-black text-purple-600 mb-2">350 Kč</div>
                        <div class="text-gray-600">za hodinu</div>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Střední zkušebna</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Profesionální vybavení</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>4-5 osob</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>QR vstup + monitoring</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Nahrávací možnosti</span>
                        </li>
                    </ul>
                    <button class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 rounded-xl font-bold hover:shadow-xl transition">
                        Vybrat
                    </button>
                </div>
                
                <div class="card-hover border-2 border-gray-200 rounded-2xl p-8">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold mb-2">Premium</h3>
                        <div class="text-5xl font-black text-purple-600 mb-2">500 Kč</div>
                        <div class="text-gray-600">za hodinu</div>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Velká zkušebna</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Top vybavení</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>6+ osob</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Vše z Pro +</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Profesionální studio</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-3"></i>
                            <span>Prioritní podpora</span>
                        </li>
                    </ul>
                    <button class="w-full bg-gray-200 text-gray-800 py-3 rounded-xl font-bold hover:bg-gray-300 transition">
                        Vybrat
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 hero-gradient">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-5xl font-black mb-6">Připraveni začít?</h2>
            <p class="text-2xl mb-10 text-purple-100">Registrujte se nyní a získejte 20% slevu na první rezervaci!</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @guest
                    <button onclick="openRegisterModal()" class="bg-white text-purple-600 px-10 py-5 rounded-full font-bold text-xl hover:shadow-2xl transition transform hover:scale-105">
                        <i class="fas fa-user-plus mr-2"></i>
                        Registrovat se zdarma
                    </button>
                    <a href="#rooms" class="border-2 border-white text-white px-10 py-5 rounded-full font-bold text-xl hover:bg-white hover:text-purple-600 transition">
                        Prohlédnout zkušebny
                    </a>
                @else
                    <a href="{{ route('reservations.create') }}" class="bg-white text-purple-600 px-10 py-5 rounded-full font-bold text-xl hover:shadow-2xl transition transform hover:scale-105">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Vytvořit rezervaci
                    </a>
                @endguest
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 px-4 bg-white">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-5xl font-black text-gray-900 mb-4">Často kladené otázky</h2>
                <p class="text-xl text-gray-600">Všechno, co potřebujete vědět</p>
            </div>
            
            <div class="space-y-4">
                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition">
                    <button class="w-full flex items-center justify-between text-left faq-toggle">
                        <h3 class="text-xl font-bold text-gray-900">Jak dlouho dopředu mohu rezervovat zkušebnu?</h3>
                        <i class="fas fa-chevron-down text-purple-600 text-xl"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Rezervace můžete vytvářet až 3 měsíce dopředu. Pro pravidelné zkoušky nabízíme také možnost opakovaných rezervací s výhodnou cenou.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition">
                    <button class="w-full flex items-center justify-between text-left faq-toggle">
                        <h3 class="text-xl font-bold text-gray-900">Mohu zrušit nebo změnit rezervaci?</h3>
                        <i class="fas fa-chevron-down text-purple-600 text-xl"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Ano, rezervaci můžete zrušit nebo upravit až do 24 hodin před začátkem. Při zrušení s dostatečným předstihem vám vrátíme 100% zaplacené částky.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition">
                    <button class="w-full flex items-center justify-between text-left faq-toggle">
                        <h3 class="text-xl font-bold text-gray-900">Jaké vybavení je ve zkušebnách?</h3>
                        <i class="fas fa-chevron-down text-purple-600 text-xl"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Všechny zkušebny obsahují: kompletní bicí soupravu, kytarové a basové zesilovače, PA systém s mixážním pultem, mikrofony a kabely. Premium zkušebny mají navíc nahrávací vybavení.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition">
                    <button class="w-full flex items-center justify-between text-left faq-toggle">
                        <h3 class="text-xl font-bold text-gray-900">Můžu přinést vlastní nástroje?</h3>
                        <i class="fas fa-chevron-down text-purple-600 text-xl"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Samozřejmě! Dokonce to doporučujeme. Vše potřebné vybavení je na místě, ale mnoho hudebníků preferuje vlastní nástroje. Máme dostatek místa i pro větší transport.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition">
                    <button class="w-full flex items-center justify-between text-left faq-toggle">
                        <h3 class="text-xl font-bold text-gray-900">Jak funguje vstup do zkušebny?</h3>
                        <i class="fas fa-chevron-down text-purple-600 text-xl"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Po zaplacení rezervace obdržíte QR kód na váš email. Stačí ho naskenovat při příchodu a dveře se automaticky odemknou. Žádné klíče, žádné čekání!</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition">
                    <button class="w-full flex items-center justify-between text-left faq-toggle">
                        <h3 class="text-xl font-bold text-gray-900">Potřebuji registraci pro rezervaci?</h3>
                        <i class="fas fa-chevron-down text-purple-600 text-xl"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Ne! Můžete rezervovat i bez registrace. Stačí zadat email a telefon, ověřit je pomocí kódu a můžete pokračovat k platbě. Registrace však přináší další výhody jako historie rezervací a preferované platební metody.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition">
                    <button class="w-full flex items-center justify-between text-left faq-toggle">
                        <h3 class="text-xl font-bold text-gray-900">Jaké platební metody přijímáte?</h3>
                        <i class="fas fa-chevron-down text-purple-600 text-xl"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Přijímáme platby kartou (Visa, MasterCard, Maestro), GoPay, ComGate a bankovní převod. Všechny platby jsou 100% zabezpečené a šifrované.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition">
                    <button class="w-full flex items-center justify-between text-left faq-toggle">
                        <h3 class="text-xl font-bold text-gray-900">Nabízíte nějaké slevy?</h3>
                        <i class="fas fa-chevron-down text-purple-600 text-xl"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Ano! Noví uživatelé získají 20% slevu na první rezervaci. Pro pravidelné klienty máme měsíční předplatné se slevou až 30%. Studenti a školy mají slevu 15%.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 px-4 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-5xl font-black text-gray-900 mb-4">Co o nás říkají</h2>
                <p class="text-xl text-gray-600">Reference od našich spokojených klientů</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            JN
                        </div>
                        <div class="ml-4">
                            <h4 class="font-bold text-lg">Jan Novák</h4>
                            <p class="text-gray-600 text-sm">Kytarista, The Rockers</p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                    </div>
                    <p class="text-gray-700 italic">"Nejlepší zkušebny v Praze! Skvělé vybavení, férové ceny a super jednoduchá rezervace. QR kód vstup je geniální nápad."</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-cyan-400 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            PK
                        </div>
                        <div class="ml-4">
                            <h4 class="font-bold text-lg">Petra Králová</h4>
                            <p class="text-gray-600 text-sm">Zpěvačka, Sólová umělkyně</p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                    </div>
                    <p class="text-gray-700 italic">"Využívám recording studio pro nahrávání svých songů. Kvalita zvuku je úžasná a personál je vždy ochotný pomoct s nastavením."</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-400 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            MD
                        </div>
                        <div class="ml-4">
                            <h4 class="font-bold text-lg">Martin Dvořák</h4>
                            <p class="text-gray-600 text-sm">Bubeník, Jazz Collective</p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                        <i class="fas fa-star text-yellow-500"></i>
                    </div>
                    <p class="text-gray-700 italic">"Jako profesionální muzikant jsem zkoušel mnoho zkušeben. RockSpace má nejlepší poměr cena/výkon. Akustika je na špičkové úrovni."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 px-4 bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-4xl font-black mb-6">Kontaktujte nás</h2>
                    <p class="text-gray-300 mb-8 text-lg">Máte otázky? Rádi vám pomůžeme!</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg mb-1">Adresa</h4>
                                <p class="text-gray-300">Rohanské nábřeží 670/17<br>186 00 Praha 8</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg mb-1">Telefon</h4>
                                <p class="text-gray-300">+420 777 888 999</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg mb-1">Email</h4>
                                <p class="text-gray-300">info@rockspace.cz</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-4 mt-8">
                        <a href="#" class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center hover:bg-purple-700 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center hover:bg-purple-700 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center hover:bg-purple-700 transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <div class="bg-gray-800 rounded-2xl p-8">
                    <form class="space-y-6">
                        <div>
                            <input type="text" placeholder="Vaše jméno" 
                                   class="w-full bg-gray-700 border-0 rounded-xl px-6 py-4 text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-600">
                        </div>
                        <div>
                            <input type="email" placeholder="Váš email" 
                                   class="w-full bg-gray-700 border-0 rounded-xl px-6 py-4 text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-600">
                        </div>
                        <div>
                            <textarea rows="4" placeholder="Vaše zpráva" 
                                      class="w-full bg-gray-700 border-0 rounded-xl px-6 py-4 text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-600"></textarea>
                        </div>
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold hover:shadow-xl transition transform hover:scale-105">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Odeslat zprávu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-gray-400 py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-guitar text-3xl text-purple-600"></i>
                        <span class="text-2xl font-bold text-white">RockSpace</span>
                    </div>
                    <p class="text-sm">Profesionální zkušebny pro kapely a hudebníky v centru Prahy.</p>
                </div>
                
                <div>
                    <h4 class="text-white font-bold mb-4">Rychlé odkazy</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-purple-400 transition">Funkce</a></li>
                        <li><a href="#rooms" class="hover:text-purple-400 transition">Zkušebny</a></li>
                        <li><a href="#pricing" class="hover:text-purple-400 transition">Ceník</a></li>
                        <li><a href="#contact" class="hover:text-purple-400 transition">Kontakt</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-bold mb-4">Právní</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-purple-400 transition">Obchodní podmínky</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Ochrana osobních údajů</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">GDPR</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-bold mb-4">Newsletter</h4>
                    <p class="text-sm mb-4">Přihlaste se k odběru novinek a speciálních nabídek.</p>
                    <div class="flex">
                        <input type="email" placeholder="Váš email" 
                               class="flex-1 bg-gray-800 border-0 rounded-l-lg px-4 py-2 text-white text-sm">
                        <button class="bg-purple-600 px-4 py-2 rounded-r-lg hover:bg-purple-700 transition">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} RockSpace. Všechna práva vyhrazena.</p>
            </div>
        </div>
    </footer>

    <!-- Promotion Modal -->
    <div id="promotionModal" class="hidden fixed inset-0 bg-black bg-opacity-70 z-[55] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-modal">
            <div class="relative">
                <!-- Close button -->
                <button onclick="closePromotionModal('dismissed')" class="absolute top-4 right-4 z-10 bg-white rounded-full w-10 h-10 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition shadow-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>

                <!-- Image (if exists) -->
                <div id="promotionImage" class="hidden">
                    <img id="promotionImageSrc" src="" alt="" class="w-full h-64 object-cover rounded-t-3xl">
                </div>

                <!-- Content -->
                <div class="p-8">
                    <!-- Icon based on type -->
                    <div id="promotionIcon" class="mb-6">
                        <!-- Will be populated dynamically -->
                    </div>

                    <!-- Title -->
                    <h2 id="promotionTitle" class="text-3xl font-black text-gray-900 mb-4"></h2>

                    <!-- Description -->
                    <div id="promotionDescription" class="text-gray-700 text-lg leading-relaxed mb-6"></div>

                    <!-- Discount info (if applicable) -->
                    <div id="promotionDiscount" class="hidden bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-purple-600 font-semibold mb-1">KÓD SLEVY</p>
                                <p id="promotionDiscountCode" class="text-2xl font-black text-purple-900"></p>
                            </div>
                            <div class="text-right">
                                <p id="promotionDiscountValue" class="text-4xl font-black text-purple-600"></p>
                                <p class="text-sm text-purple-600">sleva</p>
                            </div>
                        </div>
                        <button onclick="copyDiscountCode()" class="mt-4 w-full bg-white text-purple-600 py-2 px-4 rounded-xl font-semibold hover:bg-purple-50 transition">
                            <i class="fas fa-copy mr-2"></i>Zkopírovat kód
                        </button>
                    </div>

                    <!-- Action buttons -->
                    <div id="promotionActions" class="space-y-3">
                        <!-- Will be populated dynamically based on type -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto animate-modal">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-3xl font-black text-gray-900">Přihlášení</h2>
                    <button onclick="closeLoginModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errors->any() && request()->routeIs('login'))
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                            <div class="text-red-800 text-sm">
                                @foreach($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition"
                                   placeholder="vas@email.cz">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2"></i>Heslo
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="loginPassword" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition pr-12"
                                       placeholder="••••••••">
                                <button type="button" onclick="togglePassword('loginPassword', 'loginPasswordIcon')" 
                                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="loginPasswordIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember" class="rounded border-gray-300 text-purple-600 focus:ring-purple-600">
                                <span class="ml-2 text-sm text-gray-600">Zapamatovat si mě</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-sm text-purple-600 hover:text-purple-700 font-semibold">
                                Zapomněli jste heslo?
                            </a>
                        </div>

                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Přihlásit se
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Ještě nemáte účet? 
                        <button onclick="closeLoginModal(); openRegisterModal();" class="text-purple-600 hover:text-purple-700 font-bold">
                            Registrujte se
                        </button>
                    </p>
                </div>

                <div class="mt-6 relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">nebo pokračujte bez účtu</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('guest.reservation.create') }}" 
                       class="block w-full bg-gray-100 text-gray-700 text-center py-3 rounded-xl font-semibold hover:bg-gray-200 transition">
                        <i class="fas fa-user-clock mr-2"></i>
                        Rezervovat jako host
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cookie Consent Modal -->
    <div id="cookieModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full animate-modal">
            <div class="p-6">
                <div class="flex items-start mb-4">
                    <div class="bg-purple-100 rounded-full p-3 mr-4">
                        <i class="fas fa-cookie-bite text-purple-600 text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Soubory cookies</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Používáme cookies pro zlepšení vašeho zážitku na našich stránkách, personalizaci obsahu a analýzu návštěvnosti. 
                            Kliknutím na "Přijmout" souhlasíte s používáním všech cookies.
                        </p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <div class="flex items-start text-sm text-gray-700">
                        <i class="fas fa-info-circle text-blue-600 mr-2 mt-0.5"></i>
                        <p>
                            <span class="font-semibold">Technické cookies</span> jsou nezbytné pro fungování webu. 
                            <span class="font-semibold">Analytické cookies</span> nám pomáhají zlepšovat naše služby.
                            <a href="#" class="text-purple-600 hover:text-purple-700 font-semibold ml-1">Více informací</a>
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="acceptCookies()" 
                            class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-xl font-bold hover:shadow-lg transition transform hover:scale-105">
                        <i class="fas fa-check mr-2"></i>Přijmout vše
                    </button>
                    <button onclick="rejectCookies()" 
                            class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-xl font-semibold hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-2"></i>Pouze nezbytné
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <button onclick="acceptCookies()" class="text-sm text-gray-500 hover:text-gray-700 transition">
                        Zavřít a použít pouze nezbytné cookies
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto animate-modal">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-3xl font-black text-gray-900">Registrace</h2>
                    <button onclick="closeRegisterModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errors->any() && request()->routeIs('register'))
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                            <div class="text-red-800 text-sm">
                                @foreach($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user mr-2"></i>Jméno
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition"
                                   placeholder="Jan Novák">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition"
                                   placeholder="vas@email.cz">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2"></i>Heslo
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="registerPassword" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition pr-12"
                                       placeholder="••••••••">
                                <button type="button" onclick="togglePassword('registerPassword', 'registerPasswordIcon')" 
                                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="registerPasswordIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Minimálně 8 znaků</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2"></i>Potvrzení hesla
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="registerPasswordConfirm" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition pr-12"
                                       placeholder="••••••••">
                                <button type="button" onclick="togglePassword('registerPasswordConfirm', 'registerPasswordConfirmIcon')" 
                                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="registerPasswordConfirmIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <input type="checkbox" required class="mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-600">
                            <span class="ml-2 text-sm text-gray-600">
                                Souhlasím s <a href="#" class="text-purple-600 hover:text-purple-700 font-semibold">obchodními podmínkami</a> 
                                a <a href="#" class="text-purple-600 hover:text-purple-700 font-semibold">ochranou osobních údajů</a>
                            </span>
                        </div>

                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
                            <i class="fas fa-user-plus mr-2"></i>
                            Vytvořit účet
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Již máte účet? 
                        <button onclick="closeRegisterModal(); openLoginModal();" class="text-purple-600 hover:text-purple-700 font-bold">
                            Přihlaste se
                        </button>
                    </p>
                </div>

                <div class="mt-6 relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">nebo pokračujte bez účtu</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('guest.reservation.create') }}" 
                       class="block w-full bg-gray-100 text-gray-700 text-center py-3 rounded-xl font-semibold hover:bg-gray-200 transition">
                        <i class="fas fa-user-clock mr-2"></i>
                        Rezervovat jako host
                    </a>
                </div>

                <div class="mt-6 bg-purple-50 rounded-xl p-4">
                    <div class="flex items-start">
                        <i class="fas fa-gift text-purple-600 text-xl mr-3 mt-1"></i>
                        <div class="text-sm text-purple-900">
                            <p class="font-bold mb-1">Bonus pro nové členy!</p>
                            <p>Získejte 20% slevu na první rezervaci po registraci.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-modal {
            animation: modalSlideIn 0.3s ease-out;
        }
    </style>

    @php
        $shouldOpenLoginModal = $errors->any() && request()->routeIs('login');
        $shouldOpenRegisterModal = $errors->any() && request()->routeIs('register');
    @endphp

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-xl');
            } else {
                nav.classList.remove('shadow-xl');
            }
        });

        // FAQ Toggle
        document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                content.classList.toggle('hidden');
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            });
        });

        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            const icon = this.querySelector('i');
            
            menu.classList.toggle('hidden');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', function() {
                const menu = document.getElementById('mobile-menu');
                const icon = document.querySelector('#mobile-menu-btn i');
                
                menu.classList.add('hidden');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            });
        });

        // Modal functions
        function openLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openRegisterModal() {
            document.getElementById('registerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeRegisterModal() {
            document.getElementById('registerModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginModal();
                closeRegisterModal();
                closePromotionModal('dismissed');
            }
        });

        // Close modal on backdrop click
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoginModal();
            }
        });

        document.getElementById('registerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRegisterModal();
            }
        });

        document.getElementById('promotionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePromotionModal('dismissed');
            }
        });

        // Auto-open modals based on errors
        const shouldOpenLoginModal = {{ $shouldOpenLoginModal ? 'true' : 'false' }};
        const shouldOpenRegisterModal = {{ $shouldOpenRegisterModal ? 'true' : 'false' }};
        
        if (shouldOpenLoginModal) {
            openLoginModal();
        }
        
        if (shouldOpenRegisterModal) {
            openRegisterModal();
        }

        // Cookie consent functions
        function checkCookieConsent() {
            const consent = localStorage.getItem('cookieConsent');
            if (!consent) {
                // Show cookie modal after 1 second
                setTimeout(() => {
                    document.getElementById('cookieModal').classList.remove('hidden');
                }, 1000);
            }
        }

        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'accepted');
            localStorage.setItem('cookieConsentDate', new Date().toISOString());
            document.getElementById('cookieModal').classList.add('hidden');
            
            // Here you can enable analytics/marketing cookies
            console.log('Cookies accepted - analytics enabled');
        }

        function rejectCookies() {
            // Don't save rejection, so modal shows again next time
            document.getElementById('cookieModal').classList.add('hidden');
            console.log('Only necessary cookies - analytics disabled');
        }

        // Check cookie consent on page load
        checkCookieConsent();

        // Promotion Modal Functions
        let currentPromotion = null;

        async function loadActivePromotion() {
            try {
                const response = await fetch('/api/promotions/active');
                if (!response.ok) return;
                
                const data = await response.json();
                if (data.promotion) {
                    currentPromotion = data.promotion;
                    displayPromotion(data.promotion);
                }
            } catch (error) {
                console.error('Error loading promotion:', error);
            }
        }

        function displayPromotion(promotion) {
            // Set title and description
            document.getElementById('promotionTitle').textContent = promotion.title;
            document.getElementById('promotionDescription').textContent = promotion.description;

            // Set image if exists
            if (promotion.image_url) {
                document.getElementById('promotionImage').classList.remove('hidden');
                document.getElementById('promotionImageSrc').src = promotion.image_url;
                document.getElementById('promotionImageSrc').alt = promotion.title;
            }

            // Set icon based on type
            const iconMap = {
                'registration_discount': '<div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto"><i class="fas fa-gift text-4xl text-purple-600"></i></div>',
                'event_discount': '<div class="bg-gradient-to-r from-blue-100 to-cyan-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto"><i class="fas fa-percent text-4xl text-blue-600"></i></div>',
                'general_info': '<div class="bg-gradient-to-r from-yellow-100 to-orange-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto"><i class="fas fa-info-circle text-4xl text-orange-600"></i></div>',
                'announcement': '<div class="bg-gradient-to-r from-green-100 to-emerald-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto"><i class="fas fa-bullhorn text-4xl text-green-600"></i></div>'
            };
            document.getElementById('promotionIcon').innerHTML = iconMap[promotion.type] || iconMap['general_info'];

            // Show discount info if applicable
            if (promotion.discount_code || promotion.discount_percentage || promotion.discount_amount) {
                document.getElementById('promotionDiscount').classList.remove('hidden');
                
                if (promotion.discount_code) {
                    document.getElementById('promotionDiscountCode').textContent = promotion.discount_code;
                }
                
                let discountText = '';
                if (promotion.discount_percentage) {
                    discountText = promotion.discount_percentage + '%';
                } else if (promotion.discount_amount) {
                    discountText = promotion.discount_amount + ' Kč';
                }
                document.getElementById('promotionDiscountValue').textContent = discountText;
            }

            // Set action buttons based on type
            const actionsContainer = document.getElementById('promotionActions');
            actionsContainer.innerHTML = '';

            if (promotion.type === 'registration_discount') {
                actionsContainer.innerHTML = `
                    <button onclick="handlePromotionAction('clicked', true)" 
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
                        <i class="fas fa-user-plus mr-2"></i>${promotion.button_text || 'Registrovat se se slevou'}
                    </button>
                    <button onclick="closePromotionModal('dismissed')" 
                            class="w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition">
                        Možná později
                    </button>
                `;
            } else if (promotion.type === 'event_discount') {
                actionsContainer.innerHTML = `
                    <button onclick="handlePromotionAction('clicked', false)" 
                            class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
                        <i class="fas fa-ticket-alt mr-2"></i>${promotion.button_text || 'Chci slevu'}
                    </button>
                    <button onclick="closePromotionModal('dismissed')" 
                            class="w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition">
                        Ne, děkuji
                    </button>
                `;
            } else {
                actionsContainer.innerHTML = `
                    <button onclick="closePromotionModal('clicked')" 
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
                        <i class="fas fa-check mr-2"></i>${promotion.button_text || 'Beru na vědomí'}
                    </button>
                `;
            }

            // Show modal with delay
            setTimeout(() => {
                document.getElementById('promotionModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                recordPromotionView();
            }, 2000); // Show after 2 seconds
        }

        async function recordPromotionView() {
            if (!currentPromotion) return;
            
            try {
                await fetch(`/api/promotions/${currentPromotion.id}/view`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            } catch (error) {
                console.error('Error recording promotion view:', error);
            }
        }

        async function handlePromotionAction(action, openRegister = false) {
            if (!currentPromotion) return;
            
            try {
                await fetch(`/api/promotions/${currentPromotion.id}/action`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ action: action })
                });
            } catch (error) {
                console.error('Error recording promotion action:', error);
            }

            closePromotionModal(action);
            
            if (openRegister) {
                setTimeout(() => openRegisterModal(), 300);
            } else if (currentPromotion.button_url) {
                window.location.href = currentPromotion.button_url;
            }
        }

        async function closePromotionModal(action = 'dismissed') {
            if (currentPromotion && action === 'dismissed') {
                try {
                    await fetch(`/api/promotions/${currentPromotion.id}/action`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ action: 'dismissed' })
                    });
                } catch (error) {
                    console.error('Error recording dismissal:', error);
                }
            }
            
            document.getElementById('promotionModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentPromotion = null;
        }

        function copyDiscountCode() {
            const code = document.getElementById('promotionDiscountCode').textContent;
            navigator.clipboard.writeText(code).then(() => {
                // Show success feedback
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check mr-2"></i>Zkopírováno!';
                button.classList.add('bg-green-50', 'text-green-600');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-50', 'text-green-600');
                }, 2000);
            });
        }

        // Load promotion on page load (after cookie consent)
        window.addEventListener('load', function() {
            setTimeout(() => {
                loadActivePromotion();
            }, 1500); // Wait 1.5s to ensure cookie modal has shown first
        });
        
        // Fix for image loading issues
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.room-image');
            images.forEach(img => {
                // Set timeout for loading
                const loadTimeout = setTimeout(() => {
                    if (!img.complete) {
                        console.warn('Image loading timeout:', img.src);
                        img.style.display = 'none';
                    }
                }, 5000); // 5 second timeout
                
                img.addEventListener('load', () => {
                    clearTimeout(loadTimeout);
                });
                
                img.addEventListener('error', () => {
                    clearTimeout(loadTimeout);
                });
            });
        });
    </script>
</body>
</html>
