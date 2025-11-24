<?php

namespace Tests;

use App\Controller\UniversalController;
use App\Service\UniversalServiceContract;
use App\Service\LocaleManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Testy jednostkowe dla UniversalController
 */
class UniversalControllerTest extends TestCase
{
    private MockObject $universalService;
    private MockObject $localeManager;
    private UniversalController $controller;

    protected function setUp(): void
    {
        // Tworzenie mocków dla zależności
        $this->universalService = $this->createMock(UniversalServiceContract::class);
        $this->localeManager = $this->createMock(LocaleManager::class);

        // Mockowanie tłumaczeń
        $this->localeManager->method('get')
            ->willReturnCallback(function ($key, $replace = []) {
                $translations = [
                    'missing_required_field' => 'Brak wymaganego pola {field}.',
                    'order_created_successfully' => 'Zamówienie utworzone pomyślnie.',
                    'order_updated_successfully' => 'Zamówienie zaktualizowane pomyślnie.',
                    'method_not_allowed' => 'Metoda niedozwolona.',
                    'missing_id_for_update' => 'Brak ID zamówienia do aktualizacji.',
                    'missing_id_for_delete' => 'Brak ID zamówienia do usunięcia.',
                    'invalid_json' => 'Nieprawidłowy format JSON.',
                    'order_not_found' => 'Zamówienie o ID {id} nie zostało znalezione.',
                    'update_failed' => 'Nie udało się zaktualizować zamówienia lub zamówienie nie istnieje.',
                    'delete_failed' => 'Nie udało się usunąć zamówienia lub zamówienie nie istnieje.',
                ];
                $message = $translations[$key] ?? $key;
                foreach ($replace as $placeholder => $value) {
                    $message = str_replace('{' . $placeholder . '}', $value, $message);
                }
                return $message;
            });

        // Tworzenie instancji kontrolera z mockami
        $this->controller = new UniversalController($this->universalService, $this->localeManager);
        
        // Reset mock input
        $GLOBALS['__PHPUNIT_TEST_INPUT'] = null;
    }
    
    protected function tearDown(): void
    {
        // Clean up mock input after each test
        unset($GLOBALS['__PHPUNIT_TEST_INPUT']);
    }

    public function testHandleRequestPostValidData()
    {
        $data = ['customer_email' => 'test@example.com'];
        $pathSegments = [];

        // Symulacja input JSON
        $this->setInputJson($data);

        $this->universalService->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn(['id' => 1, 'customer_email' => 'test@example.com']);

        // Przechwytywanie outputu
        ob_start();
        $this->controller->handleRequest('POST', $pathSegments);
        $output = ob_get_clean();

        $expected = ['message' => 'Zamówienie utworzone pomyślnie.', 'data' => ['id' => 1, 'customer_email' => 'test@example.com']];
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testHandleRequestPostMissingRequiredField()
    {
        $data = [];
        $pathSegments = [];

        $this->setInputJson($data);

        ob_start();
        $this->controller->handleRequest('POST', $pathSegments);
        $output = ob_get_clean();

        $expected = ['error' => 'Brak wymaganego pola customer_email.'];
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testHandleRequestGetAll()
    {
        $orders = [['id' => 1], ['id' => 2]];
        $pathSegments = [];

        $this->universalService->expects($this->once())
            ->method('getAll')
            ->willReturn($orders);

        ob_start();
        $this->controller->handleRequest('GET', $pathSegments);
        $output = ob_get_clean();

        $expected = ['data' => $orders, 'count' => 2];
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testHandleRequestGetOneExisting()
    {
        $order = ['id' => 1, 'customer_email' => 'test@example.com'];
        $pathSegments = ['1'];

        $this->universalService->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($order);

        ob_start();
        $this->controller->handleRequest('GET', $pathSegments);
        $output = ob_get_clean();

        $expected = ['data' => $order];
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testHandleRequestGetOneNotFound()
    {
        $pathSegments = ['999'];

        $this->universalService->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        ob_start();
        $this->controller->handleRequest('GET', $pathSegments);
        $output = ob_get_clean();

        $expected = ['error' => 'Zamówienie o ID 999 nie zostało znalezione.'];
        $this->assertJson($output);
        $this->assertEquals($expected, json_decode($output, true));
    }

    public function testHandleRequestPutValid()
    {
        $data = ['customer_email' => 'updated@example.com'];
        $pathSegments = ['1'];

        $this->setInputJson($data);

        $this->universalService->expects($this->once())
            ->method('update')
            ->with(1, $data)
            ->willReturn(true);

        ob_start();
        $this->controller->handleRequest('PUT', $pathSegments);
        $output = ob_get_clean();

        $expected = ['message' => 'Zamówienie zaktualizowane pomyślnie.'];
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testHandleRequestPutMissingId()
    {
        $data = ['customer_email' => 'test@example.com'];
        $pathSegments = [];

        $this->setInputJson($data);

        ob_start();
        $this->controller->handleRequest('PUT', $pathSegments);
        $output = ob_get_clean();

        $expected = ['error' => 'Brak ID zamówienia do aktualizacji.'];
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testHandleRequestDeleteValid()
    {
        $pathSegments = ['1'];

        $this->universalService->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        ob_start();
        $this->controller->handleRequest('DELETE', $pathSegments);
        $output = ob_get_clean();

        $this->assertEmpty($output); // 204 No Content
    }

    public function testHandleRequestDeleteMissingId()
    {
        $pathSegments = [];

        ob_start();
        $this->controller->handleRequest('DELETE', $pathSegments);
        $output = ob_get_clean();

        $expected = ['error' => 'Brak ID zamówienia do usunięcia.'];
        $this->assertJson($output);
        $this->assertEquals($expected, json_decode($output, true));
    }

    public function testHandleRequestInvalidMethod()
    {
        $pathSegments = [];

        ob_start();
        $this->controller->handleRequest('OPTIONS', $pathSegments); // OPTIONS nie jest obsługiwany
        $output = ob_get_clean();

        $expected = ['message' => 'Metoda niedozwolona.'];
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testHandleRequestInvalidJson()
    {
        $pathSegments = [];

        // Symulacja nieprawidłowego JSON
        $this->setInvalidJson('invalid json');

        ob_start();
        $this->controller->handleRequest('POST', $pathSegments);
        $output = ob_get_clean();

        $expected = ['error' => 'Nieprawidłowy format JSON.'];
        $this->assertJson($output);
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    /**
     * Pomocnicza metoda do symulacji input JSON
     */
    private function setInputJson(array $data): void
    {
        $json = json_encode($data);
        $GLOBALS['__PHPUNIT_TEST_INPUT'] = $json;
    }
    
    /**
     * Pomocnicza metoda do symulacji nieprawidłowego JSON
     */
    private function setInvalidJson(string $invalidJson): void
    {
        $GLOBALS['__PHPUNIT_TEST_INPUT'] = $invalidJson;
    }
}