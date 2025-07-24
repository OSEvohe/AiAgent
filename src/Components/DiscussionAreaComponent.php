<?php

namespace App\Components;

use App\Entity\Discussion;
use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class DiscussionAreaComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(url: true)]
    public string $discussionUid = '';

    public function __construct(
        private readonly DiscussionRepository $discussionRepository)
    {
    }

    public function getDiscussions(): array
    {
        return $this->discussionRepository->findAll();
    }

    #[LiveAction]
    public function deleteDiscussion(#[LiveArg] string $discussionUid): void
    {
        $discussion = $this->discussionRepository->findByUid($discussionUid);

        if ($discussion) {
            $this->discussionRepository->remove($discussion);
        }
    }


    #[LiveAction]
    public function createDiscussion(): RedirectResponse
    {
        $discussion = new Discussion();
        $discussion->setTitle('Discussion'. Date('Y-m-d H:i:s'))
            ->setUid(uniqid());

        $this->discussionRepository->save($discussion);

        return $this->redirectToRoute('app_chat_index', ['discussionUid' => $discussion->getUid()]);
    }


}