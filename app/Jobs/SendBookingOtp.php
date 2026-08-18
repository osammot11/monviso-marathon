<?php

namespace App\Jobs;

use App\Services\BrevoMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $email, public string $name, public string $otp) {}

    public function handle(BrevoMailer $mailer): void
    {
        $mailer->send($this->email, $this->name, 'Codice verifica prenotazione', "<h2>Il tuo codice è {$this->otp}</h2><p>Scade tra 10 minuti.</p>");
    }
}
