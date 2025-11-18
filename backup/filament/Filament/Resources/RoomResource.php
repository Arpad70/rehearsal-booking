<?php  

namespace App\Filament\Resources;  

use App\Filament\Resources\RoomResource\Pages;  
use App\Models\Room;  
use Filament\Resources\Resource;  
use Filament\Schemas\Schema;  
use Filament\Forms;  
use Filament\Tables;  
use Filament\Tables\Columns;  
use Filament\Forms\Components;  
use Filament\Tables\Table as FilamentTable;  
use Filament\Tables\Columns\TextColumn;  

class RoomResource extends Resource  
{  
    protected static ?string $model = Room::class;  

    protected static ?string $navigationIcon = 'heroicon-o-collection';  

    public static function form(Schema $form): Schema  
    {  
        return $form  
            ->schema([  
                Components\TextInput::make('name')->required(),  
                Components\TextInput::make('location'),  
                Components\TextInput::make('capacity')->numeric(),  
                Components\TextInput::make('shelly_ip'),  
                Components\TextInput::make('shelly_token'),  
            ]);  
    }  

    public static function table(FilamentTable $table): FilamentTable  
    {  
        return $table  
            ->columns([  
                TextColumn::make('id'),  
                TextColumn::make('name'),  
                TextColumn::make('shelly_ip'),  
                TextColumn::make('created_at')->dateTime(),  
            ]);  
    }  

    public static function getPages(): array  
    {  
        return [  
            'index' => Pages\ListRooms::route('/'),  
            'create' => Pages\CreateRoom::route('/create'),  
            'edit' => Pages\EditRoom::route('/{record}/edit'),  
        ];  
    }  
}  