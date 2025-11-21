<?php

namespace App\Notifications;

use App\Models\Equipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class CriticalEquipmentStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Equipment $equipment,
        protected string $oldStatus,
        protected string $newStatus
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Kritické vybavení: Změna stavu - ' . $this->equipment->name)
            ->greeting('Upozornění na změnu stavu kritického vybavení')
            ->line('Stav kritického vybavení **' . $this->equipment->name . '** byl změněn.')
            ->line('**Původní stav:** ' . $this->formatStatus($this->oldStatus))
            ->line('**Nový stav:** ' . $this->formatStatus($this->newStatus))
            ->line('**Umístění:** ' . ($this->equipment->location ?? 'Neurčeno'))
            ->line('**Kategorie:** ' . ($this->equipment->category ?? 'Neurčeno'))
            ->action('Zobrazit detail vybavení', url('/admin/rfid-management/' . $this->equipment->id . '/edit'))
            ->line('Prosím, zkontrolujte stav zařízení a proveďte případné kroky.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'equipment_id' => $this->equipment->id,
            'equipment_name' => $this->equipment->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'location' => $this->equipment->location,
            'category' => $this->equipment->category,
            'message' => sprintf(
                'Kritické vybavení "%s" změnilo stav z "%s" na "%s"',
                $this->equipment->name,
                $this->formatStatus($this->oldStatus),
                $this->formatStatus($this->newStatus)
            ),
        ];
    }

    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'available' => 'Dostupné',
            'in_use' => 'Používané',
            'maintenance' => 'V údržbě',
            'damaged' => 'Poškozené',
            'lost' => 'Ztracené',
            default => $status,
        };
    }
}
