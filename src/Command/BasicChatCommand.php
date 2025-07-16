<?php

namespace App\Command;

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
    public function __construct(private readonly CodingTeam $codingTeam)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->codingTeam->initialize(
            contextManager: new ContextManagerWithIOTerminal(new ContextManager(), new Terminal($io))
        );

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
