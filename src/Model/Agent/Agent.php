<?php

namespace App\Model\Agent;

use App\Model\Core\Agent\AgentInterface;
use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Agent\UseAgentRunnerTrait;
use App\Model\Core\Message\ContextInterface;

class Agent implements AgentInterface
{
    use UseAgentRunnerTrait;

    public function initialize(ContextInterface $contextManager): void
    {
        try {
            $validator = new AgentRunner(
                openAIService: $this->aiService,
                contextManager: $this->contextManager,
                agentName: $this->agentName,
                agentId: $this->agentId,
                model: $this->model,
                tools: $this->tools,
                mcps: $this->mcps,
            //mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'validator_agent.json'),
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize ' . $this->agentName . ': ' . $e->getMessage());
        }
    }

    public function sendMessage(string $message)
    {
        // TODO: Implement sendMessage() method.
    }
}