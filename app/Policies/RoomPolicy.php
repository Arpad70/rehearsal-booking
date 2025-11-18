<?php  

namespace App\Policies;  

use App\Models\User;  
use App\Models\Room;  

class RoomPolicy  
{  
    public function viewAny(User $user): bool  
    {  
        return $user->isAdmin() || $user->can('view rooms');  
    }  

    public function view(User $user, Room $room): bool  
    {  
        return $user->isAdmin() || true;  
    }  

    public function create(User $user): bool  
    {  
        return $user->isAdmin();  
    }  

    public function update(User $user, Room $room): bool  
    {  
        return $user->isAdmin();  
    }  

    public function delete(User $user, Room $room): bool  
    {  
        return $user->isAdmin();  
    }  
}  