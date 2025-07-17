<?php

namespace App\Command;

use App\Entity\Discussion;
use App\Factory\ContextPersistedFactory;
use App\Model\Core\Message\Context;
use App\Model\Core\Message\ContextManager;
use App\Model\Core\Message\ContextWithIOTerminal;
use App\Model\IO\Terminal;
use App\Model\Agent\CodingAgentInterface;
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
        private readonly CodingAgentInterface $codingTeam,
        private readonly ContextPersistedFactory $factory,
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

        $discussionId = $this->selectDiscussion($io);

        if ($discussionId === 0){
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

        $context = new Context();
        $contextPersistedWithIO = new ContextWithIOTerminal(context: $context, terminal: new Terminal($io));
        $contextManagerPersisted = $this->factory->create(context: $contextPersistedWithIO, agentId: 'orchestrator_agent', discussion: $discussion); // Example discussion ID

        $this->codingTeam->initialize(contextManager: $contextManagerPersistedWithIO);

        // dump($contextManagerPersistedWithIO->getContext('orchestrator_agent'));

        while (true) {
            $prompt = $io->ask('You:');
            if ($prompt === '/exit' || $prompt === '/quit') {
                $io->success('Exiting the chat.');
                break;
            }
            if (empty($prompt)) {
                continue;
            }

            $this->codingTeam->sendMessage($prompt);
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
