<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Str;
use PDO;

final class Inscricao
{
    /**
     * Cria a inscrição dentro de uma transação, gerando protocolo único.
     *
     * @param array<string, mixed> $data Dados já validados e normalizados.
     * @return array{id: int, protocolo: string}
     * @throws DuplicidadeException se o estudante já estiver inscrito.
     */
    public static function criar(array $data): array
    {
        $slug = self::alunoSlug($data['aluno_nome'], $data['aluno_nascimento']);

        return Database::transaction(function (PDO $pdo) use ($data, $slug): array {
            $check = $pdo->prepare('SELECT protocolo FROM inscricoes WHERE aluno_slug = :slug');
            $check->execute([':slug' => $slug]);
            if ($check->fetchColumn() !== false) {
                throw new DuplicidadeException('Já existe uma inscrição para este estudante.');
            }

            $statusId = StatusInscricao::idPorCodigo('recebida');
            if ($statusId === null) {
                throw new \RuntimeException('Status inicial "recebida" não encontrado.');
            }

            $agora = Database::now();
            $sql = 'INSERT INTO inscricoes
                (protocolo, aluno_slug, aluno_nome, aluno_nascimento, serie_id, escola_id,
                 escola_atual, responsavel_nome, parentesco, whatsapp, email, cidade,
                 consent_privacidade, consent_contato, consent_versao, consent_data,
                 status_id, ip_hash, user_agent, criado_em, atualizado_em)
                VALUES
                (:protocolo, :slug, :aluno_nome, :aluno_nascimento, :serie_id, :escola_id,
                 :escola_atual, :responsavel_nome, :parentesco, :whatsapp, :email, :cidade,
                 :consent_privacidade, :consent_contato, :consent_versao, :consent_data,
                 :status_id, :ip_hash, :user_agent, :criado_em, :atualizado_em)';

            $stmt = $pdo->prepare($sql);

            // Protocolo com nova tentativa em caso de colisão (raríssimo)
            $protocolo = '';
            for ($tentativa = 0; $tentativa < 5; $tentativa++) {
                $protocolo = Str::protocolo();
                $exists = $pdo->prepare('SELECT 1 FROM inscricoes WHERE protocolo = :p');
                $exists->execute([':p' => $protocolo]);
                if ($exists->fetchColumn() === false) {
                    break;
                }
                if ($tentativa === 4) {
                    throw new \RuntimeException('Não foi possível gerar protocolo único.');
                }
            }

            $stmt->execute([
                ':protocolo' => $protocolo,
                ':slug' => $slug,
                ':aluno_nome' => $data['aluno_nome'],
                ':aluno_nascimento' => $data['aluno_nascimento'],
                ':serie_id' => (int) $data['serie_id'],
                ':escola_id' => (int) $data['escola_id'],
                ':escola_atual' => $data['escola_atual'],
                ':responsavel_nome' => $data['responsavel_nome'],
                ':parentesco' => $data['parentesco'],
                ':whatsapp' => $data['whatsapp'],
                ':email' => $data['email'],
                ':cidade' => $data['cidade'],
                ':consent_privacidade' => 1,
                ':consent_contato' => 1,
                ':consent_versao' => $data['consent_versao'] ?? 'v1',
                ':consent_data' => $agora,
                ':status_id' => $statusId,
                ':ip_hash' => $data['ip_hash'] ?? '',
                ':user_agent' => mb_substr((string) ($data['user_agent'] ?? ''), 0, 250),
                ':criado_em' => $agora,
                ':atualizado_em' => $agora,
            ]);

            return ['id' => (int) $pdo->lastInsertId(), 'protocolo' => $protocolo];
        });
    }

    public static function alunoSlug(string $nome, string $nascimento): string
    {
        return Str::slug($nome) . '|' . $nascimento;
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::baseSelect() . ' WHERE i.id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return array<string, mixed>|null */
    public static function findByProtocolo(string $protocolo): ?array
    {
        $stmt = Database::pdo()->prepare(self::baseSelect() . ' WHERE i.protocolo = :p');
        $stmt->execute([':p' => strtoupper(Str::clean($protocolo))]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    private static function baseSelect(): string
    {
        return 'SELECT i.*, s.nome AS serie_nome, e.nome AS escola_nome, e.cidade AS escola_cidade,
                st.nome AS status_nome, st.codigo AS status_codigo, st.cor AS status_cor
                FROM inscricoes i
                JOIN series s ON s.id = i.serie_id
                JOIN escolas e ON e.id = i.escola_id
                JOIN inscricao_status st ON st.id = i.status_id';
    }

    /**
     * Busca paginada para o painel.
     *
     * @param array<string, mixed> $filtros
     * @return array{rows: array<int, array<string, mixed>>, total: int, pages: int, page: int}
     */
    public static function buscar(array $filtros, int $page = 1, int $perPage = 20): array
    {
        [$where, $params] = self::montarFiltros($filtros);

        $pdo = Database::pdo();
        $countStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM inscricoes i ' . $where
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $orderBy = self::ordenacao($filtros['ordenar'] ?? '');

        $sql = self::baseSelect() . ' ' . $where . " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'rows' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => $pages,
            'page' => $page,
        ];
    }

    /**
     * Todas as linhas que casam com os filtros (para exportação CSV).
     *
     * @param array<string, mixed> $filtros
     * @return array<int, array<string, mixed>>
     */
    public static function exportar(array $filtros): array
    {
        [$where, $params] = self::montarFiltros($filtros);
        $stmt = Database::pdo()->prepare(self::baseSelect() . ' ' . $where . ' ORDER BY i.criado_em');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private static function montarFiltros(array $filtros): array
    {
        $conds = [];
        $params = [];

        $busca = Str::clean((string) ($filtros['busca'] ?? ''));
        if ($busca !== '') {
            $like = '%' . $busca . '%';
            $subConds = [
                'i.aluno_nome LIKE :busca',
                'i.responsavel_nome LIKE :busca2',
                'i.email LIKE :busca3',
                'i.protocolo LIKE :busca5',
            ];
            $params += [
                ':busca' => $like, ':busca2' => $like, ':busca3' => $like,
                ':busca5' => '%' . strtoupper($busca) . '%',
            ];
            $digitos = Str::digits($busca);
            if ($digitos !== '') {
                $subConds[] = 'i.whatsapp LIKE :busca4';
                $params[':busca4'] = '%' . $digitos . '%';
            }
            $conds[] = '(' . implode(' OR ', $subConds) . ')';
        }
        foreach (['escola_id' => 'i.escola_id', 'serie_id' => 'i.serie_id', 'status_id' => 'i.status_id'] as $key => $col) {
            $val = (int) ($filtros[$key] ?? 0);
            if ($val > 0) {
                $conds[] = "{$col} = :{$key}";
                $params[":{$key}"] = $val;
            }
        }
        $de = (string) ($filtros['de'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $de)) {
            $conds[] = 'i.criado_em >= :de';
            $params[':de'] = $de . ' 00:00:00';
        }
        $ate = (string) ($filtros['ate'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ate)) {
            $conds[] = 'i.criado_em <= :ate';
            $params[':ate'] = $ate . ' 23:59:59';
        }

        $where = $conds === [] ? '' : 'WHERE ' . implode(' AND ', $conds);
        return [$where, $params];
    }

    private static function ordenacao($key) {
        switch ($key) {
            case 'antigas':
                return 'i.criado_em ASC';
            case 'nome':
                return 'i.aluno_nome ASC';
            case 'nome_desc':
                return 'i.aluno_nome DESC';
            default:
                return 'i.criado_em DESC';
        }
    }

    /**
     * Atualiza campos editáveis. Retorna a lista de alterações [campo => [antes, depois]].
     *
     * @param array<string, mixed> $data
     * @return array<string, array{0: mixed, 1: mixed}>
     */
    public static function atualizar(int $id, array $data): array
    {
        $atual = self::find($id);
        if ($atual === null) {
            throw new \RuntimeException('Inscrição não encontrada.');
        }
        $editaveis = [
            'aluno_nome', 'aluno_nascimento', 'serie_id', 'escola_id', 'escola_atual',
            'responsavel_nome', 'parentesco', 'whatsapp', 'email', 'cidade', 'status_id',
        ];
        $mudancas = [];
        $sets = [];
        $params = [':id' => $id, ':agora' => Database::now()];
        foreach ($editaveis as $campo) {
            if (!array_key_exists($campo, $data)) {
                continue;
            }
            $novo = $data[$campo];
            if ((string) $atual[$campo] === (string) $novo) {
                continue;
            }
            $mudancas[$campo] = [(string) $atual[$campo], (string) $novo];
            $sets[] = "{$campo} = :{$campo}";
            $params[":{$campo}"] = $novo;
        }
        if ($sets === []) {
            return [];
        }
        if (isset($mudancas['aluno_nome']) || isset($mudancas['aluno_nascimento'])) {
            $sets[] = 'aluno_slug = :slug';
            $params[':slug'] = self::alunoSlug(
                (string) ($data['aluno_nome'] ?? $atual['aluno_nome']),
                (string) ($data['aluno_nascimento'] ?? $atual['aluno_nascimento'])
            );
        }
        $sets[] = 'atualizado_em = :agora';
        $sql = 'UPDATE inscricoes SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $mudancas;
    }

    public static function excluir(int $id): void
    {
        Database::transaction(function (PDO $pdo) use ($id): void {
            foreach (['inscricao_observacoes', 'inscricao_historico', 'email_logs'] as $tabela) {
                $stmt = $pdo->prepare("DELETE FROM {$tabela} WHERE inscricao_id = :id");
                $stmt->execute([':id' => $id]);
            }
            $stmt = $pdo->prepare('DELETE FROM inscricoes WHERE id = :id');
            $stmt->execute([':id' => $id]);
        });
    }

    /** Contagem de inscrições por IP nas últimas N horas (controle de velocidade). */
    public static function contarPorIp(string $ipHash, int $minutos): int
    {
        $limite = date('Y-m-d H:i:s', time() - $minutos * 60);
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM inscricoes WHERE ip_hash = :ip AND criado_em >= :limite'
        );
        $stmt->execute([':ip' => $ipHash, ':limite' => $limite]);
        return (int) $stmt->fetchColumn();
    }

    /** @return array<string, int> Indicadores para o dashboard. */
    public static function indicadores(): array
    {
        $pdo = Database::pdo();
        $total = (int) $pdo->query('SELECT COUNT(*) FROM inscricoes')->fetchColumn();
        $hoje = $pdo->prepare('SELECT COUNT(*) FROM inscricoes WHERE criado_em >= :h');
        $hoje->execute([':h' => date('Y-m-d') . ' 00:00:00']);
        $semana = $pdo->prepare('SELECT COUNT(*) FROM inscricoes WHERE criado_em >= :s');
        $semana->execute([':s' => date('Y-m-d', time() - 7 * 86400) . ' 00:00:00']);
        return [
            'total' => $total,
            'hoje' => (int) $hoje->fetchColumn(),
            'semana' => (int) $semana->fetchColumn(),
        ];
    }

    /** @return array<int, array<string, mixed>> Totais por dimensão. */
    public static function totaisPor(string $dim): array
    {
        $joins = [
            'escola' => ['escolas', 'escola_id'],
            'serie' => ['series', 'serie_id'],
            'status' => ['inscricao_status', 'status_id'],
        ];
        if (!isset($joins[$dim])) {
            return [];
        }
        [$tabela, $fk] = $joins[$dim];
        $sql = "SELECT t.nome, COUNT(i.id) AS total
                FROM {$tabela} t LEFT JOIN inscricoes i ON i.{$fk} = t.id
                WHERE t.ativo = 1 GROUP BY t.id, t.nome ORDER BY total DESC";
        return Database::pdo()->query($sql)->fetchAll();
    }
}
