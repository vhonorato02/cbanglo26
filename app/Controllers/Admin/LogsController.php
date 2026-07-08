<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\View;
use App\Models\AuditLog;

final class LogsController
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['pagina'] ?? 1));
        View::show('admin/logs', [
            'user' => Auth::user(),
            'resultado' => AuditLog::listar($page),
        ], 'admin');
    }
}
