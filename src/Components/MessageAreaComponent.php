<?php

namespace App\Components;

use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class MessageAreaComponent
{
    use DefaultActionTrait;

    #[LiveProp(url: true)]
    public int $discussionId = 0;

    public function __construct(
        private readonly ContextRepository $contextRepository,)
    {
    }

    public function getMessages(): array
    {
        return $this->contextRepository->findBy(['discussion' => $this->discussionId]);
    }


}