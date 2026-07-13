<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Provas;
use App\Core\Session;
use App\Core\View;
use App\Models\Escola;
use App\Models\Faq;
use App\Models\Serie;

final class HomeController
{
    public function index(): void
    {
        Session::start();
        $config = Config::all();
        $escolas = Escola::ativas();

        $events = [];
        foreach ($escolas as $escola) {
            foreach (Provas::opcoesParaNome($escola['nome']) as $opcao) {
                $events[] = [
                    '@type' => 'Event',
                    'name' => ($config['campanha_nome'] ?? 'Concurso de Bolsas') . ' - ' . $escola['nome'],
                    'description' => $config['campanha_descricao'] ?? '',
                    'startDate' => $opcao['data'] . 'T' . $opcao['hora'],
                    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                    'location' => [
                        '@type' => 'Place',
                        'name' => $escola['nome'],
                        'address' => $escola['cidade'] ?? '',
                    ],
                    'organizer' => [
                        '@type' => 'EducationalOrganization',
                        'name' => $escola['nome'],
                    ],
                    'url' => url('/'),
                ];
            }
        }

        $structuredData = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $events,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        View::show('home/index', [
            'config' => $config,
            'escolas' => $escolas,
            'series' => Serie::ativas(),
            'faqs' => Faq::ativas(),
            'provasPorEscola' => Provas::opcoesPorEscolas($escolas),
            'calendarioProvas' => Provas::resumoCampanha(),
            'inscricoesAbertas' => Config::inscricoesAbertas(),
            'csrf' => Csrf::token(),
            'formTs' => time(),
            'old' => Session::pull('form_old', []),
            'formErrors' => Session::pull('form_errors', []),
            'structuredData' => $structuredData,
        ], 'public');
    }

}
