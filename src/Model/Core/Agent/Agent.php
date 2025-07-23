<?php

namespace App\Model\Core\Agent;

use App\Model\Core\Message\ContextInterface;
use App\Model\Core\Provider\OpenAIServiceInterface;

class Agent implements AgentInterface
{
    public function __construct(
        private OpenAIServiceInterface $openAIService,
        private ContextInterface $contextManager,
        private string $agentName,
        private string $agentId,
        private string $model,
        private array $tools,
        private array $mcps,
        private bool $parallelToolCalls = false,
        private string $toolChoice = 'auto',
        private float $temperature = 0.7,
        private float $topP = 0.95,
        private float $minP = 0.01,
        private int $topk = 40,
        private float $repeatPenalty = 1.0
    ) {
    }

    public function isParallelToolCalls(): bool
    {
        return $this->parallelToolCalls;
    }

    public function getToolChoice(): string
    {
        return $this->toolChoice;
    }

    public function getTopP(): float
    {
        return $this->topP;
    }

    public function getMinP(): float
    {
        return $this->minP;
    }

    public function getOpenAIService(): OpenAIServiceInterface
    {
        return $this->openAIService;
    }

    public function getContextManager(): ContextInterface
    {
        return $this->contextManager;
    }

    public function getAgentName(): string
    {
        return $this->agentName;
    }

    public function getAgentId(): string
    {
        return $this->agentId;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getTools(): array
    {
        return $this->tools;
    }

    public function getMcps(): array
    {
        return $this->mcps;
    }

    public function initialize(AgentRunner $agentRunner): AgentRunner
    {
        $agentRunner->initialize($this);

        return $agentRunner;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getTopk(): int
    {
        return $this->topk;
    }

    public function getRepeatPenalty(): float
    {
        return $this->repeatPenalty;
    }
}