<?php

namespace App\Model;

use App\Model\IO\IOInterface;
use App\Model\Tool\ToolsHandler;
use App\Service\OpenAIServiceInterface;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class Discussion
{
    private ToolsHandler $toolsHandler;

    public function __construct(
        private OpenAIServiceInterface $openAIService,
        private string $model,
        private IOInterface $io,
        private array $context = [],
        private array $tools = [],
        private float $temperature = 0.8,
        private int $max_output_tokens = 5000,
        private string $tool_choice = 'auto',
        private bool $parallel_tool_calls = false,
        private bool $store = true,
        private array $metadata = [],
    ) {
        $this->toolsHandler = new ToolsHandler($this->tools, $this->io);
    }

    public function getContext(): array
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

    public function sendUserMessage(string $userInput): void
    {
        $this->context[] = $this->createUserMessage($userInput)->toArray();
        $this->processResponse();
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'tools' => array_map(fn($tool) => $tool->toArray(), $this->tools),
            'messages' => $this->context,
            'temperature' => $this->temperature,
            'max_output_tokens' => $this->max_output_tokens,
            'tool_choice' => $this->tool_choice,
            'parallel_tool_calls' => $this->parallel_tool_calls,
            'store' => $this->store,
            'metadata' => $this->metadata,
        ];
    }

    private function processResponse(): void
    {
        $response = $this->processContext();

        foreach ($response->choices as $choice) {
            $this->context[] = $choice->message->toArray();
            if ($choice->message->content) {
                $this->io->output($choice->message->content);
            }

            if ($choice->message->toolCalls) {
                $toolsResult = $this->toolsHandler->handleToolCalls($choice->message->toolCalls);
                array_push($this->context, ...array_map(fn($result) => $result->toArray(), $toolsResult));
            }

            if ($choice->finishReason !== 'stop') {
                $this->processResponse();
            }
        }
    }

    private function processContext(): CreateResponse|StreamResponse
    {
        dump($this->toArray());
        return $this->openAIService->sendToLlm($this->toArray());
    }
}
