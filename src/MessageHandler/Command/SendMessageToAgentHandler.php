<?php

namespace App\MessageHandler\Command;

use App\Factory\ContextPersistedFactory;
use App\Model\Agent\SimpleAgentFactory;
use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Message\Context;
use App\Repository\DiscussionRepository;
use App\Service\SimpleAgentRunner;
use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendMessageToAgentHandler
{
    private ?AgentRunner $codingAgentRunner = null;

    /**
     * @throws Exception
     */
    public function __construct(
        private readonly SimpleAgentRunner $simpleAgentRunner,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(SendMessageToAgent $command): void
    {
       $this->simpleAgentRunner->run($command);
    }

}