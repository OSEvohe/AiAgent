<?php

namespace App\Model\Tool;

class ToolResultResponse
{
    private function __construct(
        private readonly string $toolCallId,
        private readonly string $toolName = '',
        private readonly ?string $content
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            toolCallId: $data['tool_call_id'] ?? '',
            toolName: $data['tool_name'] ?? '',
            content: $data['content'] ?? null
        );
    }

    public function getToolCallId(): string
    {
        return $this->toolCallId;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function toArray(): array
    {
        return [
            'role' => 'tool',
            'name' => $this->toolName,
            'tool_call_id' => $this->toolCallId,
            'content' => $this->content,
        ];
    }
}
