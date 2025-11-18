Potvrzení rezervace - {{ config('app.name') }}

Dobrý den,

Vaše rezervace byla úspěšně vytvořena.

Detaily rezervace:
Místnost: {{ $reservation->room->name }}
Datum: {{ $reservation->start_at->format('j. n. Y') }}
Čas: {{ $reservation->start_at->format('H:i') }} - {{ $reservation->end_at->format('H:i') }}

Pro vstup do místnosti použijte přiložený QR kód.

S pozdravem,
{{ config('app.name') }}