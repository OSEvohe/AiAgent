<?php

namespace App\Model\Core\Message;

use App\Entity\Context as ContextEntity;
use App\Entity\Discussion;
use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;

class ContextManagerPersisted implements ContextManagerInterface
{
    public function __construct(
        private readonly ContextManagerInterface $contextManager,
        private readonly DiscussionRepository $discussionRepository,
        private readonly ContextRepository $contextRepository,
        private ?Discussion $discussion = null
    ) {
        if ($discussion === null) {
            $this->discussion = new Discussion();
            $this->discussion->setUid(uniqid());
            $this->discussion->setTitle('Discussion ' . date('Y-m-d H:i:s'));
            $this->discussionRepository->save($this->discussion);
        }
    }

    public function addEntry(string $agentId, array $entry): void
    {
        $this->contextManager->addEntry($agentId, $entry);

        $newContextEntity = new ContextEntity();
        $newContextEntity->setAgentId($agentId)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDiscussion($this->discussion)
            ->setRole($entry['role'])
            ->setData($entry);

        $this->contextRepository->save($newContextEntity);
    }

    public function getContexts(): array
    {
        return $this->contextManager->getContexts();
    }

    public function getContext(string $agentId): ?Context
    {
        return $this->contextManager->getContext($agentId);
    }

    public function getEntry(string $agentId, int $key): ?array
    {
        return $this->contextManager->getEntry($agentId, $key);
    }

    public function addContext(Context $context): string
    {
        return $this->contextManager->addContext($context);
    }

    public function toArray(): array
    {
        return $this->contextManager->toArray();
    }

    public function loadDiscussion(): self
    {
        $contextEntries = $this->contextRepository->findBy(['discussion' => $this->discussion]);

        $contexts = [];

        // Initialize contexts from persisted entries splitting by agent ID
        foreach ($contextEntries as $entry){
           $contexts[$entry->getAgentId()][] = $entry->getData();
        }

        // foreach agent Id, create a Context object and fill it with the entries
        foreach ($contexts as $agentId => $data) {
            $context = new Context($agentId, $data);
            $this->contextManager->addContext($context);
        }


        return $this;
    }
}