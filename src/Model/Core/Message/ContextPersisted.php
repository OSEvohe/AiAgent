<?php

namespace App\Model\Core\Message;

use App\Entity\Context as ContextEntity;
use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;

class ContextPersisted implements ContextInterface
{
    public function __construct(
        private readonly ContextInterface $contextManager,
        private readonly DiscussionRepository $discussionRepository,
        private readonly ContextRepository $contextRepository,
        private string $discussionUid,
        private string $agentId
    ) {
    }

    public function addEntry(array $entry, string $entryUid = ''): string
    {
        $discussion = $this->discussionRepository->findByUid($this->discussionUid);

        if ($discussion) {
            $entries = $this->contextRepository->findBy(['discussion' => $discussion]);
            $this->setContext((array_map(fn($entry) => $entry->getData(), $entries)));
        } else {
            throw new \InvalidArgumentException('Discussion not found for ID: ' . $this->discussionUid);
        }

        $uniquid = $this->contextManager->addEntry($entry, $entryUid);

        $newContextEntity = new ContextEntity();
        $newContextEntity->setAgentId($this->agentId)
            ->setUid($uniquid)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDiscussion($discussion)
            ->setRole($entry['role'])
            ->setData($entry);

        $this->contextRepository->save($newContextEntity);

        return $uniquid;
    }

    public function updateEntry(array $entry, string $entryUid): string
    {
        $contextEntity = $this->contextRepository->findByUid($entryUid);

        if ($contextEntity) {
            $contextEntity->setData($entry);
            $this->contextRepository->save($contextEntity);

            // Update the context manager with the new data
            $discussion = $this->discussionRepository->findByUid($contextEntity->getDiscussion()->getUid());
            if ($discussion) {
                $entries = $this->contextRepository->findBy(['discussion' => $discussion]);
                $this->setContext((array_map(fn($entry) => $entry->getData(), $entries)));
            }

            return $entryUid;
        } else {
            throw new \InvalidArgumentException('Entry not found for UID: ' . $entryUid);
        }
    }

    public function getContext(): array
    {
        return $this->contextManager->getContext();
    }

    public function getEntry(int $key): ?array
    {
        return $this->contextManager->getEntry($key);
    }

    public function toArray(): array
    {
        return $this->contextManager->toArray();
    }

    public function setContext(array $data): ContextInterface
    {
        return $this->contextManager->setContext($data);
    }

    public function getSystemMessage(): ?SystemMessage
    {
        return $this->contextManager->getSystemMessage();
    }

    public function setSystemMessage(SystemMessage $systemMessage): self
    {
        $this->contextManager->setSystemMessage($systemMessage);

        return $this;
    }

    public function getDiscussionUid(): string
    {
        return $this->discussionUid;
    }

    public function setDiscussionId(string $discussionUid): self
    {
        $this->discussionUid = $discussionUid;
        return $this;
    }
}