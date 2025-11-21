<?php

namespace App\Exports;

use App\Models\AccessLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccessLogsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = AccessLog::query()->with(['room', 'user']);

        // Filtrování podle časového rozmezí
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        // Filtrování podle uživatele
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        // Filtrování podle místnosti
        if (!empty($this->filters['room_id'])) {
            $query->where('room_id', $this->filters['room_id']);
        }

        // Filtrování podle povoleného přístupu
        if (isset($this->filters['access_granted'])) {
            $query->where('access_granted', $this->filters['access_granted']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Datum a čas',
            'Uživatel',
            'Email',
            'Místnost',
            'Umístění',
            'Akce',
            'Výsledek',
            'Přístup povolen',
            'Důvod selhání',
            'IP adresa',
        ];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->created_at?->format('d.m.Y H:i:s'),
            $log->user?->name ?? 'Neznámý',
            $log->user?->email ?? '',
            $log->room?->name ?? 'Neznámá',
            $log->location ?? '',
            $log->action ?? '',
            $log->result ?? '',
            $log->access_granted ? 'Ano' : 'Ne',
            $log->failure_reason ?? '',
            $log->ip_address ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
