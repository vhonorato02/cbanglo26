<?php

declare(strict_types=1);

/**
 * Teste de fumaça HTTP contra o servidor embutido (php -S).
 * Pré-requisito: servidor rodando com .env apontando para o SQLite semeado.
 * Uso: php tests/http_smoke.php http://127.0.0.1:8085
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8085', '/');
$cookieJar = [];
$falhas = 0;
$passou = 0;

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

function req(string $method, string $url, array $data = [], array $headers = []): array
{
    global $cookieJar;
    $h = $headers;
    if ($cookieJar !== []) {
        $pairs = [];
        foreach ($cookieJar as $k => $v) {
            $pairs[] = "{$k}={$v}";
        }
        $h[] = 'Cookie: ' . implode('; ', $pairs);
    }
    $body = http_build_query($data);
    if ($method === 'POST') {
        $h[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    $ctx = stream_context_create(['http' => [
        'method' => $method,
        'header' => implode("\r\n", $h),
        'content' => $method === 'POST' ? $body : null,
        'ignore_errors' => true,
        'follow_location' => 0,
        'timeout' => 15,
    ]]);
    $conteudo = (string) @file_get_contents($url, false, $ctx);
    $status = 0;
    $respHeaders = [];
    foreach ($http_response_header ?? [] as $linha) {
        if (preg_match('#^HTTP/\S+ (\d{3})#', $linha, $m)) {
            $status = (int) $m[1];
        } elseif (preg_match('/^Set-Cookie:\s*([^=]+)=([^;]+)/i', $linha, $m)) {
            $cookieJar[trim($m[1])] = trim($m[2]);
        } elseif (str_contains($linha, ':')) {
            [$nome, $valor] = explode(':', $linha, 2);
            $respHeaders[strtolower(trim($nome))] = trim($valor);
        }
    }
    return ['status' => $status, 'body' => $conteudo, 'headers' => $respHeaders];
}

function check(string $nome, bool $cond, string $extra = ''): void
{
    global $falhas, $passou;
    if ($cond) {
        $passou++;
        echo "  PASS {$nome}\n";
    } else {
        $falhas++;
        echo "  FAIL {$nome}" . ($extra !== '' ? " — {$extra}" : '') . "\n";
    }
}

function extrairCsrf(string $html): string
{
    return preg_match('/name="_csrf" value="([^"]+)"/', $html, $m) ? $m[1] : '';
}

echo "\nTeste de fumaça HTTP — {$base}\n=================================\n";

// ---------- Landing page ----------
$r = req('GET', "{$base}/");
check('landing responde 200', $r['status'] === 200, "status {$r['status']}");
check('landing contém o nome da campanha', str_contains($r['body'], 'Concurso'));
check('landing contém o formulário', str_contains($r['body'], 'form-inscricao'));
check('landing contém as 4 escolas', str_contains($r['body'], 'Anglo Pinda')
    && str_contains($r['body'], 'Fênix') && str_contains($r['body'], 'Drummond')
    && str_contains($r['body'], 'Anglo Cruzeiro'));
check('landing contém calendário por unidade', str_contains($r['body'], '26 de setembro')
    && str_contains($r['body'], '17 de outubro'));
check('cabeçalho CSP presente', isset($r['headers']['content-security-policy']));
check('cabeçalho nosniff presente', ($r['headers']['x-content-type-options'] ?? '') === 'nosniff');

$csrf = extrairCsrf($r['body']);
check('token CSRF presente no formulário', $csrf !== '');

$inscricao = [
    '_csrf' => $csrf,
    '_ts' => (string) (time() - 30),
    'website' => '',
    'aluno_nome' => 'Henrique Alves Moreira',
    'aluno_nascimento' => '15/04/2014',
    'serie_id' => '1',
    'escola_id' => '2',
    'data_prova' => '2026-10-17',
    'escola_atual' => 'EMEF do Centro',
    'responsavel_nome' => 'Carla Alves Moreira',
    'parentesco' => 'Mãe',
    'whatsapp' => '(12) 98811-2233',
    'email' => 'carla.moreira@example.com',
    'cidade' => 'Guaratinguetá',
    'consent_privacidade' => '1',
    'consent_contato' => '1',
];

// ---------- CSRF ----------
$semCsrf = $inscricao;
$semCsrf['_csrf'] = 'token-invalido';
$r = req('POST', "{$base}/inscricao", $semCsrf, ['Accept: application/json']);
check('POST sem CSRF válido é rejeitado (419)', $r['status'] === 419, "status {$r['status']}");

// ---------- Honeypot ----------
$comHoneypot = $inscricao;
$comHoneypot['website'] = 'http://spam.example';
$r = req('POST', "{$base}/inscricao", $comHoneypot, ['Accept: application/json']);
check('honeypot preenchido é rejeitado (422)', $r['status'] === 422, "status {$r['status']}");

// ---------- Envio rápido demais ----------
$rapido = $inscricao;
$rapido['_ts'] = (string) time();
$r = req('POST', "{$base}/inscricao", $rapido, ['Accept: application/json']);
check('envio veloz demais é rejeitado (422)', $r['status'] === 422, "status {$r['status']}");

// ---------- Campos vazios ----------
$r = req('POST', "{$base}/inscricao", ['_csrf' => $csrf, '_ts' => (string) (time() - 30)], ['Accept: application/json']);
$json = json_decode($r['body'], true);
check('campos vazios retornam 422 com erros por campo', $r['status'] === 422 && isset($json['errors']['aluno_nome']));

// ---------- XSS ----------
$xss = $inscricao;
$xss['aluno_nome'] = '<script>alert(1)</script> Nome';
$r = req('POST', "{$base}/inscricao", $xss, ['Accept: application/json']);
check('payload XSS no nome é rejeitado', $r['status'] === 422);

// ---------- Inscrição válida ----------
$r = req('POST', "{$base}/inscricao", $inscricao, ['Accept: application/json']);
$json = json_decode($r['body'], true);
check('inscrição válida retorna 201 com protocolo', $r['status'] === 201 && !empty($json['protocolo']),
    "status {$r['status']} corpo: " . substr($r['body'], 0, 200));
$protocolo = $json['protocolo'] ?? '';

// ---------- Duplicidade ----------
$r = req('POST', "{$base}/inscricao", $inscricao, ['Accept: application/json']);
check('duplicidade retorna 409', $r['status'] === 409, "status {$r['status']}");

// ---------- Comprovante ----------
$r = req('GET', "{$base}/comprovante/{$protocolo}");
check('comprovante responde 200 com o protocolo', $r['status'] === 200 && str_contains($r['body'], $protocolo));
check('comprovante da sessão mostra dados completos', str_contains($r['body'], 'Henrique Alves Moreira'));
check('comprovante mostra a data escolhida', str_contains($r['body'], '17 de outubro'));

$r = req('GET', "{$base}/comprovante/CB26-ZZZZ-ZZZZ");
check('protocolo inexistente responde 404', $r['status'] === 404, "status {$r['status']}");

// ---------- Consulta ----------
$r = req('GET', "{$base}/consulta");
check('página de consulta responde 200', $r['status'] === 200);
$csrfConsulta = extrairCsrf($r['body']);
$r = req('POST', "{$base}/consulta", ['_csrf' => $csrfConsulta, 'protocolo' => $protocolo, 'email' => 'errado@example.com']);
check('consulta com e-mail errado não autoriza', $r['status'] === 302
    && str_contains($r['headers']['location'] ?? '', 'consulta'));
$r = req('POST', "{$base}/consulta", ['_csrf' => $csrfConsulta, 'protocolo' => $protocolo, 'email' => 'carla.moreira@example.com']);
check('consulta correta redireciona ao comprovante', str_contains($r['headers']['location'] ?? '', 'comprovante'));

// ---------- Admin sem autenticação ----------
$cookieJar = [];
$r = req('GET', "{$base}/admin");
check('admin sem login redireciona para /admin/login', $r['status'] === 302
    && str_contains($r['headers']['location'] ?? '', 'admin/login'));
$r = req('GET', "{$base}/admin/inscricoes/exportar");
check('exportação sem login é bloqueada', $r['status'] === 302);

// ---------- Login ----------
$r = req('GET', "{$base}/admin/login");
check('tela de login responde 200', $r['status'] === 200);
$csrfLogin = extrairCsrf($r['body']);

$r = req('POST', "{$base}/admin/login", ['_csrf' => $csrfLogin, 'email' => 'admin', 'senha' => 'senha-errada']);
check('login inválido volta para o login', str_contains($r['headers']['location'] ?? '', 'admin/login'));

$r = req('POST', "{$base}/admin/login", ['_csrf' => $csrfLogin, 'email' => 'admin', 'senha' => 'cbanglo26##']);
check('login válido redireciona ao painel', ($r['headers']['location'] ?? '') !== ''
    && !str_contains($r['headers']['location'] ?? '', 'login'), 'location: ' . ($r['headers']['location'] ?? 'nenhum'));

$r = req('GET', "{$base}/admin");
check('dashboard responde 200 autenticado', $r['status'] === 200 && str_contains($r['body'], 'Visão geral'));

$r = req('GET', "{$base}/admin/inscricoes?busca=Henrique");
check('busca no painel encontra a inscrição', $r['status'] === 200 && str_contains($r['body'], $protocolo));

$r = req('GET', "{$base}/admin/inscricoes/exportar");
check('exportação CSV autenticada responde CSV', $r['status'] === 200
    && str_contains($r['headers']['content-type'] ?? '', 'csv')
    && str_contains($r['body'], 'Henrique Alves Moreira'));

// ---------- Bloqueio de tentativas ----------
$cookieJar = [];
$r = req('GET', "{$base}/admin/login");
$csrfLogin = extrairCsrf($r['body']);
for ($i = 0; $i < 5; $i++) {
    req('POST', "{$base}/admin/login", ['_csrf' => $csrfLogin, 'email' => 'bloqueio@local.test', 'senha' => 'x']);
}
$r = req('POST', "{$base}/admin/login", ['_csrf' => $csrfLogin, 'email' => 'bloqueio@local.test', 'senha' => 'x']);
$r2 = req('GET', "{$base}/admin/login");
check('após 5 falhas a mensagem de bloqueio aparece', str_contains($r2['body'], 'bloqueado'));

// ---------- 404 ----------
$r = req('GET', "{$base}/pagina-que-nao-existe");
check('rota inexistente responde 404', $r['status'] === 404, "status {$r['status']}");

echo "=================================\n";
echo "Passaram: {$passou} | Falharam: {$falhas}\n\n";
exit($falhas > 0 ? 1 : 0);
