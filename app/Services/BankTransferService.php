<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Str;

class BankTransferService
{
    public function settings(): array
    {
        $values = Setting::whereIn('key', [
            'bank_transfer_enabled', 'bank_account_holder', 'bank_iban', 'bank_bic', 'bank_name',
            'event_date', 'bank_transfer_cutoff_days', 'bank_transfer_reservation_days',
        ])->pluck('value', 'key');

        return [
            'enabled' => $values->get('bank_transfer_enabled') === '1',
            'account_holder' => trim((string) $values->get('bank_account_holder')),
            'iban' => strtoupper(preg_replace('/\s+/', '', (string) $values->get('bank_iban'))),
            'bic' => strtoupper(trim((string) $values->get('bank_bic'))),
            'bank_name' => trim((string) $values->get('bank_name')),
            'event_date' => CarbonImmutable::parse($values->get('event_date', '2027-05-16 09:00:00'), 'Europe/Rome'),
            'cutoff_days' => (int) $values->get('bank_transfer_cutoff_days', 7),
            'reservation_days' => (int) $values->get('bank_transfer_reservation_days', 5),
        ];
    }

    public function isAvailable(): bool
    {
        $settings = $this->settings();
        $cutoff = $settings['event_date']->subDays($settings['cutoff_days']);

        return $settings['enabled']
            && filled($settings['account_holder'])
            && filled($settings['iban'])
            && now('Europe/Rome')->lt($cutoff);
    }

    public function prepareOrderAttributes(string $contactName, string $contactEmail): array
    {
        $settings = $this->settings();
        $cutoff = $settings['event_date']->subDays($settings['cutoff_days']);
        $dueAt = now('Europe/Rome')->addDays($settings['reservation_days']);
        if ($dueAt->gt($cutoff)) {
            $dueAt = $cutoff;
        }

        $bookingCode = $this->uniqueCode('MAR2027-', 'booking_code');
        $reference = $this->uniqueCode('PHM27-', 'bank_reference');
        $publicToken = Str::random(64);

        return [
            'attributes' => [
                'booking_code' => $bookingCode,
                'public_token_hash' => hash('sha256', $publicToken),
                'payment_contact_name' => trim($contactName),
                'payment_contact_email' => strtolower(trim($contactEmail)),
                'bank_reference' => $reference,
                'payment_due_at' => $dueAt,
                'status' => 'awaiting_transfer',
            ],
            'public_token' => $publicToken,
        ];
    }

    public function qrDataUri(Order $order): string
    {
        $qrCode = new QrCode(
            data: $this->epcPayload($order),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 280,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return (new SvgWriter())->write($qrCode)->getDataUri();
    }

    public function epcPayload(Order $order): string
    {
        $settings = $this->settings();
        return implode("\n", [
            'BCD', '002', '1', 'SCT', $settings['bic'],
            Str::limit($settings['account_holder'], 70, ''),
            $settings['iban'],
            'EUR'.number_format((float) $order->total_amount, 2, '.', ''),
            '', Str::limit($order->bank_reference, 140, ''), '',
        ]);
    }

    public function resolveByPublicToken(string $token): Order
    {
        return Order::with(['tickets.category', 'bankTransactions'])
            ->where('public_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }

    private function uniqueCode(string $prefix, string $column): string
    {
        do {
            $code = $prefix.Str::upper(Str::random(5));
        } while (Order::where($column, $code)->exists());

        return $code;
    }
}
