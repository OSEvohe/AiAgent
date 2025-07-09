<?php

namespace App\Command;

use App\Model\Discussion;
use App\Model\IO\Terminal;
use App\Model\MCP\Jetbrains;
use App\Model\Tool\FileSystemTool;
use App\Model\Tool\WeatherTool;
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
    public function __construct(
        private readonly OpenAIServiceInterface $openAIService,
        //private readonly ToolServiceInterface $toolService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('llm-url', null, InputOption::VALUE_OPTIONAL, 'URL of the LLM API endpoint');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check if a custom LLM URL was provided via command line option
        $llmUrlOption = $input->getOption('llm-url');
        if ($llmUrlOption) {
            $this->openAIService->setBaseUri($llmUrlOption);
        }

        $discussion = new Discussion(
            openAIService: $this->openAIService,
            model: '',
            io: new Terminal($output),
            tools: [],
            mcps: [new Jetbrains()],
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
            $discussion->sendUserMessage($input);
        }


        return Command::SUCCESS;
    }
}
