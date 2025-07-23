<?php

namespace App\MessageHandler\Command;

use App\Factory\ContextPersistedFactory;
use App\Model\Agent\SimpleAgentFactory;
use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Message\Context;
use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendMessageToAgentHandler
{
    private ?AgentRunner $codingAgentRunner = null;
    private array $contexts = [];

    /**
     * @throws Exception
     */
    public function __construct(
        private readonly ContextPersistedFactory $contextPersistedFactory,
        private readonly SimpleAgentFactory $simpleAgentFactory,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(SendMessageToAgent $command): void
    {
        if ($this->codingAgentRunner === null) {
            // --- Create a new context manager for each agent. ---
            $this->contexts = [
                'simple_agent' => $this->contextPersistedFactory->create(
                    context: new Context(),
                    agentId: 'simple_agent',
                    discussionId: $command->discussionId,
                )
            ];
            $this->codingAgentRunner = $this->simpleAgentFactory->create($this->contexts);
        }

        $this->contexts['simple_agent']->setDiscussionId($command->discussionId);
        $this->codingAgentRunner->sendUserMessage($command->message);
    }

}