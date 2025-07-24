<?php

namespace App\Model\Core\Message;

use App\Model\IO\Terminal;

readonly class ContextWithIOTerminal implements ContextInterface
{
    public function __construct(
        private ContextInterface $context,
        private Terminal $terminal,
        private bool $outputAssistant = true,
        private bool $outputToolCalls = true,
        private string $agentDisplayName = 'AgentInterface',
    ) {
    }

    public function addEntry(array $entry, string $entryUid = ''): string
    {
        $uniqId = $this->context->addEntry($entry, $entryUid);

        if (($entry['role'] ?? false) && $entry['role'] === 'assistant') {
            if (isset($entry['content']) && $this->outputAssistant) {
                $this->terminal->output($this->agentDisplayName . ': ' . $entry['content']);
            }

            if ($this->outputToolCalls && isset($entry['tool_calls'])) {
                foreach ($entry['tool_calls'] as $toolCall) {
                    $this->terminal->output($this->agentDisplayName . " calling tool: " . json_encode($toolCall['function']['name']));
                }
            }
        }

        return $uniqId;
    }

    public function getContext(): array
    {
        return $this->context->getContext();
    }

    public function getEntry(int $key): ?array
    {
        return $this->context->getEntry($key);
    }

    public function toArray(): array
    {
        return $this->context->toArray();
    }

    public function setContext(array $data): ContextInterface
    {
        return $this->context->setContext($data);
    }

    public function getSystemMessage(): ?SystemMessage
    {
        return $this->context->getSystemMessage();
    }

    public function setSystemMessage(SystemMessage $systemMessage): ContextInterface
    {
        $this->context->setSystemMessage($systemMessage);

        return $this;
    }

    public function updateEntry(array $entry, string $entryUid): string
    {
        return $this->context->updateEntry($entry, $entryUid);
    }
}