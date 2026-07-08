<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Escola;
use App\Models\Faq;
use App\Models\Serie;

/**
 * Gerenciamento de escolas, séries e FAQs.
 * As "unidades" da rede são as próprias escolas (uma unidade por cidade),
 * portanto o cadastro de escolas cumpre os dois papéis.
 */
final class CatalogoController
{
    // ---------------- Escolas ----------------

    public function escolas(): void
    {
        View::show('admin/escolas', [
            'user' => Auth::user(),
            'escolas' => Escola::todas(),
            'csrf' => Csrf::token(),
            'flash' => Session::pull('admin_flash'),
        ], 'admin');
    }

    public function salvarEscola(): void
    {
        $this->exigirCsrf('admin/escolas');
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'nome' => Str::clean((string) ($_POST['nome'] ?? '')),
            'cidade' => Str::clean((string) ($_POST['cidade'] ?? '')),
            'logo' => Str::clean((string) ($_POST['logo'] ?? '')),
            'whatsapp' => Str::digits((string) ($_POST['whatsapp'] ?? '')),
            'telefone' => Str::digits((string) ($_POST['telefone'] ?? '')),
            'endereco' => Str::clean((string) ($_POST['endereco'] ?? '')),
            'ordem' => (int) ($_POST['ordem'] ?? 0),
            'ativo' => ($_POST['ativo'] ?? '') === '1' ? 1 : 0,
        ];
        if (mb_strlen($data['nome']) < 2) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Informe o nome da escola.']);
            redirect('admin/escolas');
        }
        if ($id > 0) {
            Escola::update($id, $data);
            AuditLog::registrar(Auth::id(), 'escola.editar', "Escola #{$id}: {$data['nome']}");
        } else {
            $id = Escola::create($data);
            AuditLog::registrar(Auth::id(), 'escola.criar', "Escola #{$id}: {$data['nome']}");
        }
        Session::set('admin_flash', ['tipo' => 'ok', 'msg' => 'Escola salva.']);
        redirect('admin/escolas');
    }

    // ---------------- Séries ----------------

    public function series(): void
    {
        View::show('admin/series', [
            'user' => Auth::user(),
            'series' => Serie::todas(),
            'csrf' => Csrf::token(),
            'flash' => Session::pull('admin_flash'),
        ], 'admin');
    }

    public function salvarSerie(): void
    {
        $this->exigirCsrf('admin/series');
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'nome' => Str::clean((string) ($_POST['nome'] ?? '')),
            'descricao' => Str::clean((string) ($_POST['descricao'] ?? '')),
            'ordem' => (int) ($_POST['ordem'] ?? 0),
            'ativo' => ($_POST['ativo'] ?? '') === '1' ? 1 : 0,
        ];
        if (mb_strlen($data['nome']) < 2) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Informe o nome da série.']);
            redirect('admin/series');
        }
        if ($id > 0) {
            Serie::update($id, $data);
            AuditLog::registrar(Auth::id(), 'serie.editar', "Série #{$id}: {$data['nome']}");
        } else {
            $id = Serie::create($data);
            AuditLog::registrar(Auth::id(), 'serie.criar', "Série #{$id}: {$data['nome']}");
        }
        Session::set('admin_flash', ['tipo' => 'ok', 'msg' => 'Série salva.']);
        redirect('admin/series');
    }

    // ---------------- FAQs ----------------

    public function faqs(): void
    {
        View::show('admin/faqs', [
            'user' => Auth::user(),
            'faqs' => Faq::todas(),
            'csrf' => Csrf::token(),
            'flash' => Session::pull('admin_flash'),
        ], 'admin');
    }

    public function salvarFaq(): void
    {
        $this->exigirCsrf('admin/faqs');
        $id = (int) ($_POST['id'] ?? 0);
        $pergunta = Str::clean((string) ($_POST['pergunta'] ?? ''));
        $resposta = Str::clean((string) ($_POST['resposta'] ?? ''));
        $ordem = (int) ($_POST['ordem'] ?? 0);
        $ativo = ($_POST['ativo'] ?? '') === '1' ? 1 : 0;
        if (mb_strlen($pergunta) < 5 || mb_strlen($resposta) < 5) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Preencha pergunta e resposta.']);
            redirect('admin/faqs');
        }
        if ($id > 0) {
            Faq::update($id, $pergunta, $resposta, $ordem, $ativo);
            AuditLog::registrar(Auth::id(), 'faq.editar', "FAQ #{$id}");
        } else {
            $id = Faq::create($pergunta, $resposta, $ordem, $ativo);
            AuditLog::registrar(Auth::id(), 'faq.criar', "FAQ #{$id}");
        }
        Session::set('admin_flash', ['tipo' => 'ok', 'msg' => 'Pergunta salva.']);
        redirect('admin/faqs');
    }

    public function excluirFaq(): void
    {
        $this->exigirCsrf('admin/faqs');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0 && Faq::find($id) !== null) {
            Faq::delete($id);
            AuditLog::registrar(Auth::id(), 'faq.excluir', "FAQ #{$id}");
            Session::set('admin_flash', ['tipo' => 'ok', 'msg' => 'Pergunta excluída.']);
        }
        redirect('admin/faqs');
    }

    private function exigirCsrf(string $voltar): void
    {
        if (!Csrf::validateRequest()) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Sessão expirada. Tente novamente.']);
            redirect($voltar);
        }
    }
}
