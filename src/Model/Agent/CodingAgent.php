<?php

namespace App\Model\Agent;

use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Agent\UseAgentRunnerTrait;
use App\Model\Core\Agent\AgentInterface;
use App\Model\Core\Mcp\McpClient;
use App\Model\Core\Message\Context;
use App\Model\Core\Message\ContextInterface;
use App\Model\Core\Message\SystemMessage;
use App\Model\Core\Provider\OpenAIService;
use App\Model\Core\Tool\AgentTool;
use App\Model\Tool\InformUserTool;

class CodingAgent extends Agent
{
    use UseAgentRunnerTrait;

    private string $systemPromptsDir;
    private ?ContextInterface $contextManager = null;

    public function __construct()
    {
        $this->systemPromptsDir = $_ENV['AGENT_PROMPTS_DIR'] ?? '';
    }

    /**
     * @throws \Exception
     */
    public function initialize(ContextInterface $contextManager): void
    {
        $aiService = new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']);
        $this->contextManager = $contextManager;


        // Initialize contexts for each agent
        $this->contextManager->loadDiscussion();

        // create contexts for each agent if not exists
        if (is_null($this->contextManager->getContext('orchestrator_agent'))) {
            $this->contextManager->addContext(context: new Context(contextId: 'orchestrator_agent', context: [], isParent: true));
        }
        if (is_null($this->contextManager->getContext('search_agent'))) {
            $this->contextManager->addContext(context: new Context(contextId: 'search_agent', context: [], isParent: false));
        }
        if (is_null($this->contextManager->getContext('validator_agent'))) {
            $this->contextManager->addContext(context: new Context(contextId: 'validator_agent', context: [], isParent: false));
        }

        if (is_null($this->contextManager->getContext('coding_agent'))) {
            $this->contextManager->addContext(context: new Context(contextId: 'coding_agent', context: [], isParent: false));
        }

        // Load system prompts for each agent
        try {
            $validatorSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'validator_agent.txt');
            $codingAgentSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'coding_agent.txt');
            $searchAgentSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'search_agent.txt');
            $masterSystemMessage = $this->loadSystemPrompt($this->systemPromptsDir . 'orchestrator_agent.txt');
        } catch (\Exception $e) {
            throw new \Exception('Failed to load system prompts: ' . $e->getMessage());
        }

        // Add system messages to contexts
        $this->contextManager->getContext('orchestrator_agent')->setSystemMessage(new SystemMessage($masterSystemMessage));
        $this->contextManager->getContext('orchestrator_agent')->setIsParent();

        $this->contextManager->getContext('search_agent')->setSystemMessage(new SystemMessage($searchAgentSystemMessage));
        $this->contextManager->getContext('validator_agent')->setSystemMessage(new SystemMessage($validatorSystemMessage));
        $this->contextManager->getContext('coding_agent')->setSystemMessage(new SystemMessage($codingAgentSystemMessage));


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
                agentName: 'CodingAgentInterface',
                agentId: 'coding_agent',
                model: '',
                tools: [
                    new InformUserTool(),
                    new AgentTool($validator, 'validator_agent_tool', 'This agent as tool can review code quality by using online documentation,  it can also check git statuts, check for errors in a file'),
                ],
                mcps: McpClient::fromJsonConfig($_ENV['AGENT_CONFIG_DIR'] . 'coding_agent.json'),
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize CodingAgentInterface: ' . $e->getMessage());
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
