<?php

namespace App\Model\Core\Team;

use App\Model\Core\Message\Context;

class TeamContextManager
{
    /** @var Context[] */
    private array $contexts = [];

    public function __construct(array $contexts = [])
    {
        foreach ($contexts as $context) {
            if ($context instanceof Context) {
                $this->contexts[$context->getAgentId()] = $context;
            }
        }
    }

    public function getContext(string $agentId): ?Context
    {
        return $this->contexts[$agentId] ?? null;
    }

    public function addEntry(string $agentId, array $entry): void
    {
        if (isset($this->contexts[$agentId])) {
            $this->contexts[$agentId]->addEntry($entry);
        } else {
            throw new \Exception("Context for agent ID '$agentId' not found.");
        }
    }

    public function getEntry(string $agentId, int $key): ?array
    {
        if (isset($this->contexts[$agentId])) {
            return $this->contexts[$agentId]->get($key);
        }
        throw new \Exception("Context for agent ID '$agentId' not found.");
    }


    public function addContext(Context $context): void
    {
        $this->contexts[$context->getAgentId()] = $context;
    }

}