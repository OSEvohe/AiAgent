<?php

namespace App\Model\Core\Team;


use App\Model\Core\Message\Context;

/**
 * Class ContextManager
 *
 * Manages contexts for different agents. Allows adding and retrieving context entries.
 */
interface ContextManagerInterface
{
    /**
     * Gets all contexts.
     *
     * @return Context[]
     */
    public function getContexts(): array;

    /**
     * Gets a context by agent ID.
     *
     * @param string $agentId
     * @return Context|null
     */
    public function getContext(string $agentId): ?Context;

    /**
     * Adds an entry to a context identified by agent ID.
     *
     * @param string $agentId
     * @param array $entry
     * @throws \Exception if context for the given agent ID is not found
     */
    public function addEntry(string $agentId, array $entry): void;

    /**
     * Gets an entry from a context by agent ID and key.
     *
     * @param string $agentId
     * @param int $key
     * @return array|null
     * @throws \Exception if context for the given agent ID is not found
     */
    public function getEntry(string $agentId, int $key): ?array;

    /**
     * Adds a context to the manager.
     * Should return context Id
     *
     * @param Context $context
     */
    public function addContext(Context $context): string;

    public function toArray(): array;
}