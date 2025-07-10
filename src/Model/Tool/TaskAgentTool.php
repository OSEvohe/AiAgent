<?php

namespace App\Model\Tool;

use App\Model\Discussion;
use App\Model\IO\IOInterface;

use App\Service\OpenAIService;
use OpenAI\Responses\Chat\CreateResponseToolCall;

class TaskAgentTool extends AITool
{
    private Discussion $discussion;

    /**
     * TaskAgentTool constructor.
     * @param IOInterface $output
     * @param string $agentName
     * @param array $tools
     * @param array $mcps
     * @param string $systemMessage
     */
    public function __construct(IOInterface $output, string $agentName = 'TaskAgent', string $description = 'use an AI agent to perform a task', array $tools = [], array $mcps = [], string $systemMessage = '')
    {
        $name = $agentName . 'Tool';
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
            tools: $tools,
            mcps: $mcps,
        );

        if (!empty($systemMessage)) {
            $this->discussion->addToContext($this->discussion->createUserMessage($systemMessage)->toArray());
        }

        parent::__construct($name, $description, $parameters);
    }

    /**
     * Executes the task agent tool.
     * @param CreateResponseToolCall $toolCall
     * @return ToolResultResponse
     */
    public function execute(CreateResponseToolCall $toolCall): ToolResultResponse
    {
        $arguments = json_decode($toolCall->function->toArray()['arguments'], true);
        $task = $arguments['task'];

        $completedTask = $this->discussion->sendUserMessage($task);

        $result = [
            'result' => sprintf('%s', $completedTask),
        ];

        return ToolResultResponse::fromArray([
            'tool_call_id' => $toolCall->id,
            'tool_name' => $this->getName(),
            'content' => json_encode($result),
        ]);
    }
}
