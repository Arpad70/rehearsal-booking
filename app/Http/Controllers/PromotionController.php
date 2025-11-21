<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    /**
     * Get active promotion for current user
     */
    public function getActive(Request $request)
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();
        
        $promotion = Promotion::getActivePromotions($userId, $sessionId);
        
        if (!$promotion) {
            return response()->json(['promotion' => null]);
        }
        
        return response()->json([
            'promotion' => [
                'id' => $promotion->id,
                'title' => $promotion->title,
                'description' => $promotion->description,
                'type' => $promotion->type,
                'discount_code' => $promotion->discount_code,
                'discount_percentage' => $promotion->discount_percentage,
                'discount_amount' => $promotion->discount_amount,
                'image_url' => $promotion->image_url,
                'button_text' => $promotion->button_text,
                'button_url' => $promotion->button_url,
            ]
        ]);
    }

    /**
     * Record promotion view
     */
    public function recordView(Request $request, Promotion $promotion)
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();
        
        $promotion->recordView($userId, $sessionId, $ipAddress, 'viewed');
        
        return response()->json(['success' => true]);
    }

    /**
     * Record promotion action (click, dismiss, register)
     */
    public function recordAction(Request $request, Promotion $promotion)
    {
        $request->validate([
            'action' => 'required|in:clicked,dismissed,registered',
        ]);
        
        $userId = Auth::id();
        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();
        
        $promotion->recordView($userId, $sessionId, $ipAddress, $request->action);
        
        return response()->json(['success' => true]);
    }
}
