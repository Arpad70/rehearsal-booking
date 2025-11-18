<?php  
namespace App\Http\Controllers\Api;  
use App\Http\Controllers\Controller;  
use App\Models\Room;  
use Illuminate\Http\Request;  

class RoomController extends Controller
{  
    /**
     * List all rooms
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {  
        return response()->json(Room::all());  
    }  

    /**
     * Get room availability in date range
     *
     * @param Room $room
     * @param Request $r
     * @return \Illuminate\Http\JsonResponse
     */
    public function availability(Room $room, Request $r): \Illuminate\Http\JsonResponse
    {  
        /** @var ?string */
        $fromStr = $r->query('from');
        /** @var ?string */
        $toStr = $r->query('to');
        
        $from = $fromStr ? \Carbon\Carbon::parse($fromStr) : now();  
        $to = $toStr ? \Carbon\Carbon::parse($toStr) : $from->copy()->addDays(7);
        $reservations = $room->reservations()->where('start_at','<',$to)->where('end_at','>',$from)->get();  
        return response()->json(['reservations'=>$reservations]);  
    }  
}