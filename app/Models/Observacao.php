<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Observacao
{
    public static function adicionar(int $inscricaoId, ?int $adminId, string $texto): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO inscricao_observacoes (inscricao_id, admin_id, texto, criado_em)
             VALUES (:i, :a, :t, :agora)'
        );
        $stmt->execute([
            ':i' => $inscricaoId,
            ':a' => $adminId,
            ':t' => mb_substr($texto, 0, 2000),
            ':agora' => Database::now(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function porInscricao(int $inscricaoId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT o.*, u.nome AS admin_nome FROM inscricao_observacoes o
             LEFT JOIN admin_usuarios u ON u.id = o.admin_id
             WHERE o.inscricao_id = :i ORDER BY o.criado_em DESC'
        );
        $stmt->execute([':i' => $inscricaoId]);
        return $stmt->fetchAll();
    }
}
