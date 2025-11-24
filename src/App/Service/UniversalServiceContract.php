<?php

namespace App\Service;

/**
 * Interfejs dla uniwersalnego serwisu.
 * Zapewnia elastyczność i testowalność (Dependency Inversion Principle).
 */
interface UniversalServiceContract
{
    public function create(array $data): array;
    public function getById(int $id): ?array;
    public function getAll(): array;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}