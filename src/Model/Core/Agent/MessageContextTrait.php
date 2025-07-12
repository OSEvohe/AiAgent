<?php

namespace App\Model\Core\Agent;

trait MessageContextTrait
{
    private AgentRunner $agent;
    /**
     * @throws \Exception
     */
    public function sendMessage(string $message): string
    {
        if (!isset($this->agent)) {
            throw new \Exception('AgentRunner instance not found in context. Please ensure the team is initialized by calling the initialize() method before sending messages.');
        }

        return $this->agent->sendUserMessage($message);
    }

    /**
     * @throws \Exception
     */
    public function getContext(): array
    {
        if (!isset($this->agent)) {
            throw new \Exception('AgentRunner instance not found in context. Please ensure the team is initialized by calling the initialize() method before accessing the context.');
        }

        return $this->agent->getContext();
    }
}
