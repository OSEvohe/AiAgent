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
        //private readonly DiscussionRepository $discussionRepository,
        private readonly ContextRepository $contextRepository,
        private ?Discussion $discussion,
        private string $agentId
    ) {
        /*if ($discussion === null) {
            $this->discussion = new Discussion();
            $this->discussion->setUid(uniqid());
            $this->discussion->setTitle('Discussion ' . date('Y-m-d H:i:s'));
            $this->discussionRepository->save($this->discussion);
        }*/
    }

    public function addEntry(array $entry): self
    {
        $this->contextManager->addEntry($entry);

        $newContextEntity = new ContextEntity();
        $newContextEntity->setAgentId($this->agentId)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDiscussion($this->discussion)
            ->setRole($entry['role'])
            ->setData($entry);

        $this->contextRepository->save($newContextEntity);
        dump('ContextPersisted::addEntry', $entry);


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
}