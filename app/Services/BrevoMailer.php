<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoMailer
{
    public function send(string $email, string $name, string $subject, string $html): void
    {
        $response = Http::withHeaders([
            'api-key' => env('BREVO_API_KEY'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => env('BREVO_SENDER_NAME', '100 Miglia Monviso'),
                'email' => env('BREVO_SENDER_EMAIL', 'info@100migliamonviso.eu'),
            ],
            'to' => [['email' => $email, 'name' => $name]],
            'subject' => $subject,
            'htmlContent' => $html,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Invio email Brevo fallito: '.$response->body());
        }
    }
}
