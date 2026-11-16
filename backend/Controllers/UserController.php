<?php

declare(strict_types=1);

namespace Controllers;

use Lib\Auth;
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

    public static function login(): void
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input ?: '', true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            responseJson(statusCode: 400, message: 'Request body must be a valid JSON object');
        }

        $phone = $data['phone'] ?? null;
        $password = $data['password'] ?? null;

        if (!is_string($phone) || trim($phone) === '' || !is_string($password) || $password === '') {
            responseJson(statusCode: 400, message: 'phone and password are required');
        }

        $user = User::findByPhone(trim($phone));
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            responseJson(statusCode: 401, message: 'Invalid phone number or password');
        }

        unset($user['password_hash']);
        $token = Auth::issueToken($user);

        responseJson(
            data: ['token' => $token, 'user' => $user],
            statusCode: 200,
            message: 'Login successful'
        );
    }

    public static function me(): void
    {
        $claims = Auth::requireAuth();
        $user = User::findById($claims['id']);
        if ($user === null) {
            responseJson(statusCode: 404, message: 'User not found');
        }

        responseJson(data: ['user' => $user], statusCode: 200, message: 'Authenticated user');
    }

    public static function deleteMe(): void
    {
        $claims = Auth::requireAuth();
        if (!User::deleteById($claims['id'])) {
            responseJson(statusCode: 404, message: 'User not found');
        }

        responseJson(data: [], statusCode: 200, message: 'Account deleted successfully');
    }
}
