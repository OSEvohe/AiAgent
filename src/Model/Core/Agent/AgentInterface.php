<?php

namespace App\Model\Core\Agent;

use App\Model\Core\Message\ContextInterface;

interface AgentInterface
{
    public function initialize(ContextInterface $contextManager): void;

    public function sendMessage(string $message);
}
