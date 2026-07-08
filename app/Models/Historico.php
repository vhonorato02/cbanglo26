<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Histórico de alterações das inscrições.
 */
final class Historico
{
    /** @param array<string, array{0: mixed, 1: mixed}> $mudancas */
    public static function registrar(int $inscricaoId, ?int $adminId, array $mudancas): void
    {
        if ($mudancas === []) {
            return;
        }
        $stmt = Database::pdo()->prepare(
            'INSERT INTO inscricao_historico (inscricao_id, admin_id, campo, valor_anterior, valor_novo, criado_em)
             VALUES (:i, :a, :campo, :antes, :depois, :agora)'
        );
        foreach ($mudancas as $campo => [$antes, $depois]) {
            $stmt->execute([
                ':i' => $inscricaoId,
                ':a' => $adminId,
                ':campo' => $campo,
                ':antes' => mb_substr((string) $antes, 0, 500),
                ':depois' => mb_substr((string) $depois, 0, 500),
                ':agora' => Database::now(),
            ]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    public static function porInscricao(int $inscricaoId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT h.*, u.nome AS admin_nome FROM inscricao_historico h
             LEFT JOIN admin_usuarios u ON u.id = h.admin_id
             WHERE h.inscricao_id = :i ORDER BY h.criado_em DESC'
        );
        $stmt->execute([':i' => $inscricaoId]);
        return $stmt->fetchAll();
    }
}
