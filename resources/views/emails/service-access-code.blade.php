@component('mail::message')

# Přístupový kód pro {{ $accessType }}

Dobrý den {{ $user->first_name }},

Byl vám přidělen nový **přístupový kód** pro {{ $accessType }} v našem systému.

@component('mail::panel')

## Detaily přístupu
**Typ:** {{ ucfirst($accessType) }}  
**Přístupový kód:** `{{ $serviceAccess->access_code }}`  
@if(!$serviceAccess->unlimited_access)
**Místnosti:** {{ count($allowedRooms ?? []) }} vybraných místností
@else
**Místnosti:** Všechny místnosti
@endif

@if($serviceAccess->valid_from)
**Platný od:** {{ $serviceAccess->valid_from->format('j. n. Y H:i') }}
@endif
@if($serviceAccess->valid_until)
**Platný do:** {{ $serviceAccess->valid_until->format('j. n. Y H:i') }}
@else
**Platný:** Bez časového omezení
@endif

**Status:** 
@if($isActive)
✅ Aktivní
@else
❌ Neaktivní
@endif

@endcomponent

## Jak používat kód

1. Přijďte k čtečce QR kódu
2. Naskenujte QR kód (připojen v příloze) nebo zadejte kód `{{ $serviceAccess->access_code }}`
3. Systém ověří váš přístup
4. Dveře se odemknou

@if(!$serviceAccess->unlimited_access)
## Povolené místnosti
@foreach($allowedRooms ?? [] as $room)
- {{ $room->name }}
@endforeach
@endif

## Důležitá upozornění

- Kód sdílejte pouze se schválenými osobami
- Používejte kód pouze pro účel, na který vám byl přidělen
- V případě podezření na zneužití kontaktujte správce
- Kód může být kdykoli zrušen

@component('mail::footer')
V případě otázek prosím kontaktujte správce systému.
@endcomponent

@endcomponent
