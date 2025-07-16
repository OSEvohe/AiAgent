<?php

namespace App\Model\Team;

use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Agent\MessageContextTrait;
use App\Model\Core\Agent\Team;
use App\Model\Core\Mcp\McpClient;
use App\Model\Core\Message\Context;
use App\Model\Core\Message\SystemMessage;
use App\Model\Core\Provider\OpenAIService;
use App\Model\Core\Team\ContextManagerInterface;
use App\Model\Core\Tool\AgentTool;
use App\Model\Tool\InformUserTool;

class CodingTeam implements Team
{
    use MessageContextTrait;

    private string $systemPromptsDir;
    private ?ContextManagerInterface $contextManager = null;

    public function __construct()
    {
        $this->systemPromptsDir = $_ENV['AGENT_PROMPTS_DIR'] ?? '';
    }

    /**
     * @throws \Exception
     */
    public function initialize(ContextManagerInterface $contextManager): void
    {
        $aiService = new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']);
        $this->contextManager = $contextManager;

        if (empty($this->contextManager->getContexts())) {
            dump('No contexts found, initializing default contexts.');
            $this->contextManager->addContext(context: new Context('validator_agent', context: []));
            $this->contextManager->addContext(context: new Context(contextId: 'coding_agent', context: []));
            $this->contextManager->addContext(context: new Context(contextId: 'search_agent', context: []));
            $this->contextManager->addContext(context: new Context(contextId: 'orchestrator_agent', context: [], isParent: true));
            try {
                $validatorSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'validator_agent.txt');
                $this->contextManager->getContext('validator_agent')->addEntry((new SystemMessage($validatorSystemMessage))->toArray());

                $codingAgentSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'coding_agent.txt');
                $this->contextManager->getContext('coding_agent')->addEntry((new SystemMessage($codingAgentSystemMessage))->toArray());

                $searchAgentSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'search_agent.txt');
                $this->contextManager->getContext('search_agent')->addEntry((new SystemMessage($searchAgentSystemMessage))->toArray());

                $masterSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'orchestrator_agent.txt');
                $this->contextManager->getContext('orchestrator_agent')->addEntry((new SystemMessage($masterSystemMessage))->toArray());
            } catch (\Exception $e) {
                throw new \Exception('Failed to load system prompts: ' . $e->getMessage());
            }
        }


        try {
            $validator = new AgentRunner(
                openAIService: $aiService,
                contextManager: $this->contextManager,
                agentName: 'ReviewerAgent',
                agentId: 'validator_agent',
                model: '',
                tools: [new InformUserTool()],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'validator_agent.json'),
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize ValidatorAgent: ' . $e->getMessage());
        }

        try {
            $codingAgent = new AgentRunner(
                openAIService: $aiService,
                contextManager: $this->contextManager,
                agentName: 'CodingAgent',
                agentId: 'coding_agent',
                model: '',
                tools: [
                    new InformUserTool(),
                    new AgentTool($validator, 'validator_agent_tool', 'This agent as tool can review code quality by using online documentation,  it can also check git statuts, check for errors in a file'),
                ],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'coding_agent.json'),
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize CodingAgent: ' . $e->getMessage());
        }

        try {
            $search_agent = new AgentRunner(
                openAIService: $aiService,
                contextManager: $this->contextManager,
                agentName: 'SearchAgent',
                agentId: 'search_agent',
                model: '',
                tools: [
                    new InformUserTool(),
                ],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'search_agent.json'),
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize SearchAgent: ' . $e->getMessage());
        }

        try {
            $this->agent = new AgentRunner(
                openAIService: $aiService,
                contextManager: $this->contextManager,
                agentName: 'Orchestrator',
                agentId: 'orchestrator_agent',
                model: '',
                tools: [
                    new InformUserTool(),
                    new AgentTool(
                        agent: $validator,
                        agentName: 'validator_agent_tool',
                        description: 'This agent as tool can review code quality by using online documentation,  it can also check git statuts, check for errors in a file'
                    ),
                    new AgentTool(
                        agent: $codingAgent,
                        agentName: 'coding_agent_tool',
                        description: 'this agent as too can read, write, modify or create any code files, this is your primary tool for coding tasks'
                    ),
                    new AgentTool(
                        agent: $search_agent,
                        agentName: 'search_agent_tool',
                        description: 'This agent as tool can search online documentation and resources to find information. You must specify in the task argument to do a deep search if it is required'
                    )
                ],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'orchestrate_agent.json'),
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize OrchestratorAgent: ' . $e->getMessage());
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
