<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;
use App\Models\Inscricao;

final class ComprovanteController
{
    /**
     * Exibe o comprovante. Dados completos aparecem somente quando:
     *  - a inscrição acabou de ser feita nesta sessão; ou
     *  - o visitante confirmou o e-mail cadastrado na consulta.
     * Caso contrário, mostra somente dados não sensíveis (LGPD).
     */
    public function show(string $protocolo): void
    {
        Session::start();
        $protocolo = strtoupper(Str::clean($protocolo));
        $inscricao = Inscricao::findByProtocolo($protocolo);
        if ($inscricao === null) {
            http_response_code(404);
            View::show('comprovante/nao-encontrado', ['protocolo' => $protocolo, 'pageTitle' => 'Inscrição não encontrada'], 'public');
            return;
        }
        $autorizado = Session::get('comprovante_' . $protocolo) === true;

        View::show('comprovante/show', [
            'inscricao' => $inscricao,
            'autorizado' => $autorizado,
            'config' => Config::all(),
            'csrf' => Csrf::token(),
            'pageTitle' => 'Comprovante de inscrição — ' . Config::get('campanha_nome', 'Concurso de Bolsas'),
        ], 'public');
    }

    /** Formulário de consulta de inscrição (protocolo + e-mail). */
    public function consulta(): void
    {
        Session::start();
        View::show('comprovante/consulta', [
            'csrf' => Csrf::token(),
            'erro' => Session::pull('consulta_erro'),
            'config' => Config::all(),
            'pageTitle' => 'Consultar inscrição — ' . Config::get('campanha_nome', 'Concurso de Bolsas'),
        ], 'public');
    }

    public function consultar(): void
    {
        Session::start();
        if (!Csrf::validateRequest()) {
            Session::set('consulta_erro', 'Sessão expirada. Tente novamente.');
            redirect('consulta');
        }
        $protocolo = strtoupper(Str::clean((string) ($_POST['protocolo'] ?? '')));
        $email = mb_strtolower(Str::clean((string) ($_POST['email'] ?? '')), 'UTF-8');

        $inscricao = $protocolo !== '' ? Inscricao::findByProtocolo($protocolo) : null;
        if ($inscricao === null || !hash_equals(mb_strtolower($inscricao['email']), $email)) {
            // Mensagem única — não revela se o protocolo existe
            Session::set('consulta_erro', 'Não encontramos uma inscrição com este protocolo e e-mail. Confira os dados e tente novamente.');
            redirect('consulta');
        }
        Session::set('comprovante_' . $protocolo, true);
        redirect('comprovante/' . $protocolo);
    }
}
