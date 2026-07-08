<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Models\AdminUser;
use App\Models\LoginAttempt;

T::test('senha é armazenada com password_hash e verificada', function () {
    $id = AdminUser::create('Admin Teste', 'admin@teste.com', 'senha-super-secreta');
    $user = AdminUser::find($id);
    T::assert($user['senha_hash'] !== 'senha-super-secreta', 'senha em claro!');
    T::assert(password_verify('senha-super-secreta', $user['senha_hash']));
    T::assert(!password_verify('senha-errada', $user['senha_hash']));
});

T::test('login válido autentica e regenera sessão', function () {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    AdminUser::create('Admin Teste', 'admin@teste.com', 'senha-super-secreta');
    $resultado = @Auth::attempt('Admin@Teste.com', 'senha-super-secreta');
    T::assertEquals('ok', $resultado, 'login válido (e-mail com caixa diferente)');
    T::assert(Auth::check(), 'sessão não autenticada');
    T::assert(Auth::id() !== null);
    @Auth::logout();
});

T::test('login inválido é recusado', function () {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    AdminUser::create('Admin Teste', 'admin@teste.com', 'senha-super-secreta');
    T::assertEquals('invalid', @Auth::attempt('admin@teste.com', 'senha-errada'));
    T::assert(!Auth::check());
});

T::test('usuário inativo não entra', function () {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $id = AdminUser::create('Admin Teste', 'admin@teste.com', 'senha-super-secreta', 0);
    T::assertEquals('invalid', @Auth::attempt('admin@teste.com', 'senha-super-secreta'));
});

T::test('bloqueio temporário após 5 tentativas falhas', function () {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    AdminUser::create('Admin Teste', 'admin@teste.com', 'senha-super-secreta');
    for ($i = 0; $i < 5; $i++) {
        T::assertEquals('invalid', @Auth::attempt('admin@teste.com', 'errada'));
    }
    // 6ª tentativa: bloqueado mesmo com a senha certa
    T::assertEquals('locked', @Auth::attempt('admin@teste.com', 'senha-super-secreta'));
});

T::test('limpeza de tentativas antigas', function () {
    LoginAttempt::record('velho@teste.com', 'hash', false);
    \App\Core\Database::pdo()->exec(
        "UPDATE login_tentativas SET criado_em = '2020-01-01 00:00:00'"
    );
    LoginAttempt::limparAntigas();
    $total = \App\Core\Database::pdo()->query('SELECT COUNT(*) FROM login_tentativas')->fetchColumn();
    T::assertEquals(0, (int) $total);
});

T::test('configurações: get/set e cache', function () {
    T::assertEquals('Concurso de Bolsas 2026', Config::get('campanha_nome'));
    Config::set('campanha_nome', 'Novo Nome');
    T::assertEquals('Novo Nome', Config::get('campanha_nome'));
    T::assertEquals('padrão', Config::get('chave_inexistente', 'padrão'));
});

T::test('inscrições fecham por flag, período e limite', function () {
    T::assert(Config::inscricoesAbertas(), 'deveriam começar abertas');

    Config::set('inscricoes_abertas', '0');
    T::assert(!Config::inscricoesAbertas(), 'flag manual');
    Config::set('inscricoes_abertas', '1');

    Config::set('inscricoes_fim', date('Y-m-d', strtotime('-1 day')));
    T::assert(!Config::inscricoesAbertas(), 'período encerrado');
    Config::set('inscricoes_fim', '');

    Config::set('inscricoes_inicio', date('Y-m-d', strtotime('+1 day')));
    T::assert(!Config::inscricoesAbertas(), 'período ainda não começou');
    Config::set('inscricoes_inicio', '');

    Config::set('inscricoes_limite', '1');
    $v = new \App\Validation\InscricaoValidator();
    $v->validate(test_inscricao_valida());
    $data = $v->data();
    $data['ip_hash'] = '';
    \App\Models\Inscricao::criar($data);
    T::assert(!Config::inscricoesAbertas(), 'limite atingido');
});

T::test('e-mail: mailer sem SMTP configurado não quebra a aplicação', function () {
    $mailer = new \App\Core\Mailer([
        'from_address' => '', 'from_name' => '', 'smtp_host' => '',
        'smtp_port' => 587, 'smtp_user' => '', 'smtp_pass' => '', 'encryption' => 'tls',
    ]);
    T::assert(!$mailer->isConfigured());
    T::assert($mailer->send('x@y.com', 'Teste', '<p>oi</p>') === false, 'deveria retornar false sem lançar exceção');
});

T::test('e-mail: falha de SMTP é capturada e registrada sem exceção', function () {
    $mailer = new \App\Core\Mailer([
        'from_address' => 'de@teste.com', 'from_name' => 'Teste',
        'smtp_host' => '127.0.0.1', 'smtp_port' => 1, // porta fechada
        'smtp_user' => '', 'smtp_pass' => '', 'encryption' => 'none',
    ]);
    T::assert($mailer->isConfigured());
    $ok = $mailer->send('x@y.com', 'Teste', '<p>oi</p>');
    T::assert($ok === false, 'falha de conexão deveria retornar false');
});
