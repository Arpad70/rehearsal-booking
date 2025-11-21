<?php

namespace App\Filament\Resources\RfidManagementResource\Pages;

use App\Filament\Resources\RfidManagementResource;
use App\Http\Controllers\Api\RfidController;
use App\Models\Room;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class InventoryScanner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = RfidManagementResource::class;

    protected static string $view = 'filament.resources.rfid-management-resource.pages.inventory-scanner';

    protected static ?string $title = 'Automatická inventura';

    public ?array $data = [];
    public array $scannedTags = [];
    public ?array $results = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('room_id')
                    ->label('Místnost (volitelné)')
                    ->options(Room::all()->pluck('name', 'id'))
                    ->searchable()
                    ->helperText('Vyberte místnost pro porovnání s očekávaným vybavením'),
            ])
            ->statePath('data');
    }

    public function startScanning(): void
    {
        $this->scannedTags = [];
        $this->results = null;
        
        Notification::make()
            ->title('Skenování spuštěno')
            ->body('Začněte skenovat tagy. Po dokončení klikněte na "Ukončit a vyhodnotit"')
            ->success()
            ->send();
    }

    public function processInventory(): void
    {
        if (empty($this->scannedTags)) {
            Notification::make()
                ->title('Žádné tagy')
                ->body('Nejprve naskenujte nějaké tagy')
                ->warning()
                ->send();
            return;
        }

        $tags = array_map(fn($tag) => ['tag_id' => $tag], $this->scannedTags);
        
        $request = Request::create('/api/v1/rfid/batch-scan', 'POST', [
            'tags' => $tags,
            'room_id' => $this->data['room_id'] ?? null,
        ]);

        $controller = app(RfidController::class);
        $response = $controller->batchScan($request);
        $this->results = json_decode($response->getContent(), true)['results'];

        Notification::make()
            ->title('Inventura dokončena')
            ->body("Naskenováno: {$this->results['scanned']}, Nalezeno: {$this->results['found']}")
            ->success()
            ->send();
    }

    public function clearResults(): void
    {
        $this->scannedTags = [];
        $this->results = null;
        
        Notification::make()
            ->title('Výsledky smazány')
            ->success()
            ->send();
    }
}
