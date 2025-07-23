<?php

namespace App\Model\Core\Message;

use App\Entity\Context as ContextEntity;
use App\Entity\Discussion;
use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;

class ContextPersisted implements ContextInterface
{
    public function __construct(
        private readonly ContextInterface $contextManager,
        private readonly DiscussionRepository $discussionRepository,
        private readonly ContextRepository $contextRepository,
        private int $discussionId,
        private string $agentId
    ) {
    }

    public function addEntry(array $entry): self
    {

        $discussion = $this->discussionRepository->find($this->discussionId);

        if ($discussion) {
            $entries = $this->contextRepository->findBy(['discussion' => $discussion]);
            $this->setContext((array_map(fn($entry) => $entry->getData(), $entries)));
        } else {
            throw new \InvalidArgumentException('Discussion not found for ID: ' . $this->discussionId);
        }


        $this->contextManager->addEntry($entry);

        $newContextEntity = new ContextEntity();
        $newContextEntity->setAgentId($this->agentId)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDiscussion($discussion)
            ->setRole($entry['role'])
            ->setData($entry);

        $this->contextRepository->save($newContextEntity);

        return $this;
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

    public function getDiscussionId(): int
    {
        return $this->discussionId;
    }

    public function setDiscussionId(int $discussionId): self
    {
        $this->discussionId = $discussionId;
        return $this;
    }
}