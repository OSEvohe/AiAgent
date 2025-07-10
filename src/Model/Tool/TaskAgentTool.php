<?php

namespace App\Model\Tool;

use App\Model\Discussion;
use App\Model\IO\IOInterface;
use App\Model\IO\Terminal;
use App\Model\MCP\Jetbrains;
use App\Service\OpenAIService;
use OpenAI\Responses\Chat\CreateResponseToolCall;

class TaskAgentTool extends AITool
{
    private Discussion $discussion;

    public function __construct(IOInterface $output, string $agentName = 'TaskAgent')
    {
        $name = $agentName . 'Tool';
        $description = 'Initializes a new agent to perform a task';
        $parameters = [
            'type' => 'object',
            'properties' => [
                'task' => ['type' => 'string', 'description' => 'The task to be performed'],
            ],
            'required' => ['task']
        ];

        $this->discussion = new Discussion(
            openAIService: new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']),
            model: '',
            io: $output,
            tools: [],
            mcps: [new Jetbrains()],
        );

        parent::__construct($name, $description, $parameters);
    }

    public function execute(CreateResponseToolCall $toolCall): ToolResultResponse
    {
        $arguments = json_decode($toolCall->function->toArray()['arguments'], true);
        $task = $arguments['task'];

        // Initialize the agent (implementation depends on your application logic)
        $result = [
            'result' => sprintf('%s', $task),
        ];

        return ToolResultResponse::fromArray([
            'tool_call_id' => $toolCall->id,
            'tool_name' => $this->getName(),
            'content' => json_encode($result),
        ]);
    }
}
