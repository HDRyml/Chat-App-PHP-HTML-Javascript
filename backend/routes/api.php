<?php

declare(strict_types=1);

use Controllers\ContactController;
use Controllers\UserController;
use Lib\Router;
use function Lib\responseJson;
use Lib\DB;

return static function (Router $router): void {
    $router->get('/api/health', static function (): void {
        // check database connection
        try {
            $db = DB::getInstance();
            $db->query('SELECT 1');
        } catch (\PDOException $e) {
            responseJson(data: ['ok' => false, 'database' => false], message: 'Database connection failed: ' . $e->getMessage(), statusCode: 500);
            return;
        }
        responseJson(data: ['ok' => true, 'database' => true], message: 'API is running');
    });

    $router->post('/api/users', [UserController::class, 'register']);
    $router->post('/api/auth/login', [UserController::class, 'login']);
    $router->get('/api/users/me', [UserController::class, 'me']);
    $router->delete('/api/users/me', [UserController::class, 'deleteMe']);

    $router->get('/api/contacts', [ContactController::class, 'index']);
    $router->post('/api/contacts', [ContactController::class, 'store']);
    $router->put('/api/contacts/{id}', [ContactController::class, 'update']);
    $router->delete('/api/contacts/{id}', [ContactController::class, 'destroy']);
};
