<?php  

namespace App\Filament\Resources\RoomResource\Pages;  

use App\Filament\Resources\RoomResource;  
use Filament\Resources\Pages\EditRecord;  
use Filament\Notifications\Notification;  
use Illuminate\Support\Facades\Gate;  

class EditRoom extends EditRecord  
{  
    protected static string $resource = RoomResource::class;  

    protected function beforeSave(): void  
    {  
        // autorizace úpravy záznamu  
        Gate::authorize('update', $this->record);  
    }  

    protected function afterSave(): void  
    {  
        Notification::make()  
            ->title('Změny uloženy')  
            ->success()  
            ->send();  
    }  

    protected function afterDelete(): void  
    {  
        Notification::make()  
            ->title('Místnost byla smazána')  
            ->success()  
            ->send();  

        $this->redirect($this->getResource()::getUrl('index'));  
    }  
}  