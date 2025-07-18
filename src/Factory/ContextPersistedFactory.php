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
        private ContextRepository $contextRepository
    ) {
    }

    public function create(ContextInterface $context, string $agentId, Discussion $discussion): ContextPersisted
    {
        $context = new ContextPersisted(
            contextManager: $context,
            contextRepository: $this->contextRepository,
            discussion: $discussion,
            agentId: $agentId
        );

        // If the discussion is persisted, we can load the existing context entries
        if ($discussion->getId() !== null) {
            $entries = $this->contextRepository->findBy(['discussion' => $discussion]);
            $context->setContext(array_map(fn($entry) => $entry->getData(), $entries));
        }

        return $context;
    }
}