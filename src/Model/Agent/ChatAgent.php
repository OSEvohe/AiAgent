<?php

namespace App\Model\Agent;

use App\Model\Agent;

class ChatAgent
{
    public function getName(): string
    {
        return 'ChatAgent';
    }

    public function getDescription(): string
    {
        return 'An agent that handles chat interactions, processing user messages and generating responses';
    }

    public function getTools(): array
    {
        return [];
    }

    public function getMcps(): array
    {
        return [];
    }
}
