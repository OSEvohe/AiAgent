<?php

namespace App\Model\Core\Message;

use App\Model\Core\Team\ContextManagerInterface;

/**
 * Class ContextManager
 *
 * Manages contexts for different agents. Allows adding and retrieving context entries.
 * With Decorator pattern, it can be extended to add more functionalities like logging, storing, display output, etc...
 */
class ContextManager implements ContextManagerInterface
{
    /** @var Context[] */
    private array $contexts = [];

    /**
     * Gets all contexts.
     *
     * @return Context[]
     */
    public function getContexts(): array
    {
        return $this->contexts;
    }

    /**
     * Gets a context by agent ID.
     *
     * @param string $agentId
     * @return Context|null
     */
    public function getContext(string $agentId): ?Context
    {
        return $this->contexts[$agentId] ?? null;
    }

    /**
     * Adds an entry to a context identified by agent ID.
     *
     * @param string $agentId
     * @param array $entry
     * @throws \Exception if context for the given agent ID is not found
     */
    public function addEntry(string $agentId, array $entry): void
    {
        if (isset($this->contexts[$agentId])) {
            $this->contexts[$agentId]->addEntry($entry);
        } else {
            throw new \Exception("Context for agent ID '$agentId' not found.");
        }
    }

    /**
     * Gets an entry from a context by agent ID and key.
     *
     * @param string $agentId
     * @param int $key
     * @return array|null
     * @throws \Exception if context for the given agent ID is not found
     */
    public function getEntry(string $agentId, int $key): ?array
    {
        if (isset($this->contexts[$agentId])) {
            return $this->contexts[$agentId]->get($key);
        }
        throw new \Exception("Context for agent ID '$agentId' not found.");
    }

    /**
     * Adds a context to the manager.
     *
     * @param Context $context
     * @return string
     */
    public function addContext(Context $context): string
    {
        $this->contexts[$context->getContextId()] = $context;

        return $context->getContextId();
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->contexts as $context) {
            $result[$context->getContextId()] = $context->toArray();
        }
        return $result;
    }

    public function loadDiscussion(): ContextManagerInterface
    {
        return $this; // For stateless context manager, return itself
    }
}