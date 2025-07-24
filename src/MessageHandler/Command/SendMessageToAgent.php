<?php

namespace App\MessageHandler\Command;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * @see SendMessageToAgentHandler
 */
#[AsMessage('async')]
readonly class SendMessageToAgent
{
    public function __construct(
        public string $discussionUid,
        public string $message,
        public string $messageUid = ''
    )
    {
    }
}