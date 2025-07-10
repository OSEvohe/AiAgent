<?php

namespace App\Command;

use App\Model\Agent\CodingAgent;
use App\Model\Agent\OrchestrateAgent;
use App\Model\Discussion;
use App\Model\IO\Terminal;
use App\Model\MCP\Jetbrains;
use App\Model\Tool\AgentTool;
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

        $openAIService = new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']);

        $discussion = new Discussion(
            openAIService: $openAIService,
            model: '',
            agent: new OrchestrateAgent(agents: [new CodingAgent()], io: new Terminal($output)),
            io: new Terminal($output),

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
