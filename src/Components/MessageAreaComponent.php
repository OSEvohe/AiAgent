<?php

namespace App\Components;

use App\Repository\ContextRepository;
use App\Repository\DiscussionRepository;
use Michelf\Markdown;
use Michelf\MarkdownExtra;
use Parsedown;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class MessageAreaComponent
{
    use DefaultActionTrait;

    #[LiveProp(url: true)]
    public string $discussionUid = '';

    public function __construct(
        private readonly ContextRepository $contextRepository,
        private readonly DiscussionRepository $discussionRepository,
    )
    {
    }

    public function getMessages(): array
    {
        $discussion = $this->discussionRepository->findByUid($this->discussionUid);

        return $this->contextRepository->findBy(['discussion' => $discussion], ['createdAt' => 'ASC']);
    }

    public function getMarkdown(string $text): string
    {
        $parsedown = new Parsedown();
        return $parsedown->text($text);
    }


}