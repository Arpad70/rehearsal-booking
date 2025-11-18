@component('mail::message')

# Potvrzení rezervace - {{ $room->name }}

Dobrý den {{ $user->first_name }},

Vaše rezervace byla **úspěšně potvrzena**. Níže najdete všechny důležité informace pro přístup do místnosti.

@component('mail::panel')

## Informace o místnosti
**Místnost:** {{ $room->name }}  
**Datum:** {{ $reservation->start_at->format('j. n. Y') }}  
**Čas začátku:** {{ $reservation->start_at->format('H:i') }}  
**Čas konce:** {{ $reservation->end_at->format('H:i') }}

**Doba přístupu:** {{ $accessWindow['earliest_access'] }} až {{ $accessWindow['latest_access'] }}

@endcomponent

## QR kód pro přístup

Níže je QR kód, který můžete naskenovat na čtečce u místnosti. 
QR kód je platný **15 minut před vaší rezervací** až do **konce rezervace**.

```
[QR kód je připojen jako obrázek]
```

**Tip:** QR kód si můžete stáhnout nebo jej fotografií v tomto emailu naskenovat přímo ze svého telefonu.

## Instrukce pro přístup

1. Přijďte k dveřím místnosti {{ $room->name }}
2. Naskenujte QR kód na čtečce (nebo zadejte přístupový kód)
3. Dveře se odemknou na **5 sekund**
4. Vstupte do místnosti

## Bezpečnostní poznámky

- QR kód je osobní a není přenositelný
- Pokud máte problémy s přístupem, kontaktujte správce
- QR kód je platný pouze během období rezervace + 15 minut před

@component('mail::footer')
Pokud máte jakékoli dotazy, prosím kontaktujte podporu.
@endcomponent

@endcomponent
