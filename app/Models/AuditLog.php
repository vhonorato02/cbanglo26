<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Trilha de auditoria das ações administrativas.
 */
final class AuditLog
{
    public static function registrar(?int $adminId, string $acao, string $detalhes = ''): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO admin_logs (admin_id, acao, detalhes, ip_hash, criado_em)
             VALUES (:admin, :acao, :detalhes, :ip, :agora)'
        );
        $stmt->execute([
            ':admin' => $adminId,
            ':acao' => mb_substr($acao, 0, 100),
            ':detalhes' => mb_substr($detalhes, 0, 2000),
            ':ip' => function_exists('client_ip_hash') ? client_ip_hash() : '',
            ':agora' => Database::now(),
        ]);
    }

    /** @return array{rows: array<int, array<string, mixed>>, total: int, pages: int, page: int} */
    public static function listar(int $page = 1, int $perPage = 40): array
    {
        $pdo = Database::pdo();
        $total = (int) $pdo->query('SELECT COUNT(*) FROM admin_logs')->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $rows = $pdo->query(
            'SELECT l.*, u.nome AS admin_nome FROM admin_logs l
             LEFT JOIN admin_usuarios u ON u.id = l.admin_id
             ORDER BY l.criado_em DESC LIMIT ' . $perPage . ' OFFSET ' . $offset
        )->fetchAll();

        return ['rows' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }
}
