<?php

namespace App\Model;

use App\Model\IO\IOInterface;
use App\Model\Tool\ToolsHandler;
use App\Service\OpenAIServiceInterface;
use Exception;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class Agent
{
    private ToolsHandler $toolsHandler;

    private string $agentId;

    public function __construct(
        private OpenAIServiceInterface $openAIService,
        private string $model,
        private array $tools = [],
        private array $mcps = [],
        private IOInterface $io,
        private array $context = [],
        private float $temperature = 0.15,
        private int $max_output_tokens = 5000,
        private string $tool_choice = 'auto',
        private bool $parallel_tool_calls = true,
        private bool $store = true,
        private array $metadata = [],
    ) {
        $this->toolsHandler = new ToolsHandler($this->tools, $this->mcps, $this->io);
        $this->agentId = uniqid('agent_', true);
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
