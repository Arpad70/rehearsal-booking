<?php

namespace App\Exports;

use App\Models\Equipment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Request;

class EquipmentExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Equipment::query();

        // Filtrování podle statusu
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Filtrování podle místnosti
        if (!empty($this->filters['location'])) {
            $query->where('location', 'LIKE', '%' . $this->filters['location'] . '%');
        }

        // Filtrování podle kategorie
        if (!empty($this->filters['category'])) {
            $query->where('category', 'LIKE', '%' . $this->filters['category'] . '%');
        }

        // Filtrování podle kritičnosti
        if (isset($this->filters['is_critical'])) {
            $query->where('is_critical', $this->filters['is_critical']);
        }

        // Filtrování podle typu tagu
        if (!empty($this->filters['tag_type'])) {
            $query->where('tag_type', $this->filters['tag_type']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Název',
            'Popis',
            'Kategorie',
            'Místnost',
            'Status',
            'Tag ID',
            'Typ tagu',
            'Je kritické',
            'Pořizovací cena',
            'Poznámka',
            'Vytvořeno',
            'Aktualizováno',
        ];
    }

    public function map($equipment): array
    {
        return [
            $equipment->id,
            $equipment->name,
            $equipment->description,
            $equipment->category,
            $equipment->location,
            $this->formatStatus($equipment->status),
            $equipment->tag_id,
            $equipment->tag_type === 'nfc' ? 'NFC' : 'RFID',
            $equipment->is_critical ? 'Ano' : 'Ne',
            $equipment->purchase_price ? number_format($equipment->purchase_price, 2, ',', ' ') . ' Kč' : '',
            $equipment->notes,
            $equipment->created_at?->format('d.m.Y H:i'),
            $equipment->updated_at?->format('d.m.Y H:i'),
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
            'available' => 'Dostupné',
            'in_use' => 'Používané',
            'maintenance' => 'V údržbě',
            'damaged' => 'Poškozené',
            'lost' => 'Ztracené',
            default => $status ?? '',
        };
    }
}
