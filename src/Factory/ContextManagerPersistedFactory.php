<?php

namespace App\Factory;

use App\Model\Core\Message\ContextManagerInterface;
use App\Model\Core\Message\ContextManagerPersisted;
use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;

class ContextManagerPersistedFactory
{
    public function __construct(
        private readonly DiscussionRepository $discussionRepository,
        private readonly ContextRepository $contextRepository
    ) {
    }

    public function create(
        ContextManagerInterface $contextManager,
        ?string $discussionId = null
    ): ContextManagerPersisted {
        if ($discussionId){
            $discussion = $this->discussionRepository->findOneBy(['uid' => $discussionId]);
        } else {
            $discussion = null;
        }

        return new ContextManagerPersisted(
            $contextManager,
            $this->discussionRepository,
            $this->contextRepository,
            $discussion
        );
    }
}