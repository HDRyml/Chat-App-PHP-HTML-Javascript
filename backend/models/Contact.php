<?php

declare(strict_types=1);

namespace Models;

use Lib\DB;

class Contact
{
    public static function listByUser(int $userId): array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'SELECT id, name, phone, created_at, updated_at
             FROM contacts
             WHERE user_id = :user_id
             ORDER BY name ASC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function findByIdAndUser(int $id, int $userId): ?array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('SELECT id, name, phone FROM contacts WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public static function create(int $userId, string $name, string $phone): int
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO contacts (user_id, name, phone)
             VALUES (:user_id, :name, :phone)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':phone' => $phone,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, int $userId, string $name, string $phone): bool
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'UPDATE contacts
             SET name = :name, phone = :phone
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':name' => $name,
            ':phone' => $phone,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id, int $userId): bool
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('DELETE FROM contacts WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
