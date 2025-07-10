<?php

namespace App\Command;

use App\Model\Discussion;
use App\Model\IO\Terminal;
use App\Model\MCP\Jetbrains;
use App\Model\Tool\TaskAgentTool;
use App\Service\OpenAIService;
use App\Service\OpenAIServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'basic-chat-agent',
    description: 'Add a short description for your command',
)]
class BasicChatCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $discussion = new Discussion(
            openAIService: new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']),
            model: '',
            io: new Terminal($output),
            tools: [
                new TaskAgentTool(
                    output: new Terminal($output),
                    agentName: 'Jetbrains_Agent',
                    description: 'This is an AI agent that can perform coding tasks using Jetbrains tools. Use this agent to automate coding tasks. Ask for precise tasks, Agent may ask you for more details if needed. Split Your tasks by calling this tool multiple times. Do not ask for the same task if an error is returned',
                    mcps: [new Jetbrains()],
                    systemMessage: 'You are a coding agent that can perform tasks using Jetbrains tools. You can use the tools provided by the MCPs to perform tasks. If you need more information, ask the user for details.'
                )
            ],
            mcps: [],
        );

        while (true) {
            $input = $io->ask('You:');
            if ($input === '/exit' || $input === '/quit') {
                $io->success('Exiting the chat.');
                break;
            }
            if (empty($input)) {
                continue;
            }
            $preparedInput = $discussion->preparePrompt($input);
            $io->writeln($discussion->sendUserMessage($preparedInput));
        }

        return Command::SUCCESS;
    }
}
