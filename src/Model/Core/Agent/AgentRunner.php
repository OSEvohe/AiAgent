<?php

namespace App\Model\Core\Agent;

use App\Model\Core\Message\Context;
use App\Model\Core\Message\ContextInterface;
use App\Model\Core\Message\UserMessage;
use App\Model\Core\Provider\OpenAIServiceInterface;
use App\Model\Core\Tool\ToolsHandler;
use Exception;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class AgentRunner
{
    private ToolsHandler $toolsHandler;

    public function __construct(
        private OpenAIServiceInterface $openAIService,
        private readonly ContextInterface $contextManager,
        private readonly string $agentName,
        private string $agentId,
        private string $model = '', // Will use the default model if not specified
        private array $tools = [],
        private array $mcps = [],
        private float $temperature = 0.15,
        private readonly string $tool_choice = 'auto',
        private readonly bool $parallel_tool_calls = true,
        private array $metadata = [],
        private readonly ?AgentRunner $prePromptProcessor = null
    ) {
        $this->toolsHandler = new ToolsHandler($this->tools, $this->mcps);
    }

    public function getContext(): Context
    {
        return $this->contextManager->getContext($this->agentId);
    }

    public function getTools(): array
    {
        return $this->tools;
    }

    public function createUserMessage(string $userInput): UserMessage
    {
        return new UserMessage($userInput);
    }

    public function sendUserMessage(string $userInput): string
    {
        try {
            if ($this->prePromptProcessor) {
                $userInput = $this->prePromptProcessor->sendUserMessage("prepare this message: " . $userInput);
            }

            $this->contextManager->addEntry($this->agentId, $this->createUserMessage($userInput)->toArray());

            return $this->processResponse();
        } catch (Exception $e) {
            return 'Error processing response: ' . $e->getMessage();
        }
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'tools' => array_map(fn($tool) => $tool->toArray(), $this->toolsHandler->getTools()),
            'messages' => $this->contextManager->getContext($this->agentId)->toArray(),
            'temperature' => $this->temperature,
            'top_p' => 0.95,
            'min_p' => 0.01,
            'tool_choice' => $this->tool_choice,
            'parallel_tool_calls' => $this->parallel_tool_calls,
        ];
    }

    /**
     * @throws Exception
     */
    public function processResponse(int $step = 0): string
    {
        $response = $this->processContext();
        $responseContent = '';

        foreach ($response->choices as $choice) {
            $this->contextManager->addEntry($this->agentId, $choice->message->toArray());

            if ($choice->message->content) {
                $responseContent = $choice->message->content;
            }

            if ($choice->message->toolCalls) {
                $toolResult = $this->toolsHandler->handleSingleToolCall($choice->message->toolCalls[0]);
                $this->contextManager->addEntry($this->agentId, $toolResult->toArray());
                $responseContent = $this->processResponse($step + 1);
            }
        }

        return $responseContent;
    }

    public function processContext(): CreateResponse|StreamResponse
    {
        return $this->openAIService->sendToLlm($this->toArray());
    }
}