<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomReader;
use App\Models\GlobalReader;
use App\Models\ServiceAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API Controller for device enable/disable operations
 * Allows software control of Shelly devices, QR readers, and service access
 */
class DeviceControlController extends Controller
{
    /**
     * Toggle a Room (Shelly device) enabled status
     *
     * @param Request $request
     * @param Room $room
     * @return JsonResponse
     */
    public function toggleRoom(Request $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);

        $room->update([
            'enabled' => !$room->enabled
        ]);

        return response()->json([
            'success' => true,
            'message' => "Místnost '{$room->name}' je nyní " . ($room->enabled ? 'aktivní' : 'vypnutá'),
            'device' => [
                'id' => $room->id,
                'name' => $room->name,
                'enabled' => $room->enabled,
                'type' => 'room_shelly'
            ]
        ]);
    }

    /**
     * Disable a Room (Shelly device) enabled status
     *
     * @param Request $request
     * @param Room $room
     * @return JsonResponse
     */
    public function disableRoom(Request $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);

        if (!$room->enabled) {
            return response()->json([
                'success' => false,
                'message' => "Místnost '{$room->name}' je již vypnutá"
            ], 400);
        }

        $room->update(['enabled' => false]);

        return response()->json([
            'success' => true,
            'message' => "Místnost '{$room->name}' byla vypnutá",
            'device' => [
                'id' => $room->id,
                'name' => $room->name,
                'enabled' => false,
                'type' => 'room_shelly'
            ]
        ]);
    }

    /**
     * Enable a Room (Shelly device) enabled status
     *
     * @param Request $request
     * @param Room $room
     * @return JsonResponse
     */
    public function enableRoom(Request $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);

        if ($room->enabled) {
            return response()->json([
                'success' => false,
                'message' => "Místnost '{$room->name}' je již aktivní"
            ], 400);
        }

        $room->update(['enabled' => true]);

        return response()->json([
            'success' => true,
            'message' => "Místnost '{$room->name}' byla aktivována",
            'device' => [
                'id' => $room->id,
                'name' => $room->name,
                'enabled' => true,
                'type' => 'room_shelly'
            ]
        ]);
    }

    /**
     * Toggle a Room Reader (QR reader) enabled status
     *
     * @param Request $request
     * @param RoomReader $roomReader
     * @return JsonResponse
     */
    public function toggleRoomReader(Request $request, RoomReader $roomReader): JsonResponse
    {
        $this->authorize('update', $roomReader->room);

        $roomReader->update([
            'enabled' => !$roomReader->enabled
        ]);

        return response()->json([
            'success' => true,
            'message' => "QR čtečka '{$roomReader->reader_name}' v místnosti '{$roomReader->room->name}' je nyní " . ($roomReader->enabled ? 'aktivní' : 'vypnutá'),
            'device' => [
                'id' => $roomReader->id,
                'name' => $roomReader->reader_name,
                'room_id' => $roomReader->room_id,
                'enabled' => $roomReader->enabled,
                'type' => 'room_reader'
            ]
        ]);
    }

    /**
     * Disable a Room Reader (QR reader)
     *
     * @param Request $request
     * @param RoomReader $roomReader
     * @return JsonResponse
     */
    public function disableRoomReader(Request $request, RoomReader $roomReader): JsonResponse
    {
        $this->authorize('update', $roomReader->room);

        if (!$roomReader->enabled) {
            return response()->json([
                'success' => false,
                'message' => "QR čtečka '{$roomReader->reader_name}' je již vypnutá"
            ], 400);
        }

        $roomReader->update(['enabled' => false]);

        return response()->json([
            'success' => true,
            'message' => "QR čtečka '{$roomReader->reader_name}' byla vypnutá",
            'device' => [
                'id' => $roomReader->id,
                'name' => $roomReader->reader_name,
                'room_id' => $roomReader->room_id,
                'enabled' => false,
                'type' => 'room_reader'
            ]
        ]);
    }

    /**
     * Enable a Room Reader (QR reader)
     *
     * @param Request $request
     * @param RoomReader $roomReader
     * @return JsonResponse
     */
    public function enableRoomReader(Request $request, RoomReader $roomReader): JsonResponse
    {
        $this->authorize('update', $roomReader->room);

        if ($roomReader->enabled) {
            return response()->json([
                'success' => false,
                'message' => "QR čtečka '{$roomReader->reader_name}' je již aktivní"
            ], 400);
        }

        $roomReader->update(['enabled' => true]);

        return response()->json([
            'success' => true,
            'message' => "QR čtečka '{$roomReader->reader_name}' byla aktivována",
            'device' => [
                'id' => $roomReader->id,
                'name' => $roomReader->reader_name,
                'room_id' => $roomReader->room_id,
                'enabled' => true,
                'type' => 'room_reader'
            ]
        ]);
    }

    /**
     * Toggle a Global Reader enabled status
     *
     * @param Request $request
     * @param GlobalReader $globalReader
     * @return JsonResponse
     */
    public function toggleGlobalReader(Request $request, GlobalReader $globalReader): JsonResponse
    {
        $this->authorize('update', 'global_reader');

        $globalReader->update([
            'enabled' => !$globalReader->enabled
        ]);

        return response()->json([
            'success' => true,
            'message' => "Globální čtečka '{$globalReader->reader_name}' je nyní " . ($globalReader->enabled ? 'aktivní' : 'vypnutá'),
            'device' => [
                'id' => $globalReader->id,
                'name' => $globalReader->reader_name,
                'access_type' => $globalReader->access_type,
                'enabled' => $globalReader->enabled,
                'type' => 'global_reader'
            ]
        ]);
    }

    /**
     * Toggle Service Access enabled status
     *
     * @param Request $request
     * @param ServiceAccess $serviceAccess
     * @return JsonResponse
     */
    public function toggleServiceAccess(Request $request, ServiceAccess $serviceAccess): JsonResponse
    {
        $this->authorize('update', 'service_access');

        $serviceAccess->update([
            'enabled' => !$serviceAccess->enabled
        ]);

        return response()->json([
            'success' => true,
            'message' => "Servisní přístup uživatele '{$serviceAccess->user->name}' je nyní " . ($serviceAccess->enabled ? 'aktivní' : 'vypnutý'),
            'device' => [
                'id' => $serviceAccess->id,
                'user_id' => $serviceAccess->user_id,
                'access_type' => $serviceAccess->access_type,
                'enabled' => $serviceAccess->enabled,
                'type' => 'service_access'
            ]
        ]);
    }
}
