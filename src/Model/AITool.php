<?php

namespace App\Model;

abstract class AITool
{
    private string $type = 'function';
    protected array $function;

    public function __construct(string $name, string $description, array $properties, array $required = [])
    {
        $this->function = [
            'name' => $name,
            'description' => $description,
            'parameters' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
            ],
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

    abstract public function execute(array $arguments): array;
}
