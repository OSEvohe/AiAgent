<?php

namespace App\Command;

use App\Model\IO\Terminal;
use App\Model\Agent\CodingAgentInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'agent-ai-command',
    description: 'Add a short description for your command',
)]
class AgentAiCommand extends Command
{
    public function __construct(private readonly CodingAgentInterface $codingTeam)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->codingTeam->initialize();

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
