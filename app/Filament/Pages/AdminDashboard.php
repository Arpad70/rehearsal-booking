<?php

namespace App\Filament\Pages;

use App\Models\AccessLog;
use App\Models\RoomReader;
use App\Models\GlobalReader;
use App\Models\ReaderAlert;
use App\Models\ServiceAccess;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;

class AdminDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.admin-dashboard';
    protected static ?string $navigationLabel = 'Admin panel';
    protected static ?string $title = '📊 QR Reader Admin Panel';
    protected static ?string $slug = 'admin-dashboard';

    public function getWidgets(): array
    {
        return [];
    }

    public function getStats(): array
    {
        // Dnes
        $todayAccess = AccessLog::whereDate('created_at', today())->count();
        $todayErrors = AccessLog::whereDate('created_at', today())
            ->where('access_granted', false)
            ->count();

        // Tento týden
        $weekAccess = AccessLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Aktivní čtečky
        $activeReaders = RoomReader::where('enabled', true)->count() + 
                        GlobalReader::where('enabled', true)->count();

        $totalReaders = RoomReader::count() + GlobalReader::count();

        // Aktivní upozornění
        $activeAlerts = ReaderAlert::where('resolved', false)->count();

        return [
            Stat::make('Přístupy dnes', $todayAccess)
                ->description('Celkový počet přístupů dnes')
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before)
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17, 8, 12, 6, 13, 9, 14, 5, 11]),

            Stat::make('Chyby dnes', $todayErrors)
                ->description('Neautorizované pokusy')
                ->descriptionIcon('heroicon-m-exclamation-triangle', IconPosition::Before)
                ->color($todayErrors > 5 ? 'danger' : 'warning'),

            Stat::make('Přístupy týden', $weekAccess)
                ->description('Celkový počet za 7 dní')
                ->descriptionIcon('heroicon-m-calendar', IconPosition::Before)
                ->color('info'),

            Stat::make('Čtečky online', "{$activeReaders}/{$totalReaders}")
                ->description('Aktivní z celkového počtu')
                ->descriptionIcon('heroicon-m-signal', IconPosition::Before)
                ->color($activeReaders === $totalReaders ? 'success' : 'warning'),

            Stat::make('Aktivní upozornění', $activeAlerts)
                ->description('Vyžaduje řešení')
                ->descriptionIcon('heroicon-m-bell-alert', IconPosition::Before)
                ->color($activeAlerts > 0 ? 'danger' : 'success'),

            Stat::make('Servisní přístupy', ServiceAccess::where('enabled', true)->count())
                ->description('Aktivní servisní účty')
                ->descriptionIcon('heroicon-m-wrench-screwdriver', IconPosition::Before)
                ->color('primary'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AccessLog::query()->latest())
            ->columns([
                TextColumn::make('user.name')
                    ->label('Uživatel')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user'),

                TextColumn::make('room.name')
                    ->label('Místnost')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reader_type')
                    ->label('Typ čtečky')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'room_reader' => 'info',
                        'global_reader' => 'success',
                        default => 'gray',
                    }),

                IconColumn::make('access_granted')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('failure_reason')
                    ->label('Důvod odmítnutí')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP adresa')
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user_agent')
                    ->label('Device')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Čas')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->striped();
    }
}
