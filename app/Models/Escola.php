<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Escola
{
    /** @return array<int, array<string, mixed>> */
    public static function ativas(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM escolas WHERE ativo = 1 ORDER BY ordem, nome')
            ->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public static function todas(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM escolas ORDER BY ordem, nome')
            ->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM escolas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function existsAtiva(int $id): bool
    {
        $stmt = Database::pdo()->prepare('SELECT 1 FROM escolas WHERE id = :id AND ativo = 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() !== false;
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO escolas (nome, cidade, logo, whatsapp, telefone, endereco, ordem, ativo, criado_em, atualizado_em)
             VALUES (:nome, :cidade, :logo, :whatsapp, :telefone, :endereco, :ordem, :ativo, :agora, :agora2)'
        );
        $agora = Database::now();
        $stmt->execute([
            ':nome' => $data['nome'],
            ':cidade' => $data['cidade'] ?? '',
            ':logo' => $data['logo'] ?? '',
            ':whatsapp' => $data['whatsapp'] ?? '',
            ':telefone' => $data['telefone'] ?? '',
            ':endereco' => $data['endereco'] ?? '',
            ':ordem' => (int) ($data['ordem'] ?? 0),
            ':ativo' => (int) ($data['ativo'] ?? 1),
            ':agora' => $agora,
            ':agora2' => $agora,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public static function update(int $id, array $data): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE escolas SET nome = :nome, cidade = :cidade, logo = :logo, whatsapp = :whatsapp,
             telefone = :telefone, endereco = :endereco, ordem = :ordem, ativo = :ativo, atualizado_em = :agora
             WHERE id = :id'
        );
        $stmt->execute([
            ':nome' => $data['nome'],
            ':cidade' => $data['cidade'] ?? '',
            ':logo' => $data['logo'] ?? '',
            ':whatsapp' => $data['whatsapp'] ?? '',
            ':telefone' => $data['telefone'] ?? '',
            ':endereco' => $data['endereco'] ?? '',
            ':ordem' => (int) ($data['ordem'] ?? 0),
            ':ativo' => (int) ($data['ativo'] ?? 1),
            ':agora' => Database::now(),
            ':id' => $id,
        ]);
    }
}
