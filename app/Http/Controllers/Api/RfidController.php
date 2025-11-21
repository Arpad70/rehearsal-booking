<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RfidController extends Controller
{
    /**
     * Přečíst RFID/NFC tag - najde vybavení v databázi
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function read(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rfid_tag' => 'sometimes|string|max:255',  // Zpětná kompatibilita
            'tag_id' => 'sometimes|string|max:255',    // Nový formát
            'tag_type' => 'sometimes|in:rfid,nfc',     // Typ tagu
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Neplatný formát tagu',
                'details' => $validator->errors()
            ], 422);
        }

        // Podporujeme oba formáty - starý (rfid_tag) i nový (tag_id)
        $tagId = $request->input('tag_id') ?? $request->input('rfid_tag');
        
        // Ověříme, že alespoň jeden z tagů byl poskytnut
        if (!$tagId) {
            return response()->json([
                'success' => false,
                'error' => 'Musí být poskytnut tag_id nebo rfid_tag',
            ], 422);
        }
        
        $tagType = $request->input('tag_type'); // může být null

        // Najít vybavení podle tagu (tag_id bylo dříve rfid_tag)
        $query = Equipment::where('tag_id', $tagId);
        
        // Pokud je specifikován typ, filtrujeme i podle něj
        if ($tagType) {
            $query->where('tag_type', $tagType);
        }
        
        $equipment = $query->with(['category', 'rooms'])->first();

        if (!$equipment) {
            Log::warning('Tag not found', [
                'tag_id' => $tagId,
                'tag_type' => $tagType ?? 'any'
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Tag nenalezen v databázi',
                'tag_id' => $tagId,
                'tag_type' => $tagType,
                'suggestion' => 'Zaregistrujte tento tag v admin panelu'
            ], 404);
        }

        // Zalogovat přístup k vybavení
        $userId = Auth::id();
        if ($userId !== null) {
            AccessLog::create([
                'user_id' => $userId,
                'equipment_id' => $equipment->id,
                'room_id' => null,
                'action' => $equipment->tag_type === 'nfc' ? 'nfc_scan' : 'rfid_scan',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Log::info('Tag scan successful', [
            'tag_id' => $tagId,
            'tag_type' => $equipment->tag_type,
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name
        ]);

        // Get category relation explicitly (avoid conflict with 'category' string attribute)
        $categoryRelation = $equipment->relationLoaded('category') ? $equipment->getRelation('category') : null;

        return response()->json([
            'success' => true,
            'tag_id' => $equipment->tag_id,
            'tag_type' => $equipment->tag_type,
            'rfid_tag' => $equipment->tag_id, // Zpětná kompatibilita
            'equipment' => [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'description' => $equipment->description,
                'category' => $categoryRelation ? [
                    'id' => $categoryRelation->id,
                    'name' => $categoryRelation->name,
                    'icon' => $categoryRelation->icon,
                ] : null,
                'model' => $equipment->model,
                'serial_number' => $equipment->serial_number,
                'tag_type' => $equipment->tag_type,
                'tag_type_label' => $equipment->getTagTypeLabel(),
                'status' => $equipment->status,
                'location' => $equipment->location,
                'is_critical' => $equipment->is_critical,
                'quantity_available' => $equipment->quantity_available,
                'rooms' => $equipment->rooms ? $equipment->rooms->map(fn($room) => [
                    'id' => $room->id,
                    'name' => $room->name,
                ]) : [],
            ]
        ]);
    }

    /**
     * Zapsat RFID tag - vytvoří nebo aktualizuje vybavení
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function write(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rfid_tag' => 'required|string|max:255|unique:equipment,tag_id,' . $request->input('equipment_id'),
            'tag_type' => 'nullable|in:rfid,nfc',
            'equipment_id' => 'nullable|exists:equipment,id',
            'equipment_name' => 'required_without:equipment_id|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Neplatná data',
                'details' => $validator->errors()
            ], 422);
        }

        $rfidTag = $request->input('rfid_tag');
        $tagType = $request->input('tag_type', 'rfid');
        $equipmentId = $request->input('equipment_id');

        // Aktualizovat existující vybavení
        if ($equipmentId) {
            $equipment = Equipment::findOrFail($equipmentId);
            
            // Kontrola, jestli tag už není použitý
            $existingTag = Equipment::where('tag_id', $rfidTag)
                ->where('id', '!=', $equipmentId)
                ->first();
            
            if ($existingTag) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tag již je přiřazen jinému vybavení',
                    'existing_equipment' => [
                        'id' => $existingTag->id,
                        'name' => $existingTag->name,
                    ]
                ], 409);
            }

            $equipment->update([
                'tag_id' => $rfidTag,
                'tag_type' => $tagType,
            ]);

            Log::info('Tag assigned to existing equipment', [
                'tag_id' => $rfidTag,
                'tag_type' => $tagType,
                'equipment_id' => $equipment->id,
                'equipment_name' => $equipment->name
            ]);

            return response()->json([
                'success' => true,
                'action' => 'updated',
                'message' => 'Tag přiřazen k existujícímu vybavení',
                'tag_id' => $rfidTag,
                'tag_type' => $tagType,
                'rfid_tag' => $rfidTag,
                'equipment' => [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'rfid_tag' => $equipment->rfid_tag,
                ]
            ]);
        }

        // Vytvořit nové vybavení
        $equipment = Equipment::create([
            'name' => $request->input('equipment_name'),
            'description' => $request->input('description'),
            'category_id' => $request->input('category_id'),
            'model' => $request->input('model'),
            'serial_number' => $request->input('serial_number'),
            'tag_id' => $rfidTag,
            'tag_type' => $tagType,
            'location' => $request->input('location'),
            'status' => 'available',
            'quantity_available' => 1,
        ]);

        Log::info('New equipment created with tag', [
            'tag_id' => $rfidTag,
            'tag_type' => $tagType,
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name
        ]);

        return response()->json([
            'success' => true,
            'action' => 'created',
            'message' => 'Nové vybavení vytvořeno s tagem',
            'tag_id' => $rfidTag,
            'tag_type' => $tagType,
            'rfid_tag' => $rfidTag,
            'equipment' => [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'rfid_tag' => $equipment->rfid_tag,
            ]
        ], 201);
    }

    /**
     * Ověřit dostupnost RFID tagu (zda už není použitý)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rfid_tag' => 'sometimes|string|max:255',  // Zpětná kompatibilita
            'tag_id' => 'sometimes|string|max:255',
            'tag_type' => 'sometimes|in:rfid,nfc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Neplatný formát tagu',
            ], 422);
        }

        $tagId = $request->input('tag_id') ?? $request->input('rfid_tag');
        $tagType = $request->input('tag_type');
        
        $query = Equipment::where('tag_id', $tagId);
        if ($tagType) {
            $query->where('tag_type', $tagType);
        }
        
        $equipment = $query->with('category')->first();

        if ($equipment) {
            $categoryRelation = $equipment->relationLoaded('category') ? $equipment->getRelation('category') : null;
            
            return response()->json([
                'available' => false,
                'tag_id' => $tagId,
                'tag_type' => $equipment->tag_type,
                'rfid_tag' => $tagId,  // Zpětná kompatibilita
                'used_by' => [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'category' => $categoryRelation?->name,
                ]
            ]);
        }

        return response()->json([
            'available' => true,
            'tag_id' => $tagId,
            'tag_type' => $tagType,
            'rfid_tag' => $tagId,  // Zpětná kompatibilita
        ]);
    }

    /**
     * Výpůjčka vybavení přes RFID
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkOut(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rfid_tag' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Neplatná data',
                'details' => $validator->errors()
            ], 422);
        }

        $equipment = Equipment::where('rfid_tag', $request->input('rfid_tag'))->first();

        if (!$equipment) {
            return response()->json([
                'success' => false,
                'error' => 'RFID tag nenalezen',
            ], 404);
        }

        if ($equipment->status !== 'available') {
            return response()->json([
                'success' => false,
                'error' => 'Vybavení není dostupné',
                'current_status' => $equipment->status,
            ], 409);
        }

        // Zalogovat výpůjčku
        AccessLog::create([
            'user_id' => $request->input('user_id'),
            'equipment_id' => $equipment->id,
            'room_id' => $request->input('room_id'),
            'action' => 'checkout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Aktualizovat stav (volitelně)
        // $equipment->update(['status' => 'in_use']);

        return response()->json([
            'success' => true,
            'action' => 'checked_out',
            'equipment' => [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'rfid_tag' => $equipment->rfid_tag,
            ]
        ]);
    }

    /**
     * Vrácení vybavení přes RFID
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rfid_tag' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Neplatná data',
                'details' => $validator->errors()
            ], 422);
        }

        $equipment = Equipment::where('rfid_tag', $request->input('rfid_tag'))->first();

        if (!$equipment) {
            return response()->json([
                'success' => false,
                'error' => 'RFID tag nenalezen',
            ], 404);
        }

        // Zalogovat vrácení
        AccessLog::create([
            'user_id' => $request->input('user_id'),
            'equipment_id' => $equipment->id,
            'action' => 'checkin',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Aktualizovat stav (volitelně)
        // $equipment->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'action' => 'checked_in',
            'equipment' => [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'rfid_tag' => $equipment->rfid_tag,
            ]
        ]);
    }

    /**
     * Status čtečky - kontrola, zda API běží
     * 
     * @return JsonResponse
     */
    public function readerStatus(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'api_version' => '1.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Batch scan - zpracování více tagů najednou pro inventuru
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function batchScan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tags' => 'required|array|min:1',
            'tags.*.tag_id' => 'required|string|max:255',
            'tags.*.tag_type' => 'sometimes|in:rfid,nfc',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Neplatná data',
                'details' => $validator->errors()
            ], 422);
        }

        $tags = $request->input('tags');
        $roomId = $request->input('room_id');
        $results = [
            'scanned' => count($tags),
            'found' => 0,
            'not_found' => 0,
            'equipment' => [],
            'missing_tags' => [],
        ];

        foreach ($tags as $tagData) {
            $tagId = $tagData['tag_id'];
            $equipment = Equipment::where('tag_id', $tagId)->first();

            if ($equipment) {
                $results['found']++;
                $results['equipment'][] = [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'tag_id' => $tagId,
                    'tag_type' => $equipment->tag_type,
                    'status' => $equipment->status,
                    'location' => $equipment->location,
                    'category' => $equipment->category?->name,
                ];
            } else {
                $results['not_found']++;
                $results['missing_tags'][] = $tagId;
            }
        }

        // Pokud je specifikována místnost, najít vybavení které tam má být ale nebylo naskenováno
        if ($roomId) {
            $expectedEquipment = Equipment::whereHas('rooms', function ($query) use ($roomId) {
                $query->where('rooms.id', $roomId);
            })->whereNotNull('tag_id')->get();

            $scannedIds = collect($results['equipment'])->pluck('id')->toArray();
            $missing = $expectedEquipment->filter(function ($item) use ($scannedIds) {
                return !in_array($item->id, $scannedIds);
            });

            $results['expected_count'] = $expectedEquipment->count();
            $results['missing_equipment'] = $missing->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'tag_id' => $item->tag_id,
                    'location' => $item->location,
                ];
            })->values()->toArray();
        }

        Log::info('Batch scan completed', [
            'scanned' => $results['scanned'],
            'found' => $results['found'],
            'room_id' => $roomId,
        ]);

        return response()->json([
            'success' => true,
            'results' => $results,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
