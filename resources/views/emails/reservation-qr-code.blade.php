<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Potvrzení rezervace - {{ $room->name }}</title>
	</head>
	<body>
		<h1>Potvrzení rezervace - {{ $room->name }}</h1>

		<p>Dobrý den {{ $user->first_name }},</p>

		<p>Vaše rezervace byla <strong>úspěšně potvrzena</strong>. Níže najdete všechny důležité informace pro přístup do místnosti.</p>

		<h2>Informace o místnosti</h2>
		<ul>
			<li><strong>Místnost:</strong> {{ $room->name }}</li>
			<li><strong>Datum:</strong> {{ $reservation->start_at->format('j. n. Y') }}</li>
			<li><strong>Čas začátku:</strong> {{ $reservation->start_at->format('H:i') }}</li>
			<li><strong>Čas konce:</strong> {{ $reservation->end_at->format('H:i') }}</li>
			<li><strong>Doba přístupu:</strong> {{ $accessWindow['earliest_access'] }} až {{ $accessWindow['latest_access'] }}</li>
		</ul>

		<h2>QR kód pro přístup</h2>
		<p>Níže je QR kód, který můžete naskenovat na čtečce u místnosti. QR kód je platný <strong>15 minut před vaší rezervací</strong> až do <strong>konce rezervace</strong>.</p>

		<p>[QR kód je připojen jako obrázek]</p>

		<h2>Instrukce pro přístup</h2>
		<ol>
			<li>Přijďte k dveřím místnosti {{ $room->name }}</li>
			<li>Naskenujte QR kód na čtečce (nebo zadejte přístupový kód)</li>
			<li>Dveře se odemknou na <strong>5 sekund</strong></li>
			<li>Vstupte do místnosti</li>
		</ol>

		<h2>Bezpečnostní poznámky</h2>
		<ul>
			<li>QR kód je osobní a není přenositelný</li>
			<li>Pokud máte problémy s přístupem, kontaktujte správce</li>
			<li>QR kód je platný pouze během období rezervace + 15 minut před</li>
		</ul>

		<p>Pokud máte jakékoli dotazy, prosím kontaktujte podporu.</p>
	</body>
</html>
