<?php

declare(strict_types=1);

namespace Controllers;

use Lib\Auth;
use Models\Contact;
use function Lib\responseJson;

class ContactController
{
    public static function index(): void
    {
        $auth = Auth::requireAuth();
        $contacts = Contact::listByUser($auth['id']);
        responseJson(data: ['contacts' => $contacts], statusCode: 200, message: 'Contacts retrieved');
    }

    public static function store(): void
    {
        $auth = Auth::requireAuth();
        $data = json_decode(file_get_contents('php://input') ?: '', true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            responseJson(statusCode: 400, message: 'Request body must be a valid JSON object');
        }

        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;

        if (!is_string($name) || trim($name) === '' || !is_string($phone) || trim($phone) === '') {
            responseJson(statusCode: 400, message: 'name and phone are required');
        }

        try {
            $id = Contact::create($auth['id'], trim($name), trim($phone));
            responseJson(
                data: ['id' => $id, 'name' => trim($name), 'phone' => trim($phone)],
                statusCode: 201,
                message: 'Contact created'
            );
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                responseJson(statusCode: 409, message: 'Contact with this phone number already exists');
            }
            throw $e;
        }
    }

    public static function update(string $id): void
    {
        $auth = Auth::requireAuth();
        if (!ctype_digit($id)) {
            responseJson(statusCode: 400, message: 'Invalid contact ID');
        }

        $contactId = (int) $id;
        if (Contact::findByIdAndUser($contactId, $auth['id']) === null) {
            responseJson(statusCode: 404, message: 'Contact not found');
        }

        $data = json_decode(file_get_contents('php://input') ?: '', true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            responseJson(statusCode: 400, message: 'Request body must be a valid JSON object');
        }

        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;

        if (!is_string($name) || trim($name) === '' || !is_string($phone) || trim($phone) === '') {
            responseJson(statusCode: 400, message: 'name and phone are required');
        }

        try {
            Contact::update($contactId, $auth['id'], trim($name), trim($phone));
            responseJson(
                data: ['id' => $contactId, 'name' => trim($name), 'phone' => trim($phone)],
                statusCode: 200,
                message: 'Contact updated'
            );
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                responseJson(statusCode: 409, message: 'Contact with this phone number already exists');
            }
            throw $e;
        }
    }

    public static function destroy(string $id): void
    {
        $auth = Auth::requireAuth();
        if (!ctype_digit($id)) {
            responseJson(statusCode: 400, message: 'Invalid contact ID');
        }

        if (!Contact::delete((int) $id, $auth['id'])) {
            responseJson(statusCode: 404, message: 'Contact not found');
        }

        responseJson(data: [], statusCode: 200, message: 'Contact deleted');
    }
}
