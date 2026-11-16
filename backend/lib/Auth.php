<?php

declare(strict_types=1);

namespace Lib;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/php-jwt/src/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/php-jwt/src/BeforeValidException.php';
require_once __DIR__ . '/php-jwt/src/ExpiredException.php';
require_once __DIR__ . '/php-jwt/src/SignatureInvalidException.php';
require_once __DIR__ . '/php-jwt/src/JWT.php';
require_once __DIR__ . '/php-jwt/src/Key.php';

class Auth
{
    private static function getSecret(): string
    {
        $secret = getenv('JWT_SECRET');
        $raw = ($secret !== false && $secret !== '')
            ? $secret
            : 'nativechat-dev-secret-key-2026-change-in-production-env';

        // php-jwt 7.x requires >= 32 bytes for HS256
        return strlen($raw) >= 32 ? $raw : hash('sha256', $raw, true);
    }

    public static function issueToken(array $user): string
    {
        $now = time();
        $payload = [
            'iss' => 'chat-app-native-php',
            'iat' => $now,
            'exp' => $now + 86400 * 7,
            'sub' => (string) $user['id'],
            'name' => $user['name'],
            'phone' => $user['phone'],
        ];

        return JWT::encode($payload, self::getSecret(), 'HS256');
    }

    public static function requireAuth(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if ($header === '' && function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value) {
                if (strtolower((string) $key) === 'authorization') {
                    $header = (string) $value;
                    break;
                }
            }
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            responseJson(statusCode: 401, message: 'Missing or invalid Authorization header');
        }

        try {
            $decoded = JWT::decode($matches[1], new Key(self::getSecret(), 'HS256'));
            return [
                'id' => (int) $decoded->sub,
                'name' => (string) $decoded->name,
                'phone' => (string) $decoded->phone,
            ];
        } catch (\Throwable) {
            responseJson(statusCode: 401, message: 'Invalid or expired token');
        }
    }
}
