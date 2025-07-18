<?php

namespace App\Model\Core\Message;

class Context implements ContextInterface
{
    private ?SystemMessage $systemMessage = null;

    public function __construct(
        private array $context = [],
    ) {
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function addEntry(array $entry): self
    {
        $this->context[uniqid()] = $entry;

        dump('Context::addEntry', $entry);

        return $this;
    }

    public function getEntry(int $key): ?array
    {
        return $this->context[$key] ?? null;
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

    public function getSystemMessage(): ?SystemMessage
    {
        return $this->systemMessage;
    }

    public function setSystemMessage(SystemMessage $systemMessage): self
    {
        $this->systemMessage = $systemMessage;

        return $this;
    }


    public function setContext(array $data): self
    {
        $this->context = $data;

        return $this;
    }
}