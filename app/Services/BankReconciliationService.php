<?php

namespace App\Services;

use App\Models\BankImportProfile;
use App\Models\BankTransaction;
use App\Models\BankTransferAudit;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    public function preview(string $path, BankImportProfile $profile): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Impossibile leggere il file CSV.');
        }

        $headers = fgetcsv($handle, 0, $profile->delimiter) ?: [];
        $rows = [];
        while (count($rows) < 10 && ($row = fgetcsv($handle, 0, $profile->delimiter)) !== false) {
            if (! array_filter($row, fn ($value) => trim((string) $value) !== '')) {
                continue;
            }
            $rows[] = $this->combineRow($headers, $row);
        }
        fclose($handle);

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function import(UploadedFile|string $file, BankImportProfile $profile): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Impossibile leggere il file CSV.');
        }
        $headers = fgetcsv($handle, 0, $profile->delimiter);
        if (! is_array($headers) || $headers === []) {
            fclose($handle);
            throw new \InvalidArgumentException('Il CSV non contiene una riga di intestazione valida.');
        }
        $indexes = collect($profile->mapping)->mapWithKeys(function ($header, $field) use ($headers) {
            $index = array_search($header, $headers, true);
            return [$field => $index === false ? null : $index];
        });

        foreach (['date', 'amount', 'description'] as $required) {
            if ($indexes[$required] === null) {
                fclose($handle);
                throw new \InvalidArgumentException("Colonna CSV non trovata: {$profile->mapping[$required]}");
            }
        }

        $stats = ['imported' => 0, 'duplicates' => 0, 'matched' => 0, 'review' => 0, 'unmatched' => 0];
        while (($row = fgetcsv($handle, 0, $profile->delimiter)) !== false) {
            if (! array_filter($row, fn ($value) => trim((string) $value) !== '')) {
                continue;
            }

            $date = CarbonImmutable::createFromFormat($profile->date_format, trim($row[$indexes['date']]))->startOfDay();
            $amount = $this->normalizeAmount($row[$indexes['amount']]);
            $description = trim($row[$indexes['description']]);
            $externalIdIndex = $indexes->get('external_id');
            $currencyIndex = $indexes->get('currency');
            $externalId = $externalIdIndex !== null ? trim((string) ($row[$externalIdIndex] ?? '')) : null;
            $currency = $currencyIndex !== null ? strtoupper(trim((string) ($row[$currencyIndex] ?? ''))) : 'EUR';
            $currency = $currency ?: 'EUR';
            $fingerprint = hash('sha256', implode('|', [$externalId, $date->toDateString(), number_format($amount, 2, '.', ''), $currency, $description]));

            if (BankTransaction::where('fingerprint', $fingerprint)->exists()) {
                $stats['duplicates']++;
                continue;
            }

            $transaction = BankTransaction::create([
                'fingerprint' => $fingerprint, 'external_id' => $externalId ?: null,
                'booked_at' => $date, 'amount' => $amount, 'currency' => $currency,
                'description' => $description, 'raw_data' => $this->combineRow($headers, $row),
            ]);
            $stats['imported']++;
            $result = $this->match($transaction);
            $stats[$result]++;
        }
        fclose($handle);

        return $stats;
    }

    public function match(BankTransaction $transaction): string
    {
        preg_match('/MM26-[A-Z0-9]{5}/i', $transaction->description, $matches);
        if (empty($matches[0])) {
            $transaction->update(['match_status' => 'unmatched']);
            return 'unmatched';
        }

        return DB::transaction(function () use ($transaction, $matches) {
            $order = Order::where('bank_reference', strtoupper($matches[0]))->lockForUpdate()->first();
            if (! $order || $order->payment_method !== 'bank_transfer') {
                $transaction->update(['match_status' => 'unmatched']);
                return 'unmatched';
            }

            $inWindow = $transaction->booked_at->betweenIncluded($order->created_at->startOfDay(), $order->payment_due_at->endOfDay());
            $exact = $transaction->currency === 'EUR' && abs((float) $transaction->amount - (float) $order->total_amount) < 0.005;
            $transaction->update(['matched_order_id' => $order->id]);

            if ($inWindow && $exact) {
                $transaction->update(['match_status' => 'matched']);
                $order->update(['transfer_verified_at' => now(), 'transfer_verification_method' => 'csv_auto']);
                app(OrderPaymentFinalizer::class)->finalize($order);
                BankTransferAudit::create(['order_id' => $order->id, 'action' => 'csv_auto_matched', 'metadata' => ['transaction_id' => $transaction->id]]);
                return 'matched';
            }

            $reason = ! $exact ? 'Importo o valuta non corrispondenti.' : 'Data movimento fuori dalla finestra di pagamento.';
            $transaction->update(['match_status' => 'review']);
            $order->update(['status' => 'transfer_review', 'transfer_review_reason' => $reason]);
            BankTransferAudit::create(['order_id' => $order->id, 'action' => 'csv_review', 'reason' => $reason, 'metadata' => ['transaction_id' => $transaction->id]]);
            return 'review';
        });
    }

    private function normalizeAmount(string $value): float
    {
        $value = preg_replace('/[^0-9,.-]/', '', trim($value));
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }
        return (float) str_replace(',', '.', $value);
    }

    private function combineRow(array $headers, array $row): array
    {
        $row = array_slice(array_pad($row, count($headers), null), 0, count($headers));
        return array_combine($headers, $row) ?: [];
    }
}
