<?php

namespace App\Command;

use App\Entity\Discussion;
use App\Factory\ContextPersistedFactory;
use App\Model\Agent\CodingAgentFactory;
use App\Model\Agent\CodingAgentInterface;
use App\Model\Core\Message\Context;
use App\Model\Core\Message\ContextManager;
use App\Model\Core\Message\ContextWithIOTerminal;
use App\Model\IO\Terminal;
use App\Repository\DiscussionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'basic-chat-agent',
    description: 'Add a short description for your command',
)]
class BasicChatCommand extends Command
{
    public function __construct(
        private readonly CodingAgentFactory $codingAgentFactory,
        private readonly ContextPersistedFactory $contextPersistedFactory,
        private readonly DiscussionRepository $discussionRepository
    ) {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        // --- Select or create a discussion ---
        $discussionId = $this->selectDiscussion($io);

        if ($discussionId === 0) {
            $discussion = new Discussion();
            $discussion->setTitle($io->ask('Enter a Discussion Title or hit Enter', 'Discussion ' . date('Y-m-d H:i:s')));
            $discussion->setUid(uniqid());

            // Persist the new discussion but do not flush yet
            $this->discussionRepository->persist($discussion);
        } else {
            $discussion = $this->discussionRepository->find($discussionId);
            if (!$discussion) {
                $io->error('Discussion not found.');
                return Command::FAILURE;
            }
        }


        // --- Create a new context manager for each agent. ---
        $contexts = [
            'coding_agent' => $this->contextPersistedFactory->create(
                context: new ContextWithIOTerminal(
                    context: new Context(),
                    terminal: new Terminal($io)
                ),
                agentId: 'coding_agent',
                discussion: $discussion
            ),
            'search_agent' => $this->contextPersistedFactory->create(
                context: new ContextWithIOTerminal(
                    context: new Context(),
                    terminal: new Terminal($io),
                    outputAssistant: false
                ),
                agentId: 'search_agent',
                discussion: $discussion
            )
        ];


        // --- Initialize the coding agent with context managers ---
        $codingAgentRunner = $this->codingAgentFactory->create($contexts);


        // --- Start the chat loop ---
        while (true) {
            $prompt = $io->ask('You:');
            if ($prompt === '/exit' || $prompt === '/quit') {
                $io->success('Exiting the chat.');
                break;
            }
            if (empty($prompt)) {
                continue;
            }

            $codingAgentRunner->sendUserMessage($prompt);
        }

        return Command::SUCCESS;
    }

    protected function selectDiscussion(SymfonyStyle $io): ?int
    {
        $discussions = $this->discussionRepository->findAll();

        if (empty($discussions)) {
            $io->writeln('No discussions found.');
            return null;
        }

        $io->writeln('Available discussions:');
        foreach ($discussions as $discussion) {
            $io->writeln(sprintf('Discussion ID: %s, Title: %s, Uid: %s', $discussion->getId(), $discussion->getTitle(), $discussion->getUid()));
        }
        $io->writeln('0 : Create a new discussion');

        $discussionId = $io->ask('Please enter the discussion ID you want to use:');

        return is_numeric($discussionId) ? (int)$discussionId : throw new \InvalidArgumentException('Invalid discussion ID provided.');
    }
}
