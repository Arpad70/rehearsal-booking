<?php  

namespace App\Filament\Resources\RoomResource\Pages;  

use App\Filament\Resources\RoomResource;  
use Filament\Resources\Pages\CreateRecord;  
use Filament\Notifications\Notification;  
use Illuminate\Support\Facades\Gate;  

class CreateRoom extends CreateRecord  
{  
    protected static string $resource = RoomResource::class;  

    protected function beforeCreate(): void  
    {  
        // explicitní autorizace přes Laravel Gate / Policy  
        Gate::authorize('create', \App\Models\Room::class);  
    }  

    protected function afterCreate(): void  
    {  
        Notification::make()  
            ->title('Místnost vytvořena')  
            ->success()  
            ->send();  

        // přesměruje na edit page nově vytvořeného záznamu  
        $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));  
    }  
}  