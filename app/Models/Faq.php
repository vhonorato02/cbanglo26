<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Faq
{
    /** @return array<int, array<string, mixed>> */
    public static function ativas(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM faqs WHERE ativo = 1 ORDER BY ordem, id')
            ->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public static function todas(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM faqs ORDER BY ordem, id')
            ->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM faqs WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(string $pergunta, string $resposta, int $ordem, int $ativo): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO faqs (pergunta, resposta, ordem, ativo, criado_em, atualizado_em)
             VALUES (:p, :r, :o, :a, :agora, :agora2)'
        );
        $agora = Database::now();
        $stmt->execute([
            ':p' => $pergunta, ':r' => $resposta, ':o' => $ordem, ':a' => $ativo,
            ':agora' => $agora, ':agora2' => $agora,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, string $pergunta, string $resposta, int $ordem, int $ativo): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE faqs SET pergunta = :p, resposta = :r, ordem = :o, ativo = :a, atualizado_em = :agora
             WHERE id = :id'
        );
        $stmt->execute([
            ':p' => $pergunta, ':r' => $resposta, ':o' => $ordem, ':a' => $ativo,
            ':agora' => Database::now(), ':id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM faqs WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
