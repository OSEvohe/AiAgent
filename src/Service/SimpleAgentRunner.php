<?php

namespace App\Service;

use App\Factory\ContextPersistedFactory;
use App\MessageHandler\Command\SendMessageToAgent;
use App\Model\Agent\SimpleAgentFactory;
use App\Model\Core\Agent\AgentRunner;
use App\Model\Core\Message\Context;
use App\Repository\DiscussionRepository;
use Exception;

class SimpleAgentRunner
{
    private ?AgentRunner $codingAgentRunner = null;


    public function __construct(
        private readonly ContextPersistedFactory $contextPersistedFactory,
        private readonly SimpleAgentFactory $simpleAgentFactory,
        private readonly DiscussionRepository $discussionRepository,
    ) {
        echo('SendMessageToAgentHandler initialized');
    }

    /**
     * @throws Exception
     */
    public function run(SendMessageToAgent $command): void
    {
        if ($this->codingAgentRunner === null) {
            $discussion = $this->discussionRepository->find($command->discussionId);

            if (is_null($discussion)) {
                throw new Exception('Discussion not found');
            }

            // --- Create a new context manager for each agent. ---
            $contexts = [
                'simple_agent' => $this->contextPersistedFactory->create(
                    context: new Context(),
                    agentId: 'simple_agent',
                    discussion: $discussion,
                )
            ];
            $this->codingAgentRunner = $this->simpleAgentFactory->create($contexts);
        }

        $this->codingAgentRunner->sendUserMessage($command->message);

    }
}