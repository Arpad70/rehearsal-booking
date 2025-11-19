<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Payment;
use App\Models\Reservation;

class ImportPayments extends Command
{
    protected $signature = 'payments:import {file : Path to CSV file}';
    protected $description = 'Import payments from a CSV file. Columns: reservation_id,amount,currency,paid_at';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error('File not found: ' . $file);
            return self::FAILURE;
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error('Cannot open file');
            return self::FAILURE;
        }

        $header = fgetcsv($handle);
        $rows = 0;
        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);
            $reservationId = isset($row['reservation_id']) && $row['reservation_id'] !== '' ? (int)$row['reservation_id'] : null;
            $amount = (float)($row['amount'] ?? 0);
            $currency = $row['currency'] ?? 'CZK';
            $paidAt = $row['paid_at'] ?? now();

            // Optional: validate reservation exists
            if ($reservationId && !Reservation::where('id', $reservationId)->exists()) {
                $this->warn("Reservation {$reservationId} not found, skipping row.");
                continue;
            }

            Payment::create([
                'reservation_id' => $reservationId,
                'amount' => $amount,
                'currency' => $currency,
                'paid_at' => $paidAt,
            ]);

            $rows++;
        }

        fclose($handle);
        $this->info("Imported {$rows} payments.");
        return self::SUCCESS;
    }
}
