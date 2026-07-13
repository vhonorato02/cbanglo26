<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Logger;
use App\Core\Mailer;
use App\Core\Provas;
use App\Core\Session;
use App\Models\DuplicidadeException;
use App\Models\EmailLog;
use App\Models\Escola;
use App\Models\Inscricao;
use App\Models\Serie;
use App\Validation\InscricaoValidator;

final class InscricaoController
{
    /** Janela mínima entre carregar o formulário e enviar (anti-bot). */
    private const MIN_SEGUNDOS_ENVIO = 4;

    /** Máximo de inscrições por IP na janela de tempo. */
    private const MAX_POR_IP = 5;
    private const JANELA_IP_MINUTOS = 60;

    public function store(): void
    {
        Session::start();

        // 1. Inscrições abertas?
        if (!Config::inscricoesAbertas()) {
            $this->falha(403, ['_geral' => Config::get('mensagem_encerrada', 'As inscrições estão encerradas.')]);
        }

        // 2. CSRF
        if (!Csrf::validateRequest()) {
            $this->falha(419, ['_geral' => 'Sessão expirada. Recarregue a página e tente novamente.']);
        }

        // 3. Honeypot — bots preenchem o campo oculto
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            Logger::warning('Honeypot acionado na inscrição.');
            // Resposta genérica para não revelar a defesa
            $this->falha(422, ['_geral' => 'Não foi possível processar a inscrição.']);
        }

        // 4. Controle de velocidade — envio rápido demais é bot
        $formTs = (int) ($_POST['_ts'] ?? 0);
        if ($formTs > 0 && (time() - $formTs) < self::MIN_SEGUNDOS_ENVIO) {
            $this->falha(422, ['_geral' => 'Envio muito rápido. Revise os dados e tente novamente.']);
        }

        // 5. Limite por IP
        $ipHash = client_ip_hash();
        if (Inscricao::contarPorIp($ipHash, self::JANELA_IP_MINUTOS) >= self::MAX_POR_IP) {
            $this->falha(429, ['_geral' => 'Limite de inscrições atingido a partir desta conexão. Tente novamente mais tarde.']);
        }

        // 6. Validação de campos
        $validator = new InscricaoValidator();
        if (!$validator->validate($_POST)) {
            $this->falha(422, $validator->errors());
        }

        $data = $validator->data();
        $data['ip_hash'] = $ipHash;
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $data['consent_versao'] = Config::get('consent_versao', 'v1');

        // 7. Persistência (transação; protocolo único gerado no servidor)
        try {
            $resultado = Inscricao::criar($data);
        } catch (DuplicidadeException $e) {
            $this->falha(409, [
                'aluno_nome' => 'Já existe uma inscrição para este estudante. Se precisar corrigir os dados, fale com a escola escolhida.',
            ]);
        } catch (\Throwable $e) {
            Logger::error('Erro ao salvar inscrição: ' . $e->getMessage());
            $this->falha(500, ['_geral' => 'Não foi possível concluir a inscrição. Tente novamente em instantes.']);
        }

        // 8. E-mail de confirmação — falha não impede a inscrição
        $this->enviarConfirmacao($resultado['id'], $resultado['protocolo'], $data);

        // 9. Autoriza a visualização completa do comprovante nesta sessão
        Session::set('comprovante_' . $resultado['protocolo'], true);

        if (wants_json()) {
            json_response([
                'ok' => true,
                'protocolo' => $resultado['protocolo'],
                'comprovante' => url('comprovante/' . $resultado['protocolo']),
                'mensagem' => Config::get('mensagem_confirmacao'),
            ], 201);
        }
        redirect('comprovante/' . $resultado['protocolo']);
    }

    /** @param array<string, string> $errors */
    private function falha(int $status, array $errors): void
    {
        if (wants_json()) {
            json_response(['ok' => false, 'errors' => $errors], $status);
        }
        // Fallback sem JavaScript: preserva dados e erros na sessão
        $old = $_POST;
        unset($old['_csrf'], $old['website'], $old['_ts']);
        Session::set('form_old', $old);
        Session::set('form_errors', $errors);
        redirect('/?erro=1#inscricao');
    }

    /** @param array<string, mixed> $data */
    private function enviarConfirmacao(int $inscricaoId, string $protocolo, array $data): void
    {
        $mailer = Mailer::fromConfig();
        if (!$mailer->isConfigured()) {
            return;
        }
        $serie = Serie::find((int) $data['serie_id']);
        $escola = Escola::find((int) $data['escola_id']);
        $campanha = Config::get('campanha_nome', 'Concurso de Bolsas');
        $dataProva = (string) ($data['data_prova'] ?? '');
        $horaProva = '09:00';

        $assunto = "{$campanha} — Inscrição recebida ({$protocolo})";
        $html = \App\Core\View::render('emails/confirmacao', [
            'protocolo' => $protocolo,
            'alunoNome' => (string) $data['aluno_nome'],
            'serieNome' => $serie['nome'] ?? '',
            'escolaNome' => $escola['nome'] ?? '',
            'campanha' => $campanha,
            'dataProva' => $dataProva,
            'horaProva' => $horaProva,
            'provaTexto' => $dataProva !== '' ? Provas::rotulo($dataProva, $horaProva) : '',
            'comprovanteUrl' => url('comprovante/' . $protocolo),
        ]);

        $enviado = $mailer->send((string) $data['email'], $assunto, $html);
        try {
            EmailLog::registrar($inscricaoId, (string) $data['email'], $assunto, $enviado, $enviado ? '' : 'Falha no envio SMTP (ver logs)');
        } catch (\Throwable $e) {
            Logger::error('Falha ao registrar email_log: ' . $e->getMessage());
        }
    }
}
