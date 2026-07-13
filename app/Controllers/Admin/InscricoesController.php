<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Provas;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Escola;
use App\Models\Historico;
use App\Models\Inscricao;
use App\Models\Observacao;
use App\Models\Serie;
use App\Models\StatusInscricao;
use App\Validation\InscricaoValidator;

final class InscricoesController
{
    /** @return array<string, mixed> */
    private function filtros(): array
    {
        return [
            'busca' => (string) ($_GET['busca'] ?? ''),
            'escola_id' => (int) ($_GET['escola_id'] ?? 0),
            'serie_id' => (int) ($_GET['serie_id'] ?? 0),
            'status_id' => (int) ($_GET['status_id'] ?? 0),
            'data_prova' => (string) ($_GET['data_prova'] ?? ''),
            'de' => (string) ($_GET['de'] ?? ''),
            'ate' => (string) ($_GET['ate'] ?? ''),
            'ordenar' => (string) ($_GET['ordenar'] ?? ''),
        ];
    }

    public function index(): void
    {
        $filtros = $this->filtros();
        $page = max(1, (int) ($_GET['pagina'] ?? 1));
        $resultado = Inscricao::buscar($filtros, $page, 20);

        View::show('admin/inscricoes/index', [
            'user' => Auth::user(),
            'resultado' => $resultado,
            'filtros' => $filtros,
            'escolas' => Escola::todas(),
            'series' => Serie::todas(),
            'statusLista' => StatusInscricao::todos(),
            'calendarioProvas' => Provas::resumoCampanha(),
            'csrf' => Csrf::token(),
            'flash' => Session::pull('admin_flash'),
        ], 'admin');
    }

    public function show(string $id): void
    {
        $inscricao = Inscricao::find((int) $id);
        if ($inscricao === null) {
            http_response_code(404);
            View::show('errors/404');
            return;
        }
        View::show('admin/inscricoes/show', [
            'user' => Auth::user(),
            'inscricao' => $inscricao,
            'observacoes' => Observacao::porInscricao((int) $id),
            'historico' => Historico::porInscricao((int) $id),
            'escolas' => Escola::todas(),
            'series' => Serie::todas(),
            'statusLista' => StatusInscricao::todos(),
            'provasPorEscola' => Provas::opcoesPorEscolas(Escola::todas()),
            'csrf' => Csrf::token(),
            'flash' => Session::pull('admin_flash'),
            'erros' => Session::pull('admin_erros', []),
        ], 'admin');
    }

    public function update(string $id): void
    {
        $this->exigirCsrf();
        $inscricaoId = (int) $id;
        $inscricao = Inscricao::find($inscricaoId);
        if ($inscricao === null) {
            http_response_code(404);
            View::show('errors/404');
            return;
        }

        // Reaproveita o validador público (sem exigir consentimentos novamente)
        $input = $_POST;
        $input['consent_privacidade'] = '1';
        $input['consent_contato'] = '1';
        $validator = new InscricaoValidator();
        if (!$validator->validate($input)) {
            Session::set('admin_erros', $validator->errors());
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Corrija os campos destacados.']);
            redirect('admin/inscricoes/' . $inscricaoId);
        }
        $data = $validator->data();
        unset($data['consent_privacidade'], $data['consent_contato']);

        $statusId = (int) ($_POST['status_id'] ?? 0);
        if ($statusId > 0 && StatusInscricao::find($statusId) !== null) {
            $data['status_id'] = $statusId;
        }

        // Duplicidade ao editar nome/nascimento
        $novoSlug = Inscricao::alunoSlug((string) $data['aluno_nome'], (string) $data['aluno_nascimento']);
        if ($novoSlug !== $inscricao['aluno_slug']) {
            $existente = \App\Core\Database::pdo()->prepare(
                'SELECT id FROM inscricoes WHERE aluno_slug = :s AND id <> :id'
            );
            $existente->execute([':s' => $novoSlug, ':id' => $inscricaoId]);
            if ($existente->fetchColumn() !== false) {
                Session::set('admin_erros', ['aluno_nome' => 'Já existe outra inscrição para este estudante.']);
                Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Duplicidade detectada.']);
                redirect('admin/inscricoes/' . $inscricaoId);
            }
        }

        $mudancas = Inscricao::atualizar($inscricaoId, $data);
        if ($mudancas !== []) {
            Historico::registrar($inscricaoId, Auth::id(), $mudancas);
            AuditLog::registrar(
                Auth::id(),
                'inscricao.editar',
                "Inscrição {$inscricao['protocolo']}: " . implode(', ', array_keys($mudancas))
            );
        }
        Session::set('admin_flash', ['tipo' => 'ok', 'msg' => $mudancas === [] ? 'Nenhuma alteração detectada.' : 'Inscrição atualizada com sucesso.']);
        redirect('admin/inscricoes/' . $inscricaoId);
    }

    public function observar(string $id): void
    {
        $this->exigirCsrf();
        $inscricaoId = (int) $id;
        $texto = Str::clean((string) ($_POST['texto'] ?? ''));
        if ($texto === '') {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Escreva a observação antes de salvar.']);
            redirect('admin/inscricoes/' . $inscricaoId);
        }
        $inscricao = Inscricao::find($inscricaoId);
        if ($inscricao === null) {
            http_response_code(404);
            View::show('errors/404');
            return;
        }
        Observacao::adicionar($inscricaoId, Auth::id(), $texto);
        AuditLog::registrar(Auth::id(), 'inscricao.observacao', "Observação na inscrição {$inscricao['protocolo']}");
        Session::set('admin_flash', ['tipo' => 'ok', 'msg' => 'Observação registrada.']);
        redirect('admin/inscricoes/' . $inscricaoId);
    }

    public function excluir(string $id): void
    {
        $this->exigirCsrf();
        $inscricaoId = (int) $id;
        $inscricao = Inscricao::find($inscricaoId);
        if ($inscricao === null) {
            http_response_code(404);
            View::show('errors/404');
            return;
        }
        Inscricao::excluir($inscricaoId);
        AuditLog::registrar(
            Auth::id(),
            'inscricao.excluir',
            "Inscrição {$inscricao['protocolo']} excluída (titular: solicitação LGPD ou duplicidade)"
        );
        Session::set('admin_flash', ['tipo' => 'ok', 'msg' => "Inscrição {$inscricao['protocolo']} excluída definitivamente."]);
        redirect('admin/inscricoes');
    }

    /** Exportação CSV com os filtros atuais. */
    public function exportar(): void
    {
        $filtros = $this->filtros();
        $rows = Inscricao::exportar($filtros);
        AuditLog::registrar(Auth::id(), 'inscricao.exportar', 'Exportação CSV (' . count($rows) . ' registros)');

        $filename = 'inscricoes-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'w');
        // BOM UTF-8 para o Excel reconhecer acentos
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Protocolo', 'Estudante', 'Nascimento', 'Série', 'Escola escolhida', 'Escola atual',
            'Data da prova', 'Responsável', 'Parentesco', 'WhatsApp', 'E-mail', 'Cidade', 'Status',
            'Consent. dados', 'Consent. contato', 'Versão do termo', 'Data do aceite', 'Inscrito em',
        ], ';');
        foreach ($rows as $r) {
            $linha = [
                $r['protocolo'],
                $r['aluno_nome'],
                data_br($r['aluno_nascimento']),
                $r['serie_nome'],
                $r['escola_nome'],
                $r['escola_atual'],
                data_br($r['data_prova']),
                $r['responsavel_nome'],
                $r['parentesco'],
                $r['whatsapp'],
                $r['email'],
                $r['cidade'],
                $r['status_nome'],
                $r['consent_privacidade'] ? 'sim' : 'não',
                $r['consent_contato'] ? 'sim' : 'não',
                $r['consent_versao'],
                data_br($r['consent_data'], true),
                data_br($r['criado_em'], true),
            ];
            fputcsv($out, array_map('csv_cell', $linha), ';');
        }
        fclose($out);
        exit;
    }

    private function exigirCsrf(): void
    {
        if (!Csrf::validateRequest()) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Sessão expirada. Tente novamente.']);
            redirect('admin/inscricoes');
        }
    }
}
