<?php

namespace App\Command;

use App\Model\Agent\CodingAgent;
use App\Model\Agent\OrchestrateAgent;
use App\Model\Discussion;
use App\Model\IO\Terminal;
use App\Model\MCP\Jetbrains;
use App\Model\Tool\AgentTool;
use App\Service\OpenAIService;
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
    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::OPTIONAL, 'Text Input sent to LLM');
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
        $io = new SymfonyStyle($input, $output);
        $prompt = $input->getArgument('input');

        if (!$prompt) {
            $io->note('No input provided. Please provide a prompt as an argument.');
            return Command::FAILURE;
        }

        $aiService = new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']);

        $discussion = new Discussion(
            openAIService: $aiService,
            model: '',
            agent: new OrchestrateAgent([new CodingAgent()], new Terminal($output)),
            io: new Terminal($output),
        );

        $preparedPrompt = $discussion->preparePrompt($prompt);

        $io->writeln($discussion->sendUserMessage($preparedPrompt));

        return Command::SUCCESS;
    }

    /**
     * tools: [
     * new AgentTool(
     * output: new Terminal($output),
     * agentName: 'Jetbrains_Agent',
     * description: 'This is an AI agent that can perform coding tasks using Jetbrains tools. Use this agent to automate coding tasks. Ask for precise tasks, Agent may ask you for more details if needed. Split Your tasks by calling this tool multiple times. Do not ask for the same task if an error is returned',
     * mcps: [new Jetbrains()],
     * systemMessage: 'You are a coding agent that can perform tasks using Jetbrains tools. You can use the tools provided by the MCPs to perform tasks. If you need more information, ask the user for details.'
     * )
     * ],
     * mcps: [],
     */
}
