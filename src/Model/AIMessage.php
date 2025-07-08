<?php

namespace App\Model;

class AIMessage
{
    private string $model;
    private array $tools = [];
    private string $input;
    private float $temperature = 0.7;
    private int $max_output_tokens = 150;
    private string $tool_choice = 'auto';
    private bool $parallel_tool_calls = true;
    private bool $store = true;
    private array $metadata = [];

    public function __construct(string $input, string $model = 'devstral-small-2505@q5_k_xl')
    {
        $this->input = $input;
        $this->model = $model;
    }

    public function setTools(array $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setMaxOutputTokens(int $max_output_tokens): self
    {
        $this->max_output_tokens = $max_output_tokens;
        return $this;
    }

    public function setToolChoice(string $tool_choice): self
    {
        $this->tool_choice = $tool_choice;
        return $this;
    }

    public function setParallelToolCalls(bool $parallel_tool_calls): self
    {
        $this->parallel_tool_calls = $parallel_tool_calls;
        return $this;
    }

    public function setStore(bool $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'tools' => $this->tools,
            'input' => $this->input,
            'temperature' => $this->temperature,
            'max_output_tokens' => $this->max_output_tokens,
            'tool_choice' => $this->tool_choice,
            'parallel_tool_calls' => $this->parallel_tool_calls,
            'store' => $this->store,
            'metadata' => $this->metadata,
        ];
    }
}

