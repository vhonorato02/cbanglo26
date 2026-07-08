<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Config;
use App\Core\View;
use App\Models\Inscricao;

final class DashboardController
{
    public function index(): void
    {
        View::show('admin/dashboard', [
            'user' => Auth::user(),
            'indicadores' => Inscricao::indicadores(),
            'porEscola' => Inscricao::totaisPor('escola'),
            'porSerie' => Inscricao::totaisPor('serie'),
            'porStatus' => Inscricao::totaisPor('status'),
            'inscricoesAbertas' => Config::inscricoesAbertas(),
            'recentes' => Inscricao::buscar([], 1, 8)['rows'],
        ], 'admin');
    }
}
