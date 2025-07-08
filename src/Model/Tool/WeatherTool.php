<?php

namespace App\Model\Tool;

use App\Model\AITool;

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

    public function execute(array $arguments): array
    {
        // For now, we'll just return a dummy array.
        // In the future, we would call a real weather API here.
        return ['text' => sprintf("The weather in %s is sunny.", $arguments['location'])];
    }
}
