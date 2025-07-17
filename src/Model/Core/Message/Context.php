<?php

namespace App\Model\Core\Message;

class Context
{
    private ?SystemMessage $systemMessage = null;

    public function __construct(
        private readonly string $contextId,
        private array $context = [],
        private bool $isParent = false
    ) {
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
        // merge system message if it exists
        $contextArray = [];

        if ($this->systemMessage) {
            $contextArray[] = $this->systemMessage->toArray();
        }

        foreach ($this->context as $entry) {
            $contextArray[] = $entry;
        }

        return $contextArray;
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

    public function getSystemMessage(): ?SystemMessage
    {
        return $this->systemMessage;
    }

    public function setSystemMessage(SystemMessage $systemMessage): void
    {
        $this->systemMessage = $systemMessage;
    }

    public function setIsParent(): void
    {
        $this->isParent = true;
    }

    public function setIsChild(): void
    {
        $this->isParent = false;
    }

}