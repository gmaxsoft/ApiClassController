<?php

/**
 * Entry point dla API
 * 
 * Konfiguracja:
 * - Umieść ten plik w katalogu htdocs/api/
 * - Upewnij się, że mod_rewrite jest włączony w Apache
 * - Lub skonfiguruj .htaccess do przekierowania wszystkich żądań do tego pliku
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Router;
use App\Controller\UniversalController;
use App\Service\UniversalService;
use App\Service\LocaleManager;

// Inicjalizacja zależności
$langFile = __DIR__ . '/resources/lang/PL.json';
$localeManager = new LocaleManager($langFile);
$universalService = new UniversalService();
$controller = new UniversalController($universalService, $localeManager);

// Utworzenie routera i obsługa żądania
$router = new Router($controller);
$router->route();

