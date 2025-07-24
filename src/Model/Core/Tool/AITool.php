<?php

namespace App\Model\Core\Tool;

use App\Model\Core\Message\ToolResultResponse;
use OpenAI\Responses\Chat\CreateResponseToolCall;

abstract class AITool
{
    /** @var array<string, mixed> $function */
    protected array $function;

    private string $type = 'function';

    /**
     * @param string $name
     * @param string $description
     * @param array<string, mixed> $parameters
     */
    public function __construct(string $name, string $description, array $parameters)
    {
       $this->function = [
            'name' => $name,
            'description' => $description,
            'parameters' => $parameters,
        ];
    }

    abstract public function execute(CreateResponseToolCall $toolCall): ToolResultResponse;

    /**
     * Convert the tool to an array representation.
     *
     * @return array<string, mixed>
     */
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
}
