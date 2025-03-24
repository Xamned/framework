<?php

namespace xamned\framework\http\resource;

use RuntimeException;
use xamned\framework\contracts\http\resource\ResourceWriterInterface;

class JsonResourceWriter implements ResourceWriterInterface
{
    private string $resourceName;
    private string $filePath;

    private function loadData(): array
    {
        if (file_exists($this->filePath) === false) {
            return [];
        }

        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?? [];
    }

    private function saveData(array $data): void
    {
        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function setResourceName(string $name): static
    {
        $this->filePath = $name . '.json';
        return $this;
    }

    public function create(array $values): void
    {
        $data = $this->loadData();

        $data[] = $values;
        $this->saveData($data);
    }

    function update(int|string $id, array $values): void
    {
        $data = $this->loadData();

        if (isset($data[$id]) === false) {
            throw new RuntimeException("Запись с ID '{$id}' не найдена.");
        }

        $data[$id] = $values;
        $this->saveData($data);
    }

    function patch(int|string $id, array $values): void
    {
        $data = $this->loadData();

        if (isset($data[$id]) === false) {
            throw new RuntimeException("Запись с ID '{$id}' не найдена.");
        }

        $data[$id] = array_merge($data[$id], $values);
        $this->saveData($data);
    }

    function delete(int|string $id): void
    {
        $data = $this->loadData();

        if (isset($data[$id]) === false) {
            throw new RuntimeException("Запись с ID '{$id}' не найдена.");
        }

        unset($data[$id]);
        $this->saveData($data);
    }
}