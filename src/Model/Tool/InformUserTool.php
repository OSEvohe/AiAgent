<?php

namespace App\Model\Tool;

use App\Model\Core\Message\ToolResultResponse;
use App\Model\Core\Tool\AITool;
use App\Model\Core\IOInterface;
use OpenAI\Responses\Chat\CreateResponseToolCall;

/**
 * InformUserTool class to inform the user with a custom message.
 * This tool simulates providing information and returns a predefined message.
 */
class InformUserTool extends AITool
{
    public function __construct()
    {
        $name = 'inform_user';
        $description = 'Inform the user with a custom message';
        $parameters = [
            'type' => 'object',
            'properties' => [
                'message' => [
                    'type' => 'string',
                    'description' => 'The message to inform the user',
                ],
            ],
            'required' => ['message'],
        ];

        parent::__construct($name, $description, $parameters);
    }

    public function execute(CreateResponseToolCall $toolCall): ToolResultResponse
    {
        $arguments = json_decode($toolCall->function->toArray()['arguments'], true);

        $message = $arguments['message'];

        $result = [
            'text' => $message,
        ];

        return ToolResultResponse::fromArray([
            'tool_call_id' => $toolCall->id,
            'tool_name' => $this->getName(),
            'content' => json_encode($result),
        ]);
    }
}
