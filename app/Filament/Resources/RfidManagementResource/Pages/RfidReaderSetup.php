<?php

namespace App\Filament\Resources\RfidManagementResource\Pages;

use App\Filament\Resources\RfidManagementResource;
use App\Http\Controllers\Api\RfidController;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class RfidReaderSetup extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = RfidManagementResource::class;

    protected static string $view = 'filament.resources.rfid-management-resource.pages.rfid-reader-setup';

    protected static ?string $title = 'Nastavení RFID čtečky';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stav čtečky')
                    ->schema([
                        Forms\Components\Placeholder::make('status')
                            ->label('Připojení')
                            ->content(fn() => $this->getReaderStatus()),

                        Forms\Components\Placeholder::make('api_endpoint')
                            ->label('API Endpoint')
                            ->content(url('/api/v1/rfid')),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('test_connection')
                                ->label('Testovat připojení')
                                ->icon('heroicon-o-signal')
                                ->color('primary')
                                ->action(function () {
                                    $this->testConnection();
                                }),
                        ]),
                    ]),

                Forms\Components\Section::make('Konfigurace USB čtečky')
                    ->description('Podporované módy připojení')
                    ->schema([
                        Forms\Components\Placeholder::make('mode1')
                            ->label('Mód 1: Keyboard Emulation')
                            ->content('Čtečka funguje jako klávesnice - nejběžnější mód. Stačí přiložit tag k čtečce.'),

                        Forms\Components\Placeholder::make('mode2')
                            ->label('Mód 2: Serial Communication')
                            ->content('Připojení přes sériový port - použijte Python skript: python_gateway/rfid_scanner.py'),

                        Forms\Components\Placeholder::make('mode3')
                            ->label('Mód 3: NFC přes mobil')
                            ->content('Použijte mobilní aplikaci s NFC pro Android'),
                    ]),

                Forms\Components\Section::make('Web rozhraní')
                    ->schema([
                        Forms\Components\Placeholder::make('web_interface')
                            ->label('RFID Manager')
                            ->content(fn() => view('filament.components.rfid-web-link')),
                        
                        Forms\Components\Placeholder::make('nfc_scanner')
                            ->label('📱 NFC Scanner (Mobilní)')
                            ->content(fn() => view('filament.components.nfc-scanner-link')),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getReaderStatus(): string
    {
        try {
            // Přímé volání API controlleru místo HTTP requestu
            $request = Request::create('/api/v1/rfid/read', 'POST', [
                'tag_id' => 'TEST-STATUS-CHECK',
            ]);
            
            $controller = app(RfidController::class);
            $response = $controller->read($request);
            
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 404) {
                return '🟢 API server běží';
            }
        } catch (\Exception $e) {
            return '🔴 API server neodpovídá';
        }

        return '🔴 Čtečka není připojena';
    }

    protected function testConnection(): void
    {
        try {
            // Přímé volání API controlleru místo HTTP requestu
            $request = Request::create('/api/v1/rfid/read', 'POST', [
                'tag_id' => 'TEST-CONNECTION',
            ]);
            
            $controller = app(RfidController::class);
            $response = $controller->read($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() === 200) {
                if (isset($data['success']) && !$data['success']) {
                    Notification::make()
                        ->title('API server funguje')
                        ->body('Tag nebyl nalezen (očekávané chování pro test)')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('API server funguje')
                        ->success()
                        ->send();
                }
            } elseif ($response->getStatusCode() === 404) {
                Notification::make()
                    ->title('API server běží')
                    ->body('Test tag nebyl nalezen (normální chování)')
                    ->success()
                    ->send();
            } else {
                throw new \Exception('HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Chyba připojení')
                ->body('Nelze se připojit k API serveru: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Zpět')
                ->url(static::$resource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
