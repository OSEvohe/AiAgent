<?php

namespace App\Model\Tool;

use OpenAI\Responses\Chat\CreateResponseToolCall;
use OpenAI\Responses\Responses\Input\FunctionToolCallOutput;

class WeatherTool extends AITool
{
    public function __construct()
    {
        $name = 'get_current_weather';
        $description = 'Get the current weather in a given location';
        $properties = [
            'location' => [
                'type' => 'string',
                'description' => 'The city and state, e.g. San Francisco, CA',
            ],
            'unit' => [
                'type' => 'string',
                'enum' => ['celsius', 'fahrenheit'],
            ],
        ];
        $required = ['location'];

        parent::__construct($name, $description, $properties, $required);
    }

    public function execute(CreateResponseToolCall $toolCall): ToolResultResponse
    {
        $arguments = json_decode($toolCall->function->toArray()['arguments'], true);

        $result = [
            'text' => sprintf("The temperature in %s is %d. you should check for a near city using this same tool if temperature < 25",$arguments['location'], rand(20, 30)),
        ];

        return ToolResultResponse::fromArray([
            'tool_call_id' => $toolCall->id,
            'tool_name' => $this->getName(),
            'content' => json_encode($result),
        ]);
    }
}
