<?php

declare(strict_types=1);

use App\Validation\InscricaoValidator;

T::test('inscrição válida passa na validação', function () {
    $v = new InscricaoValidator();
    T::assert($v->validate(test_inscricao_valida()), 'deveria validar: ' . json_encode($v->errors()));
    $data = $v->data();
    T::assertEquals('2014-03-10', $data['aluno_nascimento'], 'data normalizada');
    T::assertEquals('12988776655', $data['whatsapp'], 'whatsapp somente dígitos');
    T::assertEquals('ana.souza@example.com', $data['email']);
});

T::test('todos os campos vazios são rejeitados com mensagens', function () {
    $v = new InscricaoValidator();
    T::assert(!$v->validate([]), 'não deveria validar vazio');
    $erros = $v->errors();
    foreach (['aluno_nome', 'aluno_nascimento', 'serie_id', 'escola_id', 'escola_atual',
              'responsavel_nome', 'parentesco', 'whatsapp', 'email', 'cidade',
              'consent_privacidade', 'consent_contato'] as $campo) {
        T::assert(isset($erros[$campo]), "faltou erro para {$campo}");
    }
});

T::test('e-mail inválido é rejeitado', function () {
    $v = new InscricaoValidator();
    T::assert(!$v->validate(test_inscricao_valida(['email' => 'nao-e-email'])));
    T::assert(isset($v->errors()['email']));
});

T::test('data inexistente (31/02) é rejeitada', function () {
    $v = new InscricaoValidator();
    T::assert(!$v->validate(test_inscricao_valida(['aluno_nascimento' => '31/02/2014'])));
    T::assert(isset($v->errors()['aluno_nascimento']));
});

T::test('data futura é rejeitada', function () {
    $v = new InscricaoValidator();
    $futuro = date('d/m/Y', strtotime('+1 year'));
    T::assert(!$v->validate(test_inscricao_valida(['aluno_nascimento' => $futuro])));
});

T::test('data no formato ISO (input date) também é aceita', function () {
    $v = new InscricaoValidator();
    T::assert($v->validate(test_inscricao_valida(['aluno_nascimento' => '2014-03-10'])));
    T::assertEquals('2014-03-10', $v->data()['aluno_nascimento']);
});

T::test('telefone fixo com 10 dígitos é aceito; celular sem 9 é rejeitado', function () {
    $v = new InscricaoValidator();
    T::assert($v->validate(test_inscricao_valida(['whatsapp' => '(12) 3642-1234'])), 'fixo válido');
    $v2 = new InscricaoValidator();
    T::assert(!$v2->validate(test_inscricao_valida(['whatsapp' => '(12) 88877-6655'])), 'celular sem 9');
});

T::test('nome com tags/script (XSS) é rejeitado pela regra de letras', function () {
    $v = new InscricaoValidator();
    T::assert(!$v->validate(test_inscricao_valida(['aluno_nome' => '<script>alert(1)</script>'])));
    T::assert(isset($v->errors()['aluno_nome']));
});

T::test('payload de SQL injection não passa pelo validador de nome', function () {
    $v = new InscricaoValidator();
    T::assert(!$v->validate(test_inscricao_valida(['aluno_nome' => "Robert'); DROP TABLE inscricoes;--"])));
});

T::test('série e escola inexistentes são rejeitadas', function () {
    $v = new InscricaoValidator();
    T::assert(!$v->validate(test_inscricao_valida(['serie_id' => '999'])));
    T::assert(isset($v->errors()['serie_id']));
    $v2 = new InscricaoValidator();
    T::assert(!$v2->validate(test_inscricao_valida(['escola_id' => '999'])));
    T::assert(isset($v2->errors()['escola_id']));
});

T::test('consentimentos desmarcados bloqueiam a inscrição', function () {
    $v = new InscricaoValidator();
    $dados = test_inscricao_valida();
    unset($dados['consent_privacidade']);
    T::assert(!$v->validate($dados));
    T::assert(isset($v->errors()['consent_privacidade']));
});

T::test('nome sem sobrenome é rejeitado', function () {
    $v = new InscricaoValidator();
    T::assert(!$v->validate(test_inscricao_valida(['aluno_nome' => 'Maria'])));
});
