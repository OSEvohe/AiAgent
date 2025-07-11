<?php

namespace App\Model\Team;

use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Agent\MessageContextTrait;
use App\Model\Core\Agent\Team;
use App\Model\Core\IOInterface;
use App\Model\Core\Mcp\McpClient;
use App\Model\Core\Provider\OpenAIService;
use App\Model\Core\Tool\AgentTool;

class CodingTeam implements Team
{
    use MessageContextTrait;


    public function initialize(IOInterface $io): void
    {
        $aiService = new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']);

        $validator = new AgentRunner(
            openAIService: $aiService,
            model: '',
            systemMessage: "Your role is to check that the actions and decisions made by the coding agent are correct and valid. You will receive messages from the coding agent and you should validate them.",
            tools: [],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/validator_agent.json'),
            io: $io,
        );

        $codingAgent = new AgentRunner(
            openAIService: $aiService,
            model: '',
            systemMessage: "Your role is to perform coding tasks. You will receive messages from the user and you should perform the coding tasks. You can use the Validator agent to validate your actions and decisions.",
            tools: [],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/coding_agent.json'),
            io: $io,
        );

        $this->agent = new AgentRunner(
            openAIService: $aiService,
            model: '',
            systemMessage: 'You are a coding team that can help with programming tasks. You can use the Validator agent to validate yours action and the coding agent to coding.',

            tools: [
                new AgentTool($io, $validator, 'Validator', 'This agent can validate actions and decisions'),
                new AgentTool($io, $codingAgent, 'CodingAgent', 'This agent can perform coding tasks'),
            ],
            mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . '/orchestrate_agent.json'),
            io: $io,
        );
    }
}
