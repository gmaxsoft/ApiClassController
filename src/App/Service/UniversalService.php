<?php

namespace App\Service;

/**
 * Prosta implementacja UniversalServiceContract
 * W wersji produkcyjnej powinna łączyć się z bazą danych
 */
class UniversalService implements UniversalServiceContract
{
    private array $data = [];
    private int $nextId = 1;

    public function create(array $data): array
    {
        $data['id'] = $this->nextId++;
        $this->data[$data['id']] = $data;
        return $data;
    }

    public function getById(int $id): ?array
    {
        return $this->data[$id] ?? null;
    }

    public function getAll(): array
    {
        return array_values($this->data);
    }

    public function update(int $id, array $data): bool
    {
        if (!isset($this->data[$id])) {
            return false;
        }
        $this->data[$id] = array_merge($this->data[$id], $data);
        $this->data[$id]['id'] = $id; // Ensure ID is preserved
        return true;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->data[$id])) {
            return false;
        }
        unset($this->data[$id]);
        return true;
    }
}

