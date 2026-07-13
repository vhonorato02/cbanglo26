<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\DuplicidadeException;
use App\Models\Historico;
use App\Models\Inscricao;
use App\Validation\InscricaoValidator;

function criar_inscricao_teste(array $override = []): array
{
    $v = new InscricaoValidator();
    if (!$v->validate(test_inscricao_valida($override))) {
        throw new RuntimeException('fixture inválida: ' . json_encode($v->errors()));
    }
    $data = $v->data();
    $data['ip_hash'] = client_ip_hash();
    $data['consent_versao'] = 'v1-teste';
    return Inscricao::criar($data);
}

T::test('inscrição válida é salva com protocolo único', function () {
    $r = criar_inscricao_teste();
    T::assert($r['id'] > 0);
    T::assert((bool) preg_match('/^CB26-/', $r['protocolo']));

    $inscricao = Inscricao::findByProtocolo($r['protocolo']);
    T::assert($inscricao !== null, 'não localizada pelo protocolo');
    T::assertEquals('Maria Clara de Souza', $inscricao['aluno_nome']);
    T::assertEquals('recebida', $inscricao['status_codigo']);
    T::assertEquals('2026-09-26', $inscricao['data_prova']);
    T::assertEquals(1, (int) $inscricao['consent_privacidade']);
    T::assert($inscricao['consent_data'] !== '', 'consentimento sem data');
});

T::test('duplicidade (mesmo nome + nascimento) é bloqueada', function () {
    criar_inscricao_teste();
    T::assertThrows(DuplicidadeException::class, function () {
        criar_inscricao_teste(['email' => 'outro@example.com']);
    });
});

T::test('duplicidade ignora acentos e caixa no nome', function () {
    criar_inscricao_teste();
    T::assertThrows(DuplicidadeException::class, function () {
        criar_inscricao_teste(['aluno_nome' => 'MARIA CLARA DE SOUZA']);
    });
});

T::test('estudantes diferentes com mesmo responsável são aceitos', function () {
    criar_inscricao_teste();
    $r2 = criar_inscricao_teste(['aluno_nome' => 'Pedro Henrique de Souza', 'aluno_nascimento' => '02/07/2010', 'serie_id' => '2']);
    T::assert($r2['id'] > 0);
});

T::test('contagem por IP funciona (controle de velocidade)', function () {
    $ip = client_ip_hash();
    T::assertEquals(0, Inscricao::contarPorIp($ip, 60));
    criar_inscricao_teste();
    T::assertEquals(1, Inscricao::contarPorIp($ip, 60));
});

T::test('busca por nome, protocolo e filtros', function () {
    $r = criar_inscricao_teste();
    criar_inscricao_teste([
        'aluno_nome' => 'Pedro Henrique Lima',
        'aluno_nascimento' => '02/07/2010',
        'serie_id' => '2',
        'escola_id' => '2',
        'data_prova' => '2026-10-17',
    ]);

    $porNome = Inscricao::buscar(['busca' => 'maria clara']);
    T::assertEquals(1, $porNome['total'], 'busca por nome');

    $porProtocolo = Inscricao::buscar(['busca' => $r['protocolo']]);
    T::assertEquals(1, $porProtocolo['total'], 'busca por protocolo');

    $porEscola = Inscricao::buscar(['escola_id' => 2]);
    T::assertEquals(1, $porEscola['total'], 'filtro por escola');

    $porSerie = Inscricao::buscar(['serie_id' => 1]);
    T::assertEquals(1, $porSerie['total'], 'filtro por série');

    $porData = Inscricao::buscar(['de' => date('Y-m-d'), 'ate' => date('Y-m-d')]);
    T::assertEquals(2, $porData['total'], 'filtro por data');

    $semResultado = Inscricao::buscar(['busca' => 'inexistente xyz']);
    T::assertEquals(0, $semResultado['total']);
});

T::test('paginação divide os resultados', function () {
    for ($i = 1; $i <= 25; $i++) {
        $letra = chr(64 + $i); // A..Y
        criar_inscricao_teste([
            'aluno_nome' => "Estudante {$letra} da Silva",
            'aluno_nascimento' => sprintf('%02d/05/2012', ($i % 27) + 1),
        ]);
    }
    $p1 = Inscricao::buscar([], 1, 10);
    T::assertEquals(25, $p1['total']);
    T::assertEquals(3, $p1['pages']);
    T::assertEquals(10, count($p1['rows']));
    $p3 = Inscricao::buscar([], 3, 10);
    T::assertEquals(5, count($p3['rows']));
    // Página além do limite volta para a última
    $p9 = Inscricao::buscar([], 9, 10);
    T::assertEquals(3, $p9['page']);
});

T::test('edição registra histórico de alterações', function () {
    $r = criar_inscricao_teste();
    $mudancas = Inscricao::atualizar($r['id'], ['cidade' => 'Taubaté', 'status_id' => 3]);
    T::assert(isset($mudancas['cidade']), 'mudança de cidade detectada');
    T::assert(isset($mudancas['status_id']), 'mudança de status detectada');

    Historico::registrar($r['id'], null, $mudancas);
    $hist = Historico::porInscricao($r['id']);
    T::assertEquals(2, count($hist));

    $atual = Inscricao::find($r['id']);
    T::assertEquals('Taubaté', $atual['cidade']);
    T::assertEquals('confirmada', $atual['status_codigo']);
});

T::test('exclusão remove inscrição e dependências (LGPD)', function () {
    $r = criar_inscricao_teste();
    \App\Models\Observacao::adicionar($r['id'], null, 'Observação de teste');
    Inscricao::excluir($r['id']);
    T::assert(Inscricao::find($r['id']) === null, 'inscrição ainda existe');
    $obs = Database::pdo()->query('SELECT COUNT(*) FROM inscricao_observacoes')->fetchColumn();
    T::assertEquals(0, (int) $obs, 'observações órfãs');
});

T::test('exportação retorna todas as linhas filtradas', function () {
    criar_inscricao_teste();
    criar_inscricao_teste(['aluno_nome' => 'Pedro Henrique Lima', 'aluno_nascimento' => '02/07/2010']);
    $rows = Inscricao::exportar([]);
    T::assertEquals(2, count($rows));
    T::assert(isset($rows[0]['protocolo'], $rows[0]['serie_nome'], $rows[0]['escola_nome'], $rows[0]['status_nome']));
});

T::test('falha de banco não corrompe: transação faz rollback', function () {
    $antes = (int) Database::pdo()->query('SELECT COUNT(*) FROM inscricoes')->fetchColumn();
    try {
        Database::transaction(function ($pdo) {
            $pdo->exec("INSERT INTO configuracoes (chave, valor) VALUES ('tx_teste', '1')");
            throw new RuntimeException('falha simulada');
        });
    } catch (RuntimeException $e) {
        // esperado
    }
    $cfg = Database::pdo()->query("SELECT COUNT(*) FROM configuracoes WHERE chave = 'tx_teste'")->fetchColumn();
    T::assertEquals(0, (int) $cfg, 'rollback não desfez o insert');
    $depois = (int) Database::pdo()->query('SELECT COUNT(*) FROM inscricoes')->fetchColumn();
    T::assertEquals($antes, $depois);
});
