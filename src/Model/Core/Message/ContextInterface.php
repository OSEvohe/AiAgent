<?php

namespace App\Model\Core\Message;


/**
 * Class ContextManager
 *
 * Manages contexts for different agents. Allows adding and retrieving context entries.
 */
interface ContextInterface
{
    /**
     * Gets a context by agent ID.
     *
     * @return array
     */
    public function getContext(): array;

    /**
     * Adds an entry to context
     * @param array $entry
     */
    public function addEntry(array $entry): self;

    /**
     * Gets an entry from context by key.
     *
     * @param int $key
     * @return array|null
     */
    public function getEntry(int $key): ?array;

    /**
     * Adds a context to the manager.
     *
     * @param array $data
     * @return ContextInterface
     */
    public function setContext(array $data): self;

    /**
     * Converts the object to an array representation.
     *
     * @return array
     */
    public function toArray(): array;

    public function getSystemMessage(): ?SystemMessage;

    public function setSystemMessage(SystemMessage $systemMessage): self;
}