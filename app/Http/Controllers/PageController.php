<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Throwable;

class PageController extends Controller
{
    private const PAGES = [
        '' => [
            'view' => 'pages.home',
            'title' => 'Pescara Half Marathon 2027 | 21KM, 10KM e Passeggiata',
            'description' => 'Pescara Half Marathon 2027: corri il 16 maggio sul lungomare di Pescara. Scegli tra 21KM competitiva, 10KM non competitiva e passeggiata 5KM.',
            'canonical' => 'https://www.pescarahalfmarathon.it/',
            'bodyClass' => 'home-page',
        ],
        'contattaci' => [
            'view' => 'pages.contattaci',
            'title' => 'Contatti | Pescara Half Marathon 2027',
            'description' => 'Contatta lo staff di Pescara Half Marathon per informazioni su iscrizioni, gare e organizzazione.',
            'canonical' => 'https://www.pescarahalfmarathon.it/contattaci',
        ],
        'contatti' => [
            'view' => 'pages.contattaci',
            'title' => 'Contatti | Pescara Half Marathon 2027',
            'description' => 'Contatta lo staff di Pescara Half Marathon per informazioni su iscrizioni, gare e organizzazione.',
            'canonical' => 'https://www.pescarahalfmarathon.it/contattaci',
        ],
        'cookie-policy' => [
            'view' => 'pages.cookie-policy',
            'title' => 'Cookie Policy | Pescara Half Marathon',
            'description' => 'Informativa sull\'uso dei cookie del sito Pescara Half Marathon.',
            'canonical' => 'https://www.pescarahalfmarathon.it/cookie-policy',
        ],
        'partnership' => [
            'view' => 'pages.partnership',
            'title' => 'Partner | Pescara Half Marathon 2027',
            'description' => 'Sponsor e partner di Pescara Half Marathon 2027.',
            'canonical' => 'https://www.pescarahalfmarathon.it/partnership',
        ],
        'privacy-policy' => [
            'view' => 'pages.privacy-policy',
            'title' => 'Privacy Policy | Pescara Half Marathon',
            'description' => 'Informativa privacy per iscrizioni e contatti di Pescara Half Marathon.',
            'canonical' => 'https://www.pescarahalfmarathon.it/privacy-policy',
        ],
        'regolamento' => [
            'view' => 'pages.regolamento',
            'title' => 'Regolamento | Pescara Half Marathon 2027',
            'description' => 'Regolamento di Pescara Half Marathon 2027: partecipazione, distanze, certificazioni e indicazioni utili.',
            'canonical' => 'https://www.pescarahalfmarathon.it/regolamento',
        ],
        'termini-condizioni' => [
            'view' => 'pages.regolamento',
            'title' => 'Regolamento | Pescara Half Marathon 2027',
            'description' => 'Regolamento di Pescara Half Marathon 2027: partecipazione, distanze, certificazioni e indicazioni utili.',
            'canonical' => 'https://www.pescarahalfmarathon.it/regolamento',
        ],
    ];

    private const HOME_ALIASES = [
        'come-arrivare',
        'gallery',
        'orario',
        'percorsi',
        'ricettivita',
        'styleguide',
    ];

    public function show(?string $slug = ''): Response
    {
        $slug = trim((string) $slug, '/');
        $slug = Str::replaceEnd('.php', '', $slug);

        if (in_array($slug, self::HOME_ALIASES, true)) {
            $slug = '';
        }

        abort_unless(array_key_exists($slug, self::PAGES), 404);

        $page = self::PAGES[$slug];
        $data = [
            'title' => $page['title'],
            'description' => $page['description'],
            'canonical' => $page['canonical'],
            'bodyClass' => $page['bodyClass'] ?? '',
        ];

        if ($page['view'] === 'pages.home') {
            try {
                $data['totalIscritti'] = Ticket::whereNotNull('unique_ticket_code')->count();
            } catch (Throwable $e) {
                $data['totalIscritti'] = 0;
            }
        }

        return response()->view($page['view'], $data);
    }
}
