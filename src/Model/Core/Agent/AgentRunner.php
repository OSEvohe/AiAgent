<?php

namespace App\Model\Core\Agent;

use App\Model\Core\IOInterface;
use App\Model\Core\Message\Context;
use App\Model\Core\Message\UserMessage;
use App\Model\Core\Provider\OpenAIServiceInterface;
use App\Model\Core\Tool\ToolsHandler;
use Exception;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class AgentRunner
{
    private ToolsHandler $toolsHandler;

    private string $agentId;

    public function __construct(
        private OpenAIServiceInterface $openAIService,
        private Context $context,
        private string $agentName = '',
        private string $model = '', // Will use the default model if not specified
        private array $tools = [],
        private array $mcps = [],
        private ?IOInterface $io = null,
        private float $temperature = 0.15,
        private readonly string $tool_choice = 'auto',
        private readonly bool $parallel_tool_calls = true,
        private array $metadata = [],
        private readonly ?AgentRunner $prePromptProcessor = null,
    ) {
        $this->toolsHandler = new ToolsHandler($this->tools, $this->mcps, $this->io);
        $this->agentId = uniqid();

        if (empty($this->agentName)){
            $this->agentName = 'Agent_' . $this->agentId;
        }
    }

    public function getContext(): Context
    {
        return $this->context;
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

            $this->context->addEntry($this->createUserMessage($userInput)->toArray());

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
            'messages' => $this->context->toArray(),
            'temperature' => $this->temperature,
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
            $this->context->addEntry($choice->message->toArray());

            if ($choice->message->content) {
                $responseContent = $choice->message->content;
                $this->io?->output($this->agentName.': '.$responseContent);

            }

            if ($choice->message->toolCalls) {
                $toolResult = $this->toolsHandler->handleSingleToolCall($choice->message->toolCalls[0]);
                $this->context->addEntry($toolResult->toArray());
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
