<?php

namespace App\Factory;

use App\Entity\Discussion;
use App\Model\Core\Message\ContextInterface;
use App\Model\Core\Message\ContextPersisted;
use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;

readonly class ContextPersistedFactory
{
    public function __construct(
        private ContextRepository $contextRepository,
        private DiscussionRepository $discussionRepository
    ) {
    }

    public function create(ContextInterface $contextManager, string $agentId, string $discussionUid): ContextPersisted
    {
        return new ContextPersisted(
            contextManager: $contextManager,
            discussionRepository: $this->discussionRepository,
            contextRepository: $this->contextRepository,
            discussionUid: $discussionUid,
            agentId: $agentId
        );
    }
}