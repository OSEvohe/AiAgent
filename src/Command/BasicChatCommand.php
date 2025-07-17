<?php

namespace App\Command;

use App\Factory\ContextManagerPersistedFactory;
use App\Model\Core\Message\ContextManager;
use App\Model\Core\Message\ContextManagerWithIOTerminal;
use App\Model\IO\Terminal;
use App\Model\Team\CodingTeam;
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
    public function __construct(private readonly CodingTeam $codingTeam, private readonly ContextManagerPersistedFactory $factory)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // exemple using Decorator pattern to add IO and Persisting capabilities to the context manager
        $contextManager = new ContextManager();
        $contextManagerPersisted = $this->factory->create(contextManager: $contextManager, discussionId: '6878131721aaf'); // Example discussion ID
        $contextManagerPersistedWithIO = new ContextManagerWithIOTerminal(contextManager: $contextManagerPersisted, terminal: new Terminal($io));

        $this->codingTeam->initialize(contextManager: $contextManagerPersistedWithIO);

        dump($contextManagerPersistedWithIO->getContext('orchestrator_agent'));

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
}
