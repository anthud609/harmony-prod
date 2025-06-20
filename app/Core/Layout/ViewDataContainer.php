<?php

// File: app/Core/Layout/ViewDataContainer.php

namespace App\Core\Layout;

/**
 * Responsible ONLY for managing view data
 */
class ViewDataContainer
{
    private array $data = [];
    private array $defaultData = [];

    public function __construct(array $defaultData = [])
    {
        $this->defaultData = $defaultData;
        $this->data = $defaultData;
    }

    /**
     * Set multiple data values
     */
    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Set a single data value
     */
    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get a data value
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get all data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Check if data exists
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Reset to default data
     */
    public function reset(): self
    {
        $this->data = $this->defaultData;

        return $this;
    }
}
