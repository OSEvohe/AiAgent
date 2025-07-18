<?php

namespace App\Command;

use App\Interactive;
use App\Model\Agent\CodingAgentFactory;
use App\Model\IO\Terminal;
use App\Model\Agent\CodingAgentInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'basic-agent',
    description: 'Add a short description for your command',
)]
class BasicAgentCommand extends Command
{
    public function __construct(private readonly CodingAgentFactory $codingTeam)
    {
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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $interactive = new Interactive();

        return Command::SUCCESS;
        $prompt = $input->getArgument('input');

        if (!$prompt) {
            $io->note('No input provided. Please provide a prompt as an argument.');
            return Command::FAILURE;
        }

        try {
            $this->codingTeam->initialize(new Terminal($io));
            $this->codingTeam->sendMessage($prompt);
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * tools: [
     * new AgentTool(
     * output: new Terminal($output),
     * agentName: 'Jetbrains_Agent',
     * description: 'This is an AI agent that can perform coding tasks using Jetbrains tools. Use this agent to automate coding tasks. Ask for precise tasks, AgentRunner may ask you for more details if needed. Split Your tasks by calling this tool multiple times. Do not ask for the same task if an error is returned',
     * mcps: [new Jetbrains()],
     * systemMessage: 'You are a coding agent that can perform tasks using Jetbrains tools. You can use the tools provided by the MCPs to perform tasks. If you need more information, ask the user for details.'
     * )
     * ],
     * mcps: [],
     */
}
