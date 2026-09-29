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
            'title' => '100 Miglia Monviso 2026 | Due versanti, una sfida',
            'description' => '100 Miglia Monviso: quattro gare di trail running tra Valle Po, Valle Bronda e Valle Varaita, ai piedi del Monviso.',
            'canonical' => 'https://www.100migliamonviso.eu/',
            'bodyClass' => 'home-page',
        ],
        'contattaci' => [
            'view' => 'pages.contattaci',
            'title' => 'Contatti | 100 Miglia Monviso',
            'description' => 'Contatta lo staff di 100 Miglia Monviso per informazioni su gare, territorio e organizzazione.',
            'canonical' => 'https://www.100migliamonviso.eu/contatti/',
        ],
        'contatti' => [
            'view' => 'pages.contattaci',
            'title' => 'Contatti | 100 Miglia Monviso',
            'description' => 'Contatta lo staff di 100 Miglia Monviso per informazioni su gare, territorio e organizzazione.',
            'canonical' => 'https://www.100migliamonviso.eu/contatti/',
        ],
        'cookie-policy' => [
            'view' => 'pages.cookie-policy',
            'title' => 'Cookie Policy | 100 Miglia Monviso',
            'description' => 'Informativa sull\'uso dei cookie del sito 100 Miglia Monviso.',
            'canonical' => 'https://www.100migliamonviso.eu/cookie-policy/',
        ],
        'partnership' => [
            'view' => 'pages.partnership',
            'title' => 'Partner | 100 Miglia Monviso',
            'description' => 'I partner che sostengono 100 Miglia Monviso e il territorio.',
            'canonical' => 'https://www.100migliamonviso.eu/sponsor/',
        ],
        'privacy-policy' => [
            'view' => 'pages.privacy-policy',
            'title' => 'Privacy Policy | 100 Miglia Monviso',
            'description' => 'Informativa privacy del sito 100 Miglia Monviso.',
            'canonical' => 'https://www.100migliamonviso.eu/privacy-policy/',
        ],
        'regolamento' => [
            'view' => 'pages.regolamento',
            'title' => 'Regolamento | 100 Miglia Monviso',
            'description' => 'Regolamento e indicazioni utili per le gare di 100 Miglia Monviso.',
            'canonical' => 'https://www.100migliamonviso.eu/m160k-it/',
        ],
        'termini-condizioni' => [
            'view' => 'pages.regolamento',
            'title' => 'Regolamento | 100 Miglia Monviso',
            'description' => 'Regolamento e indicazioni utili per le gare di 100 Miglia Monviso.',
            'canonical' => 'https://www.100migliamonviso.eu/m160k-it/',
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
