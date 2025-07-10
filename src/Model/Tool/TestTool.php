<?php

namespace App\Model\Tool;

use OpenAI\Responses\Chat\CreateResponseToolCall;

class TestTool extends AITool
{
    public function __construct()
    {
        $name = 'get_test';
        $description = 'Get the current weather in a given location';
        $parameters = [
            'type' => 'object',
            'properties' => [
                'none' => 'no parameters'
            ]
        ];

        parent::__construct($name, $description, $parameters);
    }

    public function execute(CreateResponseToolCall $toolCall): ToolResultResponse
    {
        $arguments = json_decode($toolCall->function->toArray()['arguments'], true);

        $result = [
            'text' => sprintf("The temperature in %s is %d", $arguments['location'], rand(10, 30)),
        ];

        return ToolResultResponse::fromArray([
            'tool_call_id' => $toolCall->id,
            'tool_name' => $this->getName(),
            'content' => json_encode($result),
        ]);
    }
}
