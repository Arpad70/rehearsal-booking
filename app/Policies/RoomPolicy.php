<?php  

namespace App\Policies;  

use App\Models\User;  
use App\Models\Room;  

class RoomPolicy  
{  
    public function viewAny(User $user): bool  
    {  
        return $user->hasRole('admin') || $user->can('view rooms');  
    }  

    public function view(User $user, Room $room): bool  
    {  
        return $user->hasRole('admin') || $user->company_id === $room->company_id;  
    }  

    public function create(User $user): bool  
    {  
        return $user->hasRole('admin');  
    }  

    public function update(User $user, Room $room): bool  
    {  
        return $user->hasRole('admin');  
    }  

    public function delete(User $user, Room $room): bool  
    {  
        return $user->hasRole('admin');  
    }  
}  