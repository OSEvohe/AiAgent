<?php

namespace App\Model\Tool;

use App\Model\Core\Message\ToolResultResponse;
use App\Model\Core\Tool\AITool;
use OpenAI\Responses\Chat\CreateResponseToolCall;

/**
 * WeatherTool class to get the current weather in a given location.
 * This tool simulates fetching weather data and returns a random temperature.
 */
class WeatherTool extends AITool
{
    public function __construct()
    {
        $name = 'get_current_weather';
        $description = 'Get the current weather in a given location';
        $parameters = [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'The city and state, e.g. San Francisco, CA',
                ],
                'unit' => [
                    'type' => 'string',
                    'enum' => ['celsius', 'fahrenheit'],
                    'default' => 'celsius',
                ],
            ],
            'required' => ['location'],
        ];

        parent::__construct($name, $description, $parameters);
    }

    public function execute(CreateResponseToolCall $toolCall): ToolResultResponse
    {
        $arguments = json_decode($toolCall->function->toArray()['arguments'], true);

        $result = [
            'text' => sprintf("The temperature in %s is %d",$arguments['location'], rand(10, 30)),
        ];

        return ToolResultResponse::fromArray([
            'tool_call_id' => $toolCall->id,
            'tool_name' => $this->getName(),
            'content' => json_encode($result),
        ]);
    }
}
