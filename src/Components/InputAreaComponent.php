<?php

namespace App\Components;

use App\MessageHandler\Command\SendMessageToAgent;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class InputAreaComponent
{
    use DefaultActionTrait;

    #[LiveProp(url: true)]
    public string $discussionUid = '';

    #[LiveProp(writable: true)]
    public string $message = '';


    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    #[LiveAction]
    public function sendMessage(): void
    {
        // Create a command to send the message to the agent
        $command = new SendMessageToAgent(
            discussionUid: $this->discussionUid,
            message: $this->message
        );

        // Dispatch the command using the message bus
        $this->messageBus->dispatch($command);

        $this->message = ''; // Clear the message input after sending
    }
}