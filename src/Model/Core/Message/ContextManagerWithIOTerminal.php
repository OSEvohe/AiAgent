<?php

namespace App\Model\Core\Message;

use App\Model\Core\Team\ContextManagerInterface;
use App\Model\IO\Terminal;

class ContextManagerWithIOTerminal extends ContextManager implements ContextManagerInterface
{
    public function __construct(
        ContextManagerInterface $contextManager,
        private readonly Terminal $terminal
    ) {
        parent::__construct($contextManager->getContexts());
    }

    public function addEntry(string $agentId, array $entry): void
    {
        parent::addEntry($agentId, $entry);

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
}