<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AdminUser
{
    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM admin_usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return array<string, mixed>|null */
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM admin_usuarios WHERE email = :email');
        $stmt->execute([':email' => mb_strtolower(trim($email), 'UTF-8')]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return array<int, array<string, mixed>> */
    public static function todos(): array
    {
        return Database::pdo()
            ->query('SELECT id, nome, email, ativo, ultimo_login, criado_em FROM admin_usuarios ORDER BY nome')
            ->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM admin_usuarios')->fetchColumn();
    }

    public static function create(string $nome, string $email, string $senha, int $ativo = 1): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO admin_usuarios (nome, email, senha_hash, ativo, criado_em, atualizado_em)
             VALUES (:nome, :email, :hash, :ativo, :agora, :agora2)'
        );
        $agora = Database::now();
        $stmt->execute([
            ':nome' => $nome,
            ':email' => mb_strtolower(trim($email), 'UTF-8'),
            ':hash' => password_hash($senha, PASSWORD_DEFAULT),
            ':ativo' => $ativo,
            ':agora' => $agora,
            ':agora2' => $agora,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function updatePassword(int $id, string $senha): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE admin_usuarios SET senha_hash = :hash, atualizado_em = :agora WHERE id = :id'
        );
        $stmt->execute([
            ':hash' => password_hash($senha, PASSWORD_DEFAULT),
            ':agora' => Database::now(),
            ':id' => $id,
        ]);
    }

    public static function updateDados(int $id, string $nome, string $email, int $ativo): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE admin_usuarios SET nome = :nome, email = :email, ativo = :ativo, atualizado_em = :agora WHERE id = :id'
        );
        $stmt->execute([
            ':nome' => $nome,
            ':email' => mb_strtolower(trim($email), 'UTF-8'),
            ':ativo' => $ativo,
            ':agora' => Database::now(),
            ':id' => $id,
        ]);
    }

    public static function touchLogin(int $id): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE admin_usuarios SET ultimo_login = :agora WHERE id = :id'
        );
        $stmt->execute([':agora' => Database::now(), ':id' => $id]);
    }
}
