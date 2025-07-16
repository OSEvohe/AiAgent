<?php

namespace App\Model\Core\Tool;

use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\IOInterface;
use App\Model\Core\Message\ToolResultResponse;
use OpenAI\Responses\Chat\CreateResponseToolCall;

class AgentTool extends AITool
{
    /**
     * AgentTool constructor.
     * @param IOInterface $output
     * @param AgentRunner $agent
     * @param string $agentName
     * @param string $description
     * @param array $tools
     * @param array $mcps
     * @param string $systemMessage
     */
    public function __construct(private readonly AgentRunner $agent, string $agentName = 'TaskAgent', string $description = 'you can use this agent to perform a task', array $tools = [], array $mcps = [], string $systemMessage = '')
    {
        $name = $agentName;
        $parameters = [
            'type' => 'object',
            'properties' => [
                'task' => ['type' => 'string', 'description' => 'The task to be performed'],
            ],
            'required' => ['task']
        ];


        if (!empty($systemMessage)) {
            $this->agent->addToContext($this->agent->createUserMessage($systemMessage)->toArray());
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

        $completedTask = $this->agent->sendUserMessage($task);

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
