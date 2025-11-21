<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReservationsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Reservation::query()->with(['room', 'user']);

        // Filtrování podle časového rozmezí
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('start_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('end_at', '<=', $this->filters['date_to']);
        }

        // Filtrování podle místnosti
        if (!empty($this->filters['room_id'])) {
            $query->where('room_id', $this->filters['room_id']);
        }

        // Filtrování podle uživatele
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        // Filtrování podle statusu
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('start_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Místnost',
            'Uživatel',
            'Email',
            'Začátek',
            'Konec',
            'Status',
            'Účel',
            'Poznámka',
            'Vytvořeno',
        ];
    }

    public function map($reservation): array
    {
        return [
            $reservation->id,
            $reservation->room?->name ?? 'Neznámá',
            $reservation->user?->name ?? 'Neznámý',
            $reservation->user?->email ?? '',
            $reservation->start_at?->format('d.m.Y H:i'),
            $reservation->end_at?->format('d.m.Y H:i'),
            $this->formatStatus($reservation->status),
            $reservation->purpose ?? '',
            $reservation->notes ?? '',
            $reservation->created_at?->format('d.m.Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    protected function formatStatus(?string $status): string
    {
        return match ($status) {
            'pending' => 'Čekající',
            'confirmed' => 'Potvrzeno',
            'cancelled' => 'Zrušeno',
            'completed' => 'Dokončeno',
            default => $status ?? '',
        };
    }
}
