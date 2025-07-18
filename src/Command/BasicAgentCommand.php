<?php

namespace App\Command;

use App\Interactive;
use App\Model\Agent\CodingAgentFactory;
use App\Model\Agent\CodingAgentInterface;
use App\Model\Core\Message\Context;
use App\Model\Core\Message\ContextWithIOTerminal;
use App\Model\IO\Terminal;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'basic-agent',
    description: 'A basic agent command that interacts with a coding agent on a one turn interaction',
)]
class BasicAgentCommand extends Command
{
    public function __construct(
        private readonly CodingAgentFactory $codingAgentFactory,
    ) {
        parent::__construct();
    }


    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this->addArgument('input', InputArgument::OPTIONAL, 'Text Input sent to LLM');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // --- Create a new context manager for each agent. ---
        $contexts = [
            'coding_agent' => new ContextWithIOTerminal(
                context: new Context(),
                terminal: new Terminal($io)
            ),
            'search_agent' => new ContextWithIOTerminal(
                context: new Context(),
                terminal: new Terminal($io),
                outputAssistant: false
            )
        ];


        // --- Initialize the coding agent with context managers ---
        $codingAgentRunner = $this->codingAgentFactory->create($contexts);

        $codingAgentRunner->sendUserMessage($input->getArgument('input'));


        return Command::SUCCESS;
    }
}
