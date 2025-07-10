<?php

namespace App\Model;

use App\Model\IO\IOInterface;
use App\Model\Tool\ToolsHandler;
use App\Service\OpenAIServiceInterface;
use Exception;
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
        private array $mcps = [],
        private float $temperature = 0.15,
        private int $max_output_tokens = 5000,
        private string $tool_choice = 'auto',
        private bool $parallel_tool_calls = false,
        private bool $store = true,
        private array $metadata = [],
    ) {
        $this->toolsHandler = new ToolsHandler($this->tools, $this->mcps, $this->io);
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
        try {
            $preInput = "Complete the following task step by step. Please summarize the current state and do not ask for further instructions unless asked to do so.";
            $this->context[] = $this->createUserMessage($preInput)->toArray();
            $this->context[] = $this->createUserMessage($userInput)->toArray();
            $this->processResponse();
        } catch (Exception $e) {
            $this->io->output('Error processing response: ' . $e->getMessage());
        }
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'tools' => array_map(fn($tool) => $tool->toArray(), $this->toolsHandler->getTools()),
            'messages' => $this->context,
            'temperature' => $this->temperature,
            //'max_output_tokens' => $this->max_output_tokens,
            'tool_choice' => $this->tool_choice,
            'parallel_tool_calls' => $this->parallel_tool_calls,
            //'store' => $this->store,
            //'metadata' => $this->metadata,
        ];
    }

    /**
     * @throws Exception
     */
    private function processResponse(int $step = 0): void
    {
        $response = $this->processContext();

        foreach ($response->choices as $choice) {
            $this->context[] = $choice->message->toArray();

            if ($choice->message->content) {
                $this->io->output($choice->message->content);
            }

            if ($choice->message->toolCalls) {
                $toolResult = $this->toolsHandler->handleSingleToolCall($choice->message->toolCalls[0]);
                $this->context[] = $toolResult->toArray();
                $this->processResponse($step + 1);

                if ($step === 0) {
                    $this->sendUserMessage('If task is not complete continue with the next step. If task is complete ask for further instructions. If you are unsure about the next step, please ask for clarification.');
                }

            }
        }
    }

    private function processContext(): CreateResponse|StreamResponse
    {
        return $this->openAIService->sendToLlm($this->toArray());
    }
}
