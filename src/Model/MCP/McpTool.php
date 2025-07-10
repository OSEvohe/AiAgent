<?php

namespace App\Model\MCP;

use App\Model\Tool\AITool;
use App\Model\Tool\ToolResultResponse;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use PhpMcp\Client\Client;
use PhpMcp\Client\Model\Definitions\ToolDefinition;
use Throwable;

class McpTool extends AITool
{
    public function __construct(ToolDefinition $tool, private readonly McpClient $server)
    {
        parent::__construct(
            name: $tool->name,
            description: $tool->description,
            parameters: [
                'type' => $tool->inputSchema['type'] ?? 'object',
                'properties' => ($tool->inputSchema['properties'] ?? []) ?: ['none' => 'no parameters'],
                'required' => $tool->inputSchema['required'] ?? [],
            ]
        );
    }

    public function execute(CreateResponseToolCall $toolCall): ToolResultResponse
    {
        $arguments = json_decode($toolCall->function->toArray()['arguments'], true);

        try {

                $result = $this->server->callTool($this->getName(), $arguments);

                return ToolResultResponse::fromArray([
                    'tool_call_id' => $toolCall->id,
                    'tool_name' => $this->getName(),
                    'content' => json_encode($result->content),
                ]);

        } catch (Throwable $e) {
            return ToolResultResponse::fromArray([
                'tool_call_id' => $toolCall->id,
                'tool_name' => $this->getName(),
                'content' => json_encode(['error' => $e->getMessage()]),
            ]);
        }
    }
}
