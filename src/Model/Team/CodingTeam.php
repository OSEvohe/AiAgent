<?php

namespace App\Model\Team;

use App\Model\Agent;
use App\Model\IO\IOInterface;
use App\Model\MCP\McpClient;
use App\Model\SystemMessage;
use App\Model\Tool\AgentTool;
use App\Model\UserMessage;
use App\Service\OpenAIService;

class CodingTeam
{
    private Agent $agent;
    private string $systemMessage = 'You are a coding team that can help with programming tasks. You can use the Validator agent to validate yours action and the coding agent to coding.';

    public function initialize(IOInterface $io): void
    {
        $aiService = new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']);

        $validator = new Agent(
            openAIService: $aiService,
            model: '',
            tools: [],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/validator_agent.json'),
            io: $io,
        );

        $codingAgent = new Agent(
            openAIService: $aiService,
            model: '',
            tools: [],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/coding_agent.json'),
            io: $io,
        );

        $master = new Agent(
            openAIService: $aiService,
            model: '',
            tools: [
                new AgentTool($io, $validator, 'Validator', 'This agent can perform search tasks on internet'),
                new AgentTool($io, $codingAgent, 'CodingAgent', 'This agent can perform coding tasks'),
            ],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/orchestrate_agent.json'),
            io: $io,
        );




        $this->agent = $master;
    }

    /**
     * @throws \Exception
     */
    public function sendMessage(string $message): string
    {
        if (!isset($this->agent)) {
            throw new \Exception('Agent not initialized. Please call initialize() first.');
        }
        $this->agent->addToContext((new SystemMessage($this->systemMessage))->toArray());
        return $this->agent->sendUserMessage($message);
    }

    public function getContext(): array
    {
        return $this->agent->getContext();
    }
}
