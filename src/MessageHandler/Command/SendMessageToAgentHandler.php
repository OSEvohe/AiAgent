<?php

namespace App\MessageHandler\Command;

use App\Factory\ContextPersistedFactory;
use App\Model\Agent\CodingAgentFactory;
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
        private readonly CodingAgentFactory $factory,
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
                'coding_agent' => $this->contextPersistedFactory->create(
                    context: new Context(),
                    agentId: 'coding_agent',
                    discussionId: $command->discussionId,
                ),
                'search_agent' => $this->contextPersistedFactory->create(
                    context: new Context(),
                    agentId: 'search_agent',
                    discussionId: $command->discussionId,
                )
            ];
            $this->codingAgentRunner = $this->factory->create($this->contexts);
        }
        $this->contexts['coding_agent']->setDiscussionId($command->discussionId);
        $this->contexts['search_agent']->setDiscussionId($command->discussionId);
        $this->codingAgentRunner->sendUserMessage($command->message);
    }

}