<?php

namespace App\Model\Core\Message;

use App\Model\Core\Team\ContextManagerInterface;
use App\Model\IO\Terminal;

readonly class ContextManagerWithIOTerminal implements ContextManagerInterface
{
    public function __construct(
        private ContextManagerInterface $contextManager,
        private Terminal $terminal
    ) {
    }

    public function addEntry(string $agentId, array $entry): void
    {
        $this->contextManager->addEntry($agentId, $entry);

        dump($entry, $agentId);

        if (($entry['role'] ?? false) && $entry['role'] === 'assistant') {
            if (isset($entry['content']) && $this->getContext($agentId)->isParent()) {
                $this->terminal->output($agentId . ': ' . $entry['content']);
            }

            if (isset($entry['tool_calls'])) {
                foreach ($entry['tool_calls'] as $toolCall) {
                    $this->terminal->output("Tool call: " . json_encode($toolCall['function']['name']));
                }
            }
        }
    }

    public function getContexts(): array
    {
        return $this->contextManager->getContexts();
    }

    public function getContext(string $agentId): ?Context
    {
        return $this->contextManager->getContext($agentId);
    }

    public function getEntry(string $agentId, int $key): ?array
    {
        return $this->contextManager->getEntry($agentId, $key);
    }

    public function addContext(Context $context): string
    {
        return $this->contextManager->addContext($context);
    }

    public function toArray(): array
    {
        return $this->contextManager->toArray();
    }

    public function loadDiscussion(): ContextManagerInterface
    {
        return $this->contextManager->loadDiscussion();
    }
}