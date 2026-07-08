<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Csrf;
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

        $eventData = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $config['campanha_nome'] ?? 'Concurso de Bolsas',
            'description' => $config['campanha_descricao'] ?? '',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'organizer' => [
                '@type' => 'EducationalOrganization',
                'name' => 'Anglo Pinda, Colégio Fênix, Colégio Drummond e Anglo Cruzeiro',
            ],
            'url' => url('/'),
        ];
        if (($config['data_prova'] ?? '') !== '') {
            $eventData['startDate'] = ($config['data_prova'] ?? '') . 'T' . ($config['hora_prova'] ?? '09:00');
        }

        $structuredData = json_encode($eventData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        View::show('home/index', [
            'config' => $config,
            'escolas' => Escola::ativas(),
            'series' => Serie::ativas(),
            'faqs' => Faq::ativas(),
            'inscricoesAbertas' => Config::inscricoesAbertas(),
            'csrf' => Csrf::token(),
            'formTs' => time(),
            'old' => Session::pull('form_old', []),
            'formErrors' => Session::pull('form_errors', []),
            'structuredData' => $structuredData,
        ], 'public');
    }

}
