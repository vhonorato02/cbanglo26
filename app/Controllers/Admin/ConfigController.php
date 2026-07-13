<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;
use App\Models\AuditLog;

final class ConfigController
{
    /** Chaves editáveis e seus rótulos. */
    private const CAMPOS = [
        'campanha_nome' => 'Nome da campanha',
        'campanha_chamada' => 'Chamada principal (hero)',
        'campanha_descricao' => 'Descrição da campanha',
        'data_prova' => 'Data principal da campanha (aaaa-mm-dd)',
        'hora_prova' => 'Horário padrão da prova (hh:mm)',
        'inscricoes_abertas' => 'Inscrições abertas (1 = sim, 0 = não)',
        'inscricoes_inicio' => 'Início das inscrições (aaaa-mm-dd, vazio = sem restrição)',
        'inscricoes_fim' => 'Fim das inscrições (aaaa-mm-dd, vazio = sem restrição)',
        'inscricoes_limite' => 'Limite de inscrições (0 = sem limite)',
        'mensagem_confirmacao' => 'Mensagem de confirmação da inscrição',
        'mensagem_encerrada' => 'Mensagem de inscrições encerradas',
        'contato_whatsapp' => 'WhatsApp geral de contato (somente números)',
        'contato_email' => 'E-mail geral de contato',
        'consent_versao' => 'Versão do termo de consentimento',
        'resultado_info' => 'Informações sobre o resultado (opcional)',
    ];

    public function index(): void
    {
        View::show('admin/configuracoes', [
            'user' => Auth::user(),
            'campos' => self::CAMPOS,
            'valores' => Config::all(),
            'csrf' => Csrf::token(),
            'flash' => Session::pull('admin_flash'),
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!Csrf::validateRequest()) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Sessão expirada. Tente novamente.']);
            redirect('admin/configuracoes');
        }
        $alteradas = [];
        foreach (array_keys(self::CAMPOS) as $chave) {
            if (!array_key_exists($chave, $_POST)) {
                continue;
            }
            $valor = Str::clean((string) $_POST[$chave]);

            if (in_array($chave, ['data_prova', 'inscricoes_inicio', 'inscricoes_fim'], true)
                && $valor !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                Session::set('admin_flash', ['tipo' => 'erro', 'msg' => "Formato de data inválido em \"" . self::CAMPOS[$chave] . '". Use aaaa-mm-dd.']);
                redirect('admin/configuracoes');
            }
            if ($chave === 'hora_prova' && $valor !== '' && !preg_match('/^\d{2}:\d{2}$/', $valor)) {
                Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Horário da prova inválido. Use hh:mm.']);
                redirect('admin/configuracoes');
            }
            if (in_array($chave, ['inscricoes_abertas'], true)) {
                $valor = $valor === '1' ? '1' : '0';
            }
            if ($chave === 'inscricoes_limite') {
                $valor = (string) max(0, (int) $valor);
            }
            if (Config::get($chave) !== $valor) {
                Config::set($chave, $valor);
                $alteradas[] = $chave;
            }
        }
        if ($alteradas !== []) {
            AuditLog::registrar(Auth::id(), 'config.salvar', 'Configurações alteradas: ' . implode(', ', $alteradas));
        }
        Session::set('admin_flash', ['tipo' => 'ok', 'msg' => $alteradas === [] ? 'Nenhuma alteração detectada.' : 'Configurações salvas.']);
        redirect('admin/configuracoes');
    }
}
