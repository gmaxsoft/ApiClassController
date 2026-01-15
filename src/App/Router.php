<?php

namespace App;

use App\Controller\UniversalController;

/**
 * Prosty router do obsługi żądań HTTP
 */
class Router
{
    private UniversalController $controller;

    public function __construct(UniversalController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Główna metoda routingu
     */
    public function route(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Parsowanie URI
        $parsedUri = parse_url($uri);
        $path = $parsedUri['path'] ?? '/';
        
        // Usunięcie początkowego slasha i podział na segmenty
        $path = ltrim($path, '/');
        $segments = explode('/', $path);
        
        // Filtrowanie pustych segmentów
        $segments = array_filter($segments, function($segment) {
            return $segment !== '';
        });
        $segments = array_values($segments);
        
        // Jeśli pierwszy segment to "api", usuń go
        if (isset($segments[0]) && $segments[0] === 'api') {
            array_shift($segments);
        }
        
        // Jeśli pierwszy segment to "orders", usuń go i przekaż resztę do kontrolera
        if (isset($segments[0]) && $segments[0] === 'orders') {
            array_shift($segments);
            // Reszta segmentów (np. ID) jest przekazywana do kontrolera
            $this->controller->handleRequest($method, $segments);
        } else {
            // Jeśli nie ma "orders" w ścieżce, zwróć 404
            http_response_code(404);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => 'Endpoint not found']);
        }
    }
}



