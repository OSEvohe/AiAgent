<?php

namespace App\Model\Core\Agent;

use App\Model\Core\Message\ContextInterface;
use App\Model\Core\Provider\OpenAIServiceInterface;

interface AgentInterface
{
    public function initialize(AgentRunner $agentRunner): AgentRunner;

    public function getOpenAIService(): OpenAIServiceInterface;

    public function getContextManager(): ContextInterface;

    public function getAgentName(): string;

    public function getAgentId(): string;

    public function getModel(): string;

    public function getTools(): array;

    public function getMcps(): array;

    public function getTemperature(): float;

    public function isParallelToolCalls(): bool;

    public function getToolChoice(): string;

    public function getTopP(): float;

    public function getMinP(): float;

    public function getTopk(): int;

    public function getRepeatPenalty(): float;
}
