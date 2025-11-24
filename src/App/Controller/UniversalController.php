<?php

namespace App\Controller;
use App\Service\UniversalServiceContract;
use App\Service\LocaleManager;
use Exception;

// ----------------------------------------------------
// Klasa Kontrolera (Główna Klasa API)
// Obsługuje routing, walidację wstępną i formatowanie odpowiedzi
// ----------------------------------------------------

class UniversalController
{
    private UniversalServiceContract $universalService;
    private LocaleManager $locale;

    // Użycie Dependency Injection
    public function __construct(UniversalServiceContract $universalService, LocaleManager $locale)
    {
        $this->universalService = $universalService;
        $this->locale = $locale;
    }

    // Główna metoda do obsługi wszystkich żądań HTTP
    public function handleRequest(string $method, array $pathSegments): void
    {
        $id = $pathSegments[0] ?? null;

        try {
            switch ($method) {
                case 'POST':
                    // Tworzenie (Create): /orders
                    $requestBody = $this->getJsonBody();
                    $this->handleCreate($requestBody);
                    break;
                case 'GET':
                    if ($id) {
                        // Pobieranie pojedynczego (Read): /orders/123
                        $this->handleGetOne((int) $id);
                    } else {
                        // Pobieranie wszystkich (Read): /orders
                        $this->handleGetAll();
                    }
                    break;
                case 'PUT':
                case 'PATCH':
                    // Aktualizacja (Update): /orders/123
                    if (!$id) throw $this->createException('missing_id_for_update', [], 400);
                    $requestBody = $this->getJsonBody();
                    $this->handleUpdate((int) $id, $requestBody);
                    break;
                case 'DELETE':
                    // Usuwanie (Delete): /orders/123
                    if (!$id) throw $this->createException('missing_id_for_delete', [], 400);
                    $this->handleDelete((int) $id);
                    break;
                default:
                    $this->sendResponse(405, ['message' => $this->locale->get('method_not_allowed')]);
                    break;
            }
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            $this->sendResponse($statusCode, ['error' => $e->getMessage()]);
        }
    }

    // --- Metody obsługujące poszczególne operacje CRUD ---

    private function handleCreate(array $data): void
    {
        // Tutaj powinna być walidacja, np. czy 'product_id' jest w $data
        if (empty($data['customer_email'])) {
            throw $this->createException('missing_required_field', ['field' => 'customer_email'], 400);
        }

        $newOrder = $this->universalService->create($data);

        // Zwrócenie kodu 201 (Created)
        $this->sendResponse(201, ['message' => $this->locale->get('order_created_successfully'), 'data' => $newOrder]);
    }

    private function handleGetOne(int $id): void
    {
        $order = $this->universalService->getById($id);

        if (!$order) {
            // Zwrócenie kodu 404 (Not Found)
            throw $this->createException('order_not_found', ['id' => $id], 404);
        }

        // Zwrócenie kodu 200 (OK)
        $this->sendResponse(200, ['data' => $order]);
    }

    private function handleGetAll(): void
    {
        $orders = $this->universalService->getAll();
        $this->sendResponse(200, ['data' => $orders, 'count' => count($orders)]);
    }

    private function handleUpdate(int $id, array $data): void
    {
        $success = $this->universalService->update($id, $data);

        if (!$success) {
            throw $this->createException('update_failed', [], 404);
        }

        // Zwrócenie kodu 200 (OK) i pustej odpowiedzi (No Content) lub zaktualizowanego zasobu
        $this->sendResponse(200, ['message' => $this->locale->get('order_updated_successfully')]);
    }

    private function handleDelete(int $id): void
    {
        $success = $this->universalService->delete($id);

        if (!$success) {
            throw $this->createException('delete_failed', [], 404);
        }

        // Zwrócenie kodu 204 (No Content)
        $this->sendResponse(204, null);
    }

    // --- Metody pomocnicze ---

    private function createException(string $messageKey, array $replace = [], int $code = 500): Exception
    {
        return new Exception($this->locale->get($messageKey, $replace), $code);
    }

    private function getJsonBody(): array
    {
        // Wsparcie dla testów - jeśli zdefiniowana jest zmienna globalna, użyj jej
        $body = $GLOBALS['__PHPUNIT_TEST_INPUT'] ?? file_get_contents('php://input');
        $data = json_decode($body, true);

        // Sprawdzenie, czy JSON jest poprawny
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createException('invalid_json', [], 400);
        }
        return $data ?? [];
    }

    private function validateIdForOperation(?string $id, string $errorKey): void
    {
        if (!$id) {
            throw $this->createException($errorKey, [], 400);
        }
    }

    private function sendResponse(int $statusCode, ?array $responseData): void
    {
        // Wysyłaj nagłówki tylko w środowisku webowym, nie w CLI (testy)
        if (PHP_SAPI !== 'cli') {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code($statusCode);
        }

        if ($responseData !== null) {
            echo json_encode($responseData);
        }
    }
}