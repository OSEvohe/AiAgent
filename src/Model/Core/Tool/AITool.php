<?php

namespace App\Model\Core\Tool;

use App\Model\Core\Message\ToolResultResponse;
use OpenAI\Responses\Chat\CreateResponseToolCall;

abstract class AITool
{
    private string $type = 'function';
    protected array $function;

    public function __construct(string $name, string $description, array $parameters)
    {
        $this->function = [
            'name' => $name,
            'description' => $description,
            'parameters' => $parameters,
        ];
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'function' => $this->function,
        ];
    }

    public function getName(): string
    {
        return $this->function['name'];
    }

    abstract public function execute(CreateResponseToolCall $toolCall): ToolResultResponse;
}
