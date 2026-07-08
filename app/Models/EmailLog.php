<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class EmailLog
{
    public static function registrar(?int $inscricaoId, string $destinatario, string $assunto, bool $enviado, string $erro = ''): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO email_logs (inscricao_id, destinatario, assunto, enviado, erro, criado_em)
             VALUES (:i, :d, :a, :e, :erro, :agora)'
        );
        $stmt->execute([
            ':i' => $inscricaoId,
            ':d' => mb_substr($destinatario, 0, 190),
            ':a' => mb_substr($assunto, 0, 190),
            ':e' => $enviado ? 1 : 0,
            ':erro' => mb_substr($erro, 0, 500),
            ':agora' => Database::now(),
        ]);
    }
}
