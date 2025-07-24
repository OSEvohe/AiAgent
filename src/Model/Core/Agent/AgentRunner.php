<?php

namespace App\Model\Core\Agent;

use App\Model\Core\Message\ContextInterface;
use App\Model\Core\Message\UserMessage;
use App\Model\Core\Tool\ToolsHandler;
use Exception;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class AgentRunner
{
    private ToolsHandler $toolsHandler;
    private AgentInterface $agent;
    private ContextInterface $contextManager;

    public function __construct()
    {
    }


    public function createUserMessage(string $userInput): UserMessage
    {
        return new UserMessage($userInput);
    }

    public function sendUserMessage(string $userInput, string $messageUid): string
    {
        try {
            $this->contextManager->addEntry($this->createUserMessage($userInput)->toArray(), $messageUid);

            return $this->processResponse();
        } catch (Exception $e) {
            return 'Error processing response: ' . $e->getMessage();
        }
    }

    public function toArray(): array
    {
        return [
            'model' => $this->agent->getModel(),
            'tools' => array_map(fn($tool) => $tool->toArray(), $this->toolsHandler->getTools()),
            'messages' => $this->contextManager->toArray(),
            'temperature' => $this->agent->getTemperature(),
            'top_p' => $this->agent->getTopP(),
            'min_p' => $this->agent->getMinP(),
            'top_k' => $this->agent->getTopk(),
            'repeat_penalty' => $this->agent->getRepeatPenalty(),
            'tool_choice' => $this->agent->getToolChoice(),
            'parallel_tool_calls' => $this->agent->isParallelToolCalls(),
        ];
    }

    /**
     * @throws Exception
     */
    public function processResponse(int $step = 0): string
    {
        // first create an empty entry in the context so that we can update it later, allowing UI to show a pending response and allowing stream responses (later)
        $entryUid = $this->contextManager->addEntry(
            entry: ['role' => 'assistant', 'content' => 'Processing...', 'tool_calls' => []],
            entryUid: uniqid()
        );

        $response = $this->processContext();
        $responseContent = '';

        foreach ($response->choices as $choice) {
            $this->contextManager->updateEntry($choice->message->toArray(), $entryUid);

            if ($choice->message->content) {
                $responseContent = $choice->message->content;
            }

            if ($choice->message->toolCalls && $step <= 99) {
                try {
                    $toolResult = $this->toolsHandler->handleSingleToolCall($choice->message->toolCalls[0]);
                    $this->contextManager->addEntry(entry: $toolResult->toArray());
                    $responseContent = $this->processResponse($step + 1);
                } catch (Exception $e) {
                    return 'Error executing tool: ' . $e->getMessage();
                }
            }
        }

        return $responseContent;
    }

    public function processContext(): CreateResponse|StreamResponse
    {
        return $this->agent->getOpenAIService()->sendToLlm($this->toArray());
    }

    public function initialize(Agent $agent): void
    {
        $this->agent = $agent;
        $this->toolsHandler = new ToolsHandler($agent->getTools(), $agent->getMcps());
        $this->contextManager = $agent->getContextManager();
    }
}