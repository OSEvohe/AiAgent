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

    public function setContext(array $data): ContextInterface
    {
        return $this->contextManager->setContext($data);
    }

    public function getSystemMessage(): ?SystemMessage
    {
        return $this->contextManager->getSystemMessage();
    }

    public function setSystemMessage(SystemMessage $systemMessage): ContextInterface
    {
        return $this->contextManager->setSystemMessage($systemMessage);
    }
}