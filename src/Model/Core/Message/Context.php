<?php

namespace App\Model\Core\Message;

class Context
{
    private array $context = [];

    public function __construct(private readonly string $contextId, array $context = [], private bool $isParent = false)
    {
        $this->context = $context;
    }

    public function getContextId(): string
    {
        return $this->contextId;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function addEntry(array $entry): void
    {
        $this->context[uniqid()] = $entry;
    }

    public function get(int $index): ?array
    {
        return $this->context[$index] ?? null;
    }

    public function toArray(): array
    {
        return array_values($this->context);
    }

    // In multi agent systems, the parent context is the one interacting with the user, while child contexts are used for sub-agents or tasks.
    public function isParent(): bool
    {
        return $this->isParent;
    }

    public function isChild(): bool
    {
        return !$this->isParent;
    }
}