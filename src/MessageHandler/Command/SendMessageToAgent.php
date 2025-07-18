<?php

namespace App\MessageHandler\Command;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class SendMessageToAgent
{
    public function __construct(
        public string $discussionId,
        public string $message
    )
    {
    }
}