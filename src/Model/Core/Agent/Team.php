<?php

namespace App\Model\Core\Agent;

use App\Model\Core\Message\ContextManagerInterface;

interface Team
{
    public function initialize(ContextManagerInterface $contextManager): void;

    public function sendMessage(string $message);
}
