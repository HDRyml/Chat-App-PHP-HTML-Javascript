<?php

declare(strict_types=1);

namespace Controllers;

use Models\User;
use function Lib\responseJson;

class UserController
{
    public static function register(): void
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input ?: '', true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            responseJson(statusCode: 400, message: 'Request body must be a valid JSON object');
        }

        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;
        $password = $data['password'] ?? null;
        $profilePicture = $data['profile_picture'] ?? null;

        if (!is_string($name) || trim($name) === '' || !is_string($phone) || trim($phone) === '') {
            responseJson(statusCode: 400, message: 'name and phone are required');
        }

        if (!is_string($password) || strlen($password) < 6) {
            responseJson(statusCode: 400, message: 'password must be at least 6 characters');
        }

        $cleanPhone = trim($phone);
        if (User::findByPhone($cleanPhone) !== null) {
            responseJson(statusCode: 409, message: 'Phone number is already registered');
        }

        $id = User::create([
            'name' => trim($name),
            'phone' => $cleanPhone,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'profile_picture' => is_string($profilePicture) && trim($profilePicture) !== '' ? trim($profilePicture) : null,
        ]);

        responseJson(
            data: ['id' => $id, 'name' => trim($name), 'phone' => $cleanPhone],
            statusCode: 201,
            message: 'User registered successfully'
        );
    }
}
