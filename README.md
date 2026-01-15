# Universal REST API Controller

Uniwersalny kontroler REST API napisany w PHP, zgodny z zasadami SOLID. Obsługuje podstawowe operacje CRUD (Create, Read, Update, Delete) dla zamówień.

## Wymagania

- PHP 7.4 lub nowszy
- Composer
- XAMPP lub inny serwer Apache/Nginx z obsługą PHP

## Zastosowane Technologie

### Język i Framework
- **PHP 7.4+** - Język programowania
- **PSR-4** - Standard autoloadingu klas

### Narzędzia i Biblioteki
- **Composer** - Menedżer zależności dla PHP
- **PHPUnit 9.x** - Framework do testów jednostkowych

### Architektura i Wzorce Projektowe
- **SOLID Principles** - Zasady projektowania obiektowego
  - Single Responsibility Principle (SRP)
  - Open/Closed Principle (OCP)
  - Liskov Substitution Principle (LSP)
  - Interface Segregation Principle (ISP)
  - Dependency Inversion Principle (DIP)
- **Dependency Injection** - Wstrzykiwanie zależności
- **Repository Pattern** - Abstrakcja warstwy danych przez interfejsy

### Technologie Webowe
- **REST API** - Architektura API oparta na protokole HTTP
- **JSON** - Format wymiany danych
- **Apache mod_rewrite** - Przekierowanie URL-i do routera
- **HTTP Methods** - GET, POST, PUT, DELETE, PATCH

### Struktura Projektu
- **MVC Pattern** - Separacja logiki (Controller, Service)
- **Router** - Obsługa routingu żądań HTTP
- **Locale Manager** - Zarządzanie tłumaczeniami (i18n)

## Instalacja

1. Sklonuj repozytorium lub pobierz pliki projektu.
2. Przejdź do katalogu projektu:
   ```
   cd /path/to/api
   ```
3. Zainstaluj zależności za pomocą Composera:
   ```
   composer install
   ```

## Uruchamianie testów jednostkowych

Projekt zawiera kompletne testy jednostkowe napisane z użyciem PHPUnit.

Aby uruchomić testy:

```
./vendor/bin/phpunit
```

lub jeśli PHPUnit jest zainstalowany globalnie:

```
phpunit
```

Wszystkie testy powinny przejść pomyślnie, wyświetlając komunikat OK.

## Jak sprawdzić API w Postman

### Konfiguracja serwera

Aby przetestować API, musisz uruchomić serwer PHP. W środowisku XAMPP:

1. Umieść katalog `api` w folderze `htdocs` XAMPP (np. `C:\Xampp\htdocs\api`).
2. Uruchom Apache i MySQL w panelu kontrolnym XAMPP.

### Endpointy API

API obsługuje następujące endpointy dla zamówień:

- **GET /api/orders** - Pobierz wszystkie zamówienia
- **GET /api/orders/{id}** - Pobierz zamówienie o podanym ID
- **POST /api/orders** - Utwórz nowe zamówienie
- **PUT /api/orders/{id}** - Zaktualizuj zamówienie o podanym ID
- **DELETE /api/orders/{id}** - Usuń zamówienie o podanym ID

### Przykłady w Postman

#### 1. Pobierz wszystkie zamówienia
- Metoda: GET
- URL: `http://localhost/api/orders`
- Headers: Content-Type: application/json
- Body: (puste)

Oczekiwana odpowiedź (200 OK):
```json
{
    "data": [
        {
            "id": 1,
            "customer_email": "example@example.com"
        }
    ],
    "count": 1
}
```

#### 2. Utwórz nowe zamówienie
- Metoda: POST
- URL: `http://localhost/api/orders`
- Headers: Content-Type: application/json
- Body (raw JSON):
```json
{
    "customer_email": "test@example.com"
}
```

Oczekiwana odpowiedź (201 Created):
```json
{
    "message": "Zamówienie utworzone pomyślnie.",
    "data": {
        "id": 1,
        "customer_email": "test@example.com"
    }
}
```

#### 3. Pobierz pojedyncze zamówienie
- Metoda: GET
- URL: `http://localhost/api/orders/1`
- Headers: Content-Type: application/json
- Body: (puste)

Oczekiwana odpowiedź (200 OK):
```json
{
    "data": {
        "id": 1,
        "customer_email": "test@example.com"
    }
}
```

#### 4. Zaktualizuj zamówienie
- Metoda: PUT
- URL: `http://localhost/api/orders/1`
- Headers: Content-Type: application/json
- Body (raw JSON):
```json
{
    "customer_email": "updated@example.com"
}
```

Oczekiwana odpowiedź (200 OK):
```json
{
    "message": "Zamówienie zaktualizowane pomyślnie."
}
```

#### 5. Usuń zamówienie
- Metoda: DELETE
- URL: `http://localhost/api/orders/1`
- Headers: Content-Type: application/json
- Body: (puste)

Oczekiwana odpowiedź (204 No Content):
(pusta odpowiedź)

### Obsługa błędów

API zwraca odpowiednie kody błędów HTTP wraz z komunikatami w języku polskim:

- 400 Bad Request: Brak wymaganych pól lub nieprawidłowy JSON
- 404 Not Found: Zamówienie nie zostało znalezione
- 405 Method Not Allowed: Niedozwolona metoda HTTP

Przykład błędu:
```json
{
    "error": "Brak wymaganego pola customer_email."
}
```

## Uwagi

- W obecnym stanie API używa mocków dla warstwy serwisowej. Aby podłączyć rzeczywistą bazę danych, należy zaimplementować klasę `UniversalService` implementującą interfejs `UniversalServiceContract`.
- Tłumaczenia są ładowane z pliku `resources/lang/PL.json`.
- Kod jest zgodny z PSR-4 autoloadingiem.