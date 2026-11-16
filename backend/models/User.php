<?php

declare(strict_types=1);

namespace Models;

use Lib\DB;

class User
{
    public static function create(array $attributes): int
    {
        $db = DB::getInstance();
        $statement = $db->prepare(
            'INSERT INTO users (name, phone, password_hash, profile_picture)
             VALUES (:name, :phone, :password_hash, :profile_picture)'
        );
        $statement->execute([
            ':name' => $attributes['name'],
            ':phone' => $attributes['phone'],
            ':password_hash' => $attributes['password_hash'],
            ':profile_picture' => $attributes['profile_picture'],
        ]);

        return (int) $db->lastInsertId();
    }

    public static function findByPhone(string $phone): ?array
    {
        $db = DB::getInstance();
        $statement = $db->prepare(
            'SELECT id, name, phone, password_hash, profile_picture, created_at
             FROM users WHERE phone = :phone LIMIT 1'
        );
        $statement->execute([':phone' => $phone]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }
}
