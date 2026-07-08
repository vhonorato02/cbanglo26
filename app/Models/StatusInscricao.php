<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class StatusInscricao
{
    /** @return array<int, array<string, mixed>> */
    public static function todos(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM inscricao_status ORDER BY ordem, nome')
            ->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public static function ativos(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM inscricao_status WHERE ativo = 1 ORDER BY ordem, nome')
            ->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM inscricao_status WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function idPorCodigo(string $codigo): ?int
    {
        $stmt = Database::pdo()->prepare('SELECT id FROM inscricao_status WHERE codigo = :c');
        $stmt->execute([':c' => $codigo]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }
}
