<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Display a listing of the rooms.
     */
    public function index(): View
    {
        $rooms = Room::orderBy('name')->get();
        return view('rooms.index', compact('rooms'));
    }

    /**
     * Display the specified room.
     */
    public function show(Room $room): View
    {
        return view('rooms.show', compact('room'));
    }
}