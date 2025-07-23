<?php

namespace App\Components;

use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class DiscussionAreaComponent
{
    use DefaultActionTrait;

    #[LiveProp(url: true)]
    public int $discussionId = 0;

    public function __construct(
        private readonly DiscussionRepository $discussionRepository)
    {
    }

    public function getDicussions(): array
    {
        return $this->discussionRepository->findAll();
    }

    #[LiveAction]
    public function deleteDiscussion(#[LiveArg] int $discussionId): void
    {
        $discussion = $this->discussionRepository->find($discussionId);

        if ($discussion) {
            $this->discussionRepository->remove($discussion, true);
        }
    }


}