<?php

namespace App\Model\Core\Agent;

use App\Model\Core\IOInterface;
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
        private string $agentName = '',
        private string $model = '', // Will use the default model if not specified
        private string $systemMessage = 'You are an agent that can help with various tasks. Use the tools provided to assist in completing tasks.',
        private array $tools = [],
        private array $mcps = [],
        private ?IOInterface $io = null,
        private array $context = [],
        private float $temperature = 0.15,
        private int $max_output_tokens = 5000,
        private string $tool_choice = 'auto',
        private bool $parallel_tool_calls = true,
        private bool $store = true,
        private array $metadata = [],
        private ?AgentRunner $prePromptProcessor = null,
    ) {
        $this->toolsHandler = new ToolsHandler($this->tools, $this->mcps, $this->io);
        $this->agentId = uniqid();

        if (empty($this->agentName)){
            $this->agentName = 'Agent_' . $this->agentId;
        }

        // Ensure the system message is always the first message in the context,
        // unless the context already starts with a system message.
        if (!empty($this->context) && $this->context[0]['role'] !== 'system') {
            $this->context = array_merge([['role' => 'system', 'content' => $this->systemMessage]], $this->context);
        } elseif (empty($this->context)) {
            $this->context[] = ['role' => 'system', 'content' => $this->systemMessage];
        }

    }

    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $message
     * @return void
     */
    public function addToContext(array $message = []): void
    {
        if (!empty($message)) {
            $this->context[] = $message;
        }
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
                $userInput = $this->prePromptProcessor->sendUserMessage($userInput);
            }

            $this->context[] = $this->createUserMessage($userInput)->toArray();
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
            'messages' => $this->context,
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
            $this->context[] = $choice->message->toArray();

            if ($choice->message->content) {
                $responseContent = $choice->message->content;
                $this->io?->output($this->agentName.': '.$responseContent);

            }

            if ($choice->message->toolCalls) {
                $toolResult = $this->toolsHandler->handleSingleToolCall($choice->message->toolCalls[0]);
                $this->context[] = $toolResult->toArray();
                $responseContent = $this->processResponse($step + 1);
                /*if ($step === 0) {
                    $this->context[] = $this->createUserMessage('If task is not complete continue with the next step. If task is complete ask for further instructions. If you are unsure about the next step, please ask for clarification.')->toArray();
                    $this->processResponse();
                }*/
            }
        }

        return $responseContent;
    }

    public function processContext(): CreateResponse|StreamResponse
    {
        return $this->openAIService->sendToLlm($this->toArray());
    }
}
