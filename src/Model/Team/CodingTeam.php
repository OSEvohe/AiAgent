<?php

namespace App\Model\Team;

use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Agent\MessageContextTrait;
use App\Model\Core\Agent\Team;
use App\Model\Core\IOInterface;
use App\Model\Core\Mcp\McpClient;
use App\Model\Core\Provider\OpenAIService;
use App\Model\Core\Tool\AgentTool;
use App\Model\Tool\InformUserTool;

class CodingTeam implements Team
{
    use MessageContextTrait;

    private string $systemPromptsDir;

    public function __construct()
    {
        $this->systemPromptsDir = $_ENV['AGENT_PROMPTS_DIR'] ?? '';
    }

    public function initialize(IOInterface $io): void
    {
        $aiService = new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']);

        try {
            $validatorSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'validator_agent.txt');
        } catch (\Exception $e) {
            $io->error('Failed to load Validator system message: ' . $e->getMessage());
            return;
        }

        try {
            $codingAgentSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'coding_agent.txt');
        } catch (\Exception $e) {
            $io->error('Failed to load CodingAgent system message: ' . $e->getMessage());
            return;
        }

        try {
            $searchAgentSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'search_agent.txt');
        } catch (\Exception $e) {
            $io->error('Failed to load SearchAgent system message: ' . $e->getMessage());
            return;
        }

        try {
            $masterSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'orchestrator_agent.txt');
        } catch (\Exception $e) {
            $io->error('Failed to load Orchestrator system message: ' . $e->getMessage());
            return;
        }

        try {
            $validator = new AgentRunner(
                openAIService: $aiService,
                agentName: 'Validator',
                model: '',
                systemMessage: $validatorSystemMessage,
                tools: [new InformUserTool($io)],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'validator_agent.json'),
                io: null
            );
        } catch (\Exception $e) {
            $io->error('Failed to initialize Validator: ' . $e->getMessage());
            return;
        }

        try {
            $codingAgent = new AgentRunner(
                openAIService: $aiService,
                agentName: 'CodingAgent',
                model: '',
                systemMessage: $codingAgentSystemMessage,
                tools: [
                    new InformUserTool($io),
                    new AgentTool($io, $validator, 'validator_agent_tool', 'This agent as tool can review code quality by using online documentation,  it can also check git statuts, check for errors in a file'),
                ],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'coding_agent.json'),
                io: null
            );
        } catch (\Exception $e) {
            $io->error('Failed to initialize CodingAgent: ' . $e->getMessage());
            return;
        }

        try {
            $search_agent = new AgentRunner(
                openAIService: $aiService,
                agentName: 'SearchAgent',
                model: '',
                systemMessage: $searchAgentSystemMessage,
                tools: [
                    new InformUserTool($io),
                ],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'search_agent.json'),
                io: null
            );
        } catch (\Exception $e) {
            $io->error('Failed to initialize SearchAgent: ' . $e->getMessage());
            return;
        }

        try {
            $this->agent = new AgentRunner(
                openAIService: $aiService,
                agentName: 'Orchestrator',
                model: '',
                systemMessage: $masterSystemMessage,
                tools: [
                    new InformUserTool($io),
                    new AgentTool($io, $validator, 'validator_agent_tool', 'This agent as tool can review code quality by using online documentation,  it can also check git statuts, check for errors in a file'),
                    new AgentTool($io, $codingAgent, 'coding_agent_tool', 'this agent as too can read, write, modify or create any code files, this is your primary tool for coding tasks'),
                    new AgentTool(
                        $io,
                        $search_agent,
                        'search_agent_tool',
                        'This agent as tool can search online documentation and resources to find information related to coding tasks. Use this tool to gather information, examples, and best practices for coding tasks.'
                    )
                ],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'orchestrate_agent.json'),
                io: $io
            );
        } catch (\Exception $e) {
            $io->error('Failed to initialize Orchestrator: ' . $e->getMessage());
            return;
        }
    }

    private function loadSystemPrompt(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception('System prompt file not found: ' . $filePath);
        }
        return file_get_contents($filePath);
    }
}
