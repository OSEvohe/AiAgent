<?php

namespace App\Model\Core\Message;

class Context
{
    private array $context = [];

    public function __construct(private readonly string $agentId, array $context = [])
    {
        $this->context = $context;
    }

    public function getAgentId(): string
    {
        return $this->agentId;
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
}