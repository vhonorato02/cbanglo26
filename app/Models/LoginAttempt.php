<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class LoginAttempt
{
    public static function record(string $email, string $ipHash, bool $sucesso): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO login_tentativas (email, ip_hash, sucesso, criado_em)
             VALUES (:email, :ip, :sucesso, :agora)'
        );
        $stmt->execute([
            ':email' => mb_substr($email, 0, 190),
            ':ip' => $ipHash,
            ':sucesso' => $sucesso ? 1 : 0,
            ':agora' => Database::now(),
        ]);
    }

    /** Bloqueado quando há $max falhas nos últimos $minutos (por e-mail OU por IP). */
    public static function isLocked(string $email, string $ipHash, int $max, int $minutos): bool
    {
        $desde = date('Y-m-d H:i:s', time() - $minutos * 60);
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM login_tentativas
             WHERE sucesso = 0 AND criado_em >= :desde AND (email = :email OR ip_hash = :ip)'
        );
        $stmt->execute([':desde' => $desde, ':email' => $email, ':ip' => $ipHash]);
        return (int) $stmt->fetchColumn() >= $max;
    }

    /** Remove registros com mais de 30 dias (higiene de dados). */
    public static function limparAntigas(): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM login_tentativas WHERE criado_em < :limite');
        $stmt->execute([':limite' => date('Y-m-d H:i:s', time() - 30 * 86400)]);
    }
}
