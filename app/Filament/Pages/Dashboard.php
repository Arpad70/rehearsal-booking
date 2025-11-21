<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Nastavení počtu sloupců v gridu
     * 
     * Responsive grid:
     * - Na mobilech: 1 sloupec
     * - Na tabletech (md): 2 sloupce
     * - Na desktopu (xl): 3 sloupce
     */
    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
            'xl' => 6,
            '2xl' => 6,
        ];
    }
}
