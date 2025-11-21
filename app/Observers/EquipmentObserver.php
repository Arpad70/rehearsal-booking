<?php

namespace App\Observers;

use App\Models\Equipment;
use App\Models\User;
use App\Notifications\CriticalEquipmentStatusChanged;
use Illuminate\Support\Facades\Notification;

class EquipmentObserver
{
    /**
     * Handle the Equipment "updated" event.
     */
    public function updated(Equipment $equipment): void
    {
        // Kontrola, zda je vybavení kritické
        if (!$equipment->is_critical) {
            return;
        }

        // Kontrola, zda se změnil status
        if (!$equipment->wasChanged('status')) {
            return;
        }

        $oldStatus = $equipment->getOriginal('status');
        $newStatus = $equipment->status;

        // Získat administrátory a zodpovědné osoby
        $usersToNotify = User::where(function ($query) {
            $query->where('is_admin', true)
                  ->orWhere('receive_critical_notifications', true);
        })->get();

        // Odeslat notifikaci
        Notification::send(
            $usersToNotify,
            new CriticalEquipmentStatusChanged($equipment, $oldStatus, $newStatus)
        );

        // Přidat také in-app notifikaci pomocí Filament
        foreach ($usersToNotify as $user) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Změna stavu kritického vybavení')
                ->body(sprintf(
                    'Vybavení "%s" změnilo stav z "%s" na "%s"',
                    $equipment->name,
                    $this->formatStatus($oldStatus),
                    $this->formatStatus($newStatus)
                ))
                ->icon('heroicon-o-exclamation-triangle')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('Zobrazit')
                        ->url('/admin/rfid-management/' . $equipment->id . '/edit'),
                ])
                ->sendToDatabase($user);
        }
    }

    protected function formatStatus(?string $status): string
    {
        return match ($status) {
            'available' => 'Dostupné',
            'in_use' => 'Používané',
            'maintenance' => 'V údržbě',
            'damaged' => 'Poškozené',
            'lost' => 'Ztracené',
            default => $status ?? 'Neznámý',
        };
    }
}
